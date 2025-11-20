<?php
session_start();

// Proteção: Usuário deve estar logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: acesso.php");
    exit();
}

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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="styles/style_vendas.css" rel="stylesheet">
</head>

<body>
    <header class="pdv-header">
        <img src="img/logo pdv.png" class="logo" alt="Logo PDV">
        <div class="caixa-status-indicator">
            <?php if ($caixa_status === 'aberto'): ?>
                <span class="badge bg-success">✔️ CAIXA ABERTO</span>
            <?php else: ?>
                <span class="badge bg-danger">❌ CAIXA FECHADO</span>
            <?php endif; ?>
        </div>
        <div class="dropdown">
            <button class="btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="img/3riscos.png" class="menu-icon" alt="Menu">
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#exampleModalToggle">Status Caixa</a></li>
                <li><a class="dropdown-item" href="estoque.php">Estoque</a></li>
                <?php if ($_SESSION['tipo_usuario'] == 1): ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="adm.php" style="color: red; font-weight: bold;">⚙️ Painel Administrativo</a></li>
                <li><a class="dropdown-item" href="cad_produto.php" style="color: red;">📦 Cadastro de Produtos</a></li>
                <li><a class="dropdown-item" href="cad_categoria.php" style="color: red;">🏷️ Cadastro de Categorias</a></li>
                <li><a class="dropdown-item" href="cad_usuario.php" style="color: red;">👥 Cadastro de Usuários</a></li>
                <?php endif; ?>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="acesso.php">Logout</a></li>
            </ul>
        </div>
    </header>

    <main class="pdv-main">
        <div class="container-fluid h-100">
            <div class="row justify-content-center h-100">
                <div class="col-lg-7 col-md-8 h-100">
                    <div class="product-panel">
                        <h4 class="panel-title">Categorias de Produtos</h4>
                        <div class="product-grid">
                            <?php foreach ($categorias as $cat): ?>
                                <button class="btn btn-produto" data-bs-toggle="modal"
                                    data-bs-target="#modalCat<?= htmlspecialchars($cat['id_categoria']) ?>">
                                    <?= htmlspecialchars($cat['nome_categoria']) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 h-100">
                    <div class="cart-panel">
                        <h5 class="panel-title">Carrinho</h5>
                        <div class="cart-list p-2">
                            <ul id="carrinhoLista" class="list-group list-group-flush">
                            </ul>
                        </div>
                        <div class="cart-total-container">
                            <p class="mb-2">Total:</p>
                            <h3 id="totalCarrinho" class="cart-total-value mb-3">R$ 0,00</h3>
                            <div class="d-grid gap-2">
                                <button class="btn btn-success btn-lg" onclick="finalizarVenda()">Finalizar
                                    Venda</button>
                                <button class="btn btn-outline-danger" onclick="limparCarrinho()">Limpar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="pdv-footer">
        <span>SISTEMA PDV</span>
    </footer>

    <div class="modal fade" id="exampleModalToggle" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalToggleLabel">Status do Caixa</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($caixa_status === 'aberto'): ?>
                        <form method="post" action="fechar_caixa.php">
                            <button type="submit" class="btn btn-danger w-100">Fechar o Caixa</button>
                        </form>
                    <?php else: ?>
                        <form method="post" action="abrir_caixa.php">
                            <button type="submit" class="btn btn-success w-100">Abrir o Caixa</button>
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
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><?= htmlspecialchars($nomeCat) ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-4">
                            <?php foreach ($produtos_da_categoria as $prod):
                                $inputId = "prod" . (int) $prod['id_produto'];
                                $preco = number_format((float) $prod['preco_venda'], 2, ',', '.');
                                ?>
                                <div class="col-lg-3 col-md-4">
                                    <div class="product-card text-center">
                                        <h6><?= htmlspecialchars($prod['nome']) ?>         <?= htmlspecialchars($prod['sabor']) ?></h6>
                                        <p class="price">R$ <?= $preco ?></p>
                                        <div class="quantity-selector">
                                            <button class="btn btn-outline-danger"
                                                onclick="alterarQtd('<?= $inputId ?>', -1)">-</button>
                                            <input id="<?= htmlspecialchars($inputId) ?>" type="text" value="1" readonly
                                                class="form-control text-center">
                                            <button class="btn btn-outline-success"
                                                onclick="alterarQtd('<?= $inputId ?>', 1)">+</button>
                                        </div>
                                        <button class="btn btn-primary w-100"
                                            onclick="adicionarProduto(<?= (int) $prod['id_produto'] ?>, '<?= addslashes($prod['nome'] . ' ' . $prod['sabor']) ?>', document.getElementById('<?= htmlspecialchars($inputId) ?>').value, <?= (float) $prod['preco_venda'] ?>, '<?= htmlspecialchars($inputId) ?>')">
                                            Adicionar
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="modal fade" id="modalPagamentoPDV" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Finalizar Venda</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h5 class="text-center mb-3">Resumo da Venda</h5>
                            <div class="input-group mb-3"><span class="input-group-text">Subtotal R$</span><input
                                    type="text" id="subtotalPDV" class="form-control" readonly></div>
                            <div class="input-group mb-3"><span class="input-group-text">Total R$</span><input
                                    type="text" id="totalPDV" class="form-control" readonly></div>
                            <hr class="my-4">
                            <div class="input-group mb-3"><span class="input-group-text">Dinheiro R$</span><input
                                    type="number" id="dinheiroPDV" class="form-control" value="0"
                                    oninput="calcularTrocoPDV()"></div>
                            <div class="input-group"><span class="input-group-text">Troco R$</span><input type="text"
                                    id="trocoPDV" class="form-control" readonly></div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-center mb-3">Formas de Pagamento</h5>
                            <div class="mb-3 d-flex flex-wrap gap-2">
                                <button class="btn btn-outline-primary btn-valor-rapido"
                                    onclick="adicionarValorPDV(2)">+2</button>
                                <button class="btn btn-outline-primary btn-valor-rapido"
                                    onclick="adicionarValorPDV(5)">+5</button>
                                <button class="btn btn-outline-primary btn-valor-rapido"
                                    onclick="adicionarValorPDV(10)">+10</button>
                                <button class="btn btn-outline-primary btn-valor-rapido"
                                    onclick="adicionarValorPDV(20)">+20</button>
                                <button class="btn btn-outline-primary btn-valor-rapido"
                                    onclick="adicionarValorPDV(50)">+50</button>
                                <button class="btn btn-outline-primary btn-valor-rapido"
                                    onclick="adicionarValorPDV(100)">+100</button>
                            </div>
                            <div class="d-grid gap-3">
                                <button class="btn btn-success btn-metodo-pagamento"
                                    onclick="finalizarPagamentoPDV('DINHEIRO')"><img src="img/dinheiro.png" alt="Editar"
                                        width="40">DINHEIRO</button>
                                <button class="btn btn-info text-white btn-metodo-pagamento"
                                    onclick="finalizarPagamentoPDV('DÉBITO')"><img src="img/cartao-de-credito.png"
                                        alt="Editar" width="30"> DÉBITO</button>
                                <button class="btn btn-primary btn-metodo-pagamento"
                                    onclick="finalizarPagamentoPDV('CRÉDITO')">💳 CRÉDITO</button>
                                <button class="btn btn-warning btn-metodo-pagamento"
                                    onclick="finalizarPagamentoPDV('PIX')"><img src="img/pix.png" alt="Editar"
                                        width="30"> PIX</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const statusCaixa = '<?php echo $caixa_status ?? "fechado"; ?>';
        let carrinho = [];
        function alterarQtd(id, valor) {
            const input = document.getElementById(id);
            let qtd = parseInt(input.value, 10) + valor;
            if (isNaN(qtd) || qtd < 1) qtd = 1;
            input.value = qtd;
        }
        function adicionarProduto(id, nome, qtd, preco, inputId) {
            if (statusCaixa !== 'aberto') { alert("Não é possível realizar a venda enquanto o caixa estiver fechado. Por favor abra o caixa para efetuar a venda."); return; }
            qtd = parseInt(qtd, 10);
            preco = parseFloat(preco);
            if (qtd > 0 && !isNaN(preco)) {
                const index = carrinho.findIndex(item => item.id === id);
                if (index >= 0) { carrinho[index].qtd += qtd; } else { carrinho.push({ id, nome, qtd, preco }); }
                atualizarCarrinho();
                if (inputId) document.getElementById(inputId).value = 1;
            }
        }
        function atualizarCarrinho() {
            const lista = document.getElementById("carrinhoLista");
            lista.innerHTML = "";
            let total = 0;
            carrinho.forEach((item, i) => {
                total += item.qtd * item.preco;
                const li = document.createElement("li");
                li.className = "list-group-item d-flex justify-content-between align-items-center";
                li.innerHTML = `${item.qtd}x ${item.nome} - ${(item.qtd * item.preco).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}` + `<button class="btn btn-sm btn-danger" onclick="removerProduto(${i})">X</button>`;
                lista.appendChild(li);
            });
            document.getElementById("totalCarrinho").innerText = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        }
        function removerProduto(i) { carrinho.splice(i, 1); atualizarCarrinho(); }
        function limparCarrinho() { carrinho = []; atualizarCarrinho(); }
        function finalizarVenda() {
            if (statusCaixa !== 'aberto') { alert("Não é possível realizar a venda enquanto o caixa estiver fechado. Por favor abra o caixa para efetuar a venda."); return; }
            if (carrinho.length === 0) { alert("Carrinho vazio!"); return; }
            let total = 0;
            carrinho.forEach(item => total += item.qtd * item.preco);
            document.getElementById("subtotalPDV").value = total.toFixed(2).replace('.', ',');
            document.getElementById("totalPDV").value = total.toFixed(2).replace('.', ',');
            document.getElementById("dinheiroPDV").value = '0';
            document.getElementById("trocoPDV").value = "0,00";
            new bootstrap.Modal(document.getElementById("modalPagamentoPDV")).show();
        }
        function calcularTrocoPDV() {
            let total = parseFloat(document.getElementById("totalPDV").value.replace(',', '.')) || 0;
            let pago = parseFloat(document.getElementById("dinheiroPDV").value) || 0;
            let troco = pago - total;
            if (troco < 0) troco = 0;
            document.getElementById("trocoPDV").value = troco.toFixed(2).replace('.', ',');
        }
        function adicionarValorPDV(valor) {
            let dinheiroInput = document.getElementById("dinheiroPDV");
            let atual = parseFloat(dinheiroInput.value) || 0;
            dinheiroInput.value = atual + valor;
            calcularTrocoPDV();
        }
        function finalizarPagamentoPDV(metodo) {
            let totalValor = document.getElementById("totalPDV").value;
            let totalNumerico = parseFloat(totalValor.replace(',', '.'));
            if (carrinho.length === 0) { alert("Carrinho vazio!"); return; }
            if (metodo === 'DINHEIRO') {
                let pago = parseFloat(document.getElementById("dinheiroPDV").value) || 0;
                if (pago < totalNumerico) { alert("O valor pago em dinheiro é menor que o total da venda."); return; }
            }
            const confirmacaoMsg = `Confirmar pagamento de R$ ${totalValor} em ${metodo}?`;
            if (confirm(confirmacaoMsg)) {
                let dados = { total: totalNumerico, forma_pagamento: metodo, itens: carrinho };
                fetch("registrar_venda.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(dados)
                })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.mensagem);
                        if (data.sucesso) {
                            limparCarrinho();
                            const modalPagamento = bootstrap.Modal.getInstance(document.getElementById("modalPagamentoPDV"));
                            if (modalPagamento) { modalPagamento.hide(); }
                            document.getElementById("totalCarrinho").innerText = "R$ 0,00";
                        }
                    })
                    .catch(error => {
                        console.error("Erro ao registrar venda:", error);
                        alert("Ocorreu um erro ao tentar registrar a venda.");
                    });
            }
        }
    </script>
</body>

</html>