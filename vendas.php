<?php
session_start();

// Se o usuário não estiver logado, redireciona para o login
if (!isset($_SESSION['nome_usuario']) || !isset($_SESSION['id_estabelecimento'])) {
    header("Location: acesso.php");
    exit();
}

// Define tipo de usuário (evita erro se não existir)
$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : 0;

include "conexao.php";

$sqlStatus = "SELECT status FROM caixa_status ORDER BY id_status DESC LIMIT 1";
$stmtStatus = $conn->prepare($sqlStatus);
$stmtStatus->execute();
$caixa_status = $stmtStatus->fetchColumn();

$sqlCat = "SELECT * FROM categoria ORDER BY nome_categoria ASC";
$stmtCat = $conn->prepare($sqlCat);
$stmtCat->execute();
$categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

$sqlProd = "SELECT p.*, c.id_categoria AS cat_id, c.nome_categoria FROM produto p LEFT JOIN categoria c ON p.id_categoria = c.id_categoria ORDER BY c.nome_categoria, p.nome";
$stmtProd = $conn->prepare($sqlProd);
$stmtProd->execute();
$produtos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);
$produtos_por_categoria = [];
foreach ($produtos as $p) {
    $catKey = $p['cat_id'] ?? 0;
    $produtos_por_categoria[$catKey][] = $p;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>PDV - Vendas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="styles/style_vendas.css">
</head>

<body>
    <header class="pdv-header">
        <div class="d-flex align-items-center gap-3">
            <img src="img/logoagilizasemfundo.png" class="logo" alt="Logo PDV">
            <h4 class="m-0 fw-bold text-secondary d-none d-md-block">Ponto de Venda</h4>
        </div>

        <div class="d-flex align-items-center gap-3">

            <span class="d-none d-md-block text-muted small">Olá,
                <strong><?= htmlspecialchars($_SESSION['nome_usuario']) ?></strong></span>

            <?php if ($caixa_status === 'aberto'): ?>
                <span class="badge bg-success caixa-badge"><i class='bx bxs-lock-open-alt'></i> Caixa Aberto</span>
            <?php else: ?>
                <span class="badge bg-danger caixa-badge"><i class='bx bxs-lock-alt'></i> Caixa Fechado</span>
            <?php endif; ?>

            <div class="dropdown">
                <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown">
                    <i class='bx bx-menu fs-3'></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">

                    <?php if ($tipo_usuario == 1): ?>
                        <li>
                            <a class="dropdown-item py-2 text-primary fw-bold" href="adm.php">
                                <i class='bx bxs-dashboard'></i> Dashboard Administrativo
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                    <?php endif; ?>

                    <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal"
                            data-bs-target="#exampleModalToggle"><i class='bx bx-toggle-left'></i> Abrir/Fechar
                            Caixa</a></li>
                    <li><a class="dropdown-item py-2" href="estoque.php"><i class='bx bx-box'></i> Estoque</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i>
                            Sair</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main class="pdv-main">
        <div class="container-fluid h-100-custom">
            <div class="row h-100-custom g-4">

                <div class="col-lg-8 col-md-7 h-100-custom">
                    <div class="product-panel">
                        <h5 class="panel-title"><i class='bx bx-category'></i> Categorias</h5>
                        <div class="product-grid">
                            <?php foreach ($categorias as $cat): ?>
                                <button class="btn btn-produto" data-bs-toggle="modal"
                                    data-bs-target="#modalCat<?= htmlspecialchars($cat['id_categoria']) ?>">
                                    <i class='bx bxs-package fs-1 mb-2 text-primary opacity-50'></i>
                                    <?= htmlspecialchars($cat['nome_categoria']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-5 h-100-custom">
                    <div class="cart-panel">
                        <div class="cart-header d-flex justify-content-between align-items-center">
                            <span><i class='bx bx-cart'></i> Carrinho Atual</span>
                            <button class="btn btn-sm btn-outline-danger border-0" onclick="limparCarrinho()"
                                title="Limpar Carrinho"><i class='bx bx-trash'></i></button>
                        </div>

                        <div class="cart-list">
                            <ul id="carrinhoLista" class="list-group list-group-flush">
                            </ul>
                            <div id="emptyCartMsg" class="text-center text-muted mt-5" style="display:none;">
                                <i class='bx bx-basket fs-1 opacity-25'></i>
                                <p class="mt-2 small">Seu carrinho está vazio.</p>
                            </div>
                        </div>

                        <div class="cart-total-container">
                            <div class="d-flex justify-content-between mb-2 text-secondary">
                                <span>Subtotal</span>
                                <span id="subtotalDisplay">R$ 0,00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end mb-3">
                                <span class="fw-bold">TOTAL</span>
                                <span id="totalCarrinho" class="cart-total-value">R$ 0,00</span>
                            </div>
                            <button class="btn btn-success w-100 btn-lg fw-bold shadow-sm" onclick="finalizarVenda()">
                                <i class='bx bx-check-circle'></i> Finalizar Venda
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <footer class="pdv-footer">
        &copy; <?php echo date('Y'); ?> Sistema PDV Agiliza - Atacadão do Sorvete
    </footer>

    <div class="modal fade" id="exampleModalToggle" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">Controle de Caixa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <?php if ($caixa_status === 'aberto'): ?>
                            <i class='bx bxs-lock-open-alt text-success' style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Caixa Aberto</h4>
                            <p class="text-muted">O sistema está registrando vendas.</p>
                        <?php else: ?>
                            <i class='bx bxs-lock-alt text-danger' style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Caixa Fechado</h4>
                            <p class="text-muted">Abra o caixa para começar a vender.</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($caixa_status === 'aberto'): ?>
                        <form id="formFecharCaixa" method="post" action="fechar_caixa.php">
                            <button type="button" onclick="confirmarFechamento()"
                                class="btn btn-danger w-100 btn-lg shadow-sm">
                                <i class='bx bx-stop-circle'></i> Fechar Caixa Agora
                            </button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="abrir_caixa.php">
                            <div class="mb-3 text-start">
                                <label for="valor_abertura" class="form-label fw-bold text-secondary">Fundo de Troco
                                    (R$)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light"><i class='bx bx-money'></i></span>
                                    <input type="text" class="form-control fw-bold text-primary" id="valor_abertura"
                                        name="valor_abertura" placeholder="0,00" required>
                                </div>
                                <div class="form-text">Informe o valor em dinheiro na gaveta ao iniciar.</div>
                            </div>
                            <button type="submit" class="btn btn-success w-100 btn-lg shadow-sm">
                                <i class='bx bx-play-circle'></i> Abrir Caixa
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php foreach ($categorias as $cat):
        $cid = $cat['id_categoria'];
        $nomeCat = $cat['nome_categoria'];
        $produtos_da_categoria = $produtos_por_categoria[$cid] ?? [];
        ?>
        <div class="modal fade" id="modalCat<?= htmlspecialchars($cid) ?>">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold text-primary"><i class='bx bx-grid-alt'></i>
                            <?= htmlspecialchars($nomeCat) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body bg-light">
                        <div class="row g-3">
                            <?php foreach ($produtos_da_categoria as $prod):
                                $inputId = "prod" . (int) $prod['id_produto'];
                                $preco = number_format((float) $prod['preco_venda'], 2, ',', '.');
                                ?>
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <div class="card h-100 border-0 shadow-sm product-card-modal">
                                        <div class="card-body text-center d-flex flex-column justify-content-between">
                                            <div>
                                                <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($prod['nome']) ?></h6>
                                                <small class="text-muted"><?= htmlspecialchars($prod['sabor']) ?></small>
                                                <h5 class="text-primary fw-bold mt-2">R$ <?= $preco ?></h5>
                                            </div>

                                            <div class="mt-3">
                                                <div class="input-group input-group-sm mb-2 justify-content-center">
                                                    <button class="btn btn-outline-danger" type="button"
                                                        onclick="alterarQtd('<?= $inputId ?>', -1)">-</button>
                                                    <input type="text" class="form-control text-center fw-bold"
                                                        id="<?= htmlspecialchars($inputId) ?>" value="1"
                                                        style="max-width: 50px;" readonly>
                                                    <button class="btn btn-outline-success" type="button"
                                                        onclick="alterarQtd('<?= $inputId ?>', 1)">+</button>
                                                </div>
                                                <button class="btn btn-primary w-100 btn-sm fw-bold"
                                                    onclick="adicionarProduto(<?= (int) $prod['id_produto'] ?>, '<?= addslashes($prod['nome'] . ' ' . $prod['sabor']) ?>', document.getElementById('<?= htmlspecialchars($inputId) ?>').value, <?= (float) $prod['preco_venda'] ?>, '<?= htmlspecialchars($inputId) ?>')">
                                                    Adicionar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="modal fade" id="modalPagamentoPDV" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class='bx bx-wallet'></i> Finalizar Venda</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-7 border-end">
                            <h6 class="text-secondary fw-bold text-uppercase mb-3 small">Calculadora de Pagamento</h6>

                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted">Total a Pagar</span>
                                        <span class="fs-4 fw-bold text-dark" id="displayTotal">R$ 0,00</span>
                                    </div>
                                    <input type="hidden" id="totalPDV">
                                    <input type="hidden" id="subtotalPDV">
                                </div>
                            </div>

                            <div class="payment-group-box">
                                <label for="dinheiroPDV" class="form-label fw-bold text-success"><i
                                        class='bx bx-money'></i> Pagamento em Dinheiro</label>

                                <div class="input-group mb-3">
                                    <span class="input-group-text bg-white">R$</span>
                                    <input type="number" id="dinheiroPDV"
                                        class="form-control form-control-lg fw-bold text-end text-success" value="0"
                                        oninput="calcularTrocoPDV()">
                                    <button class="btn btn-outline-danger" type="button" onclick="resetarDinheiro()"
                                        title="Limpar valor">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </div>

                                <span class="payment-label">Adicionar Notas:</span>
                                <div class="d-flex gap-2 flex-wrap mb-3">
                                    <button class="btn btn-sm btn-outline-secondary btn-shortcut"
                                        onclick="adicionarValorPDV(2)">+2</button>
                                    <button class="btn btn-sm btn-outline-secondary btn-shortcut"
                                        onclick="adicionarValorPDV(5)">+5</button>
                                    <button class="btn btn-sm btn-outline-secondary btn-shortcut"
                                        onclick="adicionarValorPDV(10)">+10</button>
                                    <button class="btn btn-sm btn-outline-secondary btn-shortcut"
                                        onclick="adicionarValorPDV(20)">+20</button>
                                    <button class="btn btn-sm btn-outline-secondary btn-shortcut"
                                        onclick="adicionarValorPDV(50)">+50</button>
                                    <button class="btn btn-sm btn-outline-secondary btn-shortcut"
                                        onclick="adicionarValorPDV(100)">+100</button>
                                </div>

                                <span class="payment-label">Adicionar Moedas:</span>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button class="btn btn-sm btn-outline-secondary btn-shortcut"
                                        onclick="adicionarValorPDV(0.05)">+0,05</button>
                                    <button class="btn btn-sm btn-outline-secondary btn-shortcut"
                                        onclick="adicionarValorPDV(0.10)">+0,10</button>
                                    <button class="btn btn-sm btn-outline-secondary btn-shortcut"
                                        onclick="adicionarValorPDV(0.25)">+0,25</button>
                                    <button class="btn btn-sm btn-outline-secondary btn-shortcut"
                                        onclick="adicionarValorPDV(0.50)">+0,50</button>
                                    <button class="btn btn-sm btn-outline-secondary btn-shortcut"
                                        onclick="adicionarValorPDV(1.00)">+1,00</button>
                                </div>
                            </div>

                            <div
                                class="mt-3 p-2 rounded border d-flex justify-content-between align-items-center bg-white">
                                <span class="fw-bold text-muted">Troco Estimado:</span>
                                <input type="text" id="trocoPDV"
                                    class="form-control-plaintext text-end fw-bold text-primary fs-5" value="0,00"
                                    readonly>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <h6 class="text-secondary fw-bold text-uppercase mb-3 small">Confirmar Forma</h6>

                            <div class="d-grid gap-3">
                                <button class="btn btn-success py-3 shadow-sm"
                                    onclick="finalizarPagamentoPDV('DINHEIRO')">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <i class='bx bx-money fs-4'></i>
                                        <span class="fw-bold">DINHEIRO</span>
                                    </div>
                                </button>

                                <button class="btn btn-info text-white py-3 shadow-sm"
                                    onclick="finalizarPagamentoPDV('DÉBITO')">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <i class='bx bx-credit-card fs-4'></i>
                                        <span class="fw-bold">DÉBITO</span>
                                    </div>
                                </button>

                                <button class="btn btn-primary py-3 shadow-sm"
                                    onclick="finalizarPagamentoPDV('CRÉDITO')">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <i class='bx bxs-credit-card fs-4'></i>
                                        <span class="fw-bold">CRÉDITO</span>
                                    </div>
                                </button>

                                <button class="btn btn-warning text-dark py-3 shadow-sm"
                                    onclick="finalizarPagamentoPDV('PIX')">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <i class='bx bxs-zap fs-4'></i>
                                        <span class="fw-bold">PIX</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const statusCaixa = '<?php echo $caixa_status ?? "fechado"; ?>';
        let carrinho = [];

        // --- SCRIPT NOVO: Formatação do Campo Fundo de Troco ---
        const inputAbertura = document.getElementById('valor_abertura');
        if (inputAbertura) {
            inputAbertura.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, "");
                value = (value / 100).toFixed(2) + "";
                value = value.replace(".", ",");
                value = value.replace(/(\d)(\d{3})(\d{3}),/g, "$1.$2.$3,");
                value = value.replace(/(\d)(\d{3}),/g, "$1.$2,");
                e.target.value = value;
            });
        }
        // -------------------------------------------------------

        // --- PROTEÇÃO DE SAÍDA DA PÁGINA ---

        // 1. Detectar fechamento de aba ou F5
        window.addEventListener('beforeunload', function (e) {
            if (carrinho.length > 0) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // 2. Detectar cliques em links internos (Estoque, Sair, Adm)
        document.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function (e) {
                const href = this.getAttribute('href');

                // Se for link real e tiver carrinho
                if (href && href !== '#' && !href.startsWith('javascript') && carrinho.length > 0) {
                    e.preventDefault();

                    Swal.fire({
                        title: 'Esvaziar Carrinho?',
                        text: "Você tem itens no carrinho. Se sair desta página, a venda atual será perdida.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sim, sair e perder venda',
                        cancelButtonText: 'Ficar aqui',
                        heightAuto: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = href;
                        }
                    });
                }
            });
        });

        // --- CONFIRMAÇÃO FECHAMENTO DE CAIXA ---
        function confirmarFechamento() {
            Swal.fire({
                title: 'Fechar o Caixa?',
                text: "Tem certeza que deseja encerrar o caixa agora?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, fechar!',
                cancelButtonText: 'Cancelar',
                heightAuto: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formFecharCaixa').submit();
                }
            })
        }

        // --- FUNÇÕES DO PDV ---

        function alterarQtd(id, valor) {
            const input = document.getElementById(id);
            let qtd = parseInt(input.value, 10) + valor;
            if (isNaN(qtd) || qtd < 1) qtd = 1;
            input.value = qtd;
        }

        function adicionarProduto(id, nome, qtd, preco, inputId) {
            if (statusCaixa !== 'aberto') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Caixa Fechado',
                    text: 'Abra o caixa primeiro.',
                    heightAuto: false
                });
                return;
            }
            qtd = parseInt(qtd, 10);
            preco = parseFloat(preco);
            if (qtd > 0 && !isNaN(preco)) {
                const index = carrinho.findIndex(item => item.id === id);
                if (index >= 0) { carrinho[index].qtd += qtd; } else { carrinho.push({ id, nome, qtd, preco }); }
                atualizarCarrinho();
                if (inputId) document.getElementById(inputId).value = 1;

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 1000,
                    timerProgressBar: false,
                });
                Toast.fire({
                    icon: 'success',
                    title: '+1 Adicionado'
                });
            }
        }

        function atualizarCarrinho() {
            const lista = document.getElementById("carrinhoLista");
            const msgVazio = document.getElementById("emptyCartMsg");

            lista.innerHTML = "";
            let total = 0;

            if (carrinho.length === 0) {
                msgVazio.style.display = 'block';
            } else {
                msgVazio.style.display = 'none';
                carrinho.forEach((item, i) => {
                    total += item.qtd * item.preco;
                    const li = document.createElement("li");
                    li.className = "list-group-item d-flex justify-content-between align-items-center bg-transparent";
                    li.innerHTML = `
                        <div>
                            <span class="fw-bold">${item.qtd}x</span> ${item.nome}
                            <br><small class="text-muted">${(item.qtd * item.preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}</small>
                        </div>
                        <button class="btn btn-sm btn-light text-danger border-0" onclick="removerProduto(${i})"><i class='bx bx-trash'></i></button>
                    `;
                    lista.appendChild(li);
                });
            }

            const totalFormatado = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            document.getElementById("totalCarrinho").innerText = totalFormatado;

            if (document.getElementById("subtotalDisplay")) {
                document.getElementById("subtotalDisplay").innerText = totalFormatado;
            }
        }

        function removerProduto(i) { carrinho.splice(i, 1); atualizarCarrinho(); }

        function limparCarrinho() { carrinho = []; atualizarCarrinho(); }

        function finalizarVenda() {
            if (statusCaixa !== 'aberto') {
                Swal.fire({ icon: 'warning', title: 'Caixa Fechado', text: 'Abra o caixa primeiro.', heightAuto: false });
                return;
            }
            if (carrinho.length === 0) {
                Swal.fire({ icon: 'info', title: 'Carrinho Vazio', text: 'Adicione produtos.', heightAuto: false });
                return;
            }

            let total = 0;
            carrinho.forEach(item => total += item.qtd * item.preco);

            document.getElementById("subtotalPDV").value = total.toFixed(2).replace('.', ',');
            document.getElementById("totalPDV").value = total.toFixed(2).replace('.', ',');
            document.getElementById("displayTotal").innerText = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

            document.getElementById("dinheiroPDV").value = '0';
            document.getElementById("trocoPDV").value = "0,00";

            new bootstrap.Modal(document.getElementById("modalPagamentoPDV")).show();
        }

        function calcularTrocoPDV() {
            let total = parseFloat(document.getElementById("totalPDV").value.replace(',', '.')) || 0;
            let pago = parseFloat(document.getElementById("dinheiroPDV").value) || 0;
            let troco = pago - total;
            if (troco < 0) troco = 0;
            document.getElementById("trocoPDV").value = troco.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function resetarDinheiro() {
            document.getElementById("dinheiroPDV").value = "0";
            calcularTrocoPDV();
        }

        function adicionarValorPDV(valor) {
            let dinheiroInput = document.getElementById("dinheiroPDV");
            let atual = parseFloat(dinheiroInput.value) || 0;
            let novo = (atual + valor).toFixed(2);
            dinheiroInput.value = novo;
            calcularTrocoPDV();
        }

        function finalizarPagamentoPDV(metodo) {
            let totalValor = document.getElementById("totalPDV").value;
            let totalNumerico = parseFloat(totalValor.replace(',', '.'));

            if (carrinho.length === 0) return;

            if (metodo === 'DINHEIRO') {
                let pago = parseFloat(document.getElementById("dinheiroPDV").value) || 0;
                if (pago < totalNumerico) {
                    Swal.fire({ icon: 'error', title: 'Valor Insuficiente', text: 'Dinheiro menor que o total.', heightAuto: false });
                    return;
                }
            }

            Swal.fire({
                title: 'Confirmar Venda?',
                text: `Valor: R$ ${totalValor} - Forma: ${metodo}`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, Confirmar',
                cancelButtonText: 'Voltar',
                heightAuto: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Processando...',
                        allowOutsideClick: false,
                        heightAuto: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    let dados = { total: totalNumerico, forma_pagamento: metodo, itens: carrinho };
                    const carrinhoBackup = [...carrinho];
                    carrinho = [];

                    fetch("registrar_venda.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify(dados)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.sucesso) {
                                // Limpa o carrinho e fecha o modal de pagamento
                                limparCarrinho();
                                const modalEl = document.getElementById("modalPagamentoPDV");
                                const modal = bootstrap.Modal.getInstance(modalEl);
                                if (modal) modal.hide();

                                // Pergunta se quer imprimir o comprovante
                                Swal.fire({
                                    title: 'Venda Realizada!',
                                    text: 'Deseja imprimir o comprovante?',
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonColor: '#3085d6',
                                    cancelButtonColor: '#d33',
                                    confirmButtonText: 'Sim, Imprimir',
                                    cancelButtonText: 'Não precisa',
                                    reverseButtons: true // Inverte posição dos botões para UX melhor
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Abre o cupom em uma nova janela pequena
                                        const idVenda = data.id_venda;
                                        const url = `imprimir_cupom.php?id=${idVenda}`;
                                        window.open(url, 'Cupom', 'width=400,height=600');
                                    }
                                });

                            } else {
                                carrinho = carrinhoBackup;
                                Swal.fire({ icon: 'error', title: 'Erro', text: data.mensagem, heightAuto: false });
                            }
                        })
                        .catch(error => {
                            carrinho = carrinhoBackup;
                            Swal.fire({ icon: 'error', title: 'Erro', text: 'Estoque Insuficiente.', heightAuto: false });
                        });
                }
            });
        }
    </script>
</body>

</html>