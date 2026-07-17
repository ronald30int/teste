<?php
include('conexao.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $conexao->real_escape_string($_POST['nome']);
    $usuario = $conexao->real_escape_string($_POST['usuario']);
    $senha = $_POST['senha']; // Em produção, use password_hash

    $sql = "INSERT INTO entregadores (nome, usuario, senha) VALUES ('$nome', '$usuario', '$senha')";
    
    if ($conexao->query($sql) === TRUE) {
        echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href='login.php';</script>";
    } else {
        $erro = "Erro ao cadastrar: " . $conexao->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Entregador</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background-color: #0099e5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { width: 100%; max-width: 400px; background-color: #0099e5; padding: 10px; border: 5px solid #0099e5; }
        .header { display: flex; justify-content: center; align-items: center; padding: 20px 5px; }
        .title { color: white; font-size: 22px; font-weight: bold; text-transform: uppercase; }
        .content-area { background-color: white; width: 100%; border-radius: 4px; padding: 25px 20px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: bold; color: #333; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .btn-submit { width: 100%; background-color: black; color: white; border: none; padding: 12px; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; text-transform: uppercase; }
        .login-link { text-align: center; margin-top: 15px; display: block; color: #0099e5; text-decoration: none; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <header class="header"><div class="title">CADASTRAR</div></header>
        <main class="content-area">
            <?php if(isset($erro)) echo "<p style='color:red; text-align:center;'>$erro</p>"; ?>
            <form action="" method="POST">
                <div class="form-group">
                    <label>NOME COMPLETO</label>
                    <input type="text" name="nome" placeholder="Digite seu nome completo" required>
                </div>
                <div class="form-group">
                    <label>CPF</label>
                    <input type="text" name="usuario" placeholder="Ex: 00000000" required>
                </div>
                <div class="form-group">
                    <label>SENHA</label>
                    <input type="password" name="senha" placeholder="Digite sua senha" required>
                </div>
                <button type="submit" class="btn-submit">Salvar Cadastro</button>
                <a href="login.php" class="login-link">Voltar para o Login</a>
            </form><br>
			"Por favor, preencha as informações completas, como nome e CPF, e adicione uma foto ao seu perfil."
        </main>
    </div>
</body>
</html>
