<?php
session_start();
require_once "config/conexao.php";

// 1. Segurança: Apenas usuários logados
if (!isset($_SESSION['nome_usuario'])) {
    header("Location: index.php");
    exit;
}

// 2. Verifica se o caixa JÁ está aberto (evitar duplicação)
$statusCheck = $conn->query("SELECT status FROM caixa_status ORDER BY id_status DESC LIMIT 1")->fetchColumn();
if ($statusCheck === 'aberto') {
    header("Location: vendas.php");
    exit;
}

// 3. Processa a Abertura
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Limpa o valor recebido (R$ 1.000,00 -> 1000.00)
        $valor_inicial = isset($_POST['valor_abertura']) ? $_POST['valor_abertura'] : '0';
        $valor_inicial = str_replace('.', '', $valor_inicial); // Tira ponto de milhar
        $valor_inicial = str_replace(',', '.', $valor_inicial); // Troca vírgula por ponto
        $valor_inicial = floatval($valor_inicial);

        $sql = "INSERT INTO caixa_status (status, valor_inicial, data_status) VALUES ('aberto', :valor, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':valor', $valor_inicial);
        $stmt->execute();

        // Redireciona com sucesso
        header("Location: vendas.php");
        exit;

    } catch (PDOException $e) {
        echo "<script>alert('Erro ao abrir caixa: " . addslashes($e->getMessage()) . "'); window.location.href='vendas.php';</script>";
    }
} else {
    // Se tentar acessar direto sem POST
    header("Location: vendas.php");
    exit;
}
?>