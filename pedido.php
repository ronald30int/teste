<?php
// 1. CONEXÃO COM O BANCO DE DADOS
$host = "localhost";
$usuario_db = "root"; 
$senha_db = "";       
$nome_db = "sistema_entregas";

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
    $quantidade = intval($_POST['quantidade']);
    
    // Captura se marcou ou não. Se não marcar, assume "Não" automaticamente sem dar erro.
    $preferencial = isset($_POST['preferencial']) ? "Sim" : "Não";
    
    $descricao_pedido = "Cliente: $nome | Rua: $rua, Nº $numero | Produto: $produto | Qtd: $quantidade | Preferencial: $preferencial";
    $status_inicial = "Aguardando";

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
    <title>Mercearia Nova Opção</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background-color: #0099e5; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 20px; }
        
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
        
        .form-group-checkbox { 
            display: flex; 
            align-items: center; 
            margin-bottom: 20px; 
            gap: 10px;
            padding: 5px 0;
        }
        
        .form-group-checkbox input {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        .form-group-checkbox label { 
            font-weight: bold; 
            color: #333; 
            font-size: 14px; 
            cursor: pointer;
            user-select: none;
        }
        
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
            margin-bottom: 15px;
        }
        
        .alert { padding: 12px; margin-bottom: 15px; font-weight: bold; text-align: center; font-size: 14px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        .aviso-pagamento { text-align: center; font-weight: bold; color: #d9534f; font-size: 13px; line-height: 1.4; }
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

            <form action="pedido.php" method="POST">
                <div class="form-group">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" required placeholder="Digite seu nome">
                </div>

                <div class="form-group">
                    <label for="rua">Nome da Rua + Numero:</label>
                    <input type="text" id="rua" name="rua" required placeholder="Ex: Rua das Flores,123">
                </div>

                <div class="form-group">
                    <label for="numero">Contato:</label>
                    <input type="text" id="numero" name="numero" required placeholder="Ex: 9890000-0000">
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

                <div class="form-group">
                    <label for="quantidade">Quantidade:</label>
                    <input type="number" id="quantidade" name="quantidade" min="1" value="1" required placeholder="Ex: 1">
                </div>

                <!-- Campo Opcional (Sem a tag 'required') -->
                <div class="form-group-checkbox">
                    <input type="checkbox" id="preferencial" name="preferencial" value="sim">
                    <label for="preferencial">Fila Preferencial (Idosos, Gestantes, com Criança, PCD ou Empresa)</label>
                </div>

                <button type="submit" class="btn-enviar">Confirmar Pedido</button>
            </form>
            
            <p class="aviso-pagamento">
                "Pagamento da entrega apenas via Pix: maisomoraes@hotmail.com. Não aceitamos dinheiro para entregadores."
            </p>
        </main>
    </div>

</body>
</html>
<?php $conexao->close(); ?>
