<?php
require_once 'conexao.php';
session_start();

if (!isset($_SESSION['nome_usuario'])) {
    // Se não houver sessão, podemos tentar pegar o último operador que abriu o caixa
    // Mas o ideal é garantir que o usuário esteja logado.
    // Por enquanto, vamos manter a lógica original.
    die('Usuário não está logado.');
}

$operador = $_SESSION['nome_usuario'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // --- MUDANÇA 1: Buscar a data de abertura correta ---
        $stmt_abertura = $conn->query("SELECT data_status FROM caixa_status WHERE status = 'aberto' ORDER BY id_status DESC LIMIT 1");
        $data_abertura = $stmt_abertura->fetchColumn();

        if (!$data_abertura) {
            throw new Exception("Não foi possível encontrar um registro de caixa aberto.");
        }

        // --- MUDANÇA 2: A query SQL para buscar os totais ---
        // Agora usamos JOIN para buscar o nome da forma de pagamento
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
            // --- MUDANÇA 3: Usar 'nome_pagamento' em vez de 'forma_pagamento' ---
            $forma = strtoupper(trim($row['nome_pagamento']));
            $total = $row['total'];

            // O switch continua funcionando, pois agora ele recebe o nome correto
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

        // --- MUDANÇA 4: Inserir a data de abertura correta ---
        $sql_insert = "INSERT INTO fechamento_caixa 
            (data_abertura, data_fechamento, operador, total_dinheiro, total_cartaoC, total_cartaoD, total_pix) 
            VALUES (:data_abertura, NOW(), :operador, :dinheiro, :cartaoC, :cartaoD, :pix)";

        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([
            ':data_abertura' => $data_abertura, // Usamos a data correta
            ':operador' => $operador,
            ':dinheiro' => $total_dinheiro,
            ':cartaoC' => $total_cartaoC,
            ':cartaoD' => $total_cartaoD,
            ':pix' => $total_pix
        ]);

        $fechamento_id = $conn->lastInsertId();

        // Atualiza as vendas para vincular ao fechamento
        $sql_update = "UPDATE vendas 
                       SET fechamento_caixa_id = :fechamento
                       WHERE status = 'finalizada' AND fechamento_caixa_id IS NULL AND data_hora >= :data_abertura";

        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([
            ':fechamento' => $fechamento_id,
            ':data_abertura' => $data_abertura
        ]);

        // Fecha o status do caixa
        $stmt = $conn->prepare("INSERT INTO caixa_status (status) VALUES ('fechado')");
        $stmt->execute();

        $conn->commit();

        // Redireciona para a página de consulta para ver o resultado
        header('Location: consulta_caixa.php?data=' . date('Y-m-d'));
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        die("Erro ao fechar o caixa: " . $e->getMessage());
    }
}
?>