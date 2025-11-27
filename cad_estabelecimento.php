<?php
session_start();

if (!isset($_SESSION['nome_usuario']) || (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] != 1)) {
  header("Location: acesso.php");
  exit();
}

require_once "conexao.php";
$mensagem_swal = "";

try {
  if (isset($_POST["cadastrar"])) {
    $nome = $_POST["nome_estabelecimento"];
    $cep = $_POST["cep"];

    // Busca endereço via PHP (Backend) ou confia no JS? 
    // Para garantir, vamos usar os campos hidden ou preenchidos que o JS populou
    // Mas aqui no seu código original você usava a API no PHP. Vou manter o PHP para ser robusto.
    $rua = "";
    $bairro = "";
    $cidade = "";
    $estado = "";
    $url = "https://viacep.com.br/ws/$cep/json/";
    $response = @file_get_contents($url);
    if ($response) {
      $data = json_decode($response, true);
      if (isset($data['logradouro'])) {
        $rua = $data['logradouro'];
        $bairro = $data['bairro'];
        $cidade = $data['localidade'];
        $estado = $data['uf'];
      }
    }

    $sql = $conn->prepare("INSERT INTO estabelecimento (nome_estabelecimento, cep, rua, bairro, cidade, estado) VALUES (:nome, :cep, :rua, :bairro, :cid, :est)");
    $sql->execute([':nome' => $nome, ':cep' => $cep, ':rua' => $rua, ':bairro' => $bairro, ':cid' => $cidade, ':est' => $estado]);

    $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Sucesso!', text: 'Estabelecimento cadastrado.', showConfirmButton: false, timer: 1500 });";
  }

  if (isset($_GET['ex'])) {
    $id = $_GET['ex'];
    $conn->prepare("DELETE FROM estabelecimento WHERE id_estabelecimento = ?")->execute([$id]);
    header("Location: cad_estabelecimento.php?msg=excluido");
    exit();
  }

  if (isset($_GET['msg']) && $_GET['msg'] == 'excluido') {
    $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Excluído!', text: 'Loja removida.', timer: 2000, showConfirmButton: false });";
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
  <title>PDV - Lojas</title>
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
    <h2 class="page-title"><i class='bx bx-store-alt'></i> Gerenciar Lojas</h2>

    <div class="form-card">
      <h5 class="mb-4 text-primary fw-bold">Novo Estabelecimento</h5>
      <form method="post">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Nome da Loja</label>
            <input type="text" class="form-control" name="nome_estabelecimento" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">CEP</label>
            <div class="input-group">
              <input type="text" class="form-control" id="cep" name="cep" required>
              <button class="btn btn-outline-secondary" type="button" id="btnBuscarCep"><i
                  class='bx bx-search'></i></button>
            </div>
          </div>

          <!-- Campos de Endereço (Bloqueados apenas para visualização, o PHP busca de novo no server) -->
          <div class="col-md-6">
            <label class="form-label">Rua</label>
            <input type="text" class="form-control bg-light" id="rua" readonly>
          </div>
          <div class="col-md-3">
            <label class="form-label">Bairro</label>
            <input type="text" class="form-control bg-light" id="bairro" readonly>
          </div>
          <div class="col-md-3">
            <label class="form-label">Cidade/UF</label>
            <input type="text" class="form-control bg-light" id="cidade" readonly>
          </div>

          <div class="col-12 mt-4">
            <button type="submit" name="cadastrar" class="btn btn-primary w-100 btn-lg"><i class='bx bx-save'></i>
              Salvar Loja</button>
          </div>
        </div>
      </form>
    </div>

    <?php $lojas = $conn->query("SELECT * FROM estabelecimento"); ?>

    <div class="table-container">
      <h5 class="mb-4 text-secondary fw-bold">Lojas Cadastradas</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Loja</th>
              <th>Endereço</th>
              <th>Localidade</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $lojas->fetch(PDO::FETCH_ASSOC)): ?>
              <tr>
                <td class="fw-bold text-dark"><?= htmlspecialchars($row['nome_estabelecimento']); ?></td>
                <td class="text-muted small">
                  <?= htmlspecialchars($row['rua']); ?>, <?= htmlspecialchars($row['bairro']); ?> <br>
                  CEP: <?= htmlspecialchars($row['cep']); ?>
                </td>
                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['cidade']); ?> -
                    <?= htmlspecialchars($row['estado']); ?></span></td>
                <td class="text-center">
                  <a href="alt_estabelecimento.php?al=<?= $row["id_estabelecimento"]; ?>"
                    class="btn btn-sm btn-outline-primary border-0"><i class='bx bx-edit-alt fs-5'></i></a>
                  <a href="#" onclick="confirmarExclusao(<?= $row['id_estabelecimento']; ?>)"
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
      Swal.fire({ title: 'Tem certeza?', text: "A loja será removida.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6' }).then((result) => { if (result.isConfirmed) window.location.href = `?ex=${id}`; })
    }

    // Script de CEP (Visual Frontend)
    const cepInput = document.getElementById('cep');
    cepInput.addEventListener('input', (e) => {
      let v = e.target.value.replace(/\D/g, "");
      v = v.replace(/^(\d{5})(\d)/, "$1-$2");
      e.target.value = v.slice(0, 9);
    });
    cepInput.addEventListener('blur', () => {
      const cep = cepInput.value.replace(/\D/g, '');
      if (cep.length === 8) {
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
          .then(res => res.json())
          .then(data => {
            if (!data.erro) {
              document.getElementById('rua').value = data.logradouro;
              document.getElementById('bairro').value = data.bairro;
              document.getElementById('cidade').value = `${data.localidade} / ${data.uf}`;
            }
          });
      }
    });
  </script>
</body>

</html>