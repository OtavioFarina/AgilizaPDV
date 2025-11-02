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

// --- LÓGICA PARA RETORNAR SABORES (CHAMADA AJAX) ---
if (isset($_GET['acao']) && $_GET['acao'] === 'get_sabores' && isset($_GET['id_categoria'])) {
    header('Content-Type: application/json; charset=utf-8');
    $id_categoria = (int) $_GET['id_categoria'];

    try {
        $stmt = $pdo->prepare("SELECT DISTINCT sabor FROM produto WHERE id_categoria = ? AND sabor <> '' ORDER BY sabor");
        $stmt->execute([$id_categoria]);
        $sabores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($sabores);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro ao buscar sabores: ' . $e->getMessage()]);
    }
    exit;
}

// --- LÓGICA DE ENTRADA DE PRODUTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_categoria'])) {
    $id_categoria = (int) $_POST['id_categoria'];
    $sabor = $_POST['sabor'] ?? '-';

    $stmtCat = $pdo->prepare("SELECT nome_categoria FROM categoria WHERE id_categoria = ?");
    $stmtCat->execute([$id_categoria]);
    $catInfo = $stmtCat->fetch(PDO::FETCH_ASSOC);
    $produto_tipo = $catInfo ? $catInfo['nome_categoria'] : 'Geral';

    $stmtProd = $pdo->prepare("SELECT nome FROM produto WHERE id_categoria = ? AND sabor = ? LIMIT 1");
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

// --- CONSULTA PARA CATEGORIAS ---
$sql_categorias = "SELECT id_categoria, nome_categoria FROM categoria ORDER BY nome_categoria";
$stmt_categorias = $pdo->query($sql_categorias);
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gestão de Estoque</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="styles/style_estoque.css" rel="stylesheet">
</head>
<body>
    <!-- TOP BAR -->
    <div class="top-bar d-flex align-items-center justify-content-between px-3 py-2">
        <img src="img/logo pdv.png" class="logo" alt="Logo PDV" style="height:50px;">
        <div class="dropdown">
            <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="img/3riscos.png" alt="Menu" style="height:25px;">
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="vendas.php">Vendas (PDV)</a></li>
                <li><a class="dropdown-item" href="historico_entradas.php">Histórico de Entradas</a></li>
                <li><a class="dropdown-item" href="adm.php">Painel Administrativo</a></li>
            </ul>
        </div>
    </div>

    <div class="container main-container">
        <h2 class="text-center mb-4" style="color: var(--primary-blue); font-weight: 700;">Gestão de Estoque</h2>

        <!-- FORMULÁRIO DE ENTRADA -->
        <div class="form-card p-3 mb-4">
            <h5 class="mb-3">Registrar Entrada de Categoria</h5>
            <form method="POST" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Categoria</label>
                    <select name="id_categoria" id="id_categoria" class="form-select" required>
                        <option value="" selected disabled>Selecione uma categoria...</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nome_categoria']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sabor</label>
                    <select name="sabor" id="sabor" class="form-select" required>
                        <option value="">Selecione uma categoria primeiro...</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quantidade</label>
                    <input type="number" name="quantidade" class="form-control" placeholder="Qtd." min="1" required />
                </div>
                <div class="col-md-2">
                    <label class="form-label">Custo Unit. (R$)</label>
                    <input type="number" step="0.01" name="valor_custo" class="form-control" placeholder="Custo" required />
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">Salvar</button>
                </div>
            </form>
        </div>

    
        <!-- TABELA DE ESTOQUE -->
        <h4 class="mt-5 mb-3" style="color: var(--primary-blue);">Saldo Atual em Estoque</h4>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Produto</th>
                        <th>Sabor</th>
                        <th>Categoria</th>
                        <th>Estoque Atual</th>
                        <th>Custo Unit. (R$)</th>
                        <th>Última Movimentação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($dados_estoque): ?>
                        <?php foreach ($dados_estoque as $linha): ?>
                            <tr>
                                <td><?= htmlspecialchars($linha['produto']) ?></td>
                                <td><?= htmlspecialchars($linha['sabor']) ?></td>
                                <td><?= htmlspecialchars($linha['tipo']) ?></td>
                                <td class="fw-bold fs-5 <?= $linha['estoque_final'] <= 10 ? 'text-danger' : ($linha['estoque_final'] <= 30 ? 'text-warning' : 'text-success') ?>">
                                    <?= (int) $linha['estoque_final'] ?>
                                </td>
                                <td><?= number_format($linha['valor_custo'], 2, ',', '.') ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($linha['ultima_data'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center p-4">Nenhum item com saldo em estoque.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>

    <!-- SCRIPT AJAX -->
    <script>
        document.getElementById('id_categoria').addEventListener('change', function() {
            const categoriaId = this.value;
            const saborSelect = document.getElementById('sabor');
            saborSelect.innerHTML = '<option>Carregando...</option>';

            fetch(`<?= $_SERVER['PHP_SELF'] ?>?acao=get_sabores&id_categoria=${categoriaId}`)
                .then(res => res.json())
                .then(data => {
                    saborSelect.innerHTML = '';
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
                    saborSelect.innerHTML = '<option value="">Erro ao carregar sabores</option>';
                });
        });
    </script>
</body>
</html>
