<?php
require_once "conexao.php";

try {
  if (isset($_GET["al"])) {
    $id_produto = $_GET["al"];
    $consulta = $conn->prepare("SELECT * FROM produto WHERE id_produto = :id_produto");
    $consulta->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
    $consulta->execute();
    $row = $consulta->fetch(PDO::FETCH_ASSOC);
  }
} catch (PDOException $erro) {
  echo $erro->getMessage();
}

if (isset($_POST["alterar"])) {
  $id_produto = $_POST["id_produto"];
  $nome = $_POST["nome"];
  $sabor = $_POST["sabor"];
  $id_categoria = $_POST["id_categoria"];
  $id_fornecedor = $_POST["id_fornecedor"];
  $preco_venda = $_POST["preco_venda"];
  $preco_compra = $_POST["preco_compra"];

  $sql = $conn->prepare("UPDATE produto 
                           SET nome = :nome, sabor = :sabor, id_categoria = :id_categoria, 
                               id_fornecedor = :id_fornecedor, preco_venda = :preco_venda, preco_compra = :preco_compra
                           WHERE id_produto = :id_produto");

  $sql->bindValue(':id_produto', $id_produto, PDO::PARAM_INT);
  $sql->bindValue(':nome', $nome);
  $sql->bindValue(':sabor', $sabor);
  $sql->bindValue(':id_categoria', $id_categoria);
  $sql->bindValue(':id_fornecedor', $id_fornecedor);
  $sql->bindValue(':preco_venda', $preco_venda);
  $sql->bindValue(':preco_compra', $preco_compra);

  if ($sql->execute()) {
    echo "<script>
                 alert('Alteração Efetuada com Sucesso !!');
                 location.href = 'cad_produto.php';
             </script>";
  } else {
    echo "<script>alert('Erro ao alterar produto!');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDV - Alterar Produtos</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

  <link href="styles/style_alt.css?v=<?php echo filemtime('styles/style_alt.css'); ?>" rel="stylesheet">
</head>

<body>

  <div class="top-bar d-flex align-items-center justify-content-between px-3 py-2">
    <img src="img/logo pdv.png" class="logo" alt="Logo PDV" style="height:50px;">
    <div class="dropdown">
      <button class="btn" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="img/3riscos.png" class="3riscos" alt="Simbolo3Riscos" style="height:25px;">
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
        <li><a class="dropdown-item" href="adm.php">Painel Administrativo</a></li>
        <li><a class="dropdown-item" href="cad_produto.php">Cadastro de Produto</a></li>
      </ul>
    </div>
  </div>

  <div class="container main-container">
    <h2 class="page-title">Alterar Produto</h2>

    <form method="POST">
      <input type="hidden" name="id_produto" value="<?= htmlspecialchars($row['id_produto'] ?? '') ?>">

      <div class="mb-3">
        <label for="nome" class="form-label">Nome do Produto</label>
        <input id="nome" type="text" class="form-control" name="nome" value="<?= htmlspecialchars($row['nome'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label for="sabor" class="form-label">Sabor do Produto</label>
        <input id="sabor" type="text" class="form-control" name="sabor" value="<?= htmlspecialchars($row['sabor'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label for="id_categoria" class="form-label">Categoria</label>
        <select id="id_categoria" name="id_categoria" class="form-select" required>
          <option value="">Selecione</option>
          <option value="1" <?= ($row['id_categoria'] ?? '') == 1 ? 'selected' : '' ?>>Palito</option>
          <option value="2" <?= ($row['id_categoria'] ?? '') == 2 ? 'selected' : '' ?>>Pote 2.5L</option>
          <option value="3" <?= ($row['id_categoria'] ?? '') == 3 ? 'selected' : '' ?>>Pote 5L</option>
          <option value="4" <?= ($row['id_categoria'] ?? '') == 4 ? 'selected' : '' ?>>Copinho</option>
          <option value="5" <?= ($row['id_categoria'] ?? '') == 5 ? 'selected' : '' ?>>Torta</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="id_fornecedor" class="form-label">Fornecedor</label>
        <select id="id_fornecedor" name="id_fornecedor" class="form-select" required>
          <option value="">Selecione</option>
          <option value="1" <?= ($row['id_fornecedor'] ?? '') == 1 ? 'selected' : '' ?>>Francisco Parra</option>
          <option value="2" <?= ($row['id_fornecedor'] ?? '') == 2 ? 'selected' : '' ?>>Rogério</option>
        </select>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="preco_venda" class="form-label">Preço de Venda</label>
          <input id="preco_venda" type="text" class="form-control" name="preco_venda" value="<?= htmlspecialchars($row['preco_venda'] ?? '') ?>" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="preco_compra" class="form-label">Preço de Compra</label>
          <input id="preco_compra" type="text" class="form-control" name="preco_compra" value="<?= htmlspecialchars($row['preco_compra'] ?? '') ?>" required>
        </div>
      </div>

      <button type="submit" name="alterar" class="btn btn-primary w-100 btn-lg">Salvar Alteração</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>