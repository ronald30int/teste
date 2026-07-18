<?php
session_start();

// 1. CONFIGURAÇÃO DA CONEXÃO COM O BANCO DE DADOS
$host = "tokaido.proxy.rlwy.net";
$user = "root";
$password = "kuijjqJlSUMnqseiUNjuITHvYsrOeUlH@tokaido";
$database = "railway";
$port = 59152;

$conexao = new mysqli($host, $usuario_db, $senha_db, $nome_db);

if ($conexao->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conexao->connect_error);
}

// 2. CALCULA A SOMA TOTAL DAS CARTEIRAS DE TODOS OS ENTREGADORES
$sql_total_carteira = "SELECT SUM(saldo_comissao) AS total_geral FROM entregadores";
$resultado_total = $conexao->query($sql_total_carteira);
$dados_total = $resultado_total->fetch_assoc();
$total_carteira = isset($dados_total['total_geral']) ? $dados_total['total_geral'] : 0.00;
$total_exibir = number_format($total_carteira, 2, ',', '.');

// 3. PROCESSAMENTO DO FORMULÁRIO DE LOGIN
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $conexao->real_escape_string($_POST['usuario']);
    $senha = $_POST['senha'];

    $sql = "SELECT id, nome, senha FROM entregadores WHERE usuario = '$usuario'";
    $resultado = $conexao->query($sql);

    if ($resultado->num_rows > 0) {
        $entregador = $resultado->fetch_assoc();
        
        if ($senha === $entregador['senha']) {
            $_SESSION['entregador_id'] = $entregador['id'];
            $_SESSION['entregador_nome'] = $entregador['nome'];
            
            header("Location: entregador.php");
            exit();
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Usuário não encontrado!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Entregas</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background-color: #0099e5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { width: 100%; max-width: 400px; background-color: #0099e5; padding: 10px; border: 5px solid #0099e5; }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 15px 5px; }
        .login-title { color: white; font-size: 22px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        
        .content-area { background-color: white; width: 100%; border-radius: 4px; padding: 25px 20px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: bold; color: #333; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; outline: none; }
        .form-group input:focus { border-color: #0099e5; }
        .btn-login { width: 100%; background-color: black; color: white; border: none; padding: 12px; font-size: 16px; font-weight: bold; border-radius: 4px; cursor: pointer; text-transform: uppercase; margin-top: 5px; margin-bottom: 20px; }
        .btn-login:hover { background-color: #222; }
        .register-container { text-align: center; border-top: 1px solid #eee; padding-top: 15px; }
        .register-link { color: #0099e5; text-decoration: none; font-size: 14px; font-weight: bold; }
        .register-link:hover { text-decoration: underline; }
        .alert-danger { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; font-size: 14px; margin-bottom: 15px; text-align: center; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container">
        <header class="header">
            <div class="login-title">ENTREGADOR</div>
            <!-- Exibe o faturamento total acumulado de todos os entregadores -->
            <div class="total-badge"></div>
        </header>
        
        <main class="content-area">
            <?php if (isset($erro)): ?>
                <div class="alert-danger"><?php echo $erro; ?></div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label for="usuario">CPF</label>
                    <input type="text" id="usuario" name="usuario" placeholder="Digite seu cpf" required>
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                </div>

                <button type="submit" class="btn-login">Entrar</button>

                <div class="register-container">
                    <a href="cadastrar.php" class="register-link">Cadastrar Novo Entregador</a>
                </div>
            </form>
        </main>
    </div>

</body>
</html>
