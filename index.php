<?php
$host = "tokaido.proxy.rlwy.net";
$user = "root";
$password = "kuijjqJlSUMnqseiUNjuITHvYsrOeUlH@tokaido";
$database = "railway";
$port = 59152;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexao = new mysqli($host, $usuario_db, $senha_db, $nome_db);
    $conexao->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Falha na conexão de forma segura.");
}

// 2. BUSCA TODOS OS PEDIDOS DO BANCO (Buscando também a coluna data_hora)
$resultado = $conexao->query("SELECT id, descricao_pedido, status, data_hora FROM pedidos ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mercearia Nova Opção</title>
    
    <!-- VÍNCULO DO MANIFEST PARA O PWA -->
    <link rel="manifest" href="manifest.json">
    
    <!-- SCRIPT DE REGISTRO DO SERVICE WORKER E SPLASH SCREEN -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('sw.js')
            .then(function() { console.log('Service Worker Ativo.'); })
            .catch(function(erro) { console.log('Falha no Service Worker:', erro); });
        }

        window.addEventListener('load', function() {
            setTimeout(function() {
                var splash = document.getElementById('splash-screen');
                if(splash) {
                    splash.style.opacity = '0';
                    setTimeout(function() { splash.style.display = 'none'; }, 500);
                }
            }, 1000);
        });
    </script>

    <style>
        /* BOTÃO FIXO DO WHATSAPP */
        .btn-whatsapp {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #25d366; 
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            font-weight: bold;
            font-size: 14px;
            border-radius: 50px; 
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3); 
            z-index: 999; 
            display: flex;
            align-items: center;
            justify-content: center;
            text-transform: uppercase;
            transition: transform 0.2s ease;
        }
        .btn-whatsapp:hover {
            transform: scale(1.05); 
            background-color: #20ba5a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: Arial, sans-serif;
            background-image: url('pl.webp'); 
            background-size: cover;          
            background-position: center;    
            background-attachment: fixed;   
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
            background-color: #0099e5; 
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999; 
            transition: opacity 0.5s ease; 
        }

        .splash-logo {
            width: 100%;
            height: 100vh;
            object-fit: cover; 
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
        
        /* Destaque visual caso o pedido seja da Fila Preferencial */
        .pedido-preferencial { background-color: #fff9e6; border-left: 4px solid #ffc107; padding: 10px 8px; margin-bottom: 5px; border-radius: 4px; }
        
        /* Estilo da Data e Hora */
        .pedido-data { font-size: 11px; color: #777; margin-bottom: 4px; }

        /* Tarjas de Status */
        .status-txt { font-weight: bold; margin-top: 5px; font-size: 13px; }
        .status-aguardando { color: #dc3545; }
        .status-entrega { color: #17a2b8; }
        .status-concluido { color: #28a745; }
		
		/* CONTAINER DE DOWNLOAD DO APLICATIVO */
        .app-download-container { margin-bottom: 10px; width: 100%; }

        /* Botão Verde de Download com Efeito Hover */
        .install-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background-color: #28a745; 
            color: white;
            text-decoration: none;
            padding: 12px 20px;
            font-weight: bold;
            font-size: 14px;
            border-radius: 4px;
            text-transform: uppercase;
            width: 100%;
            margin-bottom: 10px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .install-btn:hover {
            background-color: #218838; 
            transform: translateY(-2px); 
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .install-btn:active {
            transform: translateY(0);
        }
        
        .tag-pref { display: inline-block; background-color: #ffc107; color: #000; font-size: 11px; font-weight: bold; padding: 2px 6px; border-radius: 3px; margin-bottom: 4px; text-transform: uppercase; }
        .sem-pedidos { text-align: center; color: #777; padding: 40px 0; font-size: 15px; }
    </style>
</head>
<body>

<!-- BOTÃO FLUTUANTE DO WHATSAPP -->
<a href="https://wa.me" target="_blank" class="btn-whatsapp">
    ATENDENTE HUMANO
</a>

<!-- Tela da Logo cheia -->
<div id="splash-screen">
    <img src="200.webp" alt="Logo Sistema" class="splash-logo">
</div>

<div class="container">
    <!-- Container com Ícone, Nome e Botão Instalar Aplicativo -->
    <div class="app-download-container">
        <a href="mercearia.apk" id="meu-link" class="install-btn">
            <span style="font-size: 14px;">INSTALAR APLICATIVO 📲</span>
        </a>
    </div>
    
    <header class="header">
        <div class="title">RASTREIO</div>
        <div class="nav-buttons">
            <a href="pedido.php" class="nav-btn">Pedir</a>
            <a href="login.php" class="nav-btn">Entregador</a>
        </div>
    </header>

    <main class="content-area">
        <?php if ($resultado && $resultado->num_rows > 0): ?>
            <?php while($pedido = $resultado->fetch_assoc()): ?>
                <?php 
                $texto_original = $pedido['descricao_pedido'];
                $partes = explode('|', $texto_original);
                
                // Procura no texto do pedido se a Fila Preferencial está definida como "Sim"
                $e_preferencial = (strpos($texto_original, 'Preferencial: Sim') !== false);
                ?>
                
                <div class="pedido-card <?php echo $e_preferencial ? 'pedido-preferencial' : ''; ?>">
                    
                    <?php if (!empty($pedido['data_hora'])): ?>
                        <div class="pedido-data">
                            📅 <?php echo date('d/m/Y H:i', strtotime($pedido['data_hora'])); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($e_preferencial): ?>
                        <span class="tag-pref">⚡ Fila Preferencial</span>
                    <?php endif; ?>

                    <div>
                        <?php 
                        // Exibe os blocos de informações separados de forma organizada
                        foreach ($partes as $parte) {
                            echo htmlspecialchars(trim($parte)) . "<br>";
                        }
                        ?>
                    </div>

                    <div class="status-txt">
                        Status: 
                        <?php if ($pedido['status'] == 'Aguardando'): ?>
                            <span class="status-aguardando">Aguardando</span>
                        <?php elseif ($pedido['status'] == 'Em entrega' || $pedido['status'] == 'Saiu para entrega'): ?>
                            <span class="status-entrega">Saiu para entrega</span>
                        <?php else: ?>
                            <span class="status-concluido"><?php echo htmlspecialchars($pedido['status']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="sem-pedidos">Nenhum pedido encontrado no momento.</p>
        <?php endif; ?>
    </main>
</div>

</body>
</html>
<?php $conexao->close(); ?>
