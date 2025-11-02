<?php
$host = 'localhost';
$dbname = 'banco';
$user = 'root';
$pass = '';

try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Erro na conexão: " . $e->getMessage());
}

// Lógica de filtro (com ajuste para datas)
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
if (!empty($_GET['tipo'])) {
  $where[] = "tipo = ?";
  $params[] = $_GET['tipo'];
}
if (!empty($_GET['data_inicial'])) {
  $where[] = "DATE(data) >= ?";
  $params[] = $_GET['data_inicial'];
}
if (!empty($_GET['data_final'])) {
  $where[] = "DATE(data) <= ?";
  $params[] = $_GET['data_final'];
}

$sql = "SELECT produto, sabor, tipo, estoque_atual, valor_custo, data FROM estoque WHERE movimentacao = 'Entrada'";

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

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --primary-blue: #4682B4;
      --background-light: #f8f9fa;
      --white: #ffffff;
      --text-color: #212529;
      --border-color: #dee2e6;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      --border-radius: 0.75rem;
      /* 12px */
    }

    body {
      background-color: var(--primary-blue);
      font-family: 'Roboto', sans-serif;
      color: var(--text-color);
    }

    .top-bar {
      background-color: var(--white);
      box-shadow: var(--shadow);
    }

    .top-bar .logo {
      height: 50px;
    }

    .main-container {
      background-color: var(--white);
      border-radius: var(--border-radius);
      padding: 2rem;
      margin: 2rem auto;
      max-width: 1200px;
      box-shadow: var(--shadow);
    }

    .page-title {
      color: var(--primary-blue);
      font-weight: 700;
      text-align: center;
      margin-bottom: 2rem;
    }

    .form-card {
      background-color: var(--background-light);
      border: 1px solid var(--border-color);
      border-radius: var(--border-radius);
      padding: 1.5rem;
    }

    .table {
      border-color: var(--border-color);
    }

    .table thead {
      background-color: var(--background-light);
    }

    .table>thead>tr>th {
      color: var(--primary-blue);
      font-weight: 600;
      text-transform: uppercase;
    }

    .table-hover tbody tr:hover {
      background-color: #eef5ff;
    }
  </style>
</head>

<body>
  <div class="top-bar d-flex align-items-center justify-content-between px-3 py-2">
    <img src="img/logo pdv.png" class="logo" alt="Logo PDV" style="height:50px;">
    <div class="dropdown">
      <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="img/3riscos.png" alt="Menu" style="height:25px;">
      </button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="vendas.php">Vendas (PDV)</a></li>
        <li><a class="dropdown-item" href="estoque.php">Gestão de Estoque</a></li>
        <li><a class="dropdown-item" href="adm.php">Painel Administrativo</a></li>
      </ul>
    </div>
  </div>

  <div class="container main-container">
    <h2 class="page-title">Histórico de Entradas no Estoque</h2>

    <div class="form-card mb-4">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Produto</label>
          <input type="text" name="produto" class="form-control" placeholder="Nome do produto"
            value="<?= htmlspecialchars($_GET['produto'] ?? '') ?>" />
        </div>
        <div class="col-md-3">
          <label class="form-label">Data Inicial</label>
          <input type="date" name="data_inicial" class="form-control"
            value="<?= htmlspecialchars($_GET['data_inicial'] ?? '') ?>" />
        </div>
        <div class="col-md-3">
          <label class="form-label">Data Final</label>
          <input type="date" name="data_final" class="form-control"
            value="<?= htmlspecialchars($_GET['data_final'] ?? '') ?>" />
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100">Filtrar</button>
          <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary w-100">Limpar</a>
        </div>
      </form>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-hover table-bordered text-center align-middle">
        <thead>
          <tr>
            <th>Data</th>
            <th>Produto</th>
            <th>Sabor</th>
            <th>Categoria</th>
            <th>Quantidade</th>
            <th>Custo (R$)</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($entradas) > 0): ?>
            <?php foreach ($entradas as $row): ?>
              <tr>
                <td><?= date('d/m/Y H:i', strtotime($row['data'])) ?></td>
                <td><?= htmlspecialchars($row['produto']) ?></td>
                <td><?= htmlspecialchars($row['sabor']) ?></td>
                <td><?= htmlspecialchars($row['tipo']) ?></td>
                <td class="fw-bold"><?= $row['estoque_atual'] ?></td>
                <td><?= number_format($row['valor_custo'], 2, ',', '.') ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="p-4">Nenhum registro de entrada encontrado para os filtros aplicados.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>