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

// --- CONSULTAS KPI (CARDS) ---

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

// C. Produtos com Estoque Baixo (Seguindo lógica de alerta <= 5)
$sqlEstoque = "
    SELECT produto, sabor, tipo,
        SUM(CASE WHEN movimentacao = 'Entrada' THEN estoque_atual 
                 WHEN movimentacao = 'Saída' THEN -estoque_atual 
            END) as saldo_final
    FROM estoque 
    GROUP BY produto, sabor, tipo
    HAVING saldo_final <= 5
    ORDER BY saldo_final ASC
";
$stmtEstoque = $conn->query($sqlEstoque);
$produtosBaixoEstoque = $stmtEstoque->fetchAll(PDO::FETCH_ASSOC);
$qtdBaixoEstoque = count($produtosBaixoEstoque);

// --- CONSULTAS PARA GRÁFICOS ---

// D. Gráfico 1: Evolução de Vendas (Linha - Dias do Mês)
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

$labelsGrafico = [];
$valoresGrafico = [];
foreach ($dadosGrafico as $dado) {
  $labelsGrafico[] = date('d/m', strtotime($dado['dia']));
  $valoresGrafico[] = $dado['total'];
}

// E. Gráfico 2: Vendas por Categoria (Rosca)
// Nota: Precisamos unir saida_produtos -> produto -> categoria -> vendas (para filtrar mês/status)
$sqlCategoria = "
    SELECT c.nome_categoria, SUM(sp.quantidade) as qtd_vendida
    FROM saida_produtos sp
    JOIN produto p ON sp.id_produto = p.id_produto
    JOIN categoria c ON p.id_categoria = c.id_categoria
    JOIN vendas v ON sp.venda_id = v.id_venda
    WHERE MONTH(v.data_hora) = :mes AND YEAR(v.data_hora) = :ano AND v.status = 'finalizada'
    GROUP BY c.nome_categoria
";
$stmtCat = $conn->prepare($sqlCategoria);
$stmtCat->bindValue(':mes', $mesAtual);
$stmtCat->bindValue(':ano', $anoAtual);
$stmtCat->execute();
$dadosCat = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

$labelsCat = [];
$valoresCat = [];
foreach ($dadosCat as $d) {
  $labelsCat[] = $d['nome_categoria'];
  $valoresCat[] = $d['qtd_vendida'];
}

// F. Gráfico 3: Vendas por Forma de Pagamento (Barra ou Pizza)
$sqlPagamento = "
    SELECT f.nome_pagamento, SUM(v.valor_total) as total
    FROM vendas v
    JOIN forma_pagamento f ON v.id_forma_pagamento = f.id_forma_pagamento
    WHERE MONTH(v.data_hora) = :mes AND YEAR(v.data_hora) = :ano AND v.status = 'finalizada'
    GROUP BY f.nome_pagamento
";
$stmtPag = $conn->prepare($sqlPagamento);
$stmtPag->bindValue(':mes', $mesAtual);
$stmtPag->bindValue(':ano', $anoAtual);
$stmtPag->execute();
$dadosPag = $stmtPag->fetchAll(PDO::FETCH_ASSOC);

$labelsPag = [];
$valoresPag = [];
foreach ($dadosPag as $d) {
  $labelsPag[] = $d['nome_pagamento'];
  $valoresPag[] = $d['total'];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDV - Dashboard Administrativo</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="styles/style_adm.css">
</head>

<body>
  <div class="top-bar d-flex align-items-center justify-content-between px-4 py-2">
    <div class="d-flex align-items-center gap-3">
      <img src="img/logoagilizasemfundo.png" class="logo" alt="Logo PDV">
      <h4 class="m-0 fw-bold text-secondary d-none d-md-block">Dashboard Administrativo</h4>
    </div>
    <div class="dropdown">
      <button class="btn" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="img/3riscos.png" alt="Menu" style="height:25px;">
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
        <li><a class="dropdown-item py-2" href="vendas.php"><i class='bx bx-cart'></i> Vendas
            (PDV)</a></li>
        <li><a class="dropdown-item py-2" href="historico_entradas.php"><i class='bx bx-package'></i> Histórico de
            Entradas</a></li>
        <li><a class="dropdown-item py-2" href="historico_vendas.php"><i class='bx bx-history'></i>
            Histórico de Vendas</a></li>
        <li><a class="dropdown-item py-2" href="consulta_caixa.php"><i class="bx bx-basket"></i>
            Relatório de Caixa</a></li>
        <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a></li>
      </ul>
    </div>
  </div>

  <div class="container py-5">

    <!-- LINHA DE CARDS (KPIs) -->
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
        <div class="kpi-card position-relative" style="border-left-color: #dc3545; cursor: pointer;" id="btnEstoqueBaixo" role="button">
          <div class="kpi-title">Estoque Baixo (≤ 5)</div>
          <div class="kpi-value text-danger"><?= $qtdBaixoEstoque ?> <small style="font-size: 1rem;">itens</small></div>
          <i class='bx bx-error-circle kpi-icon' style="color: #dc3545;"></i>
        </div>
      </div>
    </div>

    <!-- GRÁFICO PRINCIPAL (EVOLUÇÃO) -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="chart-container shadow-sm p-4 bg-white rounded">
          <h5 class="mb-4 fw-bold text-secondary">Evolução de Vendas (Mês Atual)</h5>
          <canvas id="salesChart" style="max-height: 300px;"></canvas>
        </div>
      </div>
    </div>

    <!-- LINHA DOS NOVOS GRÁFICOS (LADO A LADO) -->
    <div class="row g-4 mb-5">

      <!-- Gráfico de Categorias -->
      <div class="col-md-6">
        <div class="chart-container shadow-sm p-4 bg-white rounded h-100">
          <h5 class="mb-4 fw-bold text-secondary">Mais Vendidos por Categoria (Qtd)</h5>
          <div style="height: 250px; display: flex; justify-content: center;">
            <canvas id="categoryChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Gráfico de Pagamentos -->
      <div class="col-md-6">
        <div class="chart-container shadow-sm p-4 bg-white rounded h-100">
          <h5 class="mb-4 fw-bold text-secondary">Faturamento por Pagamento</h5>
          <div style="height: 250px;">
            <canvas id="paymentChart"></canvas>
          </div>
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
          Estabelecimentos
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

  <!-- Modal para Produtos com Estoque Baixo -->
  <div class="modal fade" id="estoqueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-danger bg-opacity-10 border-bottom">
          <h5 class="modal-title fw-bold text-danger">
            <i class='bx bx-error-circle'></i> Produtos com Estoque Crítico (≤ 5)
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <div id="estoqueList" style="max-height: 400px; overflow-y: auto;">
            <!-- Lista será preenchida aqui -->
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Evento de clique no card de Estoque Baixo
    document.getElementById('btnEstoqueBaixo').addEventListener('click', function() {
      const produtosBaixo = <?= json_encode($produtosBaixoEstoque) ?>;
      const listaEl = document.getElementById('estoqueList');
      
      if (produtosBaixo.length === 0) {
        listaEl.innerHTML = '<p class="text-muted text-center py-5">Nenhum produto com estoque crítico no momento.</p>';
      } else {
        let html = '<div class="list-group">';
        produtosBaixo.forEach(item => {
          html += `
            <div class="list-group-item border-start border-4 border-danger">
              <div class="d-flex justify-content-between align-items-start">
                <div class="flex-grow-1">
                  <h6 class="mb-1 fw-bold text-dark">${item.produto}</h6>
                  <p class="mb-0 text-muted small">
                    <strong>Sabor:</strong> ${item.sabor} | <strong>Categoria:</strong> ${item.tipo}
                  </p>
                </div>
                <div class="text-end">
                  <span class="badge bg-danger text-white">Crítico</span>
                  <p class="mb-0 fw-bold text-danger mt-2" style="font-size: 1.2rem;">${item.saldo_final} un</p>
                </div>
              </div>
            </div>
          `;
        });
        html += '</div>';
        listaEl.innerHTML = html;
      }
      
      // Abrir modal
      const modal = new bootstrap.Modal(document.getElementById('estoqueModal'));
      modal.show();
    });

    // --- GRÁFICO 1: EVOLUÇÃO (Linha) ---
    const ctx = document.getElementById('salesChart').getContext('2d');
    const labels = <?= json_encode($labelsGrafico) ?>;
    const dataValues = <?= json_encode($valoresGrafico) ?>;

    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Vendas (R$)',
          data: dataValues,
          borderColor: '#4682B4',
          backgroundColor: 'rgba(70, 130, 180, 0.1)',
          borderWidth: 3,
          pointBackgroundColor: '#fff',
          pointBorderColor: '#4682B4',
          pointRadius: 5,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, grid: { borderDash: [5, 5] } },
          x: { grid: { display: false } }
        }
      }
    });

    // --- GRÁFICO 2: CATEGORIAS (Rosca/Doughnut) ---
    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    const labelsCat = <?= json_encode($labelsCat) ?>;
    const valuesCat = <?= json_encode($valoresCat) ?>;

    new Chart(ctxCat, {
      type: 'doughnut',
      data: {
        labels: labelsCat,
        datasets: [{
          data: valuesCat,
          backgroundColor: [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
          ],
          hoverOffset: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'right' }
        }
      }
    });

    // --- GRÁFICO 3: PAGAMENTOS (Barras) ---
    const ctxPag = document.getElementById('paymentChart').getContext('2d');
    const labelsPag = <?= json_encode($labelsPag) ?>;
    const valuesPag = <?= json_encode($valoresPag) ?>;

    new Chart(ctxPag, {
      type: 'bar',
      data: {
        labels: labelsPag,
        datasets: [{
          label: 'Total Recebido (R$)',
          data: valuesPag,
          backgroundColor: [
            '#20c997', // Verde (Dinheiro/Pix)
            '#0d6efd', // Azul (Débito)
            '#6f42c1', // Roxo (Crédito)
            '#ffc107'  // Amarelo
          ],
          borderRadius: 5
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  </script>

</body>

</html>