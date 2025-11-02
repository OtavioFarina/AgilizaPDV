<?php
require_once "conexao.php";

header('Content-Type: application/json');

// ... (verificação de caixa aberto continua a mesma) ...
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

    // Inserção da venda (continua igual)
    $sqlVenda = "INSERT INTO vendas (data_hora, valor_total, id_forma_pagamento, status) VALUES (NOW(), :total, :id_forma_pagamento, 'finalizada')";
    // ... (lógica para pegar o id_forma_pagamento continua a mesma) ...
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

        // --- MUDANÇA PRINCIPAL AQUI ---
        // Agora, além do nome e sabor, também buscamos a CATEGORIA do produto
        $stmtProdutoInfo = $conn->prepare("
            SELECT p.nome, p.sabor, c.nome_categoria 
            FROM produto p 
            JOIN categoria c ON p.id_categoria = c.id_categoria 
            WHERE p.id_produto = :id_produto
        ");
        $stmtProdutoInfo->execute([':id_produto' => $id_produto]);
        $produto_info = $stmtProdutoInfo->fetch(PDO::FETCH_ASSOC);

        if ($produto_info) {
            // Agora inserimos o registro de SAÍDA com a categoria correta
            $stmtSaidaEstoque = $conn->prepare(
                "INSERT INTO estoque (movimentacao, produto, sabor, tipo, estoque_atual, data) 
                 VALUES ('Saída', :produto, :sabor, :tipo, :quantidade, NOW())"
            );
            $stmtSaidaEstoque->execute([
                ':produto' => $produto_info['nome'],
                ':sabor' => $produto_info['sabor'],
                ':tipo' => $produto_info['nome_categoria'], // <<< A CORREÇÃO
                ':quantidade' => $quantidade_vendida
            ]);
        }

        // Inserção em 'saida_produtos' (continua igual)
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
    echo json_encode(['sucesso' => true, 'mensagem' => 'Venda registrada com sucesso!']);

} catch (Exception $e) {
    $conn->rollBack();
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => "Erro: " . $e->getMessage()]);
}
?>