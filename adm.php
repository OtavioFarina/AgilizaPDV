<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDV - Painel Administrativo</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="styles/style_adm.css" rel="stylesheet">

<body>

  <div class="top-bar d-flex align-items-center justify-content-between px-3 py-2">
    <img src="img/logo pdv.png" class="logo" alt="Logo PDV" style="height:50px;">
    <div class="dropdown">
      <button class="btn" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="img/3riscos.png" class="3riscos" alt="Simbolo3Riscos" style="height:25px;">
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
        <li><a class="dropdown-item" href="vendas.php">Vendas (PDV)</a></li>
        <li><a class="dropdown-item" href="estoque.php">Gestão de Estoque</a></li>
        <li><a class="dropdown-item" href="consulta_caixa.php">Relatório de Caixa</a></li>
      </ul>
    </div>
  </div>

  <div class="container main-container">
    <h2 class="page-title">Painel Administrativo</h2>

    <div class="row g-4">
      <div class="col-md-6 col-lg-4">
        <div class="adm-card">
          <div>
            <h5>Produtos</h5>
            <p>Adicione, edite ou remova produtos e seus sabores.</p>
          </div>
          <a href="cad_produto.php" class="btn btn-primary w-100">Gerenciar Produtos</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="adm-card">
          <div>
            <h5>Categorias</h5>
            <p>Gerencie as categorias dos produtos (Ex: Potes, Picolés).</p>
          </div>
          <a href="cad_categoria.php" class="btn btn-primary w-100">Gerenciar Categorias</a>
        </div>
      </div>

      <div class="col-md-6 col-lg-4">
        <div class="adm-card">
          <div>
            <h5>Fornecedores</h5>
            <p>Cadastre os fornecedores dos seus produtos.</p>
          </div>
          <a href="cad_fornecedor.php" class="btn btn-primary w-100">Gerenciar Fornecedores</a>
        </div>
      </div>

      <div class="col-md-6 col-lg-4">
        <div class="adm-card">
          <div>
            <h5>Usuários</h5>
            <p>Adicione ou edite os vendedores e administradores.</p>
          </div>
          <a href="cad_usuario.php" class="btn btn-primary w-100">Gerenciar Usuários</a>
        </div>
      </div>
      <div class="col-md-6 col-lg-4">
        <div class="adm-card">
          <div>
            <h5>Estabelecimentos</h5>
            <p>Gerencie as lojas ou pontos de venda do sistema.</p>
          </div>
          <a href="cad_estabelecimento.php" class="btn btn-primary w-100">Gerenciar Lojas</a>
        </div>
      </div>

      <div class="col-md-6 col-lg-4">
        <div class="adm-card">
          <div>
            <h5>Estoque</h5>
            <p>Veja o saldo atual de produtos e dê entrada em novas mercadorias.</p>
          </div>
          <a href="estoque.php" class="btn btn-outline-secondary w-100">Ver Estoque</a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>