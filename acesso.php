<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <title>PDV - Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
  <link href="styles/style_login.css" rel="stylesheet">
</head>

<body>
  <div class="login-container">
    <h1 class="text-center mb-4 display-4">Login - PDV</h1>

    <form method="POST" action="login.php">
      <div class="mb-3 input-group">
        <span class="input-group-text"><i class='bx bxs-user'></i></span>
        <input type="text" class="form-control" name="nome_usuario" required>
      </div>

      <div class="mb-3 input-group">
        <span class="input-group-text"><i class='bx bxs-lock-alt'></i></span>
        <input type="password" class="form-control" name="senha" required>
      </div>

      <div>
        <button type="submit" class="btn btn-login" name="bt_login">Entrar</button>
      </div>
    </form>
  </div>
</body>

</html>