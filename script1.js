// ATUALIZE APENAS A FUNÇÃO NO SEU SCRIPT
async function carregarEntregas() {
    let container = document.getElementById("container-entregas");
    let txtSaldo = document.getElementById("txt-saldo");
    
    container.innerHTML = "...Carregando dados...";

    try {
        // Busca as entregas direto do banco de dados real
        let resposta = await fetch('ps_live_VTi6bD5WR6cKcSUAAXl9rBMhFxDZLzJt', {
            method: 'GET',
            headers: { 'Authorization': 'Bearer VTi6bD5WR6cKcSUAAXl9rBMhFxDZLzJt' }
        });
        
        let listaEntregas = await resposta.json();
        
        container.innerHTML = "";
        let saldoTotal = 0;

        if (!listaEntregas || listaEntregas.length === 0) {
            container.innerHTML = '<div class="sem-dados">Nenhuma entrega no momento.</div>';
            txtSaldo.innerHTML = "Saldo: R$ 0,00";
            return;
        }

        listaEntregas.forEach((entrega, index) => {
            let valorNum = parseFloat(entrega.valor);
            let estaConcluido = entrega.progresso === 100;
            
            if (estaConcluido) {
                saldoTotal += valorNum;
            }

            let classeStatus = estaConcluido ? 'concluido' : 'pendente';
            let textoStatus = estaConcluido ? 'Concluído' : 'Clique para Concluir';

            let itemHtml = `
                <div class="item ${classeStatus}" onclick="${!estaConcluido ? `concluirEntrega('${entrega.id}')` : ''}">
                    <div class="item-title">
                        <span>${entrega.titulo} <span class="status-txt">${textoStatus}</span></span>
                        <span>R$ ${valorNum.toFixed(2).replace('.', ',')}</span>
                    </div>
                    <div class="bar">
                        <div class="fill" style="width:${entrega.progresso}%"></div>
                    </div>
                </div>
            `;
            container.innerHTML += itemHtml;
        });

        txtSaldo.innerHTML = `Saldo: R$ ${saldoTotal.toFixed(2).replace('.', ',')}`;
    } catch (erro) {
        container.innerHTML = '<div class="sem-dados">Erro ao conectar com o banco de dados.</div>';
    }
}
