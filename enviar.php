<?php
// 1. CONEXÃO COM O BANCO DE DADOS
<?php
$host = "tokaido.proxy.rlwy.net";
$user = "root";
$password = "AcniLuZnLuFmmKDvOGfZJjxzvlNoAuar";
$database = "railway";
$port = 42227;

$conexao = new mysqli($host, $usuario_db, $senha_db, $nome_db);

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

// 2. PROCESSAMENTO DO FORMULÁRIO NA MESMA PÁGINA
$mensagem_sucesso = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = htmlspecialchars($_POST['nome']);
    $rua = htmlspecialchars($_POST['rua']);
    $numero = htmlspecialchars($_POST['numero']);
    $produto = htmlspecialchars($_POST['produto']);
    
    // Organiza as informações para salvar na coluna 'descricao_pedido' do banco de dados
    $descricao_pedido = "Cliente: $nome | Rua: $rua, Nº $numero | Produto: $produto";
    $status_inicial = "Aguardando";

    // Insere o novo pedido na tabela
    $stmt = $conexao->prepare("INSERT INTO pedidos (descricao_pedido, status) VALUES (?, ?)");
    $stmt->bind_param("ss", $descricao_pedido, $status_inicial);
    
    if ($stmt->execute()) {
        $mensagem_sucesso = "Pedido enviado com sucesso!";
    } else {
        $mensagem_sucesso = "Erro ao enviar: " . $conexao->error;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fazer Pedido - Mercearia Nova Opção</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background-color: #0099e5; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        
        .container { 
            width: 100%; 
            max-width: 400px; 
            background-color: #0099e5; 
            padding: 5px; 
        }
        
        .header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 10px 5px; 
        }
        
        .title { 
            color: white; 
            font-size: 16px; 
            font-weight: bold; 
            text-transform: uppercase; 
        }
        
        .voltar-btn { 
            background-color: black; 
            color: white; 
            padding: 6px 14px; 
            text-decoration: none; 
            font-weight: bold; 
            font-size: 14px; 
        }
        
        .content-area { 
            background-color: white; 
            width: 100%; 
            min-height: 480px; 
            padding: 20px; 
            border-radius: 0px;
        }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; color: #333; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; font-size: 15px; outline: none; }
        
        .btn-enviar { 
            width: 100%; 
            background-color: black; 
            color: white; 
            border: none; 
            padding: 12px; 
            font-size: 15px; 
            font-weight: bold; 
            text-transform: uppercase; 
            cursor: pointer; 
            margin-top: 10px; 
        }
        
        .alert { padding: 12px; margin-bottom: 15px; font-weight: bold; text-align: center; font-size: 14px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

    <div class="container">
        <header class="header">
            <div class="title">NOVO PEDIDO</div>
            <a href="index.php" class="voltar-btn">Voltar</a>
        </header>

        <main class="content-area">
            <?php if (!empty($mensagem_sucesso)): ?>
                <div class="alert"><?php echo $mensagem_sucesso; ?></div>
            <?php endif; ?>

            <!-- ACTION APONTA PARA A PRÓPRIA PÁGINA AGORA -->
            <form action="pedido.php" method="POST">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required placeholder="Digite seu nome">
                </div>

                <div class="form-group">
                    <label for="rua">Nome da Rua + numero:</label>
                    <input type="text" id="rua" name="rua" required placeholder="Ex: Rua das Flores, 123">
                </div>

                <div class="form-group">
                    <label for="numero">Contato:</label>
                    <input type="text" id="numero" name="numero" required placeholder="Ex: 989000-0000">
                </div>

                <div class="form-group">
                    <label for="produto">Opção:</label>
                    <select id="produto" name="produto" required>
                        <option value="" disabled selected>Selecione um produto...</option>
                        <option value="Mar Doce">Mar Doce</option>
                        <option value="Ilha Bela">Ilha Bela</option>
                        <option value="Gás Butano">Gás Butano</option>
                        <option value="Liquigás">Liquigás</option>
                    </select>
                </div>

                <button type="submit" class="btn-enviar">Confirmar Pedido</button>
            </form>
        </main>
    </div>

</body>
</html>
<?php $conexao->close(); ?>
