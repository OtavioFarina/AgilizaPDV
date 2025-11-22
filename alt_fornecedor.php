<?php
session_start();

if (!isset($_SESSION['nome_usuario']) || (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] != 1)) {
  header("Location: acesso.php");
  exit();
}

require_once "conexao.php";
$mensagem_swal = "";
$dados = [];

if (isset($_GET["al"])) {
  $id = (int) $_GET["al"];
  $stmt = $conn->prepare("SELECT * FROM fornecedor WHERE id_fornecedor = :id");
  $stmt->bindValue(':id', $id);
  $stmt->execute();
  $dados = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$dados) {
    header("Location: cad_fornecedor.php");
    exit;
  }
}

if (isset($_POST["alterar"])) {
  $id = $_POST["id_fornecedor"];
  $nome = $_POST["nome_fornecedor"];
  $cnpj = $_POST["cnpj"];
  $tel = $_POST["telefone"];
  $email = $_POST["email"];
  $end = $_POST["endereco"];

  try {
    $sql = $conn->prepare("UPDATE fornecedor SET nome_fornecedor=:nome, cnpj=:cnpj, telefone=:tel, email=:email, endereco=:end WHERE id_fornecedor=:id");
    $sql->execute([':nome' => $nome, ':cnpj' => $cnpj, ':tel' => $tel, ':email' => $email, ':end' => $end, ':id' => $id]);

    $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Atualizado!', text: 'Fornecedor alterado.', timer: 2000, showConfirmButton: false }).then(() => { window.location.href = 'cad_fornecedor.php'; });";
  } catch (PDOException $erro) {
    $mensagem_swal = "Swal.fire({ icon: 'error', title: 'Erro', text: '" . $erro->getMessage() . "' });";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>PDV - Alterar Fornecedor</title>
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
        <li><a class="dropdown-item py-2" href="cad_fornecedor.php"><i class='bx bx-arrow-back'></i> Voltar</a></li>
        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a></li>
      </ul>
    </div>
  </div>

  <div class="main-container">
    <h2 class="page-title"><i class='bx bx-edit'></i> Alterar Fornecedor</h2>
    <div class="form-card">
      <form method="POST">
        <input type="hidden" name="id_fornecedor" value="<?= $dados['id_fornecedor'] ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nome</label>
            <input type="text" class="form-control" name="nome_fornecedor"
              value="<?= htmlspecialchars($dados['nome_fornecedor']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">CNPJ</label>
            <input type="text" class="form-control" id="cnpj" name="cnpj"
              value="<?= htmlspecialchars($dados['cnpj']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Telefone</label>
            <input type="tel" class="form-control" id="telefone" name="telefone"
              value="<?= htmlspecialchars($dados['telefone']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">E-mail</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($dados['email']) ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Endereço</label>
            <input type="text" class="form-control" name="endereco" value="<?= htmlspecialchars($dados['endereco']) ?>">
          </div>
          <div class="col-12 mt-4">
            <button type="submit" name="alterar" class="btn btn-primary w-100 btn-lg"><i class='bx bx-save'></i> Salvar
              Alterações</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    <?php if (!empty($mensagem_swal))
      echo $mensagem_swal; ?>
    document.getElementById('cnpj').addEventListener('input', function (e) {
      let v = e.target.value.replace(/\D/g, "");
      v = v.replace(/^(\d{2})(\d)/, "$1.$2"); v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3"); v = v.replace(/\.(\d{3})(\d)/, ".$1/$2"); v = v.replace(/(\d{4})(\d)/, "$1-$2");
      e.target.value = v.slice(0, 18);
    });
    document.getElementById('telefone').addEventListener('input', function (e) {
      let v = e.target.value.replace(/\D/g, "");
      v = v.replace(/^(\d{2})(\d)/g, "($1) $2"); v = v.replace(/(\d{5})(\d)/, "$1-$2");
      e.target.value = v.slice(0, 15);
    });
  </script>
</body>

</html>