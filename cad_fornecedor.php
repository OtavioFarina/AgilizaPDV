<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDV - Cadastro de Fornecedores</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="styles/style_cad.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="top-bar d-flex align-items-center justify-content-between px-3 py-2 bg-light rounded shadow-sm">
        <img src="img/logo pdv.png" class="logo" alt="Logo PDV" style="height:50px;">
        <div class="dropdown">
            <button class="btn dropdown" type="button" id="menuDropdown" data-bs-toggle="dropdown"
                aria-expanded="false">
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
            $nome_fornecedor = $_POST["nome_fornecedor"];
            $cnpj = $_POST["cnpj"];
            $telefone = $_POST["telefone"];
            $email = $_POST["email"];
            $endereco = $_POST["endereco"];

            $sql = $conn->prepare("INSERT INTO fornecedor (nome_fornecedor, cnpj, telefone, email, endereco) 
                VALUES(:nome_fornecedor, :cnpj, :telefone, :email, :endereco)");
            $sql->bindValue(':nome_fornecedor', $nome_fornecedor);
            $sql->bindValue(':cnpj', $cnpj);
            $sql->bindValue(':telefone', $telefone);
            $sql->bindValue(':email', $email);
            $sql->bindValue(':endereco', $endereco);
            $sql->execute();

            $mensagem = "<div class='alert alert-success text-center mt-3'>Fornecedor cadastrado com sucesso!</div>";
        }

        if (isset($_GET["ex"])) {
            $id_fornecedor = $_GET["ex"];
            $sql = $conn->prepare("DELETE FROM fornecedor WHERE id_fornecedor = :id_fornecedor");
            $sql->bindValue(":id_fornecedor", $id_fornecedor);
            $sql->execute();
            header("Location: cad_fornecedor.php"); // Redireciona para limpar a URL
            exit();
        }
    } catch (PDOException $erro) {

        if ($erro->getCode() == '23000') {
            $mensagem = "<div class='alert alert-danger text-center mt-3'>Erro: Este fornecedor não pode ser excluído pois está associado a um ou mais produtos!</div>";
        } else {
            $mensagem = "<div class='alert alert-danger text-center mt-3'>Erro: " . htmlspecialchars($erro->getMessage()) . "</div>";
        }
    }
    ?>

    <div class="main-container">
        <h1 class="text-center mb-4">Cadastro de Fornecedores</h1>

        <?php echo $mensagem; ?>

        <form name="formFornecedor" method="post" action="">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nome_fornecedor" class="form-label">Nome do Fornecedor</label>
                    <input type="text" class="form-control" id="nome_fornecedor" name="nome_fornecedor" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="cnpj" class="form-label">CNPJ</label>
                    <input type="text" class="form-control" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="tel" class="form-control" id="telefone" name="telefone" placeholder="(00) 00000-0000">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="contato@email.com">
                </div>
                <div class="col-12 mb-3">
                    <label for="endereco" class="form-label">Endereço</label>
                    <input type="text" class="form-control" id="endereco" name="endereco">
                </div>
            </div>

            <button type="submit" name="cadastrar" class="btn btn-light w-100">Cadastrar Fornecedor</button>
        </form>

        <?php
        try {
            $fornecedores = $conn->query("SELECT * FROM fornecedor ORDER BY nome_fornecedor ASC");
        } catch (PDOException $e) {
            echo "<div class='alert alert-danger mt-3'>Erro ao buscar fornecedores: " . htmlspecialchars($e->getMessage()) . "</div>";
            $fornecedores = null;
        }
        ?>

        <div class="table-container mt-4">
            <h2 class="text-center mb-4">Fornecedores Cadastrados</h2>

            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CNPJ</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>Alterar</th>
                        <th>Excluir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($fornecedores): ?>
                        <?php while ($fornecedor = $fornecedores->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fornecedor['id_fornecedor']); ?></td>
                                <td><?php echo htmlspecialchars($fornecedor['nome_fornecedor']); ?></td>
                                <td><?php echo htmlspecialchars($fornecedor['cnpj']); ?></td>
                                <td><?php echo htmlspecialchars($fornecedor['telefone']); ?></td>
                                <td><?php echo htmlspecialchars($fornecedor['email']); ?></td>
                                <td><a href="alt_fornecedor.php?al=<?php echo $fornecedor["id_fornecedor"]; ?>"><img
                                            src="img/caneta.png" alt="Editar" width="40"></a></td>
                                <td><a href="cad_fornecedor.php?ex=<?php echo $fornecedor["id_fornecedor"]; ?>"
                                        onclick="return confirm('Tem certeza que deseja excluir este fornecedor?')"><img
                                            src="img/apagar.png" alt="Excluir" width="40"></a></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>

        document.getElementById('cnpj').addEventListener('input', function (e) {
            var value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
            e.target.value = value.slice(0, 18); // Limita o tamanho
        });


        document.getElementById('telefone').addEventListener('input', function (e) {
            var value = e.target.value.replace(/\D/g, '');
            value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
            e.target.value = value.slice(0, 15); // Limita o tamanho
        });
    </script>

</body>

</html>