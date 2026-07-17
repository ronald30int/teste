<?php
// 1. CONEXÃO COM O BANCO DE DADOS
$host = "localhost";
$usuario_db = "root"; 
$senha_db = "";       
$nome_db = "sistema_entregas";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexao = new mysqli($host, $usuario_db, $senha_db, $nome_db);
    $conexao->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Falha na conexão de forma segura.");
}

// 2. BUSCA TODOS OS PEDIDOS DO BANCO (Mais recentes primeiro)
$resultado = $conexao->query("SELECT id, descricao_pedido, status FROM pedidos ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mercearia Nova Opção</title>
    
    <!-- VÍNCULO DO MANIFEST PARA O PWA -->
    <link rel="manifest" href="manifest.json">
    
    <!-- SCRIPT DE REGISTRO DO SERVICE WORKER -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js')
            .then(function() { console.log('Service Worker Ativo.'); })
            .catch(function(erro) { console.log('Falha no Service Worker:', erro); });
        }
    </script>

    <style>
	/* BOTÃO FIXO DO WHATSAPP */
.btn-whatsapp {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background-color: #25d366; /* Verde oficial */
    color: white;
    text-decoration: none;
    padding: 12px 20px;
    font-weight: bold;
    font-size: 14px;
    border-radius: 50px; /* Formato arredondado */
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3); /* Sombra */
    z-index: 999; /* Fica por cima do conteúdo */
    display: flex;
    align-items: center;
    justify-content: center;
    text-transform: uppercase;
    transition: transform 0.2s ease;
}
.btn-whatsapp:hover {
    transform: scale(1.05); /* Cresce levemente ao passar o mouse */
    background-color: #20ba5a;
}

	
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: Arial, sans-serif;
            background-image: url('pl.webp'); 
            background-size: cover;          /* Faz a imagem cobrir a tela toda */
            background-position: center;    /* Centraliza o fundo */
            background-attachment: fixed;   /* Impede a imagem de subir com a rolagem */
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center;
            min-height: 100vh; 
            padding: 20px 10px; 
        }
        
        /* TELA DE CARREGAMENTO (SPLASH SCREEN) */
        #splash-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background-color: #0099e5; /* Cor azul de fundo caso a imagem demore a carregar */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999; /* Fica na frente de tudo */
            transition: opacity 0.5s ease; /* Efeito suave ao sumir */
        }

        /* COMANDO PARA ENCHER A TELA COM A LOGO SEM DISTORCER */
        .splash-logo {
            width: 100%;
            height: 100vh;
            object-fit: cover; /* Recorta e adapta a imagem para cobrir toda a tela perfeitamente */
        }
        
        .container { width: 100%; max-width: 400px; }
        
        .header { display: flex; justify-content: space-between; align-items: center; padding: 10px 5px; background-color: #0099e5; border-top-left-radius: 4px; border-top-right-radius: 4px; }
        .title { color: white; font-size: 16px; font-weight: bold; text-transform: uppercase; margin-left: 10px; }
        
        .nav-buttons { display: flex; gap: 5px; margin-right: 10px; }
        .nav-btn { background-color: black; color: white; padding: 6px 14px; text-decoration: none; font-weight: bold; font-size: 14px; text-transform: uppercase; border-radius: 2px; }
        
        .content-area { background-color: white; width: 100%; min-height: 480px; padding: 20px; border-bottom-left-radius: 4px; border-bottom-right-radius: 4px; }
        
        /* Bloco de cada pedido */
        .pedido-card { border-bottom: 1px solid #eee; padding: 15px 0; font-size: 14px; color: #333; line-height: 1.5; }
        .pedido-card:last-child { border-bottom: none; }
        
        /* Tarjas de Status */
        .status-txt { font-weight: bold; margin-top: 5px; font-size: 13px; }
        .status-aguardando { color: #dc3545; }
        .status-entrega { color: #17a2b8; }
        .status-concluido { color: #28a745; }
    </style>
</head>
<body>
<!-- BOTÃO FLUTUANTE DO WHATSAPP -->
<a href="https://wa.me/5598996010129?text=" target="_blank" class="btn-whatsapp">
    ATENDENTE HUMANO
</a>


<!-- Tela da Logo cheia (Ocupa 100% do espaço visual) -->
<div id="splash-screen">
    <img src="200.webp" alt="Logo Sistema" class="splash-logo">
</div>

    <div class="container">
        <header class="header">
            <div class="title">RASTREIO</div>
            <div class="nav-buttons">
                <a href="pedido.php" class="nav-btn">Pedir</a>
                <a href="login.php" class="nav-btn">Login</a>
            </div>
        </header>

        <main class="content-area">
            <?php if ($resultado && $resultado->num_rows > 0): ?>
                <?php while($pedido = $resultado->fetch_assoc()): ?>
                    <div class="pedido-card">
                        
                        <?php 
                        $texto_original = $pedido['descricao_pedido'];
                        $partes = explode('|', $texto_original);
                        
                        if (count($partes) >= 3) {
                            $cliente = htmlspecialchars(trim($partes[0]), ENT_QUOTES, 'UTF-8');
                            $produto = htmlspecialchars(trim($partes[2]), ENT_QUOTES, 'UTF-8');
                            echo "<p><strong>$cliente</strong></p>";
                            echo "<p>$produto</p>";
                        } else {
                            echo "<p>" . htmlspecialchars($texto_original, ENT_QUOTES, 'UTF-8') . "</p>";
                        }
                        ?>
                        
                        <!-- Status com cor dinâmica -->
                        <?php 
                        $classe_status = "status-aguardando";
                        if ($pedido['status'] == 'Em Rota' || $pedido['status'] == 'Em Entrega') { 
                            $classe_status = 'status-entrega'; 
                        } elseif ($pedido['status'] == 'Entregue' || $pedido['status'] == 'Concluído' || $pedido['status'] == 'Pago') { 
                            $classe_status = 'status-concluido'; 
                        }
                        ?>
                        <div class="status-txt <?php echo $classe_status; ?>">
                            Status: <?php echo htmlspecialchars($pedido['status'], ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="text-align: center; color: #777; margin-top: 20px;">Nenhum pedido em andamento.</p>
            <?php endif; ?>
        </main>
    </div>

<script>
// Aguarda o carregamento completo da imagem na tela
window.addEventListener('load', () => {
    // Conta exatamente 3 segundos (3000ms)
    setTimeout(() => {
        const splash = document.getElementById('splash-screen');
        if (splash) {
            splash.style.opacity = '0'; // Efeito suave de sumir
            
            setTimeout(() => {
                splash.style.display = 'none'; // Libera os botões da página
            }, 500); 
        }
    }, 3000); 
});
</script>

</body>
</html>
<?php $conexao->close(); ?>
