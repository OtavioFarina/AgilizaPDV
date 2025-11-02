<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDV - Cadastro de Categorias</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles/style_cad.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
  <div class="top-bar d-flex align-items-center justify-content-between px-3 py-2 bg-light rounded shadow-sm">
    <img src="img/logo pdv.png" class="logo" alt="Logo PDV" style="height:50px;">
    <div class="dropdown">
      <button class="btn dropdown" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="img/3riscos.png" class="3riscos" alt="Simbolo3Riscos" style="height:25px;">
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
        <li>
          <a class="dropdown-item" href="adm.php">Painel Administrativo</a>
        </li>
      </ul>
    </div>
  </div>

  <?php
  require_once "conexao.php";
  $mensagem = "";

  try {
    if (isset($_POST["cadastrar"])) {
      $nome = $_POST["nome"];

      $sql = $conn->prepare("INSERT INTO categoria (id_categoria, nome_categoria) 
                             VALUES(:id_categoria, :nome_categoria)");
      $sql->bindValue(':id_categoria', null);
      $sql->bindValue(':nome_categoria', $nome);
      $sql->execute();

      $mensagem = "<div class='alert alert-success text-center mt-3'>Cadastro realizado com sucesso!</div>";
    }

    if (isset($_GET["ex"])) {
      $id_categoria = $_GET["ex"];
      $sql = $conn->prepare("DELETE FROM categoria WHERE id_categoria = :id_categoria");
      $sql->bindValue(":id_categoria", $id_categoria);
      $sql->execute();
      $mensagem = "<div class='alert alert-warning text-center mt-3'>Categoria excluída com sucesso!</div>";
    }
  } catch (PDOException $erro) {
    $mensagem = "<div class='alert alert-danger text-center mt-3'>Erro: " . htmlspecialchars($erro->getMessage()) . "</div>";
  }
  ?>

  <div class="main-container">
    <h1 class="text-center mb-4">Cadastro de Categorias</h1>

    <?php echo $mensagem; ?>

    <form name="formCategoria" method="post" action="" enctype="multipart/form-data">
      <div class="mb-3">
        <label for="nome" class="form-label">Nome da Categoria</label>
        <input type="text" class="form-control" id="nome" name="nome" required>
      </div>

      <button type="submit" name="cadastrar" class="btn btn-primary w-100">Cadastrar Categoria</button>
    </form>

    <?php
    try {
      $usuarios = $conn->query("SELECT * FROM categoria");
    } catch (PDOException $e) {
      echo "<div class='alert alert-danger mt-3'>Erro ao buscar a categoria: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>

    <div class="table-container mt-4">
      <h2 class="text-center mb-4">Categorias Cadastradas</h2>

      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>ID da Categoria</th>
            <th>Nome</th>
            <th>Alterar</th>
            <th>Excluir</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($categoria = $usuarios->fetch(PDO::FETCH_ASSOC)) : ?>
            <tr>
              <td><?php echo htmlspecialchars($categoria['id_categoria']); ?></td>
              <td><?php echo htmlspecialchars($categoria['nome_categoria']); ?></td>
              <td><a href="alt_categoria.php?al=<?php echo $categoria["id_categoria"]; ?>"><img src="img/caneta.png" alt="Editar" width="40"></a></td>
              <td><a href="cad_categoria.php?ex=<?php echo $categoria["id_categoria"]; ?>" onclick="return confirm('Tem certeza que deseja excluir esta categoria?')"><img src="img/apagar.png" alt="Excluir" width="40"></a></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>