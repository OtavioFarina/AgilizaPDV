<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>PDV - Cadastro de Produtos</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

  <link href="styles/style_cad.css" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

  <div class="top-bar d-flex align-items-center justify-content-between px-3 py-2 bg-light rounded shadow-sm">
    <img src="img/logo pdv.png" class="logo" alt="Logo PDV" style="height:50px;">
    <div class="dropdown">
      <button class="btn dropdown" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="img/3riscos.png" class="3riscos" alt="Simbolo3Riscos" style="height:25px;">
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
        <li>
          <a class="dropdown-item" href="adm.php">Painel Administrativo</a>
        </li>
      </ul>
    </div>
  </div>

  <?php
  require_once "conexao.php";

  $mensagem = "";

  try {
    if (isset($_POST["cadastrar"])) {
      $nome = trim($_POST["nome"]);
      $sabor = trim($_POST["sabor"]);
      $id_categoria = (int) $_POST["id_categoria"];
      $id_fornecedor = (int) $_POST["id_fornecedor"];
      $preco_venda = str_replace(',', '.', $_POST["preco_venda"]);
      $preco_compra = str_replace(',', '.', $_POST["preco_compra"]);

      $ins = $conn->prepare("INSERT INTO produto (nome, sabor, id_categoria, id_fornecedor, preco_venda, preco_compra)
                           VALUES (:nome, :sabor, :id_categoria, :id_fornecedor, :preco_venda, :preco_compra)");
      $ins->bindValue(':nome', $nome);
      $ins->bindValue(':sabor', $sabor);
      $ins->bindValue(':id_categoria', $id_categoria, PDO::PARAM_INT);
      $ins->bindValue(':id_fornecedor', $id_fornecedor, PDO::PARAM_INT);
      $ins->bindValue(':preco_venda', $preco_venda);
      $ins->bindValue(':preco_compra', $preco_compra);
      $ins->execute();

      $mensagem = "<div class='alert alert-success text-center mt-3'>Produto cadastrado com sucesso!</div>";
    }

    if (isset($_GET["ex"])) {
      $id = (int) $_GET["ex"];
      $del = $conn->prepare("DELETE FROM produto WHERE id_produto = :id_produto");
      $del->bindValue(':id_produto', $id, PDO::PARAM_INT);
      $del->execute();
      $mensagem = "<div class='alert alert-warning text-center mt-3'>Produto excluído com sucesso!</div>";
    }
  } catch (PDOException $e) {
    $mensagem = "<div class='alert alert-danger text-center mt-3'>Erro: " . htmlspecialchars($e->getMessage()) . "</div>";
  }

  try {
    $stmtCat = $conn->prepare("SELECT id_categoria, nome_categoria FROM categoria ORDER BY nome_categoria ASC");
    $stmtCat->execute();
    $categorias = $stmtCat->fetchAll(PDO::FETCH_ASSOC);

    $stmtFor = $conn->prepare("SELECT id_fornecedor, nome_fornecedor FROM fornecedor ORDER BY nome_fornecedor ASC");
    $stmtFor->execute();
    $fornecedores = $stmtFor->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $categorias = [];
    $fornecedores = [];
    $mensagem .= "<div class='alert alert-danger mt-3'>Erro ao buscar categorias/fornecedores: " . htmlspecialchars($e->getMessage()) . "</div>";
  }

  try {
    $sql = "SELECT p.id_produto AS id_produto, p.nome, p.sabor, p.preco_venda, p.preco_compra,
                 c.nome_categoria, f.nome_fornecedor
          FROM produto p
          LEFT JOIN categoria c ON p.id_categoria = c.id_categoria
          LEFT JOIN fornecedor f ON p.id_fornecedor = f.id_fornecedor
          ORDER BY p.id_produto DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (PDOException $e) {
    $produtos = [];
    $mensagem .= "<div class='alert alert-danger mt-3'>Erro ao buscar os produtos: " . htmlspecialchars($e->getMessage()) . "</div>";
  }
  ?>

  <div class="main-container">
    <h1 class="text-center mb-4">Cadastro de Produtos</h1>
    <?php if (!empty($mensagem))
      echo $mensagem; ?>

    <form name="formProduto" method="post" action="">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="nome" class="form-label">Nome do Produto</label>
          <input type="text" class="form-control" id="nome" name="nome" required>
        </div>

        <div class="col-md-6 mb-3">
          <label for="sabor" class="form-label">Sabor do Produto</label>
          <input type="text" class="form-control" id="sabor" name="sabor" required>
        </div>

        <div class="col-md-6 mb-3">
          <label for="id_categoria" class="form-label">Categoria do Produto</label>
          <select id="id_categoria" name="id_categoria" class="form-select" required>
            <option value="" selected disabled>Informe a categoria do produto</option>
            <?php foreach ($categorias as $row) : ?>
              <option value="<?php echo (int) $row['id_categoria']; ?>">
                <?php echo htmlspecialchars($row['nome_categoria']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label for="id_fornecedor" class="form-label">Fornecedor do Produto</label>
          <select id="id_fornecedor" name="id_fornecedor" class="form-select" required>
            <option value="" selected disabled>Informe o fornecedor do produto</option>
            <?php foreach ($fornecedores as $row) : ?>
              <option value="<?php echo (int) $row['id_fornecedor']; ?>">
                <?php echo htmlspecialchars($row['nome_fornecedor']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label for="preco_venda" class="form-label">Preço do Produto</label>
          <input type="text" class="form-control" id="preco_venda" name="preco_venda" required>
        </div>

        <div class="col-md-6 mb-3">
          <label for="preco_compra" class="form-label">Preço de Compra</label>
          <input type="text" class="form-control" id="preco_compra" name="preco_compra" required>
        </div>
      </div>

      <button type="submit" name="cadastrar" class="btn btn-primary w-100">Cadastrar Produto</button>

      <div class="table-container mt-5">
        <h2 class="text-center mb-3">Produtos Cadastrados</h2>
        <div class="table-responsive">
          <table class="table table-striped table-bordered">
            <thead>
              <tr>
                <th>ID do Produto</th>
                <th>Nome</th>
                <th>Sabor</th>
                <th>Categoria</th>
                <th>Fornecedor</th>
                <th>Preço de Venda</th>
                <th>Alterar</th>
                <th>Excluir</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($produtos)) : ?>
                <?php foreach ($produtos as $produto) : ?>
                  <tr>
                    <td><?php echo htmlspecialchars($produto['id_produto']); ?></td>
                    <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                    <td><?php echo htmlspecialchars($produto['sabor']); ?></td>
                    <td><?php echo htmlspecialchars($produto['nome_categoria'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($produto['nome_fornecedor'] ?? '-'); ?></td>
                    <td><?php echo htmlspecialchars($produto['preco_venda']); ?></td>
                    <td>
                      <a href="alt_produto.php?al=<?php echo (int) $produto['id_produto']; ?>">
                        <img src="img/caneta.png" alt="Editar" width="40">
                      </a>
                    </td>
                    <td>
                      <a href="?ex=<?php echo (int) $produto['id_produto']; ?>" onclick="return confirm('Tem certeza que deseja excluir este produto?')">
                        <img src="img/apagar.png" alt="Excluir" width="40">
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else : ?>
                <tr>
                  <td colspan="9" class="text-center">Nenhum produto cadastrado.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </form>
  </div>

</body>

</html>