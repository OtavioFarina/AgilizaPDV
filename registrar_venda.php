<?php
require_once "conexao.php";

// 🚨 CONFIGURAÇÃO DO WEBHOOK 🚨
// MUDAR: Substitua esta URL pelo Webhook Address do seu nó n8n
$WEBHOOK_URL = 'https://webhook.automaticbot.pro/webhook/94354dcd-32b9-4e30-9a88-e9b6083746eb';
$ESTOQUE_MINIMO = 5; // Gatilho: <= 5 unidades

header('Content-Type: application/json');

// --- VERIFICAÇÃO DE CAIXA ABERTO ---
$sqlStatus = "SELECT status FROM caixa_status ORDER BY id_status DESC LIMIT 1";
$stmtStatus = $conn->prepare($sqlStatus);
$stmtStatus->execute();
$caixa_status = $stmtStatus->fetchColumn() ?: 'fechado';
if ($caixa_status !== 'aberto') {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Caixa fechado! Não é possível realizar vendas.']);
    exit;
}

$dados = json_decode(file_get_contents("php://input"), true);
if (!$dados || empty($dados['itens'])) {
    http_response_code(400);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados da venda inválidos!']);
    exit;
}

try {
    $conn->beginTransaction();

    // Inserção da venda (Sem alteração)
    $sqlVenda = "INSERT INTO vendas (data_hora, valor_total, id_forma_pagamento, status) VALUES (NOW(), :total, :id_forma_pagamento, 'finalizada')";

    $forma_pagamento_nome = trim($dados['forma_pagamento']);
    $stmtPag = $conn->prepare("SELECT id_forma_pagamento FROM forma_pagamento WHERE nome_pagamento = :nome");
    $stmtPag->execute([':nome' => $forma_pagamento_nome]);
    $pag_id = $stmtPag->fetchColumn();

    $stmtVenda = $conn->prepare($sqlVenda);
    $stmtVenda->execute([
        ":total" => (float) $dados['total'],
        ":id_forma_pagamento" => $pag_id
    ]);
    $venda_id = $conn->lastInsertId();

    foreach ($dados['itens'] as $item) {
        $id_produto = (int) $item['id'];
        $quantidade_vendida = (int) $item['qtd'];

        if ($quantidade_vendida <= 0)
            continue;

        // --- BUSCA INFORMAÇÕES DO PRODUTO (Sem alteração) ---
        $stmtProdutoInfo = $conn->prepare("
            SELECT p.nome, p.sabor, c.nome_categoria 
            FROM produto p 
            JOIN categoria c ON p.id_categoria = c.id_categoria 
            WHERE p.id_produto = :id_produto
        ");
        $stmtProdutoInfo->execute([':id_produto' => $id_produto]);
        $produto_info = $stmtProdutoInfo->fetch(PDO::FETCH_ASSOC);

        if ($produto_info) {
            $produto_nome = $produto_info['nome'];
            $produto_sabor = $produto_info['sabor'];
            $produto_tipo = $produto_info['nome_categoria'];

            // --- REGISTRA A SAÍDA NO ESTOQUE ---
            // Verifica o saldo atual
            $stmtSaldo = $conn->prepare("
                SELECT COALESCE(
                    SUM(CASE 
                        WHEN movimentacao = 'Entrada' THEN estoque_atual 
                        WHEN movimentacao = 'Saída' THEN -estoque_atual 
                    END), 0
                ) as saldo
                FROM estoque 
                WHERE produto = :produto 
                AND sabor = :sabor 
                AND tipo = :tipo
            ");

            $stmtSaldo->execute([
                ':produto' => $produto_nome,
                ':sabor' => $produto_sabor,
                ':tipo' => $produto_tipo
            ]);

            $saldo_atual = (int) $stmtSaldo->fetchColumn();

            // Verifica se há estoque suficiente
            if ($saldo_atual < $quantidade_vendida) {
                throw new Exception("Estoque insuficiente para o produto $produto_nome - $produto_sabor (Disponível: $saldo_atual, Solicitado: $quantidade_vendida)");
            }

            // Registra a saída no estoque com a quantidade NEGATIVA
            $stmtSaidaEstoque = $conn->prepare("
                INSERT INTO estoque (movimentacao, produto, sabor, tipo, estoque_atual, data) 
                VALUES ('Saída', :produto, :sabor, :tipo, :quantidade, NOW())
            ");

            $stmtSaidaEstoque->execute([
                ':produto' => $produto_nome,
                ':sabor' => $produto_sabor,
                ':tipo' => $produto_tipo,
                ':quantidade' => $quantidade_vendida
            ]);

            // --------------------------------------------------------------------------------
            // 🌟 CÓDIGO INSERIDO PARA VERIFICAR ESTOQUE E DISPARAR O WEBHOOK N8N 
            // --------------------------------------------------------------------------------

            // 1. Recalcula o estoque atual do item após a movimentação de SAÍDA/VENDA.
            $stmtEstoque = $conn->prepare("
                SELECT 
                    SUM(CASE WHEN movimentacao = 'Entrada' THEN estoque_atual ELSE 0 END) -
                    SUM(CASE WHEN movimentacao = 'Saída' THEN estoque_atual ELSE 0 END) AS estoque_final
                FROM estoque
                WHERE produto = ? AND sabor = ? AND tipo = ?
                GROUP BY produto, sabor, tipo
            ");
            $stmtEstoque->execute([$produto_nome, $produto_sabor, $produto_tipo]);
            $resultado = $stmtEstoque->fetch(PDO::FETCH_ASSOC);
            $estoque_final = $resultado ? (int) $resultado['estoque_final'] : 0;

            // 2. Verifica a condição de "Estoque Baixo" (<= 5)
            if ($estoque_final <= $ESTOQUE_MINIMO) {
                // 3. Prepara o payload (JSON) para o Webhook
                $payload = json_encode([
                    'produto' => $produto_nome,
                    'sabor' => $produto_sabor,
                    'categoria' => $produto_tipo,
                    'estoque_atual' => $estoque_final,
                    'data_alerta' => date('Y-m-d H:i:s')
                ]);

                // 4. Dispara a requisição HTTP (POST) para o n8n (Não bloqueante)
                $ch = curl_init($WEBHOOK_URL);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($payload)
                ]);

                // CRÍTICO: Configurações para garantir que a requisição não bloqueie o checkout
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); // Timeout de conexão
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Apenas se houver problemas de certificado
                curl_exec($ch);
                curl_close($ch);
            }
            // --------------------------------------------------------------------------------

        }

        // Inserção em 'saida_produtos' (Sem alteração)
        $sqlItem = "INSERT INTO saida_produtos (venda_id, id_produto, quantidade, data, processado) 
                    VALUES (:venda_id, :id_produto, :quantidade, NOW(), 1)";
        $stmtItem = $conn->prepare($sqlItem);
        $stmtItem->execute([
            ':venda_id' => $venda_id,
            ':id_produto' => $id_produto,
            ':quantidade' => $quantidade_vendida
        ]);
    }

    $conn->commit();
    // ADICIONADO: Retornamos o ID da venda para poder imprimir
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Venda registrada com sucesso!',
        'id_venda' => $venda_id
    ]);

} catch (Exception $e) {

} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => "Erro: " . $e->getMessage()]);
}
?>