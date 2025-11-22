<?php
// 1. INÍCIO SEGURO (Sessão + Permissão)
session_start();

// Se não estiver logado, tchau!
if (!isset($_SESSION['nome_usuario'])) {
  header("Location: acesso.php");
  exit();
}

// Se não for ADM (Tipo 1), bloqueia!
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : 0;
if ($tipo_usuario != 1) {
  // Exibe um alerta e redireciona (usando script simples pois o cabeçalho HTML ainda não carregou)
  echo "<script>alert('Acesso Negado: Apenas administradores.'); window.location.href='vendas.php';</script>";
  exit();
}

require_once "conexao.php";

$mensagem_swal = ""; // Variável para guardar o script do SweetAlert

try {
  // --- CADASTRAR PRODUTO ---
  if (isset($_POST["cadastrar"])) {
    $nome = trim($_POST["nome"]);
    $sabor = trim($_POST["sabor"]);
    $id_categoria = (int) $_POST["id_categoria"];
    $id_fornecedor = (int) $_POST["id_fornecedor"];
    $preco_venda = str_replace(',', '.', $_POST["preco_venda"]);
    $preco_compra = str_replace(',', '.', $_POST["preco_compra"]);

    $ins = $conn->prepare("INSERT INTO produto (nome, sabor, id_categoria, id_fornecedor, preco_venda, preco_compra)
                               VALUES (:nome, :sabor, :id_categoria, :id_fornecedor, :preco_venda, :preco_compra)");
    $ins->bindValue(':nome', $nome);
    $ins->bindValue(':sabor', $sabor);
    $ins->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
    $ins->bindValue(':id_fornecedor', $id_fornecedor, PDO::PARAM_INT);
    $ins->bindValue(':preco_venda', $preco_venda);
    $ins->bindValue(':preco_compra', $preco_compra);

    if ($ins->execute()) {
      $mensagem_swal = "
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: 'Produto cadastrado corretamente.',
                    showConfirmButton: false,
                    timer: 1500
                });
            ";
    }
  }

  // --- EXCLUIR PRODUTO ---
  if (isset($_GET["ex"])) {
    $id = (int) $_GET["ex"];
    $del = $conn->prepare("DELETE FROM produto WHERE id_produto = :id_produto");
    $del->bindValue(':id_produto', $id, PDO::PARAM_INT);

    if ($del->execute()) {
      // Redireciona para limpar a URL (evita excluir de novo ao atualizar)
      header("Location: cad_produto.php?msg=excluido");
      exit();
    }
  }

  // Captura mensagem de exclusão via GET (após o redirect)
  if (isset($_GET['msg']) && $_GET['msg'] == 'excluido') {
    $mensagem_swal = "
            Swal.fire({
                icon: 'success',
                title: 'Excluído!',
                text: 'O produto foi removido com sucesso.',
                timer: 2000,
                showConfirmButton: false
            });
        ";
  }

} catch (PDOException $e) {
  $erro_msg = addslashes($e->getMessage());
  $mensagem_swal = "
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: '$erro_msg'
        });
    ";
}

// --- CARREGAR DADOS PARA OS SELECTS E TABELA ---
try {
  $stmtCat = $conn->prepare("SELECT id_categoria, nome_categoria FROM categoria ORDER BY nome_categoria ASC");
  $stmtCat->execute();
  $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

  $stmtFor = $conn->prepare("SELECT id_fornecedor, nome_fornecedor FROM fornecedor ORDER BY nome_fornecedor ASC");
  $stmtFor->execute();
  $fornecedores = $stmtFor->fetchAll(PDO::FETCH_ASSOC);

  $sql = "SELECT p.id_produto AS id_produto, p.nome, p.sabor, p.preco_venda, p.preco_compra,
                 c.nome_categoria, f.nome_fornecedor
          FROM produto p
          LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
          LEFT JOIN fornecedor f ON p.id_fornecedor = f.id_fornecedor
          ORDER BY p.id_produto DESC";
  $stmt = $conn->prepare($sql);
  $stmt->execute();
  $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  $produtos = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>PDV - Cadastro de Produtos</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Boxicons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

  <!-- CSS Padronizado -->
  <link href="styles/style_cad.css" rel="stylesheet">
</head>

<body>

  <!-- TOP BAR -->
  <div class="top-bar">
    <div class="d-flex align-items-center gap-3">
      <img src="img/logoagilizasemfundo.png" class="logo" alt="Logo PDV">
      <h5 class="m-0 fw-bold text-secondary d-none d-md-block">Administrativo</h5>
    </div>

    <div class="dropdown">
      <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class='bx bx-menu fs-3'></i>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow border-0">
        <li><a class="dropdown-item py-2" href="adm.php"><i class='bx bxs-dashboard'></i> Voltar ao Painel</a></li>
        <li>
          <hr class="dropdown-divider">
        </li>
        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a></li>
      </ul>
    </div>
  </div>

  <div class="main-container">
    <h2 class="page-title"><i class='bx bx-package'></i> Gerenciar Produtos</h2>

    <!-- FORMULÁRIO DE CADASTRO -->
    <div class="form-card">
      <h5 class="mb-4 text-primary fw-bold">Novo Produto</h5>

      <form name="formProduto" method="post" action="">
        <div class="row g-3">
          <!-- Nome -->
          <div class="col-md-6">
            <label for="nome" class="form-label">Nome do Produto</label>
            <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: Sorvete de Massa" required>
          </div>

          <!-- Sabor -->
          <div class="col-md-6">
            <label for="sabor" class="form-label">Sabor / Variação</label>
            <input type="text" class="form-control" id="sabor" name="sabor" placeholder="Ex: Chocolate Belga" required>
          </div>

          <!-- Categoria -->
          <div class="col-md-6">
            <label for="id_categoria" class="form-label">Categoria</label>
            <select id="id_categoria" name="id_categoria" class="form-select" required>
              <option value="" selected disabled>Selecione...</option>
              <?php foreach ($categorias as $row): ?>
                <option value="<?= (int) $row['id_categoria']; ?>">
                  <?= htmlspecialchars($row['nome_categoria']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Fornecedor -->
          <div class="col-md-6">
            <label for="id_fornecedor" class="form-label">Fornecedor</label>
            <select id="id_fornecedor" name="id_fornecedor" class="form-select" required>
              <option value="" selected disabled>Selecione...</option>
              <?php foreach ($fornecedores as $row): ?>
                <option value="<?= (int) $row['id_fornecedor']; ?>">
                  <?= htmlspecialchars($row['nome_fornecedor']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Preço Venda -->
          <div class="col-md-6">
            <label for="preco_venda" class="form-label">Preço de Venda (R$)</label>
            <div class="input-group">
              <span class="input-group-text text-success fw-bold">R$</span>
              <input type="text" class="form-control" id="preco_venda" name="preco_venda" placeholder="0,00" required>
            </div>
          </div>

          <!-- Preço Compra -->
          <div class="col-md-6">
            <label for="preco_compra" class="form-label">Preço de Compra (R$)</label>
            <div class="input-group">
              <span class="input-group-text text-danger fw-bold">R$</span>
              <input type="text" class="form-control" id="preco_compra" name="preco_compra" placeholder="0,00" required>
            </div>
          </div>

          <!-- Botão Salvar -->
          <div class="col-12 mt-4">
            <button type="submit" name="cadastrar" class="btn btn-primary w-100 btn-lg">
              <i class='bx bx-save'></i> Cadastrar Produto
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- TABELA DE PRODUTOS -->
    <div class="table-container">
      <h5 class="mb-4 text-secondary fw-bold">Produtos Cadastrados</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Produto</th>
              <th>Sabor</th>
              <th>Categoria</th>
              <th>Fornecedor</th>
              <th class="text-end">Venda</th>
              <th class="text-center">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($produtos)): ?>
              <?php foreach ($produtos as $produto): ?>
                <tr>
                  <td class="fw-bold text-muted">#<?= htmlspecialchars($produto['id_produto']); ?></td>
                  <td class="fw-bold text-dark"><?= htmlspecialchars($produto['nome']); ?></td>
                  <td><?= htmlspecialchars($produto['sabor']); ?></td>
                  <td><span
                      class="badge bg-light text-dark border"><?= htmlspecialchars($produto['nome_categoria'] ?? '-'); ?></span>
                  </td>
                  <td class="small text-muted"><?= htmlspecialchars($produto['nome_fornecedor'] ?? '-'); ?></td>
                  <td class="text-end fw-bold text-success">R$ <?= number_format($produto['preco_venda'], 2, ',', '.'); ?>
                  </td>

                  <td class="text-center">
                    <a href="alt_produto.php?al=<?= (int) $produto['id_produto']; ?>"
                      class="btn btn-sm btn-outline-primary border-0" title="Editar">
                      <i class='bx bx-edit-alt fs-5'></i>
                    </a>

                    <!-- Botão Excluir com Confirmação JS melhorada -->
                    <a href="#" onclick="confirmarExclusao(<?= (int) $produto['id_produto']; ?>)"
                      class="btn btn-sm btn-outline-danger border-0" title="Excluir">
                      <i class='bx bx-trash fs-5'></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center p-5 text-muted">
                  <i class='bx bx-box fs-1 opacity-25'></i>
                  <p class="mt-2">Nenhum produto cadastrado ainda.</p>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Scripts Bootstrap & SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Script para exibir mensagens do PHP -->
  <script>
    <?php if (!empty($mensagem_swal))
      echo $mensagem_swal; ?>

    // Função JS para confirmar exclusão com SweetAlert
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
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = `?ex=${id}`;
        }
      })
    }
  </script>

</body>

</html>