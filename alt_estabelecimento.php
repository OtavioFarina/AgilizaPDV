<?php
session_start();

if (!isset($_SESSION['nome_usuario']) || (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] != 1)) {
    header("Location: acesso.php");
    exit();
}

require_once "conexao.php";
$mensagem_swal = "";
$dados = [];

if (isset($_GET["al"])) {
    $id = (int) $_GET["al"];
    $stmt = $conn->prepare("SELECT * FROM estabelecimento WHERE id_estabelecimento = :id");
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$dados) {
        header("Location: cad_estabelecimento.php");
        exit;
    }
}

if (isset($_POST["alterar"])) {
    $id = $_POST["id_estabelecimento"];
    $nome = $_POST["nome_estabelecimento"];
    $cep = $_POST["cep"];
    $rua = $_POST["rua"];
    $bairro = $_POST["bairro"];
    $cidade = $_POST["cidade"];
    $estado = $_POST["estado"];

    try {
        $sql = $conn->prepare("UPDATE estabelecimento SET nome_estabelecimento=:nome, cep=:cep, rua=:rua, bairro=:bairro, cidade=:cid, estado=:est WHERE id_estabelecimento=:id");
        $sql->execute([':nome' => $nome, ':cep' => $cep, ':rua' => $rua, ':bairro' => $bairro, ':cid' => $cidade, ':est' => $estado, ':id' => $id]);

        $mensagem_swal = "Swal.fire({ icon: 'success', title: 'Atualizado!', text: 'Loja alterada com sucesso.', timer: 2000, showConfirmButton: false }).then(() => { window.location.href = 'cad_estabelecimento.php'; });";
    } catch (PDOException $erro) {
        $mensagem_swal = "Swal.fire({ icon: 'error', title: 'Erro', text: '" . $erro->getMessage() . "' });";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>PDV - Alterar Loja</title>
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
                <li><a class="dropdown-item py-2" href="cad_estabelecimento.php"><i class='bx bx-arrow-back'></i>
                        Voltar</a></li>
                <li><a class="dropdown-item py-2 text-danger" href="logout.php"><i class='bx bx-log-out'></i> Sair</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-container">
        <h2 class="page-title"><i class='bx bx-edit'></i> Alterar Loja</h2>
        <div class="form-card">
            <form method="POST">
                <input type="hidden" name="id_estabelecimento" value="<?= $dados['id_estabelecimento'] ?>">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Nome da Loja</label>
                        <input type="text" class="form-control" name="nome_estabelecimento"
                            value="<?= htmlspecialchars($dados['nome_estabelecimento']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">CEP</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="cep" name="cep"
                                value="<?= htmlspecialchars($dados['cep']) ?>" required>
                            <button class="btn btn-outline-secondary" type="button"><i
                                    class='bx bx-search'></i></button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Rua</label>
                        <input type="text" class="form-control bg-light" id="rua" name="rua"
                            value="<?= htmlspecialchars($dados['rua']) ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bairro</label>
                        <input type="text" class="form-control bg-light" id="bairro" name="bairro"
                            value="<?= htmlspecialchars($dados['bairro']) ?>" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cidade</label>
                        <input type="text" class="form-control bg-light" id="cidade" name="cidade"
                            value="<?= htmlspecialchars($dados['cidade']) ?>" readonly>
                    </div>
                    <div class="col-md-2 d-none"> <!-- Oculto mas enviado -->
                        <input type="hidden" id="estado" name="estado"
                            value="<?= htmlspecialchars($dados['estado']) ?>">
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" name="alterar" class="btn btn-primary w-100 btn-lg"><i
                                class='bx bx-save'></i> Salvar Alterações</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        <?php if (!empty($mensagem_swal))
            echo $mensagem_swal; ?>
        const cepInput = document.getElementById('cep');
        cepInput.addEventListener('input', (e) => { let v = e.target.value.replace(/\D/g, "").replace(/^(\d{5})(\d)/, "$1-$2"); e.target.value = v.slice(0, 9); });
        cepInput.addEventListener('blur', () => {
            const cep = cepInput.value.replace(/\D/g, '');
            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`).then(res => res.json()).then(data => {
                    if (!data.erro) {
                        document.getElementById('rua').value = data.logradouro;
                        document.getElementById('bairro').value = data.bairro;
                        document.getElementById('cidade').value = data.localidade;
                        document.getElementById('estado').value = data.uf;
                    }
                });
            }
        });
    </script>
</body>

</html>