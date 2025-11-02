<?php
require_once "conexao.php";

$row = [];
$estabelecimentos = [];

try {
  $stmtEst = $conn->prepare("SELECT id_estabelecimento, nome_estabelecimento FROM estabelecimento ORDER BY nome_estabelecimento ASC");
  $stmtEst->execute();
  $estabelecimentos = $stmtEst->fetchAll(PDO::FETCH_ASSOC);

  if (isset($_GET["al"])) {
    $id_usuario = $_GET["al"];
    $consulta = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = :id_usuario");
    $consulta->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $consulta->execute();
    $row = $consulta->fetch(PDO::FETCH_ASSOC);
  }
} catch (PDOException $erro) {
  echo $erro->getMessage();
}

if (isset($_POST["alterar"])) {
  $id_usuario = $_POST["id_usuario"];
  $tipo_usuario = $_POST["tipo_usuario"];
  $nome_usuario = $_POST["nome_usuario"];
  $id_estabelecimento = $_POST["id_estabelecimento"];

  try {
    $sql_update = "UPDATE usuarios 
                       SET tipo_usuario = :tipo_usuario, nome_usuario = :nome_usuario, id_estabelecimento = :id_estabelecimento
                       WHERE id_usuario = :id_usuario";
    $params = [
      ':tipo_usuario' => $tipo_usuario,
      ':nome_usuario' => $nome_usuario,
      ':id_estabelecimento' => $id_estabelecimento,
      ':id_usuario' => $id_usuario
    ];

    if (!empty($_POST["senha"])) {
      $hash = password_hash($_POST["senha"], PASSWORD_DEFAULT);
      $sql_update = "UPDATE usuarios 
                           SET tipo_usuario = :tipo_usuario, nome_usuario = :nome_usuario, id_estabelecimento = :id_estabelecimento, senha = :senha
                           WHERE id_usuario = :id_usuario";
      $params[':senha'] = $hash;
    }

    $sql = $conn->prepare($sql_update);

    if ($sql->execute($params)) {
      echo "<script>
                    alert('Usuário alterado com sucesso!');
                    location.href = 'cad_usuario.php'; 
                  </script>";
    } else {
      echo "<script>alert('Erro ao alterar usuário!');</script>";
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
  <title>PDV - Alterar Usuário</title>

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
        <li><a class="dropdown-item" href="cad_usuario.php">Cadastro de Usuário</a></li>
      </ul>
    </div>
  </div>

  <div class="container main-container">
    <h2 class="page-title">Alterar Usuário</h2>

    <form method="POST">
      <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($row['id_usuario'] ?? '') ?>">

      <div class="mb-3">
        <label for="tipo_usuario" class="form-label">Tipo de Usuário</label>
        <select id="tipo_usuario" class="form-select" name="tipo_usuario" required>
          <option value="0" <?= ($row['tipo_usuario'] ?? '') == 0 ? 'selected' : '' ?>>Comum</option>
          <option value="1" <?= ($row['tipo_usuario'] ?? '') == 1 ? 'selected' : '' ?>>Administrador</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="nome_usuario" class="form-label">Nome do Usuário</label>
        <input id="nome_usuario" type="text" class="form-control" name="nome_usuario" value="<?= htmlspecialchars($row['nome_usuario'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label for="senha" class="form-label">Nova Senha</label>
        <input id="senha" type="password" class="form-control" name="senha" value="" placeholder="Deixe em branco para não alterar">
      </div>

      <div class="mb-3">
        <label for="id_estabelecimento" class="form-label">Estabelecimento</label>
        <select id="id_estabelecimento" name="id_estabelecimento" class="form-select" required>
          <option value="">Selecione o Estabelecimento</option>
          <?php foreach ($estabelecimentos as $est) : ?>
            <option value="<?= $est['id_estabelecimento']; ?>" <?= ($row['id_estabelecimento'] ?? '') == $est['id_estabelecimento'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($est['nome_estabelecimento']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" name="alterar" class="btn btn-primary w-100 btn-lg">Salvar Alteração</button>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>