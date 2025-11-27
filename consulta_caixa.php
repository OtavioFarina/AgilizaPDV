<?php
session_start();

// 1. Segurança: Login Obrigatório
if (!isset($_SESSION['nome_usuario'])) {
    header("Location: acesso.php");
    exit();
}

require_once "conexao.php";

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : 0;
$usuario_logado = $_SESSION['nome_usuario'];

// --- CONFIGURAÇÃO DE FILTROS ---
$dataFiltro = isset($_GET['data']) ? $_GET['data'] : '';

// Lógica de Restrição de Acesso aos Dados
if ($tipo_usuario != 1) {
    // SE FOR VENDEDOR: Só vê o dia de hoje (ou o último fechamento dele)
    $dataFiltro = date('Y-m-d'); // Força a data de hoje
    $sql = "SELECT * FROM fechamento_caixa WHERE DATE(data_fechamento) = :data AND operador = :operador ORDER BY data_fechamento DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':data', $dataFiltro);
    $stmt->bindValue(':operador', $usuario_logado);
} else {
    // SE FOR ADM: Filtro livre
    if ($dataFiltro) {
        $sql = "SELECT * FROM fechamento_caixa WHERE DATE(data_fechamento) = :data ORDER BY data_fechamento DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':data', $dataFiltro);
    } else {
        // Padrão: Últimos 30 dias
        $data_final = date('Y-m-d 23:59:59');
        $data_inicial = date('Y-m-d 00:00:00', strtotime('-30 days'));
        $sql = "SELECT * FROM fechamento_caixa WHERE data_fechamento BETWEEN :ini AND :fim ORDER BY data_fechamento DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':ini', $data_inicial);
        $stmt->bindValue(':fim', $data_final);
    }
}

$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- PROCESSAMENTO DOS DADOS ---
$dados_agrupados = [];
$faturamento_total_periodo = 0;
$custo_total_periodo = 0;
$lucro_total_periodo = 0;

foreach ($registros as $key => $r) {
    $faturamento_fechamento = $r['total_dinheiro'] + $r['total_cartaoC'] + $r['total_cartaoD'] + $r['total_pix'];
    $r['faturamento_total'] = $faturamento_fechamento;
    
    // Busca Custo dos Produtos Vendidos neste Fechamento
    $custo_vendas = 0;
    try {
        $sql_custo = "
            SELECT SUM(sp.quantidade * p.preco_compra) as custo
            FROM vendas v
            JOIN saida_produtos sp ON v.id_venda = sp.venda_id
            JOIN produto p ON sp.id_produto = p.id_produto
            WHERE v.fechamento_caixa_id = :id_fechamento
        ";
        $stmtCusto = $conn->prepare($sql_custo);
        $stmtCusto->bindValue(':id_fechamento', $r['id_fechamento']);
        $stmtCusto->execute();
        $custo_vendas = (float) $stmtCusto->fetchColumn();
    } catch (Exception $e) { $custo_vendas = 0; }

    $r['custo_total'] = $custo_vendas;
    $r['lucro_total'] = $faturamento_fechamento - $custo_vendas;

    // Acumuladores para os Cards de KPI
    $faturamento_total_periodo += $faturamento_fechamento;
    $custo_total_periodo += $custo_vendas;
    $lucro_total_periodo += $r['lucro_total'];

    // Busca os detalhes dos itens vendidos para este fechamento (Para o modal/accordion)
    $sql_itens = "
        SELECT p.id_produto, p.nome, p.sabor, SUM(sp.quantidade) as qtd_total, v.id_forma_pagamento, fp.nome_pagamento
        FROM vendas v
        JOIN saida_produtos sp ON v.id_venda = sp.venda_id
        JOIN produto p ON sp.id_produto = p.id_produto
        JOIN forma_pagamento fp ON v.id_forma_pagamento = fp.id_forma_pagamento
        WHERE v.fechamento_caixa_id = :id_fechamento
        GROUP BY p.id_produto, p.nome, p.sabor, fp.nome_pagamento
    ";
    $stmtItens = $conn->prepare($sql_itens);
    $stmtItens->bindValue(':id_fechamento', $r['id_fechamento']);
    $stmtItens->execute();
    $r['itens_vendidos'] = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    $dados_agrupados[] = $r;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Relatório de Caixa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <link href="styles/style_consulta.css" rel="stylesheet" />
</head>
<body>

    <!-- Top Bar -->
    <div class="top-bar">
        <div class="d-flex align-items-center gap-3">
            <img src="img/logoagilizasemfundo.png" style="height: 125px; width: auto;" alt="Logo">
            <h4 class="m-0 fw-bold text-secondary d-none d-md-block">Relatórios</h4>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown"><i class='bx bx-menu fs-3'></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <?php if($tipo_usuario == 1): ?>
                    <li><a class="dropdown-item py-2 text-primary fw-bold" href="adm.php"><i class='bx bxs-dashboard'></i> Voltar ao Dashboard</a></li>
                <?php endif; ?>
                <li><a class="dropdown-item py-2" href="vendas.php"><i class='bx bx-cart'></i> Voltar ao PDV</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a></li>
            </ul>
        </div>
    </div>

    <div class="container main-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title m-0">Relatório de Fechamento de Caixa</h2>
        </div>

        <!-- FILTROS (APENAS ADM VÊ O SELETOR DE DATA) -->
        <?php if ($tipo_usuario == 1): ?>
        <div class="card p-3 border-0 shadow-sm mb-5">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold text-muted">Filtrar por Data Específica:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class='bx bx-calendar'></i></span>
                        <input type="date" name="data" class="form-control" value="<?= htmlspecialchars($dataFiltro) ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Buscar</button>
                </div>
                <div class="col-md-2">
                    <a href="consulta_caixa.php" class="btn btn-outline-secondary w-100">Limpar</a>
                </div>
            </form>
        </div>
        <?php else: ?>
            <div class="alert alert-info mb-4">
                <i class='bx bx-info-circle'></i> Você está visualizando o relatório do seu turno atual (<?= date('d/m/Y') ?>).
            </div>
        <?php endif; ?>

        <!-- KPIS GERAIS DO PERÍODO -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="kpi-card border-blue">
                    <div class="kpi-title">Lucro Bruto</div>
                    <div class="kpi-value text-primary">R$ <?= number_format($faturamento_total_periodo, 2, ',', '.') ?></div>
                </div>
            </div>
            
            <!-- CUSTO E LUCRO (Visível apenas para ADM para não expor margem ao vendedor) -->
            <?php if ($tipo_usuario == 1): ?>
            <div class="col-md-4">
                <div class="kpi-card border-red">
                    <div class="kpi-title">Custo das Vendas</div>
                    <div class="kpi-value text-danger">R$ <?= number_format($custo_total_periodo, 2, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="kpi-card border-green">
                    <div class="kpi-title">Lucro Líquido</div>
                    <div class="kpi-value text-success">R$ <?= number_format($lucro_total_periodo, 2, ',', '.') ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- LISTA DE FECHAMENTOS -->
        <?php if (empty($dados_agrupados)): ?>
            <div class="text-center py-5 text-muted">
                <i class='bx bx-ghost fs-1 mb-3 opacity-25'></i>
                <p>Nenhum fechamento de caixa encontrado para este período.</p>
            </div>
        <?php else: ?>
            <?php foreach ($dados_agrupados as $r): ?>
                <div class="closing-card">
                    <div class="closing-header">
                        <div>
                            <div class="fw-bold fs-5 text-dark">
                                <i class='bx bx-user-circle'></i> <?= htmlspecialchars($r['operador']) ?>
                            </div>
                            <small class="text-muted">
                                Aberto: <?= date('d/m H:i', strtotime($r['data_abertura'])) ?> &bull; 
                                Fechado: <?= date('d/m H:i', strtotime($r['data_fechamento'])) ?>
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="info-badge">Total: R$ <?= number_format($r['faturamento_total'], 2, ',', '.') ?></span>
                            <button class="btn btn-sm btn-primary ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#detalhes-<?= $r['id_fechamento'] ?>">
                                Ver Detalhes <i class='bx bx-chevron-down'></i>
                            </button>
                        </div>
                    </div>

                    <!-- DETALHES COLAPSÁVEIS -->
                    <div class="collapse" id="detalhes-<?= $r['id_fechamento'] ?>">
                        <div class="closing-body">
                            <div class="row">
                                <!-- Resumo Financeiro -->
                                <div class="col-md-4 border-end">
                                    <h6 class="fw-bold text-secondary mb-3">Resumo por Pagamento</h6>
                                    <ul class="list-group list-group-flush small">
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span><i class='bx bx-money text-success'></i> Dinheiro</span>
                                            <span class="fw-bold">R$ <?= number_format($r['total_dinheiro'], 2, ',', '.') ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span><i class='bx bx-credit-card text-primary'></i> Crédito</span>
                                            <span class="fw-bold">R$ <?= number_format($r['total_cartaoC'], 2, ',', '.') ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span><i class='bx bx-credit-card-front text-info'></i> Débito</span>
                                            <span class="fw-bold">R$ <?= number_format($r['total_cartaoD'], 2, ',', '.') ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between">
                                            <span><i class='bx bxs-zap text-warning'></i> PIX</span>
                                            <span class="fw-bold">R$ <?= number_format($r['total_pix'], 2, ',', '.') ?></span>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Itens Vendidos (O pedido novo!) -->
                                <div class="col-md-8">
                                    <h6 class="fw-bold text-secondary mb-3">Produtos Vendidos neste Caixa</h6>
                                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                        <table class="table table-sm table-striped table-details align-middle">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Produto</th>
                                                    <th>Sabor</th>
                                                    <th class="text-center">Qtd</th>
                                                    <th>Pagamento</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($r['itens_vendidos'])): ?>
                                                    <?php foreach ($r['itens_vendidos'] as $item): ?>
                                                        <tr>
                                                            <td class="text-muted small">#<?= $item['id_produto'] ?></td>
                                                            <td class="fw-bold"><?= htmlspecialchars($item['nome']) ?></td>
                                                            <td><?= htmlspecialchars($item['sabor']) ?></td>
                                                            <td class="text-center fw-bold"><?= $item['qtd_total'] ?></td>
                                                            <td class="small text-muted"><?= $item['nome_pagamento'] ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr><td colspan="5" class="text-center text-muted">Nenhum detalhe disponível.</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>