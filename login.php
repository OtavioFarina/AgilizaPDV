<?php
session_start();

require_once "conexao.php";

if (isset($_POST['bt_login'])) {
    if (!empty($_POST['nome_usuario']) && !empty($_POST['senha'])) {
        $nome_usuario = $_POST['nome_usuario'];
        $senha = $_POST['senha'];

        try {
            // 1. Busca os dados corretos (id, tipo, nome, senha)
            $consulta = $conn->prepare("SELECT id_usuario, tipo_usuario, nome_usuario, senha FROM usuarios WHERE nome_usuario = :nome_usuario");
            $consulta->bindValue(':nome_usuario', $nome_usuario);
            $consulta->execute();
            $row = $consulta->fetch(PDO::FETCH_ASSOC);

            if ($row && password_verify($senha, $row['senha'])) {
                // 2. Salva na sessão
                $_SESSION['id_usuario'] = $row['id_usuario'];
                $_SESSION['tipo_usuario'] = $row['tipo_usuario']; // Salva o tipo ('admin' ou 'vendedor')
                $_SESSION['nome_usuario'] = $row['nome_usuario'];

                // 3. VERIFICAÇÃO CORRIGIDA
                // Note que agora usamos $row['tipo_usuario'], igual está no banco
                
                // Verifica se é ADM (tipo_usuario = 1)
                if ($row['tipo_usuario'] == 1) { 
                    // É ADMINISTRADOR -> Vai para o painel completo
                    header("Location: adm.php");
                    exit();
                } else {
                    // É VENDEDOR -> Vai para a tela de vendas
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