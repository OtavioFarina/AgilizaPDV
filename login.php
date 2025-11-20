<?php
session_start();

require_once "conexao.php";

if (isset($_POST['bt_login'])) {
    if (!empty($_POST['nome_usuario']) && !empty($_POST['senha'])) {
        $nome_usuario = $_POST['nome_usuario'];
        $senha = $_POST['senha'];

        try {
            $consulta = $conn->prepare("SELECT id_usuario, tipo_usuario, nome_usuario, senha FROM usuarios WHERE nome_usuario = :nome_usuario");
            $consulta->bindValue(':nome_usuario', $nome_usuario);
            $consulta->execute();
            $row = $consulta->fetch(PDO::FETCH_ASSOC);

            if ($row && password_verify($senha, $row['senha'])) {
                $_SESSION['id_usuario'] = $row['id_usuario'];
                $_SESSION['tipo_usuario'] = $row['tipo_usuario'];
                $_SESSION['nome_usuario'] = $row['nome_usuario'];

                if ($row['tipo'] == 1) {
                    // Se 'tipo' for 1, é um ADMINISTRADOR.
                    header("Location: adm.php");
                    exit();
                } elseif ($row['tipo'] == 0) {
                    // Se 'tipo' for 0 (ou qualquer outro valor), é um USUÁRIO COMUM.
                    header("Location: vendas.php");
                    exit();
                }

            } else {
                echo "<script>
                        alert('Erro no login! Verifique o Usuário e/ou a senha.');
                        window.location.href = 'acesso.php';
                      </script>";
            }
        } catch (PDOException $e) {
            echo "<script>
                    alert('Erro de conexão com o banco de dados: " . $e->getMessage() . "');
                    window.location.href = 'acesso.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Por favor, preencha todos os campos.');
                window.location.href = 'acesso.php';
              </script>";
    }
} else {
    header("Location: acesso.php");
    exit();
}
?>