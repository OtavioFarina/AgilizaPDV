<?php
session_start();

// Proteção: Só ADM pode acessar
if (!isset($_SESSION['id_usuario']) || $_SESSION['tipo_usuario'] != 1) {
    header("Location: vendas.php");
    exit();
}

require_once "conexao.php";

// Definir fuso horário para garantir datas corretas
date_default_timezone_set('America/Sao_Paulo');
$mesAtual = date('m');
$anoAtual = date('Y');
$hoje = date('Y-m-d');

// A. Faturamento do Dia
$sqlDia = "SELECT SUM(valor_total) as total_dia FROM vendas WHERE DATE(data_hora) = :hoje AND status = 'finalizada'";
$stmt = $conn->prepare($sqlDia);
$stmt->bindValue(':hoje', $hoje);
$stmt->execute();
$faturamentoDia = $stmt->fetchColumn() ?: 0.00;

// B. Faturamento do Mês
$sqlMes = "SELECT SUM(valor_total) as total_mes FROM vendas WHERE MONTH(data_hora) = :mes AND YEAR(data_hora) = :ano AND status = 'finalizada'";
$stmt = $conn->prepare($sqlMes);
$stmt->bindValue(':mes', $mesAtual);
$stmt->bindValue(':ano', $anoAtual);
$stmt->execute();
$faturamentoMes = $stmt->fetchColumn() ?: 0.00;

// C. Produtos com Estoque Baixo (Seguindo sua lógica de alerta <= 5)
// Precisamos somar entradas e subtrair saídas agrupando por produto
$sqlEstoque = "
    SELECT COUNT(*) as qtd_baixo_estoque FROM (
        SELECT 
            produto, sabor, tipo,
            SUM(CASE WHEN movimentacao = 'Entrada' THEN estoque_atual 
                     WHEN movimentacao = 'Saída' THEN -estoque_atual 
                END) as saldo_final
        FROM estoque 
        GROUP BY produto, sabor, tipo
        HAVING saldo_final <= 5
    ) as subquery
";
$stmt = $conn->query($sqlEstoque);
$qtdBaixoEstoque = $stmt->fetchColumn() ?: 0;

// D. Dados para o Gráfico (Vendas por dia no mês atual)
$sqlGrafico = "
    SELECT DATE(data_hora) as dia, SUM(valor_total) as total 
    FROM vendas 
    WHERE MONTH(data_hora) = :mes AND YEAR(data_hora) = :ano AND status = 'finalizada'
    GROUP BY DATE(data_hora) 
    ORDER BY dia ASC
";
$stmt = $conn->prepare($sqlGrafico);
$stmt->bindValue(':mes', $mesAtual);
$stmt->bindValue(':ano', $anoAtual);
$stmt->execute();
$dadosGrafico = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar Arrays para o JavaScript
$labelsGrafico = [];
$valoresGrafico = [];

// Preenche os dias do mês no gráfico
foreach ($dadosGrafico as $dado) {
  $labelsGrafico[] = date('d/m', strtotime($dado['dia']));
  $valoresGrafico[] = $dado['total'];
}
?>
<!DOCTYPE html>>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDV - Dashboard Gerencial</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="styles/style_adm.css">
</head>

<body>

  <div class="top-bar d-flex align-items-center justify-content-between px-4 py-2">
    <img src="img/logo pdv.png" class="logo" alt="Logo PDV" style="height:50px;">
    <div class="dropdown">
      <button class="btn" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="img/3riscos.png" alt="Menu" style="height:25px;">
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
        <li><a class="dropdown-item" href="vendas.php">Ir para o PDV</a></li>
        <li><a class="dropdown-item" href="estoque.php">Gestão de Estoque</a></li>
        <li><a class="dropdown-item" href="consulta_caixa.php">Relatório de Caixa</a></li>
        <li><a class="dropdown-item" href="logout.php">Sair da Conta</a></li>
      </ul>
    </div>
  </div>

  <div class="container py-5">

    <h2 class="mb-4 fw-bold text-secondary">Dashboard Administrativo</h2>

    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="kpi-card position-relative">
          <div class="kpi-title">Vendas de Hoje</div>
          <div class="kpi-value text-success">R$ <?= number_format($faturamentoDia, 2, ',', '.') ?></div>
          <i class='bx bx-money kpi-icon'></i>
        </div>
      </div>

      <div class="col-md-4">
        <div class="kpi-card position-relative" style="border-left-color: #6610f2;">
          <div class="kpi-title">Faturamento Mensal</div>
          <div class="kpi-value">R$ <?= number_format($faturamentoMes, 2, ',', '.') ?></div>
          <i class='bx bx-calendar-check kpi-icon' style="color: #6610f2;"></i>
        </div>
      </div>

      <div class="col-md-4">
        <div class="kpi-card position-relative" style="border-left-color: #dc3545;">
          <div class="kpi-title">Estoque Baixo (≤ 5)</div>
          <div class="kpi-value text-danger"><?= $qtdBaixoEstoque ?> <small style="font-size: 1rem;">itens</small></div>
          <i class='bx bx-error-circle kpi-icon' style="color: #dc3545;"></i>
        </div>
      </div>
    </div>

    <div class="row mb-5">
      <div class="col-12">
        <div class="chart-container">
          <h5 class="mb-4">Evolução de Vendas (Mês Atual)</h5>
          <canvas id="salesChart" style="max-height: 300px;"></canvas>
        </div>
      </div>
    </div>

    <h4 class="section-title"><i class='bx bx-grid-alt'></i> Gestão e Cadastros</h4>
    <div class="row g-3">

      <div class="col-6 col-md-3 col-lg-2">
        <a href="cad_produto.php" class="btn-quick text-decoration-none">
          <i class='bx bx-package'></i>
          Produtos
        </a>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <a href="cad_categoria.php" class="btn-quick text-decoration-none">
          <i class='bx bx-category'></i>
          Categorias
        </a>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <a href="cad_fornecedor.php" class="btn-quick text-decoration-none">
          <i class='bx bx-box'></i>
          Fornecedores
        </a>
      </div>

      <div class="col-6 col-md-3 col-lg-2">
        <a href="cad_usuario.php" class="btn-quick text-decoration-none">
          <i class='bx bx-user-circle'></i>
          Usuários
        </a>
      </div>
      <div class="col-6 col-md-3 col-lg-2">
        <a href="cad_estabelecimento.php" class="btn-quick text-decoration-none">
          <i class='bx bx-store-alt'></i>
          Lojas
        </a>
      </div>

      <div class="col-6 col-md-3 col-lg-2">
        <a href="estoque.php" class="btn-quick text-decoration-none" ;">
          <i class='bx bx-bar-chart-alt-2'></i>
          Ver Estoque
        </a>
      </div>
    </div>

  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const ctx = document.getElementById('salesChart').getContext('2d');

    // Pegando os dados que vieram do PHP
    const labels = <?= json_encode($labelsGrafico) ?>;
    const dataValues = <?= json_encode($valoresGrafico) ?>;

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Vendas no Dia (R$)',
          data: dataValues,
          borderColor: '#4682B4', // Sua cor Azul
          backgroundColor: 'rgba(70, 130, 180, 0.1)',
          borderWidth: 3,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#4682B4',
          pointRadius: 5,
          fill: true,
          tension: 0.4 // Deixa a linha curvinha e suave
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false // Esconde a legenda pra ficar mais limpo
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              borderDash: [5, 5]
            }
          },
          x: {
            grid: {
              display: false
            }
          }
        }
      }
    });
  </script>

</body>

</html>