<?php
session_start();

// 1. VERIFICA LOGIN
if (!isset($_SESSION['entregador_id'])) {
    header("Location: login.php");
    exit();
}

// 2. CONEXÃO COM O BANCO DE DADOS
$host = "localhost";
$usuario_db = "root"; 
$senha_db = "";       
$nome_db = "sistema_entregas";
$conexao = new mysqli($host, $usuario_db, $senha_db, $nome_db);

$id_logado = intval($_SESSION['entregador_id']);
$mensagem = "";

// 3. BUSCA O NOME DO ENTREGADOR E A FOTO ATUAL
$stmt = $conexao->prepare("SELECT nome, foto FROM entregadores WHERE id = ?");
$stmt->bind_param("i", $id_logado);
$stmt->execute();
$resultado = $stmt->get_result()->fetch_assoc();
$nome_entregador = $resultado ? $resultado['nome'] : "entregador";
$foto_atual = $resultado['foto'] ?? "";
$stmt->close();

// 4. PROCESSA A FOTO VINDA DA CÂMERA (VALIDAÇÃO OBRIGATÓRIA)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verifica se o arquivo foi realmente enviado e se não possui erros de upload
    if (!isset($_FILES['foto_camera']) || $_FILES['foto_camera']['error'] !== UPLOAD_ERR_OK || empty($_FILES['foto_camera']['tmp_name'])) {
        $mensagem = "<div style='background-color:#f8d7da; color:#721c24; padding:10px; border-radius:4px; margin-bottom:15px; font-weight:bold; font-size:13px;'>Erro: É obrigatório tirar uma foto válida!</div>";
    } else {
        // Validação extra: Verifica se o arquivo é realmente uma imagem
        $verificar_imagem = @getimagesize($_FILES['foto_camera']['tmp_name']);
        
        if ($verificar_imagem === false) {
            $mensagem = "<div style='background-color:#f8d7da; color:#721c24; padding:10px; border-radius:4px; margin-bottom:15px; font-weight:bold; font-size:13px;'>Erro: O arquivo enviado não é uma imagem válida!</div>";
        } else {
            $diretorio = "uploads/";
            
            // Cria o diretório caso não exista
            if (!is_dir($diretorio)) {
                mkdir($diretorio, 0777, true);
            }

            // Normaliza o nome do arquivo para evitar quebras por caracteres especiais
            $nome_limpo = preg_replace("/[^a-zA-Z0-9]/", "_", strtolower($nome_entregador));
            $extensao = pathinfo($_FILES['foto_camera']['name'], PATHINFO_EXTENSION);
            if (empty($extensao)) { $extensao = "jpg"; } // Fallback seguro caso falte extensão no blob
            
            $nome_arquivo = $id_logado . "_" . $nome_limpo . "." . $extensao;
            $caminho_final = $diretorio . $nome_arquivo;

            // Se o upload físico der certo, registra o caminho exato no SQL
            if (move_uploaded_file($_FILES['foto_camera']['tmp_name'], $caminho_final)) {
                $stmt_update = $conexao->prepare("UPDATE entregadores SET foto = ? WHERE id = ?");
                $stmt_update->bind_param("si", $caminho_final, $id_logado);
                $stmt_update->execute();
                $stmt_update->close();
                
                $foto_atual = $caminho_final;
                $mensagem = "<div style='background-color:#d4edda; color:#155724; padding:10px; border-radius:4px; margin-bottom:15px; font-weight:bold; font-size:13px;'>Foto atualizada e salva com sucesso!</div>";
            } else {
                $mensagem = "<div style='background-color:#f8d7da; color:#721c24; padding:10px; border-radius:4px; margin-bottom:15px; font-weight:bold; font-size:13px;'>Erro técnico ao mover o arquivo para o servidor.</div>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mudar Foto de Perfil</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background-color: #0099e5; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 10px; }
        .box-foto { background: white; padding: 25px; border-radius: 8px; text-align: center; max-width: 340px; width: 100%; box-shadow: 0px 4px 12px rgba(0,0,0,0.2); }
        .avatar-container { position: relative; width: 130px; height: 130px; margin: 0 auto 15px auto; }
        .avatar-label { display: block; width: 130px; height: 130px; border-radius: 50%; background-color: #eceb1b; border: 4px solid #eceb1b; overflow: hidden; cursor: pointer; position: relative; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .avatar-label img { width: 100%; height: 100%; object-fit: cover; }
        .camera-icon { position: absolute; bottom: 0; background: rgba(0,0,0,0.6); width: 100%; color: white; font-size: 11px; padding: 5px 0; text-transform: uppercase; font-weight: bold; }
        #foto_camera { display: none; }
        .btn-voltar { display: inline-block; margin-top: 20px; color: #0099e5; text-decoration: none; font-weight: bold; font-size: 14px; text-transform: uppercase; }
        .btn-voltar:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="box-foto">
    <h2 style="color: #333; margin-bottom: 5px; font-size: 20px;">FOTO DE PERFIL</h2>
    <p style="color: #666; font-size: 13px; margin-bottom: 20px;">Toque na bolinha amarela para ativar a câmera</p>
    
    <!-- Exibe os alertas de sucesso ou de obrigatoriedade -->
    <?php echo $mensagem; ?>

    <form action="" method="POST" enctype="multipart/form-data" id="form-foto">
        <div class="avatar-container">
            <label for="foto_camera" class="avatar-label">
                <?php if(!empty($foto_atual) && file_exists($foto_atual)): ?>
                    <img src="<?php echo $foto_atual; ?>?t=<?php echo time(); ?>" alt="Foto">
                <?php else: ?>
                    <div style="font-size: 40px; color: #0099e5; line-height: 122px; font-weight: bold;">📸</div>
                <?php endif; ?>
                <div class="camera-icon">Abrir Câmera</div>
            </label>
            
            <!-- Restringe nativamente para acionar apenas a captura de câmera do celular -->
            <input type="file" name="foto_camera" id="foto_camera" accept="image/*" capture="camera" onchange="enviarFotoSegura()" required>
        </div>
        <strong style="color: #0099e5; text-transform: uppercase; font-size: 16px;"><?php echo htmlspecialchars($nome_entregador, ENT_QUOTES, 'UTF-8'); ?></strong>
    </form>

    <br>
    <a href="entregador.php" class="btn-voltar">← Voltar ao Painel</a>
</div>

<script>
// Função javascript que intercepta o fluxo e obriga a seleção antes do submit automático
function enviarFotoSegura() {
    var campoInput = document.getElementById('foto_camera');
    
    if (campoInput.files && campoInput.files.length > 0) {
        var arquivoValido = campoInput.files[0];
        
        // Verifica se o arquivo de fato possui tamanho maior que zero bytes
        if (arquivoValido.size > 0) {
            document.getElementById('form-foto').submit();
        } else {
            alert("Erro: Arquivo de imagem inválido capturado pela câmera.");
        }
    } else {
        alert("Aviso: Você precisa capturar uma foto com a câmera para continuar.");
    }
}
</script>

</body>
</html>
