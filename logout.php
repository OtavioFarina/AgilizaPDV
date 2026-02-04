<?php
// 1. Inicia a sessão para poder acessá-la
session_start();

// 2. Limpa todas as variáveis da sessão ($_SESSION['nome_usuario'], etc.)
$_SESSION = array();

// 3. (Opcional mas Recomendado) Apaga o cookie da sessão no navegador
// Isso garante que o navegador esqueça o ID da sessão antiga
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 4. Destrói a sessão no servidor
session_destroy();

// 5. Redireciona o usuário de volta para a tela de login
header("Location: index.php");
exit();
?>