<?php
require_once "config/conexao.php";

$lojas = [];

try {
  $stmt = $conn->query("SELECT id_estabelecimento, nome_estabelecimento FROM estabelecimento ORDER BY nome_estabelecimento ASC");
  $lojas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  // Em caso de erro, segue com array vazio (pode tratar melhor se quiser)
  $lojas = [];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>PDV - Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

  <link rel="stylesheet" href="assets/css/style_login.css">
</head>

<body>
  <script>
    // Force reset to Light Mode on Login screen (as requested)
    localStorage.removeItem('theme');
    // Optional: If you want to force apply light mode immediately to avoid flash if the login page itself has dark mode styles
    document.documentElement.setAttribute('data-theme', 'light');
  </script>

  <div class="login-card">
    <div class="login-header">
      <img src="assets/img/logoagilizasemfundo.png" alt="Logo Agiliza PDV" class="login-logo">
      <h1 class="login-title">Bem-vindo!</h1>
      <p class="login-subtitle">Insira suas credenciais para acessar</p>
    </div>

    <form method="POST" action="login.php">

      <div class="mb-3">
        <div class="input-group">
          <span class="input-group-text"><i class='bx bxs-store'></i></span>
          <select name="id_estabelecimento" class="form-select" required>
            <option value="" selected disabled>Selecione o Estabelecimento</option>
            <?php foreach ($lojas as $loja): ?>
              <option value="<?= $loja['id_estabelecimento'] ?>">
                <?= htmlspecialchars($loja['nome_estabelecimento']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <div class="input-group">
          <span class="input-group-text"><i class='bx bxs-user'></i></span>
          <input type="text" class="form-control" name="nome_usuario" placeholder="Nome de Usuário" required>
        </div>
      </div>

      <div class="mb-4">
        <div class="input-group">
          <span class="input-group-text"><i class='bx bxs-lock-alt'></i></span>
          <input type="password" class="form-control" name="senha" placeholder="Sua Senha" required>
        </div>
      </div>

      <button type="submit" class="btn btn-login" name="bt_login">
        <i class='bx bx-log-in-circle me-2'></i> Entrar no Sistema
      </button>

    </form>

    <div class="footer-text">
      &copy; <?php echo date('Y'); ?> Sistema PDV Agiliza
    </div>
  </div>

</body>

</html>