<?php
// 1. CONEXÃO COM O BANCO DE DADOS
$host = "tokaido.proxy.rlwy.net";
$user = "root";
$password = "kuijjqJlSUMnqseiUNjuITHvYsrOeUlH@tokaido";
$database = "railway";
$port = 59152;
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
    $quantidade = intval($_POST['quantidade']); // Captura a quantidade informada
    
    // Agora inclui a QUANTIDADE no padrão de texto separado por "|"
    $descricao_pedido = "Cliente: $nome | Rua: $rua, Nº $numero | Produto: $produto | Qtd: $quantidade";
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
    <title>Mercearia Nova Opção</title>
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

            <!-- NOVO CAMPO: QUANTIDADE -->
            <div class="form-group">
                <label for="quantidade">Quantidade:</label>
                <input type="number" id="quantidade" name="quantidade" min="1" value="1" required placeholder="Ex: 1">
            </div>

            <button type="submit" class="btn-enviar">Confirmar Pedido</button>
        </form>
                </form>
        
         <!-- NOVO: Botão interativo para Ativação do Pedido por Voz -->
        
        

        <br>
        <p style="text-align: center; font-weight: bold; color: #d9534f;">
            "Pagamento da entrega apenas via Pix: maisomoraes@hotmail.com. Não aceitamos dinheiro para entregadores."
        </p>
    </main>
</div>

<!-- Elemento oculto para liberar a API de áudio automaticamente no carregamento -->
<iframe style="display:none;" src="about:blank"></iframe>
<script>
// Variáveis de controle do assistente de voz por etapas
let mensagemInstancia = null;
let reconhecimento = null;
let etapaAtual = 0; // 0: Saudação, 1: Produto, 2: Nome, 3: Rua, 4: Contato, 5: Fim

// 1. FUNÇÃO: Central de comando de fala da IA
function falar(texto, proximaFuncao = null) {
    window.speechSynthesis.cancel(); // Limpa áudios travados
    
    mensagemInstancia = new SpeechSynthesisUtterance(texto);
    mensagemInstancia.lang = 'pt-BR';
    mensagemInstancia.rate = 1.0;

    if (proximaFuncao) {
        mensagemInstancia.onend = proximaFuncao;
    }
    window.speechSynthesis.speak(mensagemInstancia);
}

// 2. FUNÇÃO: Inicia o processo quando o cliente clica no botão verde
function iniciarReconhecimentoManual() {
    etapaAtual = 1; // Começa na primeira pergunta de fato
    proximaEtapa();
}

// 3. FUNÇÃO: Controla qual pergunta fazer em cada momento
function proximaEtapa() {
    const btn = document.getElementById("btn-voz");
    const statusTxt = document.getElementById("status-microfone");

    switch(etapaAtual) {
        case 1:
            statusTxt.innerText = "IA perguntando sobre o produto...";
            falar("Qual produto você deseja? Diga se quer Ilha Bela, Mar Doce, Gás Butano ou Liquigás.", ativarMicrofone);
            break;
        case 2:
            statusTxt.innerText = "IA perguntando o nome...";
            falar("Perfeito. Agora, por favor, diga o seu nome.", ativarMicrofone);
            break;
        case 3:
            statusTxt.innerText = "IA perguntando o endereço...";
            falar("Qual é o nome da sua rua e o número da entrega?", ativarMicrofone);
            break;
        case 4:
            statusTxt.innerText = "IA perguntando o contato...";
            falar("Para finalizar, diga o seu número de telefone para contato.", ativarMicrofone);
            break;
        case 5:
            statusTxt.innerText = "Enviando pedido...";
            resetarBotaoVoz();
            // Fala que o pedido foi concluído e, ASSIM QUE TERMINAR DE FALAR, envia o formulário automaticamente
            falar("Muito obrigado! Seus dados foram preenchidos e seu pedido está sendo confirmado agora mesmo. Aguarde.", function() {
                document.querySelector('form').submit(); // <--- ENVIO AUTOMÁTICO DO FORMULÁRIO AQUI
            });
            break;
    }
}

// 4. FUNÇÃO: Ativa o microfone do navegador para ouvir a etapa atual
function ativarMicrofone() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        alert("Seu navegador não suporta reconhecimento de voz. Use o Google Chrome.");
        return;
    }

    const btn = document.getElementById("btn-voz");
    reconhecimento = new SpeechRecognition();
    reconhecimento.lang = 'pt-BR';
    reconhecimento.continuous = false; // Ouve apenas uma resposta por vez
    reconhecimento.interimResults = false;

    reconhecimento.onstart = function() {
        btn.style.backgroundColor = "#d9534f";
        btn.innerHTML = "<span>🛑</span> Ouvindo sua resposta...";
    };

    reconhecimento.onresult = function(event) {
        // Correção da captura de texto para o modo de escuta única do navegador
        const textoFalado = event.results[0][0].transcript.toLowerCase().trim();
        console.log("Cliente respondeu na etapa " + etapaAtual + ": " + textoFalado);
        
        // Processa a resposta baseada na etapa atual
        salvarDadosEtapa(textoFalado);
    };

    reconhecimento.onerror = function(event) {
        console.error("Erro na captura:", event.error);
        falar("Não consegui ouvir direito. Pode repetir, por favor?", ativarMicrofone);
    };

    reconhecimento.start();
}

// 5. FUNÇÃO: Salva o dado falado no campo correto da tela
function salvarDadosEtapa(texto) {
    if (etapaAtual === 1) { // Produto
        if (texto.includes("mar doce")) {
            document.getElementById("produto").value = "Mar Doce";
        } else if (texto.includes("ilha bela")) {
            document.getElementById("produto").value = "Ilha Bela";
        } else if (texto.includes("gás") || texto.includes("butano") || texto.includes("cozinha")) {
            document.getElementById("produto").value = "Gás Butano";
        } else if (texto.includes("liquigás")) {
            document.getElementById("produto").value = "Liquigás";
        } else {
            falar("Desculpe, não entendi o produto. Escolha entre Mar Doce, Ilha Bela, Gás Butano ou Liquigás.", ativarMicrofone);
            return;
        }
    } 
    else if (etapaAtual === 2) { // Nome
        let nomeLimpo = texto.replace("meu nome é", "").replace("nome é", "").trim();
        document.getElementById("nome").value = capitalizarLetras(nomeLimpo);
    } 
    else if (etapaAtual === 3) { // Rua
        let ruaLimpa = texto.replace("moro na", "").replace("moro no", "").trim();
        document.getElementById("rua").value = capitalizarLetras(ruaLimpa);
    } 
    else if (etapaAtual === 4) { // Contato
        const numerosEncontrados = texto.match(/\d+/g);
        if (numerosEncontrados) {
            document.getElementById("numero").value = numerosEncontrados.join("");
        } else {
            falar("Não identifiquei os números do telefone. Pode dizer apenas os números do seu contato?", ativarMicrofone);
            return;
        }
    }

    // Avança para o próximo passo do formulário
    etapaAtual++;
    proximaEtapa();
}

// Restaura o botão para o estado padrão verde
function resetarBotaoVoz() {
    const btn = document.getElementById("btn-voz");
    const statusTxt = document.getElementById("status-microfone");
    if (btn) {
        btn.style.backgroundColor = "#25d366";
        btn.innerHTML = "<span>🎙️</span> Deseja pedir por voz?";
    }
}

function capitalizarLetras(str) {
    return str.replace(/\b\w/g, l => l.toUpperCase());
}

// Saudação inicial padrão automática ao abrir a página
window.onload = function() {
    falar("Bom dia, Planeta Água!");
};
</script>




</body>
</html>
<?php $conexao->close(); ?>