<?php
session_start();

// Verificação de Login
if (!isset($_SESSION['nome_usuario'])) {
    header("Location: index.php");
    exit();
}

// Verificação de Permissão (Apenas ADM)
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != 1) {
    header("Location: historico_entradas.php");
    exit();
}

require_once "conexao.php";
$mensagem_swal = "";
$dados = [];

// Carrega registro de estoque
if (isset($_GET["id"])) {
    $id = (int) $_GET["id"];
    $stmt = $conn->prepare("SELECT * FROM estoque WHERE id_estoque = :id AND movimentacao = 'Entrada'");
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$dados) {
        header("Location: historico_entradas.php");
        exit;
    }
}

// Processa Update
if (isset($_POST["alterar"])) {
    $id = (int) $_POST["id_estoque"];
    $estoque_atual = (int) $_POST["estoque_atual"];
    $valor_custo = str_replace(',', '.', $_POST["valor_custo"]);

    try {
        $sql = $conn->prepare("UPDATE estoque SET estoque_atual = :estoque_atual, valor_custo = :valor_custo WHERE id_estoque = :id");
        $sql->execute([
            ':estoque_atual' => $estoque_atual,
            ':valor_custo' => $valor_custo,
            ':id' => $id
        ]);

        $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Atualizado!', text: 'Entrada alterada com sucesso.', timer: 2000, showConfirmButton: false }).then(() => { window.location.href = 'historico_entradas.php'; });";
    } catch (PDOException $erro) {
        $mensagem_swal = "Swal.fire({ icon: 'error', title: 'Erro', text: 'Erro ao atualizar: " . str_replace("'", "\'", $erro->getMessage()) . "' });";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>PDV - Editar Entrada de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="styles/style_cad.css" rel="stylesheet">
</head>

<body>
    <div class="top-bar">
        <div class="d-flex align-items-center gap-3">
            <img src="img/logoagilizasemfundo.png" class="logo" alt="Logo">
            <h5 class="m-0 fw-bold text-secondary d-none d-md-block">Administrativo</h5>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown"><i
                    class='bx bx-menu fs-3'></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item py-2 text-primary fw-bold" href="historico_entradas.php"><i
                            class='bx bx-arrow-back'></i> Voltar</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-container">
        <h2 class="page-title"><i class='bx bx-edit'></i> Editar Entrada de Estoque</h2>
        <div class="form-card">
            <form method="POST">
                <input type="hidden" name="id_estoque" value="<?= $dados['id_estoque'] ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Produto</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($dados['produto']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Sabor</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($dados['sabor']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Categoria</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($dados['tipo']) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Data de Entrada</label>
                        <input type="text" class="form-control" value="<?= date('d/m/Y H:i', strtotime($dados['data'])) ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Quantidade Entrada *</label>
                        <input type="number" class="form-control" name="estoque_atual" 
                            value="<?= (int)$dados['estoque_atual'] ?>" min="1" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Custo Unitário (R$) *</label>
                        <input type="text" class="form-control" name="valor_custo"
                            value="<?= number_format($dados['valor_custo'], 2, ',', '.') ?>" required
                            placeholder="0,00">
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" name="alterar" class="btn btn-primary w-100 btn-lg"><i
                                class='bx bx-save'></i> Salvar Alterações</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script> <?php if (!empty($mensagem_swal))
        echo $mensagem_swal; ?> </script>
</body>

</html>
