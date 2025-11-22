<?php
session_start();
require_once "conexao.php";

// Verifica login
if (!isset($_SESSION['nome_usuario'])) {
    die("Acesso negado.");
}

// Verifica ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Venda não informada.");
}

$id_venda = (int) $_GET['id'];

try {
    // 1. Busca dados da Venda e Forma de Pagamento
    // Nota: Ajustei o JOIN para pegar o nome do pagamento corretamente
    $sqlVenda = "SELECT v.*, f.nome_pagamento 
                 FROM vendas v 
                 JOIN forma_pagamento f ON v.id_forma_pagamento = f.id_forma_pagamento 
                 WHERE v.id_venda = :id";
    $stmt = $conn->prepare($sqlVenda);
    $stmt->execute([':id' => $id_venda]);
    $venda = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venda) {
        die("Venda não encontrada.");
    }

    // 2. Busca os itens da venda (baseado na sua tabela saida_produtos)
    $sqlItens = "SELECT s.quantidade, p.nome, p.sabor, p.preco_venda 
                 FROM saida_produtos s
                 JOIN produto p ON s.id_produto = p.id_produto
                 WHERE s.venda_id = :id";
    $stmtItens = $conn->prepare($sqlItens);
    $stmtItens->execute([':id' => $id_venda]);
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro no banco: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Cupom #<?= $id_venda ?></title>
    <style>
        /* Estilo para simular papel térmico */
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        .cupom {
            width: 300px;
            /* Largura padrão de impressora 80mm */
            background-color: #fff9c4;
            /* Cor amarelada de cupom */
            padding: 15px;
            margin: 0 auto;
            border: 1px dashed #ccc;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        .divisor {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .btn-print {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 20px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        /* Apenas o cupom aparece na impressão */
        @media print {
            body {
                background: none;
                padding: 0;
            }

            .cupom {
                box-shadow: none;
                border: none;
                width: 100%;
                margin: 0;
                background-color: white;
            }

            .btn-print,
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="cupom">
        <div class="text-center">
            <h3 style="margin: 0;">ATACADÃO DO SORVETE</h3>
            <small>Sistema PDV Agiliza</small><br>
            <small><?= date('d/m/Y H:i', strtotime($venda['data_hora'])) ?></small>
        </div>

        <div class="divisor"></div>

        <div class="text-center fw-bold mb-2">CUPOM NÃO FISCAL</div>
        <div class="text-center">Venda Nº: #<?= str_pad($id_venda, 6, '0', STR_PAD_LEFT) ?></div>

        <div class="divisor"></div>

        <?php foreach ($itens as $item):
            $subtotal = $item['quantidade'] * $item['preco_venda'];
            ?>
            <div class="item-row">
                <div style="flex: 2;">
                    <?= $item['quantidade'] ?>x <?= $item['nome'] ?>     <?= $item['sabor'] ?>
                </div>
                <div style="flex: 1;" class="text-right">
                    R$ <?= number_format($subtotal, 2, ',', '.') ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="divisor"></div>

        <div class="item-row fw-bold" style="font-size: 16px;">
            <span>TOTAL A PAGAR</span>
            <span>R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></span>
        </div>

        <div class="item-row" style="margin-top: 5px;">
            <span>Forma de Pagamento:</span>
            <span><?= mb_strtoupper($venda['nome_pagamento']) ?></span>
        </div>

        <div class="divisor"></div>
        <div class="text-center">
            <small>Obrigado pela preferência!</small><br>
            <small>Volte sempre! 🍦</small>
        </div>

        <button class="btn-print" onclick="window.print()">IMPRIMIR AGORA</button>
    </div>

    <script>
        // Tenta imprimir automaticamente ao abrir a janela
        window.onload = function () {
            // Pequeno delay para carregar estilos
            setTimeout(() => {
                window.print();
            }, 500);
        }
    </script>

</body>

</html>