<?php
session_start();

if (!isset($_SESSION['nome_usuario']) || (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] != 1)) {
  header("Location: acesso.php");
  exit();
}

require_once "config/conexao.php";
$mensagem_swal = "";
$dados = [];

try {
  $estabelecimentos = $conn->query("SELECT * FROM estabelecimento WHERE ativo = 1 ORDER BY nome_estabelecimento")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $estabelecimentos = [];
}

if (isset($_GET["al"])) {
  $id = (int) $_GET["al"];
  $stmt = $conn->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
  $stmt->bindValue(':id', $id);
  $stmt->execute();
  $dados = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$dados) {
    header("Location: cad_usuario.php");
    exit;
  }
}

if (isset($_POST["alterar"])) {
  $id = $_POST["id_usuario"];
  $nome = $_POST["nome_usuario"];
  $tipo = $_POST["tipo_usuario"];
  $estab = $_POST["id_estabelecimento"];
  $senha = $_POST["senha"];

  try {
    // Lógica: Se senha estiver vazia, não atualiza a senha
    if (!empty($senha)) {
      $hash = password_hash($senha, PASSWORD_DEFAULT);
      $sql = $conn->prepare("UPDATE usuarios SET nome_usuario=:nome, tipo_usuario=:tipo, id_estabelecimento=:estab, senha=:senha WHERE id_usuario=:id");
      $sql->execute([':nome' => $nome, ':tipo' => $tipo, ':estab' => $estab, ':senha' => $hash, ':id' => $id]);
    } else {
      $sql = $conn->prepare("UPDATE usuarios SET nome_usuario=:nome, tipo_usuario=:tipo, id_estabelecimento=:estab WHERE id_usuario=:id");
      $sql->execute([':nome' => $nome, ':tipo' => $tipo, ':estab' => $estab, ':id' => $id]);
    }

    $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Atualizado!', text: 'Usuário alterado com sucesso.', timer: 2000, showConfirmButton: false }).then(() => { window.location.href = 'cad_usuario.php'; });";
  } catch (PDOException $erro) {
    $mensagem_swal = "Swal.fire({ icon: 'error', title: 'Erro', text: '" . $erro->getMessage() . "' });";
  }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>PDV - Alterar Usuário</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/css/style_cad.css" rel="stylesheet">
  <link href="assets/css/dark_mode.css" rel="stylesheet">
</head>

<body>
  <div class="top-bar">
    <div class="d-flex align-items-center gap-3">
      <img src="assets/img/logoagilizasemfundo.png" class="logo" alt="Logo">
      <h5 class="m-0 fw-bold text-secondary d-none d-md-block">Administrativo</h5>
    </div>
    <div class="dropdown">
      <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown"><i
          class='bx bx-menu fs-3'></i></button>
      <ul class="dropdown-menu dropdown-menu-end shadow border-0">
        <li><a class="dropdown-item py-2 text-primary fw-bold" href="cad_usuario.php"><i class='bx bx-arrow-back'></i>
            Voltar</a></li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li>
          <button type="button" class="dropdown-item py-2 text-dark fw-bold" data-bs-toggle="modal"
            data-bs-target="#settingsModal">
            <i class='bx bx-cog'></i> Configurações
          </button>
        </li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a></li>
      </ul>
    </div>
  </div>

  <div class="main-container">
    <h2 class="page-title"><i class='bx bx-edit'></i> Alterar Usuário</h2>
    <div class="form-card">
      <form method="POST">
        <input type="hidden" name="id_usuario" value="<?= $dados['id_usuario'] ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nome</label>
            <input type="text" class="form-control" name="nome_usuario"
              value="<?= htmlspecialchars($dados['nome_usuario']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tipo de Acesso</label>
            <select class="form-select" name="tipo_usuario" required>
              <option value="0" <?= ($dados['tipo_usuario'] == 0) ? 'selected' : '' ?>>Vendedor (Comum)</option>
              <option value="1" <?= ($dados['tipo_usuario'] == 1) ? 'selected' : '' ?>>Administrador</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Nova Senha (Opcional)</label>
            <input type="password" class="form-control" name="senha" placeholder="Deixe em branco para manter a atual">
          </div>
          <div class="col-md-6">
            <label class="form-label">Estabelecimento</label>
            <select class="form-select" name="id_estabelecimento" required>
              <?php foreach ($estabelecimentos as $est): ?>
                <option value="<?= $est['id_estabelecimento'] ?>"
                  <?= ($dados['id_estabelecimento'] == $est['id_estabelecimento']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($est['nome_estabelecimento']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 mt-4">
            <button type="submit" name="alterar" class="btn btn-primary w-100 btn-lg"><i class='bx bx-save'></i> Salvar
              Alterações</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Configurações -->
  <div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title fw-bold"><i class='bx bx-cog'></i> Configurações</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h6 class="mb-0 fw-bold">Modo Escuro</h6>
              <small class="text-muted">Alternar entre tema claro e escuro</small>
            </div>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="themeToggle"
                style="width: 3em; height: 1.5em; cursor: pointer;">
              <label class="form-check-label ms-2" for="themeToggle"><i id="themeIcon"
                  class="bx bx-sun fs-4 text-warning"></i></label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/settings.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script> <?php if (!empty($mensagem_swal))
    echo $mensagem_swal; ?> </script>
</body>

</html>