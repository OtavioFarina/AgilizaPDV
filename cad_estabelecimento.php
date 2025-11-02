<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PDV - Cadastro de Estabelecimento</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="styles/style_cad.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

  <div class="top-bar d-flex align-items-center justify-content-between px-3 py-2 bg-light rounded shadow-sm">
    <img src="img/logo pdv.png" class="logo" alt="Logo PDV" style="height:50px;">
    <div class="dropdown">
      <button class="btn dropdown" type="button" id="menuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <img src="img/3riscos.png" class="3riscos" alt="Simbolo3Riscos" style="height:25px;">
      </button>
      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="menuDropdown">
        <li>
          <a class="dropdown-item" href="adm.php">Painel Administrativo</a>
        </li>
      </ul>
    </div>
  </div>

  <div class="main-container">
    <h1 class="text-center mb-4">Cadastro de Estabelecimento</h1>

    <?php
    require "conexao.php";

    if (isset($_GET['ex'])) {
      $id_estabelecimento_excluir = $_GET['ex'];

      try {
        $sql = $conn->prepare("DELETE FROM estabelecimento WHERE id_estabelecimento = :id_estabelecimento");
        $sql->bindParam(':id_estabelecimento', $id_estabelecimento_excluir);
        $sql->execute();

        header("Location: cad_estabelecimento.php");
        exit();
      } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Erro ao excluir: " . htmlspecialchars($e->getMessage()) . "</div>";
      }
    }

    if (isset($_POST["cadastrar"])) {
      $nome_estabelecimento = $_POST["nome_estabelecimento"];
      $cep = $_POST["cep"];

      $rua = "";
      $bairro = "";
      $cidade = "";
      $estado = "";

      $url = "https://viacep.com.br/ws/$cep/json/";
      $response = @file_get_contents($url);

      if ($response) {
        $addressData = json_decode($response, true);
        if (isset($addressData['logradouro'])) {
          $rua = $addressData['logradouro'];
          $bairro = $addressData['bairro'] ?? '';
          $cidade = $addressData['localidade'] ?? '';
          $estado = $addressData['uf'] ?? '';
        }
      }

      try {
        $sql = $conn->prepare("INSERT INTO estabelecimento (nome_estabelecimento, cep, rua, bairro, cidade, estado) 
                           VALUES (:nome_estabelecimento, :cep, :rua, :bairro, :cidade, :estado)");

        $sql->bindValue(':nome_estabelecimento', $nome_estabelecimento);
        $sql->bindValue(':cep', $cep);
        $sql->bindValue(':rua', $rua);
        $sql->bindValue(':bairro', $bairro);
        $sql->bindValue(':cidade', $cidade);
        $sql->bindValue(':estado', $estado);

        $sql->execute();

        echo "<div class='alert alert-success'>Cadastro realizado com sucesso!</div>";
      } catch (PDOException $erro) {
        echo "<div class='alert alert-danger'>Erro: " . htmlspecialchars($erro->getMessage()) . "</div>";
      }
    }
    ?>

    <form name="form1" method="post" action="">
      <div class="row">
        <div class="col-sm-12 mt-3">
          <label for="nome_estabelecimento" class="form-label">Nome do Estabelecimento</label>
          <input type="text" class="form-control" id="nome_estabelecimento" name="nome_estabelecimento" required>
        </div>

        <div class="col-sm-12 mt-3">
          <label for="cep" class="form-label">CEP</label>
          <input type="text" class="form-control" id="cep" name="cep" required>
        </div>

        <div class="col-12 mt-3">
          <button type="submit" name="cadastrar" class="btn btn-light w-100">Cadastrar Estabelecimento</button>
        </div>
      </div>
    </form>

    <script>
      document.getElementById('cep').addEventListener('input', function(event) {
        let cep = event.target.value.replace(/\D/g, '');
        if (cep.length > 5) {
          cep = cep.replace(/^(\d{5})(\d)/, '$1-$2');
        }
        event.target.value = cep;
      });

      document.getElementById('cep').addEventListener('blur', function() {
        const cep = this.value.replace(/\D/g, '');

        if (cep.length === 8) {
          fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
              if (!data.erro) {
                document.getElementById('rua').value = data.logradouro;
                document.getElementById('bairro').value = data.bairro;
                document.getElementById('cidade').value = data.localidade;
                document.getElementById('estado').value = data.uf;
              } else {
                alert("CEP não encontrado.");
              }
            })
            .catch(error => console.error('Erro ao buscar CEP:', error));
        }
      });
    </script>

    <?php
    try {
      $usuarios = $conn->query("SELECT * FROM estabelecimento");
    } catch (PDOException $e) {
      echo "<div class='alert alert-danger'>Erro ao buscar usuários: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    ?>

    <div class="table-container">
      <h2 class="text-center mb-4">Estabelecimentos Cadastrados</h2>

      <table class="table table-striped table-bordered">
        <thead>
          <tr>
            <th>Estabelecimento</th>
            <th>CEP</th>
            <th>Rua</th>
            <th>Bairro</th>
            <th>Cidade</th>
            <th>Estado</th>
            <th>Alterar</th>
            <th>Excluir</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($usuario = $usuarios->fetch(PDO::FETCH_ASSOC)) : ?>
            <tr>
              <td><?php echo htmlspecialchars($usuario['nome_estabelecimento']); ?></td>
              <td><?php echo htmlspecialchars($usuario['cep']); ?></td>
              <td><?php echo htmlspecialchars($usuario['rua']); ?></td>
              <td><?php echo htmlspecialchars($usuario['bairro']); ?></td>
              <td><?php echo htmlspecialchars($usuario['cidade']); ?></td>
              <td><?php echo htmlspecialchars($usuario['estado']); ?></td>
              <td><a href="alt_estabelecimento.php?al=<?php echo $usuario["id_estabelecimento"]; ?>"><img src="img/caneta.png" alt="Editar" width="40"></a></td>
              <td><a href="cad_usuario.php?ex=<?php echo $usuario["id_estabelecimento"]; ?>" onclick="return confirm('Tem certeza que deseja excluir este estabelecimento?')"><img src="img/apagar.png" alt="Excluir" width="40">
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>

</html>