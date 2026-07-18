<?php
session_start();

// 1. VERIFICA SE O ENTREGADOR ESTÁ LOGADO
if (!isset($_SESSION['entregador_id'])) {
    header("Location: login.php");
    exit();
}

// 2. CONEXÃO COM O BANCO DE DADOS
$host = "tokaido.proxy.rlwy.net";
$user = "root";
$password = "kuijjqJlSUMnqseiUNjuITHvYsrOeUlH@tokaido";
$database = "railway";
$port = 59152;
$conexao = new mysqli($host, $usuario_db, $senha_db, $nome_db);

if ($conexao->connect_error) {
    die("Falha na conexão: " . $conexao->connect_error);
}

$id_logado = intval($_SESSION['entregador_id']);
$mensagem = "";

// 3. BUSCA O NOME E A FOTO DO ENTREGADOR
$sql_entregador = "SELECT nome, foto FROM entregadores WHERE id = $id_logado";
$resultado_entregador = $conexao->query($sql_entregador);
$dados_entregador = $resultado_entregador->fetch_assoc();
$nome_exibir = $dados_entregador ? $dados_entregador['nome'] : "Entregador";

// FUNÇÃO ISOLADA PARA GRAVAR O BACKUP TXT
function registrar_log_pedido($nome_entregador, $status_acao, $pedido_id, $descricao, $rota, $comissao, $data_recebido) {
    $nome_limpo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $nome_entregador);
    $nome_arquivo = "backup_pedidos_" . $nome_limpo . ".txt";
    
    date_default_timezone_set('America/Sao_Paulo');
    $data_hora_acao = date('d/m/Y H:i:s');
    
    // Formata o bloco de texto do pedido
    $bloco_texto = "==================================================\n";
    $bloco_texto .= "STATUS: $status_acao\n";
    $bloco_texto .= "REGISTRADO EM: $data_hora_acao\n";
    $bloco_texto .= "ID PEDIDO: $pedido_id\n";
    $bloco_texto .= "PEDIDO: $descricao\n";
    $bloco_texto .= "ROTA: $rota\n";
    $bloco_texto .= "COMISSÃO: R$ $comissao\n";
    $bloco_texto .= "HORÁRIO DO LANÇAMENTO: $data_recebido\n";
    $bloco_texto .= "==================================================\n\n";
    
    // Se for registro de recebimento, verifica se já existe no arquivo para não duplicar toda vez que atualizar a página
    if ($status_acao === "PEDIDO RECEBIDO") {
        if (file_exists($nome_arquivo)) {
            $conteudo_atual = file_get_contents($nome_arquivo);
            if (strpos($conteudo_atual, "STATUS: PEDIDO RECEBIDO\n") !== false && strpos($conteudo_atual, "ID PEDIDO: $pedido_id\n") !== false) {
                return; // Já foi registrado como recebido, ignora
            }
        }
    }
    
    file_put_contents($nome_arquivo, $bloco_texto, FILE_APPEND);
}

// AÇÃO: SALVA DATA/HORA DA ENTREGA E MUDA STATUS
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_entregar_pedido'])) {
    $pedido_id = intval($_POST['pedido_id']);
    
    // Busca dados do pedido antes de remover da tela para gravar a ação no TXT
    $sql_busca_pedido = "SELECT descricao_pedido, endereco_rota, valor_comissao, data_lancamento FROM pedidos WHERE id = $pedido_id";
    $res_pedido = $conexao->query($sql_busca_pedido);
    
    if ($res_pedido && $res_pedido->num_rows > 0) {
        $dados_p = $res_pedido->fetch_assoc();
        $desc_p = $dados_p['descricao_pedido'];
        $rota_p = $dados_p['endereco_rota'];
        $comissao_p = number_format($dados_p['valor_comissao'], 2, ',', '.');
        $data_lan_p = !empty($dados_p['data_lancamento']) ? date('d/m/Y H:i', strtotime($dados_p['data_lancamento'])) : date('d/m/Y H:i');
        
        $sql_atualizar = "UPDATE pedidos SET status = 'Entregue', data_entrega = NOW() WHERE id = $pedido_id AND entregador_id = $id_logado AND status = 'Em Rota'";
        
        if ($conexao->query($sql_atualizar) === TRUE && $conexao->affected_rows > 0) {
            $mensagem = "<div style='background-color:#d4edda; color:#155724; padding:10px; border-radius:4px; margin-bottom:15px; text-align:center; font-weight:bold; font-size:14px;'>Entrega finalizada com sucesso!</div>";
            
            // Grava a remoção/entrega no TXT
            registrar_log_pedido($nome_exibir, "PEDIDO REMOVIDO / ENTREGUE", $pedido_id, $desc_p, $rota_p, $comissao_p, $data_lan_p);
        }
    }
}

// 4. CALCULA A SOMA DE TODAS AS COMISSÕES ATIVAS (Mantido original conforme solicitado)
$sql_soma = "SELECT SUM(valor_comissao) AS total_comissao FROM pedidos WHERE entregador_id = $id_logado AND status = 'Em Rota'";
$resultado_soma = $conexao->query($sql_soma);
$dados_soma = $resultado_soma->fetch_assoc();

$total_acumulado = $dados_soma['total_comissao'] !== null ? floatval($dados_soma['total_comissao']) : 0.00;
$saldo_exibir = number_format($total_acumulado, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Entregador</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background-color: #0099e5; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 10px; }
        .container { width: 100%; max-width: 400px; background-color: #0099e5; padding: 10px; border: 5px solid #0099e5; }
        .header { display: flex; justify-content: space-between; align-items: center; padding: 10px 5px; }
        .welcome-text { color: #eceb1b; font-size: 14px; font-weight: bold; text-transform: uppercase; max-width: 100%; text-align: center; }
        .balance-badge { background-color: #eceb1b; color: #0099e5; padding: 5px 12px; font-weight: bold; font-size: 18px; border-radius: 2px; }
        .content-area { background-color: white; width: 100%; height: 450px; border-radius: 4px; padding: 15px; overflow-y: auto; }
        .card-entrega { border: 1px solid #ccc; padding: 12px; border-radius: 4px; margin-bottom: 12px; font-size: 14px; background-color: #fff; }
        .card-entrega strong { display: block; margin-bottom: 4px; color: #333; }
        .card-entrega small { display: block; color: #666; margin-bottom: 4px; }
        .horario-recebido { display: block; color: #ff3333; font-weight: bold; margin-bottom: 10px; font-size: 12px; }
        .btn-entregar { width: 100%; background-color: #28a745; color: white; border: none; padding: 10px; font-weight: bold; border-radius: 4px; cursor: pointer; text-transform: uppercase; font-size: 13px; margin-top: 5px; }
        .btn-entregar:hover { background-color: #218838; }
        .logout-link { display: block; text-align: center; color: white; margin-top: 15px; text-decoration: none; font-size: 12px; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- HEADER COM FOTO EM CIMA DO NOME -->
        <header class="header">
            <div style="display: flex; flex-direction: column; align-items: center; gap: 4px; max-width: 65%;">
                <a href="upload_foto.php" style="width: 45px; height: 45px; border-radius: 50%; background-color: #eceb1b; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid white; text-decoration: none; flex-shrink: 0;">
                    <?php if(!empty($dados_entregador['foto']) && file_exists($dados_entregador['foto'])): ?>
                        <img src="<?php echo $dados_entregador['foto']; ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php endif; ?>
                </a>
                <div class="welcome-text"><?php echo htmlspecialchars($nome_exibir, ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            
            <div class="balance-badge">R$ <?php echo $saldo_exibir; ?></div>
        </header>
        
        <main class="content-area">
            <?php echo $mensagem; ?>

            <?php
            // Listagem dos cartões de entrega ativos
            $sql_pedidos = "SELECT id, descricao_pedido, endereco_rota, valor_comissao, data_lancamento FROM pedidos WHERE entregador_id = $id_logado AND status = 'Em Rota' ORDER BY id DESC";
            $resultado_pedidos = $conexao->query($sql_pedidos);

            if ($resultado_pedidos && $resultado_pedidos->num_rows > 0) {
                while($pedido = $resultado_pedidos->fetch_assoc()) {
                    $id_p = $pedido['id'];
                    $desc = htmlspecialchars($pedido['descricao_pedido'], ENT_QUOTES, 'UTF-8');
                    $rota = htmlspecialchars($pedido['endereco_rota'], ENT_QUOTES, 'UTF-8');
                    $comissao = number_format($pedido['valor_comissao'], 2, ',', '.');
                    
                    $data_banco = $pedido['data_lancamento'];
                    $horario_formatado = !empty($data_banco) ? date('d/m/Y H:i', strtotime($data_banco)) : date('d/m/Y H:i');

                    // SALVA NO TXT COMO "RECEBIDO" ASSIM QUE O PEDIDO APARECE NA TELA
                    registrar_log_pedido($nome_exibir, "PEDIDO RECEBIDO", $id_p, $pedido['descricao_pedido'], $pedido['endereco_rota'], $comissao, $horario_formatado);

                    echo "
                    <div class='card-entrega'>
                        <strong>{$desc}</strong>
                        <small>Rota: {$rota}</small>
                        <small>Comissão: R$ {$comissao}</small>
                        <span class='horario-recebido'>Recebido em: {$horario_formatado}</span>
                        
                       
                    </div>";
                }
            } else {
                echo "<p style='text-align:center; color:#666; margin-top:20px;'>Nenhuma entrega em rota para você no momento.</p>";
            }
            ?>
        </main>
        
        <a href="logout.php" class="logout-link">Sair do Painel</a>
    </div>
</body>
</html>
