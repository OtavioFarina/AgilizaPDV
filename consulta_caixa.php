<?php
require_once "conexao.php";

$dataFiltro = isset($_GET['data']) ? $_GET['data'] : '';

// Lógica de busca principal
if ($dataFiltro) {
    $sql = "SELECT * FROM fechamento_caixa WHERE DATE(data_fechamento) = :data_fechamento ORDER BY data_fechamento DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':data_fechamento', $dataFiltro);
} else {
    $data_final = date('Y-m-d 23:59:59');
    $data_inicial = date('Y-m-d 00:00:00', strtotime('-2 months'));
    $sql = "SELECT * FROM fechamento_caixa WHERE data_fechamento BETWEEN :data_inicial AND :data_final ORDER BY data_fechamento DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':data_inicial', $data_inicial);
    $stmt->bindParam(':data_final', $data_final);
}

$stmt->execute();
$registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

$dados_agrupados = [];
$faturamento_total_periodo = 0;
$custo_total_periodo = 0;
$lucro_total_periodo = 0;

foreach ($registros as $r) {
    $faturamento_fechamento = $r['total_dinheiro'] + $r['total_cartaoC'] + $r['total_cartaoD'] + $r['total_pix'];
    $r['faturamento_total'] = $faturamento_fechamento;
    $faturamento_total_periodo += $faturamento_fechamento;

    // --- LÓGICA DE CÁLCULO DE CUSTO CORRIGIDA E SIMPLIFICADA ---
    $custo_total_vendas = 0;
    try {
        // Agora a query usa a ligação direta 'fechamento_caixa_id'
        $sql_custo = "
            SELECT SUM(sp.quantidade * p.preco_compra) AS custo_total
            FROM vendas v
            JOIN saida_produtos sp ON v.id_venda = sp.venda_id
            JOIN produto p ON sp.id_produto = p.id_produto
            WHERE v.fechamento_caixa_id = :id_fechamento
        ";
        $stmt_custo = $conn->prepare($sql_custo);
        $stmt_custo->bindParam(':id_fechamento', $r['id_fechamento']); // Usamos o ID do fechamento
        $stmt_custo->execute();
        $resultado_custo = $stmt_custo->fetch(PDO::FETCH_ASSOC);

        if ($resultado_custo && $resultado_custo['custo_total'] !== null) {
            $custo_total_vendas = (float) $resultado_custo['custo_total'];
        }
    } catch (PDOException $e) {
        $custo_total_vendas = 0;
    }

    $lucro_fechamento = $faturamento_fechamento - $custo_total_vendas;
    $r['custo_total'] = $custo_total_vendas;
    $r['lucro_total'] = $lucro_fechamento;

    $custo_total_periodo += $custo_total_vendas;
    $lucro_total_periodo += $lucro_fechamento;

    $data = date('Y-m-d', strtotime($r['data_fechamento']));
    $dados_agrupados[$data][] = $r;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <title>Consulta de Caixa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="styles/style_consulta.css" rel="stylesheet">
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
                <li><a class="dropdown-item" href="adm.php">Painel Administrativo</a></li>
            </ul>
        </div>
    </div>

    <div class="container main-container">
        <h2 class="mb-4 text-center" style="color: var(--primary-blue); font-weight: 700;">Relatório de Caixa</h2>

        <form method="GET" class="row g-3 mb-5 align-items-end p-3 rounded"
            style="background-color: var(--background-light); border: 1px solid var(--border-color);">
            <div class="col-md-4"><label class="form-label fw-bold">Filtrar por data:</label><input type="date"
                    name="data" class="form-control" value="<?= htmlspecialchars($dataFiltro) ?>" /></div>
            <div class="col-md-3"><button type="submit" class="btn btn-primary w-100">Buscar</button></div>
            <div class="col-md-3"><a href="consulta_caixa.php" class="btn btn-outline-secondary w-100">Limpar Filtro</a>
            </div>
        </form>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-card-title">Faturamento Total</div>
                    <div class="stat-card-value">R$ <?= number_format($faturamento_total_periodo, 2, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-card-cost">
                    <div class="stat-card-title">Custo Total</div>
                    <div class="stat-card-value">R$ <?= number_format($custo_total_periodo, 2, ',', '.') ?></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card stat-card-profit">
                    <div class="stat-card-title">Lucro Total</div>
                    <div class="stat-card-value">R$ <?= number_format($lucro_total_periodo, 2, ',', '.') ?></div>
                </div>
            </div>
        </div>

        <?php if (empty($dados_agrupados)): ?>
            <div class="alert alert-warning text-center">Nenhum registro encontrado para o período selecionado.</div>
        <?php else: ?>
            <?php foreach ($dados_agrupados as $data => $registros_do_dia): ?>
                <h4 class="date-header">Resultados de <?= date('d/m/Y', strtotime($data)) ?></h4>

                <?php foreach ($registros_do_dia as $r): ?>
                    <div class="closing-card">
                        <div class="closing-card-header">
                            <div><strong>Operador:</strong> <?= htmlspecialchars($r['operador']) ?><br><small
                                    class="text-muted">Aberto em <?= date('d/m/Y H:i', strtotime($r['data_abertura'])) ?> | Fechado
                                    em <?= date('d/m/Y H:i', strtotime($r['data_fechamento'])) ?></small></div>
                            <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                                data-bs-target="#details-<?= $r['id_fechamento'] ?>">Ver Detalhes</button>
                        </div>
                        <div class="closing-card-body">
                            <div class="row g-3 text-center">
                                <div class="col-md-4">
                                    <div class="metric-display metric-faturamento">
                                        <div class="metric-display-label">Faturamento</div>
                                        <div class="metric-display-value">R$
                                            <?= number_format($r['faturamento_total'], 2, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="metric-display metric-custo">
                                        <div class="metric-display-label">Custo</div>
                                        <div class="metric-display-value">R$ <?= number_format($r['custo_total'], 2, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="metric-display metric-lucro">
                                        <div class="metric-display-label">Lucro</div>
                                        <div class="metric-display-value">R$ <?= number_format($r['lucro_total'], 2, ',', '.') ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="collapse" id="details-<?= $r['id_fechamento'] ?>">
                            <div class="closing-card-body border-top">
                                <h6 class="text-center mb-3 fw-bold">Detalhes por Forma de Pagamento</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between"><span>Dinheiro:</span> <span>R$
                                            <?= number_format($r['total_dinheiro'], 2, ',', '.') ?></span></li>
                                    <li class="list-group-item d-flex justify-content-between"><span>Cartão de Crédito:</span>
                                        <span>R$ <?= number_format($r['total_cartaoC'], 2, ',', '.') ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between"><span>Cartão de Débito:</span>
                                        <span>R$ <?= number_format($r['total_cartaoD'], 2, ',', '.') ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between"><span>PIX:</span> <span>R$
                                            <?= number_format($r['total_pix'], 2, ',', '.') ?></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>