<?php
require_once 'includes/db_connect.php';
require_once 'includes/session_check.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once 'includes/head.php'; ?>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <?php require_once 'includes/sidebar.php'; ?>

        <!-- Page Content Wrapper -->
        <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column">
            <?php require_once 'includes/navbar.php'; ?>

            <div class="container-fluid py-4">
                <h1 class="mt-4 mb-4 text-primary">Montar Equipes</h1>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="filtroFilial" class="form-label">Filial</label>
                        <select id="filtroFilial" class="form-select">
                            <option value="">Selecione uma filial</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filtroProcesso" class="form-label">Processo</label>
                        <select id="filtroProcesso" class="form-select" disabled>
                            <option value="">Selecione um processo</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filtroAtividade" class="form-label">Atividade</label>
                        <select id="filtroAtividade" class="form-select" disabled>
                            <option value="">Selecione uma atividade</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <!-- Lado esquerdo: Equipes -->
                    <div class="col-md-6 border-end">
                        <div class="mb-3">
                            <input type="text" id="buscaEquipe" class="form-control" placeholder="Buscar equipe...">
                        </div>
                        <ul class="list-group" id="listaEquipes" style="min-height: 400px;">
                            <!-- Equipes serão carregadas via JS -->
                        </ul>
                    </div>

                    <!-- Lado direito: Colaboradores -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <input type="text" id="buscaColaborador" class="form-control" placeholder="Buscar colaborador...">
                        </div>
                        <ul class="list-group" id="listaColaboradores" style="min-height: 400px;">
                            <!-- Colaboradores serão carregados via JS -->
                        </ul>
                    </div>
                </div>
            </div>
            <!-- /Main Content -->

            <?php require_once 'includes/footer.php'; ?>
        </div>
        <!-- /Page Content Wrapper -->
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const listaEquipes = document.getElementById('listaEquipes');
            const listaColaboradores = document.getElementById('listaColaboradores');
            const buscaEquipe = document.getElementById('buscaEquipe');
            const buscaColaborador = document.getElementById('buscaColaborador');
            const filtroFilial = document.getElementById('filtroFilial');
            const filtroProcesso = document.getElementById('filtroProcesso');
            const filtroAtividade = document.getElementById('filtroAtividade');

            // Carregar filiais no início
            fetch('api/get_filiais.php')
                .then(res => res.json())
                .then(data => {
                    filtroFilial.innerHTML = '<option value="">Selecione uma filial</option>';
                    data.forEach(f => {
                        const option = document.createElement('option');
                        option.value = f.id;
                        option.textContent = f.nome;
                        filtroFilial.appendChild(option);
                    });

                    if (data.length > 0) {
                        filtroFilial.value = data[0].id;
                        filtroFilial.dispatchEvent(new Event('change'));
                    }
                });

            filtroFilial.addEventListener('change', () => {
                filtroProcesso.innerHTML = '<option value="">Selecione um processo</option>';
                filtroAtividade.innerHTML = '<option value="">Selecione uma atividade</option>';
                filtroProcesso.disabled = true;
                filtroAtividade.disabled = true;

                if (filtroFilial.value) {
                    fetch(`api/get_processos_por_filial.php?filial_id=${filtroFilial.value}`)
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(p => {
                                const option = document.createElement('option');
                                option.value = p.id;
                                option.textContent = p.titulo;
                                filtroProcesso.appendChild(option);
                            });
                            filtroProcesso.disabled = false;

                            // Atualiza a lista de colaboradores com base na nova filial e atividade atual
                            carregarColaboradores('', filtroFilial.value, filtroAtividade.value);
                        });
                } else {
                    carregarColaboradores('', '', filtroAtividade.value);
                }

                // Limpa equipes
                carregarEquipes('', '');
            });

            filtroProcesso.addEventListener('change', () => {
                filtroAtividade.innerHTML = '<option value="">Selecione uma atividade</option>';
                filtroAtividade.disabled = true;

                if (filtroProcesso.value) {
                    fetch(`api/get_atividades_por_processo.php?processo_id=${filtroProcesso.value}`)
                        .then(res => res.json())
                        .then(data => {
                            data.forEach(a => {
                                const option = document.createElement('option');
                                option.value = a.id;
                                option.textContent = a.titulo;
                                filtroAtividade.appendChild(option);
                            });
                            filtroAtividade.disabled = false;
                        });
                } else {
                    carregarEquipes('', '');
                }
            });

            filtroAtividade.addEventListener('change', () => {
                if (filtroAtividade.value) {
                    carregarEquipes('', filtroAtividade.value);
                    carregarColaboradores('', filtroFilial.value, filtroAtividade.value);
                } else {
                    carregarEquipes('', '');
                    carregarColaboradores('', filtroFilial.value, '');
                }
            });

            function carregarEquipes(filtro = '', atividadeId = '') {
                fetch(`api/get_equipes.php?busca=${encodeURIComponent(filtro)}&atividade_id=${atividadeId}`)
                    .then(res => res.json())
                    .then(data => {
                        listaEquipes.innerHTML = '';
                        data.forEach((eq, index) => {
                            const li = document.createElement('li');
                            li.className = `list-group-item equipe-item ${index % 2 === 0 ? 'bg-primary-subtle' : 'bg-white'}`;
                            li.textContent = `${eq.descricao || 'Equipe'} (${eq.status})`;
                            li.dataset.id = eq.id;
                            li.addEventListener('click', () => {
                                document.querySelectorAll('.equipe-item').forEach(e => e.classList.remove('active'));
                                li.classList.add('active');
                                li.setAttribute('data-selected', 'true');
                            });
                            li.addEventListener('dragover', (e) => e.preventDefault());
                            li.addEventListener('drop', (e) => {
                                e.preventDefault();
                                const colaboradorId = e.dataTransfer.getData("text/plain");
                                const equipeId = li.dataset.id;
                                if (equipeId && colaboradorId) {
                                    fetch('api/vincular_colaborador_equipe.php', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json'
                                            },
                                            body: JSON.stringify({
                                                equipe_id: parseInt(equipeId, 10),
                                                colaborador_id: parseInt(colaboradorId, 10)
                                            })
                                        })
                                        .then(res => res.json())
                                        .then(data => {
                                            console.log(data);
                                            if (data.success) {
                                                carregarEquipes('', filtroAtividade.value);
                                                carregarColaboradores('', filtroFilial.value, filtroAtividade.value);
                                            } else {
                                                alert(data.message);
                                            }
                                        });
                                }
                            });

                            const subLista = document.createElement('ul');
                            subLista.classList.add('list-group', 'mt-2');

                            fetch(`api/get_colaboradores_da_equipe.php?equipe_id=${eq.id}`)
                                .then(res => res.json())
                                .then(colabs => {
                                    colabs.forEach(colab => {
                                        const item = document.createElement('li');
                                        item.className = 'list-group-item py-1 px-2 d-flex justify-content-between align-items-center';
                                        item.textContent = `${colab.nome} (${colab.cargo})`;

                                        const btnRemover = document.createElement('button');
                                        btnRemover.className = 'btn btn-sm btn-outline-danger';
                                        btnRemover.textContent = '✖';
                                        btnRemover.onclick = () => {
                                            if (confirm(`Remover ${colab.nome} da equipe?`)) {
                                                fetch('api/remover_colaborador_equipe.php', {
                                                        method: 'POST',
                                                        headers: {
                                                            'Content-Type': 'application/json'
                                                        },
                                                        body: JSON.stringify({
                                                            equipe_id: eq.id,
                                                            colaborador_id: colab.id
                                                        })
                                                    })
                                                    .then(res => res.json())
                                                    .then(resp => {
                                                        alert(resp.message);
                                                        carregarEquipes('', filtroAtividade.value);
                                                        carregarColaboradores('', filtroFilial.value, filtroAtividade.value);
                                                    });
                                            }
                                        };

                                        item.appendChild(btnRemover);
                                        subLista.appendChild(item);
                                    });
                                });

                            li.appendChild(subLista);
                            listaEquipes.appendChild(li);
                        });
                    });
            }

            function carregarColaboradores(filtro = '', filialId = '') {
                fetch(`api/get_colaboradores.php?busca=${encodeURIComponent(filtro)}&filial_id=${filialId}`)
                    .then(res => res.json())
                    .then(colaboradores => {
                        listaColaboradores.innerHTML = '';

                        // Buscar todos os colaboradores já vinculados a qualquer equipe
                        fetch('api/get_colaboradores_vinculados.php')
                            .then(res => res.json())
                            .then(vinculados => {
                                const idsVinculados = new Set(vinculados.map(v => parseInt(v.colaborador_id, 10)));

                                colaboradores.forEach(c => {
                                    if (!idsVinculados.has(c.id)) {
                                        const li = document.createElement('li');
                                        li.className = 'list-group-item colaborador-item';
                                        li.textContent = `${c.nome} - ${c.cargo}`;
                                        li.draggable = true;
                                        li.dataset.id = c.id;
                                        li.addEventListener('dragstart', (e) => {
                                            e.dataTransfer.setData("text/plain", c.id);
                                        });
                                        listaColaboradores.appendChild(li);
                                    }
                                });
                            });
                    });
            }

            buscaEquipe.addEventListener('input', () => carregarEquipes(buscaEquipe.value, filtroAtividade.value));
            buscaColaborador.addEventListener('input', () => carregarColaboradores(buscaColaborador.value, filtroFilial.value, filtroAtividade.value));

            carregarEquipes();
            carregarColaboradores();
        });
    </script>

</body>

</html>