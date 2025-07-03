<!DOCTYPE html>
<html lang="pt-br">
<head>
    <?php require_once 'includes/db_connect.php'; ?>
    <?php require_once 'includes/head.php'; ?>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <?php require_once 'includes/sidebar.php'; ?>

        <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column">
            <?php require_once 'includes/navbar.php'; ?>

            <div class="container-fluid py-4 flex-grow-1">
                <h1 class="mt-4 mb-4 text-primary">Todas as Equipes</h1>

                <div class="mb-3">
                    <button id="btnExportarExcel" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Exportar para Excel
                    </button>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="buscaDescricao" class="form-label">Buscar por Descrição</label>
                        <input type="text" id="buscaDescricao" class="form-control" placeholder="Digite parte da descrição...">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="filtroFilial" class="form-label">Filial</label>
                        <select id="filtroFilial" class="form-select">
                            <option value="">Selecione</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filtroProcesso" class="form-label">Processo</label>
                        <select id="filtroProcesso" class="form-select" disabled>
                            <option value="">Selecione</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filtroAtividade" class="form-label">Atividade</label>
                        <select id="filtroAtividade" class="form-select" disabled>
                            <option value="">Selecione</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle" id="tabela-equipes">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Filial</th>
                                <th>Descrição</th>
                                <th>Processo</th>
                                <th>Atividade</th>
                                <th>Coordenador</th>
                                <th>Supervisor</th>
                                <th>Líder</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="corpo-tabela-equipes"></tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-center mt-3">
                    <nav>
                        <ul class="pagination" id="paginacao-equipes"></ul>
                    </nav>
                </div>
            </div>

            <?php require_once 'includes/footer.php'; ?>
        </div>
    </div>

<script>
const filtroFilial = document.getElementById('filtroFilial');
const filtroProcesso = document.getElementById('filtroProcesso');
const filtroAtividade = document.getElementById('filtroAtividade');

let todasEquipes = [];
let paginaAtual = 1;
const itensPorPagina = 10;

function carregarEquipes() {
    const filial_id = filtroFilial.value;
    const processo_id = filtroProcesso.value;
    const atividade_id = filtroAtividade.value;
    const buscaDescricao = document.getElementById('buscaDescricao')?.value || '';

    const params = new URLSearchParams();
    if (filial_id) params.append('filial_id', filial_id);
    if (processo_id) params.append('processo_id', processo_id);
    if (atividade_id) params.append('atividade_id', atividade_id);
    if (buscaDescricao) params.append('busca', buscaDescricao);

    fetch(`api/get_equipes_completo.php?${params.toString()}`)
        .then(res => res.json())
        .then(data => {
            todasEquipes = data;
            paginaAtual = 1;
            renderizarTabela();
            renderizarPaginacao();
        });
}

function renderizarTabela() {
    const corpo = document.getElementById('corpo-tabela-equipes');
    corpo.innerHTML = '';
    const inicio = (paginaAtual - 1) * itensPorPagina;
    const fim = inicio + itensPorPagina;
    const equipesPagina = todasEquipes.slice(inicio, fim);

    equipesPagina.forEach(eq => {
        const tr = document.createElement('tr');
        if (eq.status === 'finalizada') {
            tr.classList.add('table-secondary');
        }

        const tdId = document.createElement('td');
        tdId.textContent = eq.id;
        tr.appendChild(tdId);

        const tdFilial = document.createElement('td');
        tdFilial.textContent = eq.filial;
        tr.appendChild(tdFilial);

        const tdDescricao = document.createElement('td');
        const inputDesc = document.createElement('input');
        inputDesc.type = 'text';
        inputDesc.className = 'form-control';
        inputDesc.value = eq.descricao || '';
        inputDesc.addEventListener('blur', () => {
            atualizarCampo(eq.id, 'descricao', inputDesc.value);
        });
        tdDescricao.appendChild(inputDesc);
        tr.appendChild(tdDescricao);

        const tdProcesso = document.createElement('td');
        tdProcesso.textContent = eq.processo;
        tr.appendChild(tdProcesso);

        const tdAtividade = document.createElement('td');
        tdAtividade.textContent = eq.atividade;
        tr.appendChild(tdAtividade);

        ['coordenador', 'supervisor', 'lider'].forEach(funcao => {
            const td = document.createElement('td');
            const select = document.createElement('select');
            select.className = 'form-select';
            fetch(`api/get_colaboradores_por_filial.php?filial_id=${eq.filial_id}`)
                .then(res => res.json())
                .then(colabs => {
                    select.innerHTML = '<option value="">Selecione</option>';
                    colabs.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.nome;
                        if (c.id == eq[`${funcao}_id`]) opt.selected = true;
                        select.appendChild(opt);
                    });
                });

            select.addEventListener('change', () => {
                atualizarCampo(eq.id, `${funcao}_id`, select.value);
            });

            td.appendChild(select);
            tr.appendChild(td);
        });

        const tdStatus = document.createElement('td');

        if (eq.status === 'mobilizando') {
            const btn = document.createElement('button');
            btn.className = 'btn btn-sm btn-primary';
            btn.textContent = 'Ativar';
            btn.addEventListener('click', () => alterarStatus(eq.id, 'ativa'));
            tdStatus.appendChild(btn);
        } else if (eq.status === 'ativa') {
            const btn = document.createElement('button');
            btn.className = 'btn btn-sm btn-warning';
            btn.textContent = 'Finalizar';
            btn.addEventListener('click', () => {
                if (confirm('Tem certeza que deseja finalizar esta equipe?')) {
                    alterarStatus(eq.id, 'finalizada');
                }
            });
            tdStatus.appendChild(btn);
        } else {
            tdStatus.textContent = 'Finalizada';
        }

        tr.appendChild(tdStatus);

        corpo.appendChild(tr);
    });
}

function renderizarPaginacao() {
    const paginacao = document.getElementById('paginacao-equipes');
    paginacao.innerHTML = '';
    const totalPaginas = Math.ceil(todasEquipes.length / itensPorPagina);

    for (let i = 1; i <= totalPaginas; i++) {
        const li = document.createElement('li');
        li.className = 'page-item' + (i === paginaAtual ? ' active' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = i;
        a.addEventListener('click', (e) => {
            e.preventDefault();
            paginaAtual = i;
            renderizarTabela();
            renderizarPaginacao();
        });
        li.appendChild(a);
        paginacao.appendChild(li);
    }
}

function atualizarCampo(equipe_id, campo, valor) {
    fetch('api/atualizar_campo_equipe.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ equipe_id, campo, valor })
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.success) {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-bg-success border-0 position-fixed bottom-0 end-0 m-3 show';
            toast.setAttribute('role', 'alert');
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${resp.message || 'Atualização realizada com sucesso.'}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.classList.remove('show');
                toast.addEventListener('transitionend', () => toast.remove());
            }, 3000);
        } else {
            alert(resp.message);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    carregarFiliais();
    filtroFilial.addEventListener('change', () => {
        const id = filtroFilial.value;
        if (!id) {
            filtroProcesso.innerHTML = '<option value="">Selecione</option>';
            filtroAtividade.innerHTML = '<option value="">Selecione</option>';
            filtroProcesso.disabled = true;
            filtroAtividade.disabled = true;
            carregarEquipes();
            return;
        }
        fetch(`api/get_processos_por_filial.php?filial_id=${id}`)
            .then(res => res.json())
            .then(data => {
                filtroProcesso.innerHTML = '<option value="">Selecione</option>';
                data.forEach(proc => {
                    filtroProcesso.innerHTML += `<option value="${proc.id}">${proc.titulo}</option>`;
                });
                filtroProcesso.disabled = false;
                filtroAtividade.innerHTML = '<option value="">Selecione</option>';
                filtroAtividade.disabled = true;
                carregarEquipes();
            });
    });

    filtroProcesso.addEventListener('change', () => {
        const id = filtroProcesso.value;
        if (!id) {
            filtroAtividade.innerHTML = '<option value="">Selecione</option>';
            filtroAtividade.disabled = true;
            carregarEquipes();
            return;
        }
        fetch(`api/get_atividades_por_processo.php?processo_id=${id}`)
            .then(res => res.json())
            .then(data => {
                filtroAtividade.innerHTML = '<option value="">Selecione</option>';
                data.forEach(ati => {
                    filtroAtividade.innerHTML += `<option value="${ati.id}">${ati.titulo}</option>`;
                });
                filtroAtividade.disabled = false;
                carregarEquipes();
            });
    });

    filtroAtividade.addEventListener('change', carregarEquipes);

    let buscaTimeout;
    document.getElementById('buscaDescricao').addEventListener('input', () => {
        clearTimeout(buscaTimeout);
        buscaTimeout = setTimeout(() => {
            carregarEquipes();
        }, 300);
    });
});

function carregarFiliais() {
    const filtroFilial = document.getElementById('filtroFilial');
    fetch('api/get_filiais.php')
        .then(res => res.json())
        .then(data => {
            filtroFilial.innerHTML = '<option value="">Selecione</option>';
            data.forEach(filial => {
                const opt = document.createElement('option');
                opt.value = filial.id;
                opt.textContent = filial.nome;
                filtroFilial.appendChild(opt);
            });
        });
}
</script>
</body>
</html>
<script>
function alterarStatus(equipe_id, novo_status) {
    fetch('api/atualizar_status_equipe.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            equipe_id: parseInt(equipe_id),
            novo_status: novo_status.trim().toLowerCase()
        })
    })
    .then(res => res.json())
    .then(resp => {
        alert(resp.message);
        if (resp.success) carregarEquipes();
    })
    .catch(err => {
        console.error('Erro na requisição:', err);
        alert('Erro ao tentar alterar status.');
    });
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
document.getElementById('btnExportarExcel').addEventListener('click', () => {
    fetch('api/exportar_equipes_excel.php')
        .then(res => res.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = "equipes.xlsx";
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
        });
});
</script>