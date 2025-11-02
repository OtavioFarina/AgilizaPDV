<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDV - Cadastro de Usuários</title>

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

  <div class="main-container">
    <h1 class="text-center mb-4">Cadastro de Usuários</h1>

    <?php
    require "conexao.php";

    if (isset($_GET['ex'])) {
      $id_usuario = $_GET['ex'];
      try {
        $sql = $conn->prepare("DELETE FROM usuarios WHERE id_usuario = :id_usuario");
        $sql->bindParam(':id_usuario', $id_usuario);
        $sql->execute();
        header("Location: cad_usuario.php");
        exit();
      } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Erro ao excluir: " . htmlspecialchars($e->getMessage()) . "</div>";
      }
    }

    $estabelecimentos = [];
    $mensagem = "";

    try {
      $stmtEst = $conn->prepare("SELECT id_estabelecimento, nome_estabelecimento FROM estabelecimento ORDER BY nome_estabelecimento ASC");
      $stmtEst->execute();
      $estabelecimentos = $stmtEst->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $mensagem .= "<div class='alert alert-danger mt-3'>Erro ao buscar estabelecimentos: " . htmlspecialchars($e->getMessage()) . "</div>";
    }

    if (isset($_POST["cadastrar"])) {
      $nome_usuario = $_POST["nome_usuario"];
      $senha = $_POST["senha"];
      $id_estabelecimento = $_POST["estabelecimento"];
      $tipo_usuario = $_POST["tipo_usuario"];

      $hash = password_hash($senha, PASSWORD_DEFAULT);

      try {
        $sql = $conn->prepare("INSERT INTO usuarios (nome_usuario, senha, id_estabelecimento, tipo_usuario) 
                               VALUES (:nome_usuario, :hash, :id_estabelecimento, :tipo_usuario)");

        $sql->bindValue(':nome_usuario', $nome_usuario);
        $sql->bindValue(':hash', $hash);
        $sql->bindValue(':id_estabelecimento', $id_estabelecimento);
        $sql->bindValue(':tipo_usuario', $tipo_usuario);

        $sql->execute();

        echo "<div class='alert alert-success'>Cadastro realizado com sucesso! Recarregando a página...</div>";
        echo "<script>setTimeout(() => { window.location.href = 'cad_usuario.php'; }, 2000);</script>";
      } catch (PDOException $erro) {
        echo "<div class='alert alert-danger'>Erro: " . htmlspecialchars($erro->getMessage()) . "</div>";
      }
    }

    try {
      $sql = "SELECT 
                u.id_usuario, 
                u.nome_usuario, 
                e.nome_estabelecimento 
            FROM 
                usuarios u 
            LEFT JOIN 
                estabelecimento e ON u.id_estabelecimento = e.id_estabelecimento";
      $usuarios = $conn->query($sql);
    } catch (PDOException $e) {
      echo "<div class='alert alert-danger'>Erro ao buscar usuários: " . htmlspecialchars($e->getMessage()) . "</div>";
      $usuarios = null;
    }
    echo $mensagem;
    ?>

    <form name="form1" method="post" action="">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="nome_usuario" class="form-label">Nome do Usuário</label>
          <input type="text" class="form-control" id="nome_usuario" name="nome_usuario" required>
        </div>

        <div class="col-md-6 mb-3">
          <label for="tipo_usuario" class="form-label">Tipo de Usuário</label>
          <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
            <option selected disabled>Selecione o tipo de usuário</option>
            <option value="0">Comum</option>
            <option value="1">Administrador</option>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label for="senha" class="form-label">Senha</label>
          <input type="password" class="form-control" id="senha" name="senha" required>
        </div>

        <div class="col-md-6 mb-3">
          <label for="estabelecimento" class="form-label">Estabelecimento</label>
          <select id="estabelecimento" name="estabelecimento" class="form-select" required>
            <option value="" selected disabled>Informe o Estabelecimento</option>

            <?php foreach ($estabelecimentos as $row) : ?>
              <option value="<?php echo (int) $row['id_estabelecimento']; ?>">
                <?php echo htmlspecialchars($row['nome_estabelecimento']); ?>
              </option>
            <?php endforeach; ?>

          </select>
        </div>

        <div class="col-12 mt-3">
          <button type="submit" name="cadastrar" class="btn btn-primary w-100">Cadastrar Usuário</button>
        </div>
      </div>
    </form>

    <div class="table-container">
      <h2 class="text-center mb-4">Usuários Cadastrados</h2>

      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>ID do Usuário</th>
            <th>Nome</th>
            <th>Estabelecimento</th>
            <th>Alterar</th>
            <th>Excluir</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($usuario = $usuarios->fetch(PDO::FETCH_ASSOC)) : ?>
            <tr>
              <td><?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
              <td><?php echo htmlspecialchars($usuario['nome_usuario']); ?></td>
              <td><?php echo htmlspecialchars($usuario['nome_estabelecimento']); ?></td>
              <td><a href="alt_usuario.php?al=<?php echo $usuario["id_usuario"]; ?>"><img src="img/caneta.png" alt="Editar" width="40"></a></td>
              <td><a href="cad_usuario.php?ex=<?php echo $usuario["id_usuario"]; ?>" onclick="return confirm('Tem certeza que deseja excluir este usuário?')"><img src="img/apagar.png" alt="Excluir" width="40">
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>