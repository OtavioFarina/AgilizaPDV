<?php
session_start();
require_once "config/conexao.php";

// CORREÇÃO: Define $pdo como alias de $conn para compatibilidade
$pdo = isset($conn) ? $conn : null;

if (!$pdo) {
    die("Erro Crítico: Não foi possível conectar ao banco de dados.");
}

// Proteção: Só ADM pode acessar
if (!isset($_SESSION['nome_usuario']) || (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] != 1)) {
    header("Location: vendas.php");
    exit;
}

// Lógica de Restauração
$mensagem = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restaurar_id']) && isset($_POST['tabela'])) {
    $id = (int)$_POST['restaurar_id'];
    $tabela = $_POST['tabela'];
    $coluna_id = "";

    // Whitelist para segurança
    switch ($tabela) {
        case 'produto':         $coluna_id = 'id_produto'; break;
        case 'fornecedor':      $coluna_id = 'id_fornecedor'; break;
        case 'usuarios':        $coluna_id = 'id_usuario'; break; // Verifique se no banco é 'usuarios' ou 'usuario'
        case 'categoria':       $coluna_id = 'id_categoria'; break;
        case 'estabelecimento': $coluna_id = 'id_estabelecimento'; break;
        default:                $mensagem = "Tabela inválida."; break;
    }

    if ($coluna_id && $id) {
        try {
            $sql = "UPDATE $tabela SET ativo = 1 WHERE $coluna_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $mensagem = "Item restaurado com sucesso!";
            } else {
                $mensagem = "Erro ao restaurar item.";
            }
        } catch (PDOException $e) {
            $mensagem = "Erro no banco: " . $e->getMessage();
        }
    }
}

// Função para buscar itens inativos
function fetchInactive($pdo, $table) {
    try {
        $sql = "SELECT * FROM $table WHERE ativo = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        return [];
    }
}

// Carrega as listas
$produtos = fetchInactive($pdo, 'produto');
$fornecedores = fetchInactive($pdo, 'fornecedor');
$usuarios = fetchInactive($pdo, 'usuarios'); 
$categorias = fetchInactive($pdo, 'categoria');
$estabelecimentos = fetchInactive($pdo, 'estabelecimento');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lixeira - Agiliza PDV</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    
    <link href="assets/css/style_adm.css" rel="stylesheet">
    <link href="assets/css/dark_mode.css" rel="stylesheet">
</head>

<body>
    <div class="top-bar d-flex align-items-center justify-content-between px-4 py-2">
        <div class="d-flex align-items-center gap-3">
            <img src="assets/img/logoagilizasemfundo.png" style="height: 125px; width: auto;" alt="Logo">
            <h4 class="m-0 fw-bold text-secondary d-none d-md-block">Central de Restauração</h4>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                <i class='bx bx-menu fs-3'></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item py-2 text-primary fw-bold" href="adm.php"><i class='bx bxs-dashboard'></i> Voltar ao Dashboard</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <button type="button" class="dropdown-item py-2 text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#settingsModal">
                        <i class='bx bx-cog'></i> Configurações
                    </button>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a></li>
            </ul>
        </div>
    </div>

    <div class="container mt-5 main-container">
        <h2 class="mb-4 fw-bold page-title"><i class='bx bx-trash'></i> Itens Excluídos</h2>

        <?php if ($mensagem): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class='bx bx-check-circle'></i> <?= htmlspecialchars($mensagem) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs mb-4" id="recycleTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="prod-tab" data-bs-toggle="tab" data-bs-target="#prod-content" type="button">Produtos</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="cat-tab" data-bs-toggle="tab" data-bs-target="#cat-content" type="button">Categorias</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="forn-tab" data-bs-toggle="tab" data-bs-target="#forn-content" type="button">Fornecedores</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="user-tab" data-bs-toggle="tab" data-bs-target="#user-content" type="button">Usuários</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="est-tab" data-bs-toggle="tab" data-bs-target="#est-content" type="button">Estabelecimentos</button>
            </li>
        </ul>

        <div class="tab-content" id="recycleTabsContent">
            
            <div class="tab-pane fade show active" id="prod-content">
                <div class="table-container shadow-sm">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Produto</th>
                                <th>Sabor</th>
                                <th>Preço</th>
                                <th class="text-end">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($produtos)): ?>
                                <tr><td colspan="4" class="text-center text-muted p-4">Lixeira vazia para produtos.</td></tr>
                            <?php else: ?>
                                <?php foreach ($produtos as $p): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($p['nome']) ?></td>
                                        <td><?= htmlspecialchars($p['sabor']) ?></td>
                                        <td>R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></td>
                                        <td class="text-end">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="tabela" value="produto">
                                                <input type="hidden" name="restaurar_id" value="<?= $p['id_produto'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success text-white fw-bold">
                                                    <i class='bx bx-refresh'></i> Restaurar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="cat-content">
                <div class="table-container shadow-sm">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Categoria</th>
                                <th class="text-end">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($categorias)): ?>
                                <tr><td colspan="2" class="text-center text-muted p-4">Lixeira vazia.</td></tr>
                            <?php else: ?>
                                <?php foreach ($categorias as $c): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($c['nome_categoria']) ?></td>
                                        <td class="text-end">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="tabela" value="categoria">
                                                <input type="hidden" name="restaurar_id" value="<?= $c['id_categoria'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success text-white fw-bold">
                                                    <i class='bx bx-refresh'></i> Restaurar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="forn-content">
                <div class="table-container shadow-sm">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fornecedor</th>
                                <th>CNPJ</th>
                                <th class="text-end">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($fornecedores)): ?>
                                <tr><td colspan="3" class="text-center text-muted p-4">Lixeira vazia.</td></tr>
                            <?php else: ?>
                                <?php foreach ($fornecedores as $f): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($f['nome_fornecedor']) ?></td>
                                        <td><?= htmlspecialchars($f['cnpj']) ?></td>
                                        <td class="text-end">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="tabela" value="fornecedor">
                                                <input type="hidden" name="restaurar_id" value="<?= $f['id_fornecedor'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success text-white fw-bold">
                                                    <i class='bx bx-refresh'></i> Restaurar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="user-content">
                <div class="table-container shadow-sm">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Usuário</th>
                                <th>Tipo</th>
                                <th class="text-end">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($usuarios)): ?>
                                <tr><td colspan="3" class="text-center text-muted p-4">Lixeira vazia.</td></tr>
                            <?php else: ?>
                                <?php foreach ($usuarios as $u): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($u['nome_usuario']) ?></td>
                                        
                                        <td>
                                            <?php if ($u['tipo_usuario'] == 1): ?>
                                                <span class="badge bg-primary">Administrador</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Vendedor</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                        <td class="text-end">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="tabela" value="usuarios">
                                                <input type="hidden" name="restaurar_id" value="<?= $u['id_usuario'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success text-white fw-bold">
                                                    <i class='bx bx-refresh'></i> Restaurar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="est-content">
                <div class="table-container shadow-sm">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Estabelecimento</th>
                                <th class="text-end">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($estabelecimentos)): ?>
                                <tr><td colspan="2" class="text-center text-muted p-4">Lixeira vazia.</td></tr>
                            <?php else: ?>
                                <?php foreach ($estabelecimentos as $e): ?>
                                    <tr>
                                        <td class="fw-bold"><?= htmlspecialchars($e['nome_fantasia'] ?? $e['razao_social']) ?></td>
                                        <td class="text-end">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="tabela" value="estabelecimento">
                                                <input type="hidden" name="restaurar_id" value="<?= $e['id_estabelecimento'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success text-white fw-bold">
                                                    <i class='bx bx-refresh'></i> Restaurar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

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
                            <input class="form-check-input" type="checkbox" id="themeToggle" style="width: 3em; height: 1.5em; cursor: pointer;">
                            <label class="form-check-label ms-2" for="themeToggle"><i id="themeIcon" class="bx bx-sun fs-4 text-warning"></i></label>
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