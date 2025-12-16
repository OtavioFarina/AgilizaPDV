<?php
session_start();

// 1. Segurança
if (!isset($_SESSION['nome_usuario'])) {
  header("Location: acesso.php");
  exit();
}

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : 0;
if ($tipo_usuario != 1) {
  echo "<script>alert('Acesso Negado: Apenas administradores.'); window.location.href='vendas.php';</script>";
  exit();
}

require_once "config/conexao.php";
$mensagem_swal = "";

try {
  // CADASTRAR
  if (isset($_POST["cadastrar"])) {
    $nome = $_POST["nome"];

    $sql = $conn->prepare("INSERT INTO categoria (id_categoria, nome_categoria) VALUES(NULL, :nome_categoria)");
    $sql->bindValue(':nome_categoria', $nome);

    if ($sql->execute()) {
      $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Sucesso!', text: 'Categoria cadastrada.', showConfirmButton: false, timer: 1500 });";
    }
  }

  // EXCLUIR
  if (isset($_GET["ex"])) {
    $id_categoria = $_GET["ex"];

    // Verifica se tem produtos vinculados antes de excluir (Opcional, mas recomendado)
    // O banco geralmente barra por Foreign Key, vamos tratar o erro no catch
    // Soft Delete: Atualiza para inativo em vez de excluir
    $sql = $conn->prepare("UPDATE categoria SET ativo = 0 WHERE id_categoria = :id_categoria");
    $sql->bindValue(":id_categoria", $id_categoria);

    if ($sql->execute()) {
      header("Location: cad_categoria.php?msg=excluido");
      exit();
    }
  }

  if (isset($_GET['msg']) && $_GET['msg'] == 'excluido') {
    $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Excluído!', text: 'Categoria removida.', timer: 2000, showConfirmButton: false });";
  }

} catch (PDOException $erro) {
  // Tratamento específico para erro de chave estrangeira (tentar excluir categoria em uso)
  if ($erro->getCode() == '23000') {
    $msg_erro = "Não é possível excluir esta categoria pois existem produtos vinculados a ela.";
  } else {
    $msg_erro = $erro->getMessage();
  }
  $mensagem_swal = "Swal.fire({ icon: 'error', title: 'Erro!', text: '$msg_erro' });";
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDV - Categorias</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/css/style_cad.css" rel="stylesheet">
  <link href="assets/css/dark_mode.css" rel="stylesheet">
</head>

<body>

  <div class="top-bar">
    <div class="d-flex align-items-center gap-3">
      <img src="assets/img/logoagilizasemfundo.png" class="logo" alt="Logo PDV">
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
    <h2 class="page-title"><i class='bx bx-category'></i> Gerenciar Categorias</h2>

    <div class="form-card">
      <h5 class="mb-4 text-primary fw-bold">Nova Categoria</h5>
      <form method="post" action="">
        <div class="row align-items-end">
          <div class="col-md-10 mb-3">
            <label for="nome" class="form-label">Nome da Categoria</label>
            <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: Picolés Gourmet" required>
          </div>
          <div class="col-md-2 mb-3">
            <button type="submit" name="cadastrar" class="btn btn-primary w-100"><i class='bx bx-plus'></i>
              Salvar</button>
          </div>
        </div>
      </form>
    </div>

    <?php
    try {
      $lista = $conn->query("SELECT * FROM categoria WHERE ativo = 1 ORDER BY nome_categoria ASC");
    } catch (PDOException $e) {
      $lista = null;
    }
    ?>

    <div class="table-container">
      <h5 class="mb-4 text-secondary fw-bold">Categorias Cadastradas</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nome</th>
              <th class="text-center" style="width: 150px;">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($lista): ?>
              <?php while ($row = $lista->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                  <td class="fw-bold text-muted">#<?= htmlspecialchars($row['id_categoria']); ?></td>
                  <td class="fw-bold text-dark"><?= htmlspecialchars($row['nome_categoria']); ?></td>
                  <td class="text-center">
                    <a href="alt_categoria.php?al=<?= $row["id_categoria"]; ?>"
                      class="btn btn-sm btn-outline-primary border-0"><i class='bx bx-edit-alt fs-5'></i></a>
                    <a href="#" onclick="confirmarExclusao(<?= $row['id_categoria']; ?>)"
                      class="btn btn-sm btn-outline-danger border-0"><i class='bx bx-trash fs-5'></i></a>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
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
  <script>
    <?php if (!empty($mensagem_swal))
      echo $mensagem_swal; ?>
    function confirmarExclusao(id) {
      Swal.fire({
        title: 'Tem certeza?',
        text: "Você não poderá reverter isso!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
      }).then((result) => { if (result.isConfirmed) { window.location.href = `?ex=${id}`; } })
    }
  </script>
</body>

</html>