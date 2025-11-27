<?php
session_start();
require_once "conexao.php";

// Verifica login
if (!isset($_SESSION['nome_usuario'])) {
    header("Location: acesso.php");
    exit();
}

// Filtros de Data (Padrão: Mês atual)
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');

try {
    // CONSULTA DE VENDAS (Lógica original preservada)
    $sql = "SELECT v.id_venda, v.data_hora, v.valor_total, v.status, f.nome_pagamento
            FROM vendas v
            LEFT JOIN forma_pagamento f ON v.id_forma_pagamento = f.id_forma_pagamento
            WHERE DATE(v.data_hora) BETWEEN :inicio AND :fim
            ORDER BY v.data_hora DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([':inicio' => $data_inicio, ':fim' => $data_fim]);
    $vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao buscar vendas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Histórico de Vendas</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Boxicons -->
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Usando o mesmo CSS do estoque para garantir identidade visual -->
    <link rel="stylesheet" href="styles/style_estoque.css" />

</head>

<body>
    <!-- TOP BAR (IGUAL AO ESTOQUE) -->
    <div class="top-bar">
        <div class="d-flex align-items-center gap-3">
            <img src="img/logoagilizasemfundo.png" alt="Logo PDV" style="height: 125px; width: auto;">
            <h4 class="m-0 fw-bold text-secondary d-none d-md-block">Histórico de Vendas</h4>
        </div>

        <div class="dropdown">
            <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                <i class='bx bx-menu fs-3'></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item py-2 text-primary fw-bold" href="adm.php"><i
                            class='bx bxs-dashboard'></i>Voltar ao Dashboard</a></li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="container main-container">
        <h4 class="page-title">Relatório de Vendas Realizadas</h4>

        <!-- CARD DE FILTROS (ESTRUTURA MANTIDA, CAMPOS ADAPTADOS PARA VENDAS) -->
        <div class="card-filter">
            <h6 class="text-muted mb-3 fw-bold text-uppercase small"><i class='bx bx-filter-alt'></i> Filtros de Busca
            </h6>
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Data Inicial</label>
                    <input type="date" name="data_inicio" class="form-control"
                        value="<?= htmlspecialchars($data_inicio) ?>" />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Data Final</label>
                    <input type="date" name="data_fim" class="form-control"
                        value="<?= htmlspecialchars($data_fim) ?>" />
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-bold"><i class='bx bx-check'></i>
                        Filtrar</button>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-outline-secondary w-100"
                        title="Limpar Filtros"><i class='bx bx-x'></i></a>
                </div>
            </form>
        </div>

        <!-- TABELA DE RESULTADOS -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#ID</th>
                            <th>Data / Hora</th>
                            <th>Pagamento</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($vendas) > 0): ?>
                            <?php foreach ($vendas as $v): ?>
                                <tr>
                                    <td class="ps-4 fw-bold text-muted">#<?= str_pad($v['id_venda'], 5, '0', STR_PAD_LEFT) ?>
                                    </td>
                                    <td class="text-muted small">
                                        <i class='bx bx-calendar'></i> <?= date('d/m/Y', strtotime($v['data_hora'])) ?> <br>
                                        <i class='bx bx-time'></i> <?= date('H:i', strtotime($v['data_hora'])) ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= strtoupper($v['nome_pagamento'] ?? 'OUTROS') ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold text-success">R$ <?= number_format($v['valor_total'], 2, ',', '.') ?>
                                    </td>
                                    <td>
                                        <?php if ($v['status'] == 'finalizada'): ?>
                                            <span class="badge badge-concluida px-3 py-2"><i class='bx bx-check'></i>
                                                Concluída</span>
                                        <?php elseif ($v['status'] == 'cancelada'): ?>
                                            <span class="badge badge-cancelada px-3 py-2"><i class='bx bx-x'></i> Cancelada</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= ucfirst($v['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end pe-4">
                                        <!-- Botão de Imprimir -->
                                        <button class="btn btn-sm btn-outline-dark me-1"
                                            onclick="imprimirCupom(<?= $v['id_venda'] ?>)" title="Imprimir Cupom">
                                            <i class='bx bx-printer'></i>
                                        </button>

                                        <!-- Botão de Cancelar (Só aparece se não estiver cancelada) -->
                                        <?php if ($v['status'] == 'finalizada'): ?>
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="cancelarVenda(<?= $v['id_venda'] ?>)" title="Estornar Venda">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center p-5 text-muted">
                                    <i class='bx bx-search-alt fs-1 mb-3 opacity-25'></i>
                                    <p class="mb-0">Nenhuma venda encontrada neste período.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Função para abrir a janela de impressão
        function imprimirCupom(id) {
            const url = `imprimir_cupom.php?id=${id}`;
            window.open(url, 'Cupom', 'width=400,height=600');
        }

        // Função para cancelar venda (AJAX)
        function cancelarVenda(id) {
            Swal.fire({
                title: 'Cancelar Venda #' + id + '?',
                text: "Os produtos voltarão para o estoque e o valor será estornado. Essa ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, cancelar!',
                cancelButtonText: 'Voltar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processando...',
                        text: 'Devolvendo produtos ao estoque...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });

                    fetch('cancelar_venda.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_venda: id })
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.sucesso) {
                                Swal.fire({
                                    title: 'Sucesso!',
                                    text: 'Venda cancelada e estoque atualizado.',
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Erro!', data.mensagem, 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Erro!', 'Falha na comunicação com o servidor.', 'error');
                        });
                }
            })
        }
    </script>

</body>

</html>