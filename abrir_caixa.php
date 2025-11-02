<?php
include "conexao.php";

try {
    $stmt = $conn->prepare("INSERT INTO caixa_status (status) VALUES ('aberto')");
    $stmt->execute();
    header("Location: vendas.php");
    exit;
} catch (PDOException $e) {
    echo "Erro ao abrir o caixa: " . $e->getMessage();
}
