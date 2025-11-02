<?php
require_once "conexao.php";

$row = []; // Inicia a variável

// 1. Busca os dados da categoria para preencher o formulário
try {
  if (isset($_GET["al"])) {
    $id_categoria = $_GET["al"];
    $consulta = $conn->prepare("SELECT * FROM categoria WHERE id_categoria = :id_categoria");
    $consulta->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
    $consulta->execute();
    $row = $consulta->fetch(PDO::FETCH_ASSOC);
  }
} catch (PDOException $erro) {
  echo $erro->getMessage();
}

// 2. Processa a alteração quando o formulário é enviado
if (isset($_POST["alterar"])) {
  $id_categoria = $_POST["id_categoria"];
  $nome_categoria = $_POST["nome_categoria"];

  try {
    $sql = $conn->prepare("UPDATE categoria 
                               SET nome_categoria = :nome_categoria
                               WHERE id_categoria = :id_categoria");

    $sql->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
    $sql->bindValue(':nome_categoria', $nome_categoria);

    if ($sql->execute()) {
      echo "<script>
                    alert('Categoria alterada com sucesso!');
                    location.href = 'cad_categoria.php';
                  </script>";
      // Mudei o redirect para a página de cadastro (geralmente é melhor)
    } else {
      echo "<script>alert('Erro ao alterar categoria!');</script>";
    }
  } catch (PDOException $erro) {
    echo "<script>alert('Erro: " . addslashes($erro->getMessage()) . "');</script>";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDV - Alterar Categoria</title>

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
        <li><a class="dropdown-item" href="cad_categoria.php">Cadastro de Categoria</a></li>
      </ul>
    </div>
  </div>

  <div class="container main-container">
    <h2 class="page-title">Alterar Categoria</h2>

    <form method="POST">
      <input type="hidden" name="id_categoria" value="<?= htmlspecialchars($row['id_categoria'] ?? '') ?>">

      <div class="mb-3">
        <label for="nome_categoria" class="form-label">Nome da Categoria</label>
        <input id="nome_categoria" type="text" class="form-control" name="nome_categoria" value="<?= htmlspecialchars($row['nome_categoria'] ?? '') ?>" required>
      </div>

      <button type="submit" name="alterar" class="btn btn-primary w-100 btn-lg">Salvar Alteração</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>