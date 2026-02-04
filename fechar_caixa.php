<?php
require_once "config/conexao.php";
session_start();

if (!isset($_SESSION['nome_usuario'])) {
    header("Location: acesso.php");
    exit;
}

$operador = $_SESSION['nome_usuario'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // 1. Busca dados da ABERTURA (Data e Valor Inicial)
        $stmt_abertura = $conn->query("SELECT data_status, valor_inicial FROM caixa_status WHERE status = 'aberto' ORDER BY id_status DESC LIMIT 1");
        $dados_abertura = $stmt_abertura->fetch(PDO::FETCH_ASSOC);

        if (!$dados_abertura) {
            throw new Exception("Caixa já está fechado ou erro de status.");
        }

        $data_abertura = $dados_abertura['data_status'];
        $valor_abertura = $dados_abertura['valor_inicial']; // Aqui está o valor que pegamos na abertura

        // 2. Calcula os Totais das Vendas
        $sql_totais = "SELECT 
                           fp.nome_pagamento, 
                           SUM(v.valor_total) as total 
                       FROM vendas AS v
                       JOIN forma_pagamento AS fp ON v.id_forma_pagamento = fp.id_forma_pagamento
                       WHERE v.status = 'finalizada' AND v.fechamento_caixa_id IS NULL AND v.data_hora >= :data_abertura
                       GROUP BY fp.nome_pagamento";

        $stmt_totais = $conn->prepare($sql_totais);
        $stmt_totais->execute([':data_abertura' => $data_abertura]);

        $total_dinheiro = 0;
        $total_cartaoC = 0;
        $total_cartaoD = 0;
        $total_pix = 0;

        while ($row = $stmt_totais->fetch(PDO::FETCH_ASSOC)) {
            $forma = strtoupper(trim($row['nome_pagamento']));
            $total = $row['total'];
            switch ($forma) {
                case 'DINHEIRO':
                    $total_dinheiro = $total;
                    break;
                case 'CRÉDITO':
                case 'CREDITO':
                    $total_cartaoC = $total;
                    break;
                case 'DÉBITO':
                case 'DEBITO':
                    $total_cartaoD = $total;
                    break;
                case 'PIX':
                    $total_pix = $total;
                    break;
            }
        }

        // 3. Insere no Relatório Final (Agora COM o valor_abertura)
        $sql_insert = "INSERT INTO fechamento_caixa 
            (data_abertura, data_fechamento, operador, valor_abertura, total_dinheiro, total_cartaoC, total_cartaoD, total_pix) 
            VALUES (:data_abertura, NOW(), :operador, :valor_abertura, :dinheiro, :cartaoC, :cartaoD, :pix)";

        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([
            ':data_abertura' => $data_abertura,
            ':operador' => $operador,
            ':valor_abertura' => $valor_abertura, // Gravando o fundo de troco
            ':dinheiro' => $total_dinheiro,
            ':cartaoC' => $total_cartaoC,
            ':cartaoD' => $total_cartaoD,
            ':pix' => $total_pix
        ]);

        $fechamento_id = $conn->lastInsertId();

        // 4. Vincula as vendas a este fechamento
        $sql_update = "UPDATE vendas SET fechamento_caixa_id = :fechamento WHERE status = 'finalizada' AND fechamento_caixa_id IS NULL AND data_hora >= :data_abertura";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([':fechamento' => $fechamento_id, ':data_abertura' => $data_abertura]);

        // 5. Fecha o status
        $conn->exec("INSERT INTO caixa_status (status, valor_inicial, data_status) VALUES ('fechado', 0, NOW())");

        $conn->commit();

        // Redireciona para o relatório do dia
        header('Location: consulta_caixa.php');
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        die("Erro ao fechar: " . $e->getMessage());
    }
}
?>