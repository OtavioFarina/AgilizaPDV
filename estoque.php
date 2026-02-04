<?php
session_start();

// 1. Verificação de Segurança (Login Obrigatório)
if (!isset($_SESSION['nome_usuario'])) {
    header("Location: acesso.php");
    exit();
}

// Pega o tipo de usuário (1 = ADM, 0 = Comum)
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : 0;

// Usar conexão centralizada
require_once "config/conexao.php";
$pdo = $conn; // Alias para manter compatibilidade com o código existente

// --- LÓGICA PARA RETORNAR SABORES (CHAMADA AJAX) ---
if (isset($_GET['acao']) && $_GET['acao'] === 'get_sabores' && isset($_GET['id_categoria'])) {
    header('Content-Type: application/json; charset=utf-8');
    $id_categoria = (int) $_GET['id_categoria'];

    try {
        $stmt = $pdo->prepare("SELECT DISTINCT sabor FROM produto WHERE id_categoria = ? AND sabor <> '' AND ativo = 1 ORDER BY sabor");
        $stmt->execute([$id_categoria]);
        $sabores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($sabores);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro ao buscar sabores: ' . $e->getMessage()]);
    }
    exit;
}

// --- LÓGICA DE ENTRADA DE PRODUTO (PROTEGIDA PARA ADM) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_categoria'])) {

    // Verifica novamente no backend se é ADM antes de processar
    if ($tipo_usuario != 1) {
        die("Acesso negado: Apenas administradores podem registrar entradas.");
    }

    $id_categoria = (int) $_POST['id_categoria'];
    $sabor = $_POST['sabor'] ?? '-';

    $stmtCat = $pdo->prepare("SELECT nome_categoria FROM categoria WHERE id_categoria = ?");
    $stmtCat->execute([$id_categoria]);
    $catInfo = $stmtCat->fetch(PDO::FETCH_ASSOC);
    $produto_tipo = $catInfo ? $catInfo['nome_categoria'] : 'Geral';

    $stmtProd = $pdo->prepare("SELECT nome FROM produto WHERE id_categoria = ? AND sabor = ? AND ativo = 1 LIMIT 1");
    $stmtProd->execute([$id_categoria, $sabor]);
    $prodInfo = $stmtProd->fetch(PDO::FETCH_ASSOC);

    $produto_nome = $prodInfo ? $prodInfo['nome'] : $produto_tipo;
    $produto_sabor = $sabor ?: '-';

    $quantidade = (int) $_POST['quantidade'];
    $valor_custo = (float) $_POST['valor_custo'];

    $stmtInsere = $pdo->prepare("
        INSERT INTO estoque (movimentacao, produto, sabor, tipo, estoque_atual, valor_custo, data)
        VALUES ('Entrada', ?, ?, ?, ?, ?, NOW())
    ");
    $stmtInsere->execute([$produto_nome, $produto_sabor, $produto_tipo, $quantidade, $valor_custo]);

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// --- CONSULTA PRINCIPAL DO ESTOQUE ---
$sql = "
    SELECT 
        e.produto,
        e.sabor,
        e.tipo,
        COALESCE(
            SUM(CASE 
                WHEN e.movimentacao = 'Entrada' THEN e.estoque_atual 
                WHEN e.movimentacao = 'Saída' THEN -e.estoque_atual 
            END), 0
        ) as estoque_final,
        MAX(e.valor_custo) as valor_custo,
        MAX(e.data) as ultima_data
    FROM estoque e
    GROUP BY e.produto, e.sabor, e.tipo
    HAVING estoque_final > 0
    ORDER BY e.produto, e.sabor
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$dados_estoque = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- CONSULTA PARA CATEGORIAS (Apenas se for ADM para preencher o select) ---
$categorias = [];
if ($tipo_usuario == 1) {
    $sql_categorias = "SELECT id_categoria, nome_categoria FROM categoria WHERE ativo = 1 ORDER BY nome_categoria";
    $stmt_categorias = $pdo->query($sql_categorias);
    $categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestão de Estoque</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style_estoque.css" />
    <link href="assets/css/dark_mode.css" rel="stylesheet">
</head>

<body>
    <div class="top-bar">
        <div class="d-flex align-items-center gap-3">
            <img class="logo" src="assets/img/logoagilizasemfundo.png" alt="Logo PDV">
            <h4 class="m-0 fw-bold text-secondary d-none d-md-block">Controle de Estoque</h4>
        </div>

        <div class="dropdown">
            <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                <i class='bx bx-menu fs-3'></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <?php if ($tipo_usuario == 1): ?>
                    <li><a class="dropdown-item py-2 text-primary fw-bold" href="adm.php"><i class='bx bxs-dashboard'></i>
                            Voltar ao Dashboard</a></li>
                <?php endif; ?>
                <li><a class="dropdown-item py-2 text-primary fw-bold" href="vendas.php"><i class='bx bx-cart'></i>
                        Voltar ao PDV</a></li>
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
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="container main-container">

        <?php if ($tipo_usuario == 1): ?>
            <div class="card-form mb-5">
                <h5 class="mb-4 text-primary fw-bold"><i class='bx bx-plus-circle'></i> Nova Entrada de Mercadoria</h5>

                <form method="POST" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Categoria</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class='bx bx-category'></i></span>
                            <select name="id_categoria" id="id_categoria" class="form-select" required>
                                <option value="" selected disabled>Selecione...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nome_categoria']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Sabor / Produto</label>
                        <select name="sabor" id="sabor" class="form-select" required>
                            <option value="">Aguardando categoria...</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Quantidade</label>
                        <input type="number" name="quantidade" class="form-control" placeholder="0" min="1" required />
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Custo Unit. (R$)</label>
                        <div class="input-group">
                            <span class="input-group-text">R$</span>
                            <input type="number" step="0.01" name="valor_custo" class="form-control" placeholder="0,00"
                                required />
                        </div>
                    </div>

                    <div class="col-md-1">
                        <button type="submit" class="btn btn-success w-100 fw-bold"><i class='bx bx-check'></i></button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
        <h4 class="page-title">Saldo Atual em Estoque</h4>

        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Produto</th>
                            <th>Sabor</th>
                            <th>Categoria</th>
                            <th class="text-center">Qtd. Atual</th>
                            <th class="text-end">Custo Unit.</th>
                            <th class="text-end">Última Movimentação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($dados_estoque): ?>
                            <?php foreach ($dados_estoque as $linha): ?>
                                <tr>
                                    <td class="fw-bold text-secondary"><?= htmlspecialchars($linha['produto']) ?></td>
                                    <td><?= htmlspecialchars($linha['sabor']) ?></td>
                                    <td><span
                                            class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill"><?= htmlspecialchars($linha['tipo']) ?></span>
                                    </td>

                                    <td class="text-center">
                                        <?php
                                        $qtd = (int) $linha['estoque_final'];
                                        $classe = 'bg-success text-success'; // Padrão
                                
                                        if ($qtd <= 5) {
                                            $classe = 'bg-danger text-danger'; // Crítico
                                        } elseif ($qtd <= 15) {
                                            $classe = 'bg-warning text-warning'; // Alerta
                                        }
                                        ?>
                                        <span class="badge <?= $classe ?> bg-opacity-10 border border-opacity-25 status-badge">
                                            <?= $qtd ?> un
                                        </span>
                                    </td>

                                    <td class="text-end fw-bold text-muted">R$
                                        <?= number_format($linha['valor_custo'], 2, ',', '.') ?>
                                    </td>
                                    <td class="text-end small text-muted">
                                        <?= date('d/m/Y H:i', strtotime($linha['ultima_data'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center p-5 text-muted">
                                    <i class='bx bx-package fs-1 mb-3 opacity-25'></i>
                                    <p class="mb-0">Nenhum item com saldo em estoque no momento.</p>
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

    <?php if ($tipo_usuario == 1): ?>
        <script>
            document.getElementById('id_categoria').addEventListener('change', function () {
                const categoriaId = this.value;
                const saborSelect = document.getElementById('sabor');
                saborSelect.innerHTML = '<option>Carregando...</option>';
                saborSelect.disabled = true;

                fetch(`<?= $_SERVER['PHP_SELF'] ?>?acao=get_sabores&id_categoria=${categoriaId}`)
                    .then(res => res.json())
                    .then(data => {
                        saborSelect.innerHTML = '';
                        saborSelect.disabled = false;

                        if (data.error) {
                            saborSelect.innerHTML = `<option value="">${data.error}</option>`;
                            return;
                        }
                        if (data.length > 0) {
                            saborSelect.innerHTML = '<option value="" selected disabled>Selecione o sabor...</option>';
                            data.forEach(item => {
                                const opt = document.createElement('option');
                                opt.value = item.sabor;
                                opt.textContent = item.sabor;
                                saborSelect.appendChild(opt);
                            });
                        } else {
                            saborSelect.innerHTML = '<option value="">Nenhum sabor disponível</option>';
                        }
                    })
                    .catch(() => {
                        saborSelect.innerHTML = '<option value="">Erro ao carregar</option>';
                        saborSelect.disabled = false;
                    });
            });
        </script>
    <?php endif; ?>
</body>

</html>