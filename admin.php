<?php
// 1. CONFIGURAÇÃO DA CONEXÃO COM O BANCO DE DADOS
$host = "localhost";
$usuario_db = "root"; 
$senha_db = "";       
$nome_db = "sistema_entregas";

$conexao = new mysqli($host, $usuario_db, $senha_db, $nome_db);

if ($conexao->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conexao->connect_error);
}

$mensagem = "";

// 2. AÇÃO 1: LANÇAR PEDIDO EM ROTA (OU NA FILA)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_lancar'])) {
    $descricao = $conexao->real_escape_string($_POST['pedido']);
    $rota = $conexao->real_escape_string($_POST['endereco']);
    $comissao = floatval(str_replace(',', '.', $_POST['comissao'])); // Trata vírgula decimal
    $entregador_selecionado = $_POST['entregador'];

    if (empty($entregador_selecionado)) {
        $sql = "INSERT INTO pedidos (descricao_pedido, endereco_rota, valor_comissao, status, entregador_id) VALUES ('$descricao', '$rota', $comissao, 'Aguardando', NULL)";
    } else {
        $entregador_id = intval($entregador_selecionado);
        $sql = "INSERT INTO pedidos (descricao_pedido, endereco_rota, valor_comissao, status, entregador_id) VALUES ('$descricao', '$rota', $comissao, 'Em Rota', $entregador_id)";
    }

    if ($conexao->query($sql) === TRUE) {
        $mensagem = "<div class='alert-success'>Pedido lançado com sucesso!</div>";
    }
}

// 3. AÇÃO NOVA ATUALIZADA: ENVIAR PRODUTO EM ROTA COM VALOR DE COMISSÃO DEFINIDO
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_enviar_rota'])) {
    $pedido_id = intval($_POST['produto_aguardando']);
    $entregador_id = intval($_POST['entregador_rota']);
    $comissao = floatval(str_replace(',', '.', $_POST['comissao_rota'])); // Pega a comissão da tela

    if ($pedido_id > 0 && $entregador_id > 0) {
        // Atualiza o status, o entregador E o valor da comissão configurado nessa etapa
        $sql = "UPDATE pedidos SET status = 'Em Rota', entregador_id = $entregador_id, valor_comissao = $comissao WHERE id = $pedido_id AND status = 'Aguardando'";
        if ($conexao->query($sql) === TRUE) {
            $mensagem = "<div class='alert-success'>Pedido enviado para rota com sucesso!</div>";
        }
    } else {
        $mensagem = "<div class='alert-danger' style='background-color:#f8d7da; color:#721c24; padding:10px; border-radius:4px; margin-bottom:15px; text-align:center;'>Selecione o entregador e o pedido!</div>";
    }
}

// 4. AÇÃO: REMOVER PRODUTO NÃO ENTREGUE (Exclui do banco)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_remover_produto'])) {
    $pedido_id = intval($_POST['produto_remover']);
    if ($pedido_id > 0) {
        $sql = "DELETE FROM pedidos WHERE id = $pedido_id";
        if ($conexao->query($sql) === TRUE) {
            $mensagem = "<div class='alert-success'>Produto removido com sucesso!</div>";
        }
    }
}

// 5. AÇÃO: CONFIRMAR PAGAMENTO (SAQUE DA CARTEIRA)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao_confirmar_pagamento'])) {
    $entregador_id = intval($_POST['entregador_pagamento']);
    if ($entregador_id > 0) {
        $sql = "UPDATE pedidos SET status = 'Pago' WHERE entregador_id = $entregador_id AND (status = 'Em Rota' OR status = 'Entregue')";
        if ($conexao->query($sql) === TRUE) {
            $mensagem = "<div class='alert-success'>Pagamento confirmado e pedidos limpos!</div>";
        }
    }
}

// 6. BUSCA OS ENTREGADORES PARA OS MENUS SELECT
$sql_e = "SELECT id, nome FROM entregadores ORDER BY nome ASC";
$res_e = $conexao->query($sql_e);
$entregadores = [];
while($row = $res_e->fetch_assoc()) { $entregadores[] = $row; }

// 7. BUSCA PEDIDOS APENAS EM FILA (AGUARDANDO)
$sql_f = "SELECT id, descricao_pedido FROM pedidos WHERE status = 'Aguardando' ORDER BY id DESC";
$res_f = $conexao->query($sql_f);

// 8. BUSCA TODOS OS PEDIDOS ATIVOS (EM ROTA OU AGUARDANDO)
$sql_p = "SELECT id, descricao_pedido, status FROM pedidos WHERE status = 'Em Rota' OR status = 'Aguardando' ORDER BY id DESC";
$res_p = $conexao->query($sql_p);

// 9. LÓGICA DO TOTAL DINÂMICO VIA AJAX
if (isset($_GET['buscar_total'])) {
    $e_id = intval($_GET['buscar_total']);
    $res_soma = $conexao->query("SELECT SUM(valor_comissao) AS total FROM pedidos WHERE entregador_id = $e_id AND (status = 'Em Rota' OR status = 'Entregue')");
    $dados_soma = $res_soma->fetch_assoc();
    echo $dados_soma['total'] ? $dados_soma['total'] : "0.00";
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Área Administrative</title>
    <link rel="manifest" href="manifest.json">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background-color: #0099e5; display: flex; flex-direction: column; align-items: center; padding: 20px 10px; }
        .container { width: 100%; max-width: 420px; background-color: #0099e5; padding: 5px; }
        .header { padding: 15px 5px; }
        .admin-title { color: #ff3333; font-size: 24px; font-weight: bold; text-transform: uppercase; }
        .content-area { background-color: white; border-radius: 4px; padding: 20px; margin-bottom: 15px; border: 1px solid #ddd; }
        .secao-titulo { font-size: 18px; font-weight: bold; color: #333; text-align: center; margin-bottom: 15px; text-transform: uppercase; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: bold; color: #333; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; outline: none; }
        .btn-blue { width: 100%; background-color: #0099e5; color: white; border: none; padding: 12px; font-size: 15px; font-weight: bold; border-radius: 4px; cursor: pointer; text-transform: uppercase; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; font-size: 14px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .total-container { display: flex; justify-content: flex-end; align-items: center; gap: 10px; margin-bottom: 15px; font-weight: bold; font-size: 16px; }
        .total-box { background-color: #28a745; color: white; padding: 5px 15px; font-size: 18px; border-radius: 3px; font-weight: bold; }
    </style>
</head>
<body>

    <div class="container">
        <header class="header">
            <div class="admin-title">AREA ADM</div>
        </header>
        
        <?php if(!empty($mensagem)) { echo $mensagem; } ?>

        

        <!-- SEÇÃO 2: ENVIAR PRODUTO EM ROTA (FILA -> ROTA) COM CAMPO DE COMISSÃO -->
        <section class="content-area">
            <form action="" method="POST">
                <input type="hidden" name="acao_enviar_rota" value="1">
                <div class="form-group">
                    <label>Selecionar Entregador</label>
                    <select name="entregador_rota" required>
                        <option value="">-- Escolha o Entregador --</option>
                        <?php foreach($entregadores as $ent) { echo "<option value='{$ent['id']}'>{$ent['nome']}</option>"; } ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Selecionar produto estao aguardando</label>
                    <select name="produto_aguardando" required>
                        <option value="">-- Escolha o Pedido Ativo --</option>
                        <?php while($f = $res_f->fetch_assoc()) { echo "<option value='{$f['id']}'>{$f['descricao_pedido']}</option>"; } ?>
                    </select>
                </div>
                <!-- NOVO CAMPO ADICIONADO ABAIXO -->
                <div class="form-group">
                    <label>Valor da Comissão (R$)</label>
                    <input type="text" name="comissao_rota" value="0,00" onfocus="if(this.value=='0,00')this.value='';" onblur="if(this.value=='')this.value='0,00';" required>
                </div>
                <button type="submit" class="btn-blue">Enviar produto em rota</button>
            </form>
        </section>

         <!-- SEÇÃO 2: REMOVER PRODUTO OU ALTERAR PARA ENTREGUE -->
        <section class="content-area">
            <div class="secao-titulo">Remover produto nao entregue</div>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Selecionar produto para remover</label>
                    <select name="produto_remover" required>
                        <option value="">-- Escolha o Pedido Ativo --</option>
                        <?php 
                        while($p = $res_p->fetch_assoc()) { 
                            $tag = ($p['status'] == 'Aguardando') ? "Fila" : "Em Rota";
                            echo "<option value='{$p['id']}'>{$p['descricao_pedido']} ({$tag})</option>"; 
                        } 
                        ?>
                    </select>
                </div>
                
                <button type="submit" name="acao_remover_produto" class="btn-blue" onclick="return confirm('Deseja realmente remover este produto?');">PRODUTO REMOVIDO</button>
                
            </form>
        </section>

        <!-- SEÇÃO 3: REMOVER PEDIDOS DE HOJE -->
        <section class="content-area">
            <div class="secao-titulo">SAQUE DA CARTEIRA</div>
            <form action="" method="POST" onsubmit="return confirm('Deseja confirmar o pagamento?');">
                <input type="hidden" name="acao_confirmar_pagamento" value="1">
                <div class="form-group">
                    <label>Selecionar Entregador</label>
                    <select name="entregador_pagamento" onchange="buscarSaldoCarteira(this.value);" required>
                        <option value="">-- Escolha o Entregador --</option>
                        <?php foreach($entregadores as $ent) { echo "<option value='{$ent['id']}'>{$ent['nome']}</option>"; } ?>
                    </select>
                </div>
                
                <!-- CONTAINER DO SALDO DINÂMICO ADICIONADO -->
                <div class="total-container">
                    <span>Total Rota:</span>
                    <span class="total-box" id="saldo_box">R$ 0.00</span>
                </div>

                <button type="submit" class="btn-blue">Confirmar Pagamento</button>
            </form>
        </section>
    </div>

    <!-- SCRIPT JAVASCRIPT QUE EXECUTA O SEU ITEM 7 VIA AJAX -->
    <script>
    function buscarSaldoCarteira(idEntregador) {
        const campoSaldo = document.getElementById('saldo_box');
        if (!idEntregador) {
            campoSaldo.innerText = "R$ 0.00";
            return;
        }
        
        fetch('?buscar_total=' + idEntregador)
            .then(resposta => resposta.text())
            .then(valor => {
                campoSaldo.innerText = "R$ " + parseFloat(valor).toFixed(2);
            })
            .catch(erro => {
                console.error("Erro ao buscar o saldo:", erro);
                campoSaldo.innerText = "R$ 0.00";
            });
    }
    </script>
</body>
</html>
