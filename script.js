// ATUALIZE APENAS A FUNÇÃO NO SEU SCRIPT
async function salvarEntrega() {
    let titulo = document.getElementById("titulo").value.trim();
    let valor = document.getElementById("valor").value.trim();

    if (!titulo || !valor) {
        alert("Por favor, preencha todos os campos!");
        return;
    }

    let novaEntrega = {
        titulo: titulo.toUpperCase(),
        valor: parseFloat(valor),
        progresso: 0 // Começa como pendente
    };

    try {
        // Envia os dados para a API do seu banco de dados
        let resposta = await fetch('https://fllnukzilrzastjhnmez.supabase.co', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer sb_publishable_gI7bXlzFmR8JIN3HWB4aOg_7pf-qq6e'
            },
            body: JSON.stringify(novaEntrega)
        });

        if (resposta.ok) {
            document.getElementById("titulo").value = "";
            document.getElementById("valor").value = "";
            alert("Entrega salva no Banco de Dados com sucesso!");
        } else {
            alert("Erro ao salvar no banco de dados.");
        }
    } catch (erro) {
        console.error(erro);
        alert("Erro de conexão com o servidor.");
    }
}
