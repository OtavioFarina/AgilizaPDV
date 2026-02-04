<?php

session_start();
require_once "config/conexao.php";

if (isset($_POST['bt_login'])) {
    // Verifica se todos os campos foram preenchidos, incluindo a loja
    if (!empty($_POST['nome_usuario']) && !empty($_POST['senha']) && !empty($_POST['id_estabelecimento'])) {

        $nome_usuario = $_POST['nome_usuario'];
        $senha = $_POST['senha'];
        $id_loja_selecionada = (int) $_POST['id_estabelecimento']; // Loja escolhida no select

        try {
            // CORREÇÃO AQUI: Adicionado "AND ativo = 1"
            // Agora só busca usuários que não foram excluídos
            $consulta = $conn->prepare("SELECT id_usuario, tipo_usuario, nome_usuario, senha, id_estabelecimento FROM usuarios WHERE nome_usuario = :nome_usuario AND ativo = 1");
            $consulta->bindValue(':nome_usuario', $nome_usuario);
            $consulta->execute();
            $row = $consulta->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                // 1. Verifica a Senha
                if (password_verify($senha, $row['senha'])) {

                    // 2. Verifica permissões de acesso ao estabelecimento
                    $pode_acessar = false;

                    if ($row['tipo_usuario'] == 1) {
                        // Admin: pode acessar qualquer estabelecimento
                        $pode_acessar = true;
                    } else {
                        // Vendedor: só pode acessar o estabelecimento ao qual está vinculado
                        if ($row['id_estabelecimento'] == $id_loja_selecionada) {
                            $pode_acessar = true;
                        }
                    }

                    if ($pode_acessar) {
                        // Login Sucesso!
                        $_SESSION['id_usuario'] = $row['id_usuario'];
                        $_SESSION['tipo_usuario'] = $row['tipo_usuario'];
                        $_SESSION['nome_usuario'] = $row['nome_usuario'];
                        $_SESSION['id_estabelecimento'] = $id_loja_selecionada; // Usa o estabelecimento SELECIONADO

                        if ($row['tipo_usuario'] == 1) {
                            header("Location: adm.php");
                        } else {
                            header("Location: vendas.php");
                        }
                        exit();

                    } else {
                        // Usuário existe, senha ok, mas não tem permissão para esta loja
                        echo "<script>
                                alert('Acesso negado! Você não tem permissão para acessar este estabelecimento.');
                                window.location.href = 'index.php';
                              </script>";
                    }

                } else {
                    // Senha incorreta
                    echo "<script>
                            alert('Senha incorreta!');
                            window.location.href = 'index.php';
                          </script>";
                }
            } else {
                // Usuário não encontrado (ou está inativo/excluído)
                echo "<script>
                        alert('Usuário não encontrado!');
                        window.location.href = 'index.php';
                      </script>";
            }

        } catch (PDOException $e) {
            echo "<script>
                    alert('Erro de conexão: " . addslashes($e->getMessage()) . "');
                    window.location.href = 'index.php';
                  </script>";
        }
    } else {
        echo "<script>
                alert('Por favor, selecione a loja e preencha usuário e senha.');
                window.location.href = 'index.php';
              </script>";
    }
} else {
    header("Location: index.php");
    exit();
}
?>