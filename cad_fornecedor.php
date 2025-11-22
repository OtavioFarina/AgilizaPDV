<?php
session_start();

if (!isset($_SESSION['nome_usuario'])) {
    header("Location: acesso.php");
    exit();
}

$tipo_usuario = isset($_SESSION['tipo_usuario']) ? $_SESSION['tipo_usuario'] : 0;
if ($tipo_usuario != 1) {
    echo "<script>alert('Acesso Negado.'); window.location.href='vendas.php';</script>";
    exit();
}

require_once "conexao.php";
$mensagem_swal = "";

try {
    if (isset($_POST["cadastrar"])) {
        $nome_fornecedor = $_POST["nome_fornecedor"];
        $cnpj = $_POST["cnpj"];
        $telefone = $_POST["telefone"];
        $email = $_POST["email"];
        $endereco = $_POST["endereco"];

        $sql = $conn->prepare("INSERT INTO fornecedor (nome_fornecedor, cnpj, telefone, email, endereco) VALUES(:nome, :cnpj, :tel, :email, :end)");
        $sql->execute([':nome' => $nome_fornecedor, ':cnpj' => $cnpj, ':tel' => $telefone, ':email' => $email, ':end' => $endereco]);

        $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Sucesso!', text: 'Fornecedor cadastrado.', showConfirmButton: false, timer: 1500 });";
    }

    if (isset($_GET["ex"])) {
        $id_fornecedor = $_GET["ex"];
        $sql = $conn->prepare("DELETE FROM fornecedor WHERE id_fornecedor = :id");
        $sql->bindValue(":id", $id_fornecedor);

        if ($sql->execute()) {
            header("Location: cad_fornecedor.php?msg=excluido");
            exit();
        }
    }

    if (isset($_GET['msg']) && $_GET['msg'] == 'excluido') {
        $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Excluído!', text: 'Fornecedor removido.', timer: 2000, showConfirmButton: false });";
    }

} catch (PDOException $erro) {
    if ($erro->getCode() == '23000') {
        $msg = "Este fornecedor possui produtos cadastrados. Exclua os produtos primeiro.";
    } else {
        $msg = $erro->getMessage();
    }
    $mensagem_swal = "Swal.fire({ icon: 'error', title: 'Erro!', text: '$msg' });";
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDV - Fornecedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="styles/style_cad.css" rel="stylesheet">
</head>

<body>
    <div class="top-bar">
        <div class="d-flex align-items-center gap-3">
            <img src="img/logoagilizasemfundo.png" class="logo" alt="Logo">
            <h5 class="m-0 fw-bold text-secondary d-none d-md-block">Administrativo</h5>
        </div>
        <div class="dropdown">
            <button class="btn btn-outline-secondary border-0" type="button" data-bs-toggle="dropdown"><i
                    class='bx bx-menu fs-3'></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item py-2" href="adm.php"><i class='bx bxs-dashboard'></i> Voltar ao Painel</a>
                </li>
                <li>
                    <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-container">
        <h2 class="page-title"><i class='bx bx-truck'></i> Gerenciar Fornecedores</h2>

        <div class="form-card">
            <h5 class="mb-4 text-primary fw-bold">Novo Fornecedor</h5>
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome da Empresa</label>
                        <input type="text" class="form-control" name="nome_fornecedor" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">CNPJ</label>
                        <input type="text" class="form-control" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Telefone</label>
                        <input type="tel" class="form-control" id="telefone" name="telefone"
                            placeholder="(00) 00000-0000">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">E-mail</label>
                        <input type="email" class="form-control" name="email" placeholder="contato@empresa.com">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Endereço</label>
                        <input type="text" class="form-control" name="endereco">
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" name="cadastrar" class="btn btn-primary w-100 btn-lg"><i
                                class='bx bx-save'></i> Cadastrar Fornecedor</button>
                    </div>
                </div>
            </form>
        </div>

        <?php
        $fornecedores = $conn->query("SELECT * FROM fornecedor ORDER BY nome_fornecedor ASC");
        ?>

        <div class="table-container">
            <h5 class="mb-4 text-secondary fw-bold">Fornecedores Cadastrados</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>CNPJ</th>
                            <th>Contato</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $fornecedores->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td class="fw-bold text-dark"><?= htmlspecialchars($row['nome_fornecedor']); ?></td>
                                <td class="text-muted"><?= htmlspecialchars($row['cnpj']); ?></td>
                                <td>
                                    <div class="d-flex flex-column small">
                                        <span><i class='bx bx-phone'></i> <?= htmlspecialchars($row['telefone']); ?></span>
                                        <span><i class='bx bx-envelope'></i> <?= htmlspecialchars($row['email']); ?></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <a href="alt_fornecedor.php?al=<?= $row["id_fornecedor"]; ?>"
                                        class="btn btn-sm btn-outline-primary border-0"><i
                                            class='bx bx-edit-alt fs-5'></i></a>
                                    <a href="#" onclick="confirmarExclusao(<?= $row['id_fornecedor']; ?>)"
                                        class="btn btn-sm btn-outline-danger border-0"><i class='bx bx-trash fs-5'></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if (!empty($mensagem_swal))
            echo $mensagem_swal; ?>
        function confirmarExclusao(id) {
            Swal.fire({ title: 'Tem certeza?', text: "O fornecedor será removido.", icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#3085d6', confirmButtonText: 'Sim, excluir!' }).then((result) => { if (result.isConfirmed) window.location.href = `?ex=${id}`; })
        }
        // Máscaras Simples
        document.getElementById('cnpj').addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, "");
            v = v.replace(/^(\d{2})(\d)/, "$1.$2");
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
            v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
            v = v.replace(/(\d{4})(\d)/, "$1-$2");
            e.target.value = v.slice(0, 18);
        });
        document.getElementById('telefone').addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, "");
            v = v.replace(/^(\d{2})(\d)/g, "($1) $2");
            v = v.replace(/(\d{5})(\d)/, "$1-$2");
            e.target.value = v.slice(0, 15);
        });
    </script>
</body>

</html>