<?php
session_start();

if (!isset($_SESSION['nome_usuario']) || (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] != 1)) {
  header("Location: acesso.php");
  exit();
}

require_once "conexao.php";
$mensagem_swal = "";

// Carrega estabelecimentos para o Select
try {
  $stmtEst = $conn->query("SELECT id_estabelecimento, nome_estabelecimento FROM estabelecimento ORDER BY nome_estabelecimento ASC");
  $estabelecimentos = $stmtEst->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $estabelecimentos = [];
}

try {
  if (isset($_POST["cadastrar"])) {
    $nome = $_POST["nome_usuario"];
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);
    $tipo = $_POST["tipo_usuario"];
    $estab = $_POST["estabelecimento"];

    $sql = $conn->prepare("INSERT INTO usuarios (nome_usuario, senha, tipo_usuario, id_estabelecimento) VALUES (:nome, :senha, :tipo, :estab)");
    $sql->execute([':nome' => $nome, ':senha' => $senha, ':tipo' => $tipo, ':estab' => $estab]);

    $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Sucesso!', text: 'Usuário cadastrado.', showConfirmButton: false, timer: 1500 });";
  }

  if (isset($_GET['ex'])) {
    $id = $_GET['ex'];
    // Evita excluir o próprio usuário logado (opcional de segurança)
    if ($id != $_SESSION['id_usuario']) {
      $conn->prepare("DELETE FROM usuarios WHERE id_usuario = ?")->execute([$id]);
      header("Location: cad_usuario.php?msg=excluido");
      exit();
    } else {
      $mensagem_swal = "Swal.fire({ icon: 'warning', title: 'Atenção', text: 'Você não pode excluir seu próprio usuário.' });";
    }
  }

  if (isset($_GET['msg']) && $_GET['msg'] == 'excluido') {
    $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Excluído!', text: 'Usuário removido.', timer: 2000, showConfirmButton: false });";
  }

} catch (PDOException $erro) {
  $mensagem_swal = "Swal.fire({ icon: 'error', title: 'Erro!', text: '" . $erro->getMessage() . "' });";
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDV - Usuários</title>
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
        <li><a class="dropdown-item py-2 text-primary fw-bold" href="adm.php"><i class='bx bxs-dashboard'></i> Voltar ao
            Dashboard</a></li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a></li>
      </ul>
    </div>
  </div>

  <div class="main-container">
    <h2 class="page-title"><i class='bx bx-user-circle'></i> Gerenciar Usuários</h2>

    <div class="form-card">
      <h5 class="mb-4 text-primary fw-bold">Novo Usuário</h5>
      <form method="post">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Nome de Usuário</label>
            <input type="text" class="form-control" name="nome_usuario" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Tipo de Acesso</label>
            <select class="form-select" name="tipo_usuario" required>
              <option value="" disabled selected>Selecione...</option>
              <option value="0">Vendedor (Comum)</option>
              <option value="1">Administrador</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Senha de Acesso</label>
            <input type="password" class="form-control" name="senha" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Estabelecimento Vinculado</label>
            <select class="form-select" name="estabelecimento" required>
              <option value="" disabled selected>Selecione...</option>
              <?php foreach ($estabelecimentos as $est): ?>
                <option value="<?= $est['id_estabelecimento'] ?>"><?= htmlspecialchars($est['nome_estabelecimento']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 mt-4">
            <button type="submit" name="cadastrar" class="btn btn-primary w-100 btn-lg"><i class='bx bx-save'></i>
              Cadastrar Usuário</button>
          </div>
        </div>
      </form>
    </div>

    <?php
    $sql = "SELECT u.id_usuario, u.nome_usuario, u.tipo_usuario, e.nome_estabelecimento FROM usuarios u LEFT JOIN estabelecimento e ON u.id_estabelecimento = e.id_estabelecimento";
    $usuarios = $conn->query($sql);
    ?>

    <div class="table-container">
      <h5 class="mb-4 text-secondary fw-bold">Usuários do Sistema</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Usuário</th>
              <th>Loja</th>
              <th>Permissão</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $usuarios->fetch(PDO::FETCH_ASSOC)): ?>
              <tr>
                <td class="fw-bold text-dark"><?= htmlspecialchars($row['nome_usuario']); ?></td>
                <td class="text-muted"><?= htmlspecialchars($row['nome_estabelecimento'] ?? 'Sem loja'); ?></td>
                <td>
                  <?php if ($row['tipo_usuario'] == 1): ?>
                    <span class="badge bg-primary">Administrador</span>
                  <?php else: ?>
                    <span class="badge bg-secondary">Vendedor</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <a href="alt_usuario.php?al=<?= $row["id_usuario"]; ?>"
                    class="btn btn-sm btn-outline-primary border-0"><i class='bx bx-edit-alt fs-5'></i></a>
                  <a href="#" onclick="confirmarExclusao(<?= $row['id_usuario']; ?>)"
                    class="btn btn-sm btn-outline-danger border-0"><i class='bx bx-trash fs-5'></i></a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    <?php if (!empty($mensagem_swal))
      echo $mensagem_swal; ?>
    function confirmarExclusao(id) {
      Swal.fire({ title: 'Tem certeza?', text: "O usuário será removido.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6' }).then((result) => { if (result.isConfirmed) window.location.href = `?ex=${id}`; })
    }
  </script>
</body>

</html>