<?php
session_start();

if (!isset($_SESSION['nome_usuario']) || (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] != 1)) {
    header("Location: acesso.php");
    exit();
}

require_once "conexao.php";
$mensagem_swal = "";
$dados = [];

// Carrega Listas
try {
    $categorias = $conn->query("SELECT * FROM categoria ORDER BY nome_categoria")->fetchAll(PDO::FETCH_ASSOC);
    $fornecedores = $conn->query("SELECT * FROM fornecedor ORDER BY nome_fornecedor")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { $categorias = []; $fornecedores = []; }

// Carrega Produto
if (isset($_GET["al"])) {
    $id = (int)$_GET["al"];
    $stmt = $conn->prepare("SELECT * FROM produto WHERE id_produto = :id");
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    if(!$dados) { header("Location: cad_produto.php"); exit; }
}

// Processa Update
if (isset($_POST["alterar"])) {
    $id = $_POST["id_produto"];
    $nome = $_POST["nome"];
    $sabor = $_POST["sabor"];
    $cat = $_POST["id_categoria"];
    $forn = $_POST["id_fornecedor"];
    $venda = str_replace(',', '.', $_POST["preco_venda"]);
    $compra = str_replace(',', '.', $_POST["preco_compra"]);

    try {
        $sql = $conn->prepare("UPDATE produto SET nome=:nome, sabor=:sabor, id_categoria=:cat, id_fornecedor=:forn, preco_venda=:venda, preco_compra=:compra WHERE id_produto=:id");
        $sql->execute([':nome'=>$nome, ':sabor'=>$sabor, ':cat'=>$cat, ':forn'=>$forn, ':venda'=>$venda, ':compra'=>$compra, ':id'=>$id]);
        
        $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Atualizado!', text: 'Produto alterado.', timer: 2000, showConfirmButton: false }).then(() => { window.location.href = 'cad_produto.php'; });";
    } catch (PDOException $erro) {
        $mensagem_swal = "Swal.fire({ icon: 'error', title: 'Erro', text: '".$erro->getMessage()."' });";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>PDV - Alterar Produto</title>
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
      <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown"><i class='bx bx-menu fs-3'></i></button>
      <ul class="dropdown-menu dropdown-menu-end shadow border-0">
        <li><a class="dropdown-item py-2" href="cad_produto.php"><i class='bx bx-arrow-back'></i> Voltar</a></li>
        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a></li>
      </ul>
    </div>
  </div>

  <div class="main-container">
    <h2 class="page-title"><i class='bx bx-edit'></i> Alterar Produto</h2>
    <div class="form-card">
        <form method="POST">
            <input type="hidden" name="id_produto" value="<?= $dados['id_produto'] ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Produto</label>
                    <input type="text" class="form-control" name="nome" value="<?= htmlspecialchars($dados['nome']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sabor</label>
                    <input type="text" class="form-control" name="sabor" value="<?= htmlspecialchars($dados['sabor']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Categoria</label>
                    <select class="form-select" name="id_categoria" required>
                        <?php foreach($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>" <?= ($dados['id_categoria'] == $cat['id_categoria']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nome_categoria']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Fornecedor</label>
                    <select class="form-select" name="id_fornecedor" required>
                        <?php foreach($fornecedores as $forn): ?>
                            <option value="<?= $forn['id_fornecedor'] ?>" <?= ($dados['id_fornecedor'] == $forn['id_fornecedor']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($forn['nome_fornecedor']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Venda (R$)</label>
                    <input type="text" class="form-control" name="preco_venda" value="<?= htmlspecialchars($dados['preco_venda']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Compra (R$)</label>
                    <input type="text" class="form-control" name="preco_compra" value="<?= htmlspecialchars($dados['preco_compra']) ?>" required>
                </div>
                <div class="col-12 mt-4">
                    <button type="submit" name="alterar" class="btn btn-primary w-100 btn-lg"><i class='bx bx-save'></i> Salvar Alterações</button>
                </div>
            </div>
        </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script> <?php if (!empty($mensagem_swal)) echo $mensagem_swal; ?> </script>
</body>
</html>