<?php
session_start();

// 1. Verificação de Login
if (!isset($_SESSION['nome_usuario'])) {
  header("Location: index.php");
  exit();
}

// 2. Verificação de Permissão (Apenas ADM)
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : 0;

if ($tipo_usuario != 1) {
  // Se não for ADM, redireciona para vendas ou mostra erro
  // Aqui vamos mostrar um erro amigável e um botão de voltar
  die('
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <title>Acesso Negado</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="d-flex align-items-center justify-content-center vh-100 bg-light">
            <div class="text-center">
                <h1 class="display-1 fw-bold text-danger">403</h1>
                <p class="fs-3"> <span class="text-danger">Opps!</span> Acesso Negado.</p>
                <p class="lead">Você não tem permissão para acessar esta página.</p>
                <a href="vendas.php" class="btn btn-primary">Voltar para Vendas</a>
            </div>
        </body>
        </html>
    ');
}

require_once "config/conexao.php";
$pdo = $conn; // Alias para manter compatibilidade


// Lógica de filtro
$where = [];
$params = [];

if (!empty($_GET['produto'])) {
  $where[] = "produto LIKE ?";
  $params[] = "%" . $_GET['produto'] . "%";
}
if (!empty($_GET['sabor'])) {
  $where[] = "sabor LIKE ?";
  $params[] = "%" . $_GET['sabor'] . "%";
}
if (!empty($_GET['data_inicial'])) {
  $where[] = "DATE(data) >= ?";
  $params[] = $_GET['data_inicial'];
}
if (!empty($_GET['data_final'])) {
  $where[] = "DATE(data) <= ?";
  $params[] = $_GET['data_final'];
}

$sql = "SELECT id_estoque, produto, sabor, tipo, estoque_atual, valor_custo, data FROM estoque WHERE movimentacao = 'Entrada'";

if ($where) {
  $sql .= " AND " . implode(" AND ", $where);
}

$sql .= " ORDER BY data DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$entradas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Histórico de Entradas</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
  <!-- Google Fonts (Inter) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Boxicons -->
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/style_estoque.css" />
  <link href="assets/css/dark_mode.css" rel="stylesheet">
</head>

<body>
  <!-- TOP BAR -->
  <div class="top-bar">
    <div class="d-flex align-items-center gap-3">
      <img src="assets/img/logoagilizasemfundo.png" alt="Logo PDV" style="height: 125px; width: auto;">
      <h4 class="m-0 fw-bold text-secondary d-none d-md-block">Histórico de Entradas</h4>
    </div>

    <div class="dropdown">
      <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
        <i class='bx bx-menu fs-3'></i>
      </button>
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

  <div class="container main-container">
    <h4 class="page-title">Histórico de Entradas no Estoque</h4>

    <!-- CARD DE FILTROS -->
    <div class="card-filter">
      <h6 class="text-muted mb-3 fw-bold text-uppercase small"><i class='bx bx-filter-alt'></i> Filtros de Busca</h6>
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fw-semibold">Produto / Sabor</label>
          <div class="input-group">
            <span class="input-group-text bg-light"><i class='bx bx-search'></i></span>
            <input type="text" name="produto" class="form-control" placeholder="Ex: Chocolate"
              value="<?= htmlspecialchars($_GET['produto'] ?? '') ?>" />
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Data Inicial</label>
          <input type="date" name="data_inicial" class="form-control"
            value="<?= htmlspecialchars($_GET['data_inicial'] ?? '') ?>" />
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold">Data Final</label>
          <input type="date" name="data_final" class="form-control"
            value="<?= htmlspecialchars($_GET['data_final'] ?? '') ?>" />
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100 fw-bold"><i class='bx bx-check'></i> Filtrar</button>
          <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary w-100" title="Limpar Filtros"><i
              class='bx bx-x'></i></a>
        </div>
      </form>
    </div>

    <!-- TABELA DE RESULTADOS -->
    <div class="table-container">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Data / Hora</th>
              <th>Produto</th>
              <th>Sabor</th>
              <th>Categoria</th>
              <th class="text-center">Qtd. Entrada</th>
              <th class="text-end">Custo Unit.</th>
              <th class="text-end">Custo Total</th>
              <th class="text-end">Editar</th>
            </tr>
          </thead>
          <tbody>
            <?php if (count($entradas) > 0): ?>
              <?php foreach ($entradas as $row): ?>
                <tr>
                  <td class="text-muted small">
                    <i class='bx bx-calendar'></i> <?= date('d/m/Y', strtotime($row['data'])) ?> <br>
                    <i class='bx bx-time'></i> <?= date('H:i', strtotime($row['data'])) ?>
                  </td>
                  <td class="fw-bold text-dark"><?= htmlspecialchars($row['produto']) ?></td>
                  <td><?= htmlspecialchars($row['sabor']) ?></td>
                  <td><span class="badge bg-light text-secondary border"><?= htmlspecialchars($row['tipo']) ?></span></td>
                  <td class="text-center">
                    <span
                      class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                      +<?= $row['estoque_atual'] ?>
                    </span>
                  </td>
                  <td class="text-end text-muted">R$ <?= number_format($row['valor_custo'], 2, ',', '.') ?></td>
                  <td class="text-end fw-bold text-primary">
                    R$ <?= number_format($row['valor_custo'] * $row['estoque_atual'], 2, ',', '.') ?>
                  </td>
                  <td class="text-end">
                    <a href="alt_estoque.php?id=<?= $row['id_estoque'] ?>" class="btn btn-sm btn-outline-primary"><i
                        class='bx bx-edit'></i> Editar</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center p-5 text-muted">
                  <i class='bx bx-search-alt fs-1 mb-3 opacity-25'></i>
                  <p class="mb-0">Nenhum registro encontrado para os filtros aplicados.</p>
                </td>
              </tr>
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
</body>

</html>