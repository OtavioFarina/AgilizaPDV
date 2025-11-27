<?php
session_start();

// Segurança: Apenas ADM logado
if (!isset($_SESSION['nome_usuario']) || (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] != 1)) {
  header("Location: acesso.php");
  exit();
}

require_once "conexao.php";
$mensagem_swal = "";
$dados = [];

// 1. Busca dados atuais
if (isset($_GET["al"])) {
  $id = (int) $_GET["al"];
  $stmt = $conn->prepare("SELECT * FROM categoria WHERE id_categoria = :id");
  $stmt->bindValue(':id', $id);
  $stmt->execute();
  $dados = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$dados) {
    header("Location: cad_categoria.php");
    exit;
  }
} else {
  header("Location: cad_categoria.php");
  exit;
}

// 2. Processa Alteração
if (isset($_POST["alterar"])) {
  $id_categoria = $_POST["id_categoria"];
  $nome = $_POST["nome_categoria"];

  try {
    $sql = $conn->prepare("UPDATE categoria SET nome_categoria = :nome WHERE id_categoria = :id");
    $sql->bindValue(':nome', $nome);
    $sql->bindValue(':id', $id_categoria);

    if ($sql->execute()) {
      $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Atualizado!', text: 'Categoria alterada com sucesso.', timer: 2000, showConfirmButton: false }).then(() => { window.location.href = 'cad_categoria.php'; });";
    }
  } catch (PDOException $erro) {
    $mensagem_swal = "Swal.fire({ icon: 'error', title: 'Erro', text: '" . $erro->getMessage() . "' });";
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
        <li><a class="dropdown-item py-2 text-primary fw-bold" href="cad_categoria.php"><i class='bx bx-arrow-back'></i>
            Voltar</a></li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a></li>
      </ul>
    </div>
  </div>

  <div class="main-container">
    <h2 class="page-title"><i class='bx bx-edit'></i> Alterar Categoria</h2>
    <div class="form-card">
      <form method="POST">
        <input type="hidden" name="id_categoria" value="<?= $dados['id_categoria'] ?>">
        <div class="row">
          <div class="col-md-12 mb-3">
            <label class="form-label">Nome da Categoria</label>
            <input type="text" class="form-control" name="nome_categoria"
              value="<?= htmlspecialchars($dados['nome_categoria']) ?>" required>
          </div>
          <div class="col-12">
            <button type="submit" name="alterar" class="btn btn-primary w-100 btn-lg"><i class='bx bx-save'></i> Salvar
              Alterações</button>
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