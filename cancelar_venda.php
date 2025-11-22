<?php
session_start();
require_once "conexao.php";
header('Content-Type: application/json');

// Verifica permissão (opcional, mas recomendado)
if (!isset($_SESSION['nome_usuario'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não autorizado.']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$id_venda = isset($input['id_venda']) ? (int) $input['id_venda'] : 0;

if ($id_venda <= 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Venda inválida.']);
    exit;
}

try {
    $conn->beginTransaction();

    // 1. Verificar se a venda existe e já não está cancelada
    $stmtCheck = $conn->prepare("SELECT status FROM vendas WHERE id_venda = :id");
    $stmtCheck->execute([':id' => $id_venda]);
    $statusAtual = $stmtCheck->fetchColumn();

    if (!$statusAtual) {
        throw new Exception("Venda não encontrada.");
    }
    if ($statusAtual === 'cancelada') {
        throw new Exception("Esta venda já foi cancelada anteriormente.");
    }

    // 2. Buscar os itens vendidos para devolver ao estoque
    // Precisamos fazer JOIN com produto e categoria para pegar os nomes (já que sua tabela estoque usa nomes, não IDs)
    $sqlItens = "
        SELECT sp.quantidade, p.nome, p.sabor, c.nome_categoria
        FROM saida_produtos sp
        JOIN produto p ON sp.id_produto = p.id_produto
        JOIN categoria c ON p.id_categoria = c.id_categoria
        WHERE sp.venda_id = :id_venda
    ";
    $stmtItens = $conn->prepare($sqlItens);
    $stmtItens->execute([':id_venda' => $id_venda]);
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    // 3. Devolver cada item para o estoque
    $sqlEstorno = "INSERT INTO estoque (movimentacao, produto, sabor, tipo, estoque_atual, data) 
                   VALUES ('Entrada', :produto, :sabor, :tipo, :qtd, NOW())";
    $stmtEstorno = $conn->prepare($sqlEstorno);

    foreach ($itens as $item) {
        $stmtEstorno->execute([
            ':produto' => $item['nome'],
            ':sabor' => $item['sabor'],
            ':tipo' => $item['nome_categoria'],
            ':qtd' => $item['quantidade'] // Quantidade positiva, pois é Entrada (Devolução)
        ]);
    }

    // 4. Atualizar o status da venda
    $stmtUpdate = $conn->prepare("UPDATE vendas SET status = 'cancelada' WHERE id_venda = :id");
    $stmtUpdate->execute([':id' => $id_venda]);

    $conn->commit();
    echo json_encode(['sucesso' => true, 'mensagem' => 'Venda cancelada e estoque estornado.']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro: ' . $e->getMessage()]);
}
?>