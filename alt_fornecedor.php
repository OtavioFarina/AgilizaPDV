<?php
require_once "conexao.php";

try {
  if (isset($_GET["al"])) {
    $id_fornecedor = $_GET["al"];
    $consulta = $conn->prepare("SELECT * FROM fornecedor WHERE id_fornecedor = :id_fornecedor");
    $consulta->bindValue(':id_fornecedor', $id_fornecedor, PDO::PARAM_INT);
    $consulta->execute();
    $row = $consulta->fetch(PDO::FETCH_ASSOC);
  }
} catch (PDOException $erro) {
  echo $erro->getMessage();
}

if (isset($_POST["alterar"])) {
  $id_fornecedor = $_POST["id_fornecedor"];
  $nome_fornecedor = $_POST["fornecedor"];
  $cnpj = $_POST["cnpj"];
  $telefone = $_POST["telefone"];
  $email = $_POST["email"];
  $endereco = $_POST["endereco"];

  $sql = $conn->prepare("UPDATE fornecedor 
                           SET nome_fornecedor = :nome_fornecedor, cnpj = :cnpj,
                               id_fornecedor = :id_fornecedor, telefone = :telefone, email = :email, endereco = :endereco
                           WHERE id_fornecedor = :id_fornecedor");

  $sql->bindValue(':id_fornecedor', $id_fornecedor, PDO::PARAM_INT);
  $sql->bindValue(':nome_fornecedor', $nome_fornecedor);
  $sql->bindValue(':cnpj', $cnpj);
  $sql->bindValue(':telefone', $telefone);
  $sql->bindValue(':email', $email);
  $sql->bindValue(':endereco', $endereco);

  if ($sql->execute()) {
    echo "<script>
                 alert('Alteração Efetuada com Sucesso !!');
                 location.href = 'cad_fornecedor.php';
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
  <title>PDV - Alterar Fornecedores</title>

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
    <h2 class="page-title">Alterar Fornecedor</h2>

    <form method="POST">
      <input type="hidden" name="id_fornecedor" value="<?= htmlspecialchars($row['id_fornecedor'] ?? '') ?>">

      <div class="mb-3">
        <label for="fornecedor" class="form-label">Nome do Fornecedor</label>
        <input id="fornecedor" type="text" class="form-control" name="nome_fornecedor" value="<?= htmlspecialchars($row['nome_fornecedor'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label for="cnpj" class="form-label">CNPJ</label>
        <input id="cnpj" type="text" class="form-control" name="cnpj" value="<?= htmlspecialchars($row['cnpj'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label for="telefone" class="form-label">Telefone</label>
        <input id="telefone" type="text" class="form-control" name="telefone" value="<?= htmlspecialchars($row['cnpj'] ?? '') ?>" required>
        </select>
      </div>

      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="email" class="form-label">Email</label>
          <input id="email" type="text" class="form-control" name="email" value="<?= htmlspecialchars($row['email'] ?? '') ?>" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="endereco" class="form-label">Endereço</label>
          <input id="endereco" type="text" class="form-control" name="endereco" value="<?= htmlspecialchars($row['endereco'] ?? '') ?>" required>
        </div>
      </div>

      <button type="submit" name="alterar" class="btn btn-primary w-100 btn-lg">Salvar Alteração</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>