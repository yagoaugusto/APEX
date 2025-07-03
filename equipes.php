<?php
require_once 'includes/db_connect.php';
require_once 'includes/session_check.php';

$busca = $_GET['busca'] ?? '';
$filtro_filial = isset($_GET['filial_id']) ? intval($_GET['filial_id']) : 0;
$filtro_processo = isset($_GET['processo_id']) ? intval($_GET['processo_id']) : 0;
$filtro_atividade = isset($_GET['atividade_id']) ? intval($_GET['atividade_id']) : 0;

$filiais = $conn->query("SELECT id, nome FROM filiais ORDER BY nome ASC");
$colaboradores = []; // Será preenchido via JS
$atividades = []; // Será preenchido via JS
$processos = []; // Será preenchido via JS
$sql = "SELECT e.*, 
               a.titulo AS atividade_desc,
               p.titulo AS processo_desc,
               f.nome AS filial_nome,
               c1.nome AS coordenador_nome, 
               c2.nome AS supervisor_nome, 
               c3.nome AS lider_nome
        FROM equipes e
        JOIN atividades a ON a.id = e.atividade_id
        JOIN processos p ON p.id = a.processo_id
        JOIN filiais f ON f.id = p.filial_id
        LEFT JOIN colaboradores c1 ON c1.id = e.coordenador_id
        LEFT JOIN colaboradores c2 ON c2.id = e.supervisor_id
        LEFT JOIN colaboradores c3 ON c3.id = e.lider_id
        WHERE 1 = 1";

if ($busca !== '') {
    $sql .= " AND (e.descricao LIKE '%" . $conn->real_escape_string($busca) . "%')";
}
if ($filtro_filial > 0) {
    $sql .= " AND f.id = $filtro_filial";
}
if ($filtro_processo > 0) {
    $sql .= " AND p.id = $filtro_processo";
}
if ($filtro_atividade > 0) {
    $sql .= " AND a.id = $filtro_atividade";
}

$sql .= " ORDER BY e.id DESC";

$result = $conn->query($sql);

if (!$result) {
    die("Erro na consulta SQL: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
    <?php require_once 'includes/head.php'; ?>
    <style>
      #tabela-equipes td, #tabela-equipes th {
        font-size: 0.85rem;
      }
    </style>
<body>
<div class="d-flex" id="wrapper">
    <?php require_once 'includes/sidebar.php'; ?>
    <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column">
        <?php require_once 'includes/navbar.php'; ?>

        <div class="container-fluid py-4 flex-grow-1">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mt-4 mb-0 text-primary">Gerenciar Equipes</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#equipeModal" id="btnNovaEquipe">
                    <i class="bi bi-plus-circle me-2"></i>Nova Equipe
                </button>
            </div>
            <!-- Filtros -->
            <form method="get" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Buscar por descrição...">
                </div>
                <div class="col-md-3">
                    <select name="filial_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Todas as Filiais</option>
                        <?php foreach ($filiais as $f): ?>
                            <option value="<?= $f['id'] ?>" <?= ($filtro_filial == $f['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <!-- Campos adicionais para processo e atividade podem ser adicionados com JS -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                </div>
            </form>

            <div class="card shadow-sm mb-4">
                <div class="card-header"><i class="bi bi-people-fill me-2"></i>Equipes Criadas</div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tabela-equipes" class="table table-sm table-striped table-hover">
                            <thead>
                              <tr>
                                  <th>Filial / Processo / Atividade</th>
                                  <th>Descrição</th>
                                  <th>Status</th>
                                  <th>Coordenador</th>
                                  <th>Supervisor</th>
                                  <th>Líder</th>
                                  <th>Ações</th>
                              </tr>
                              <tr>
                                  <th><input type="text" placeholder="Buscar F/P/A" class="form-control form-control-sm" /></th>
                                  <th><input type="text" placeholder="Buscar Descrição" class="form-control form-control-sm" /></th>
                                  <th><input type="text" placeholder="Buscar Status" class="form-control form-control-sm" /></th>
                                  <th><input type="text" placeholder="Buscar Coordenador" class="form-control form-control-sm" /></th>
                                  <th><input type="text" placeholder="Buscar Supervisor" class="form-control form-control-sm" /></th>
                                  <th><input type="text" placeholder="Buscar Líder" class="form-control form-control-sm" /></th>
                                  <th></th>
                              </tr>
                            </thead>
                            <tbody>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr id="equipe-row-<?php echo $row['id']; ?>">
                                    <td>
                                        <?php echo htmlspecialchars($row['filial_nome']); ?> /
                                        <?php echo htmlspecialchars($row['processo_desc']); ?> /
                                        <?php echo htmlspecialchars($row['atividade_desc']); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                                    <td><?php echo ucfirst($row['status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['coordenador_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($row['supervisor_nome']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lider_nome']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary btn-estrutura" data-id="<?php echo $row['id']; ?>" title="Editar Estrutura da Equipe">
                                            <i class="bi bi-diagram-3"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary btn-status" data-id="<?php echo $row['id']; ?>" data-status="<?php echo $row['status']; ?>" title="Alterar Status da Equipe">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
            <!-- Modal Estrutura da Equipe -->
            <div class="modal fade" id="estruturaModal" tabindex="-1" aria-labelledby="estruturaModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="estruturaModalLabel">Estrutura da Equipe</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formEstrutura">
                                <input type="hidden" id="estruturaEquipeId">
                                <div id="estruturaLista"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="btnAddCargoEstrutura">+ Adicionar Cargo</button>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" form="formEstrutura" class="btn btn-primary">Salvar</button>
                        </div>
                    </div>
                </div>
            </div>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Cadastro -->
            <div class="modal fade" id="equipeModal" tabindex="-1" aria-labelledby="equipeModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" id="formEquipe">
                        <div class="modal-header">
                            <h5 class="modal-title" id="equipeModalLabel">Nova Equipe</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="equipeId" name="id">

                            <div class="mb-3">
                                <label for="filialId" class="form-label">Filial</label>
                                <select class="form-select" id="filialId" name="filial_id" required>
                                    <option value="">Selecione</option>
                                    <?php foreach ($filiais as $f): ?>
                                        <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nome']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="processoId" class="form-label">Processo</label>
                                <select class="form-select" id="processoId" name="processo_id" required disabled>
                                    <option value="">Selecione a filial primeiro</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="atividadeId" class="form-label">Atividade</label>
                                <select class="form-select" id="atividadeId" name="atividade_id" required disabled>
                                    <option value="">Selecione o processo primeiro</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="quantidade" class="form-label">Quantidade de Equipes</label>
                                <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Estrutura da Equipe</label>
                                <div id="estruturaCargosContainer"></div>
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnAddCargo">+ Adicionar Cargo</button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Salvar Equipe</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal Exclusão -->
            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <form class="modal-content" id="formDeleteEquipe">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Exclusão</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                        </div>
                        <div class="modal-body">
                            Tem certeza que deseja excluir esta equipe?
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="equipeIdToDelete" name="id">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                        </div>
                    </form>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const filialSelect = document.getElementById('filialId');
                const processoSelect = document.getElementById('processoId');
                const atividadeSelect = document.getElementById('atividadeId');
                const colaboradorSelects = document.querySelectorAll('.colaborador-select');
                const equipeModal = document.getElementById('equipeModal');
                const equipeModalLabel = document.getElementById('equipeModalLabel');
                const formEquipe = document.getElementById('formEquipe');
                const formDelete = document.getElementById('formDeleteEquipe');
                const equipeId = document.getElementById('equipeId');
                const equipeIdToDelete = document.getElementById('equipeIdToDelete');

                function loadProcessos(filialId, selectedProcessoId = null) {
                    processoSelect.innerHTML = '<option value="">Carregando...</option>';
                    processoSelect.disabled = true;
                    fetch('api/get_processos_por_filial.php?filial_id=' + filialId)
                        .then(res => res.json())
                        .then(data => {
                            if (!Array.isArray(data)) {
                                console.error('Resposta inesperada ao carregar processos:', data);
                                processoSelect.innerHTML = '<option value="">Erro ao carregar processos</option>';
                                return;
                            }
                            processoSelect.innerHTML = '<option value="">Selecione</option>';
                            data.forEach(item => {
                                const selected = selectedProcessoId == item.id ? 'selected' : '';
                                processoSelect.innerHTML += `<option value="${item.id}" ${selected}>${item.titulo}</option>`;
                            });
                            processoSelect.disabled = false;
                            if (selectedProcessoId) {
                                loadAtividades(selectedProcessoId);
                            } else {
                                atividadeSelect.innerHTML = '<option value="">Selecione o processo primeiro</option>';
                                atividadeSelect.disabled = true;
                            }
                        })
                        .catch(err => {
                            console.error('Erro ao buscar processos:', err);
                            processoSelect.innerHTML = '<option value="">Erro ao carregar processos</option>';
                        });
                }

                function loadAtividades(processoId, selectedAtividadeId = null) {
                    atividadeSelect.innerHTML = '<option value="">Carregando...</option>';
                    atividadeSelect.disabled = true;
                    fetch('api/get_atividades_por_processo.php?processo_id=' + processoId)
                        .then(res => res.json())
                        .then(data => {
                            atividadeSelect.innerHTML = '<option value="">Selecione</option>';
                            data.forEach(item => {
                                const selected = selectedAtividadeId == item.id ? 'selected' : '';
                                atividadeSelect.innerHTML += `<option value="${item.id}" ${selected}>${item.titulo}</option>`;
                            });
                            atividadeSelect.disabled = false;
                        });
                }

                function loadColaboradores(filialId, selectedCoordenador = null, selectedSupervisor = null, selectedLider = null) {
                    colaboradorSelects.forEach(select => {
                        select.innerHTML = '<option value="">Carregando...</option>';
                        select.disabled = true;
                    });
                    fetch('api/get_colaboradores_por_filial.php?filial_id=' + filialId)
                        .then(res => res.json())
                        .then(data => {
                            colaboradorSelects.forEach(select => {
                                select.innerHTML = '<option value="">Selecione</option>';
                                data.forEach(colab => {
                                    let selected = '';
                                    if (select.id === 'coordenadorId' && selectedCoordenador == colab.id) selected = 'selected';
                                    if (select.id === 'supervisorId' && selectedSupervisor == colab.id) selected = 'selected';
                                    if (select.id === 'liderId' && selectedLider == colab.id) selected = 'selected';
                                    select.innerHTML += `<option value="${colab.id}" ${selected}>${colab.nome}</option>`;
                                });
                                select.disabled = false;
                            });
                        });
                }

                filialSelect.addEventListener('change', function () {
                    const filialId = this.value;
                    if (!filialId) {
                        processoSelect.innerHTML = '<option value="">Selecione a filial primeiro</option>';
                        processoSelect.disabled = true;
                        atividadeSelect.innerHTML = '<option value="">Selecione o processo primeiro</option>';
                        atividadeSelect.disabled = true;
                        colaboradorSelects.forEach(select => {
                            select.innerHTML = '<option value="">Selecione a filial primeiro</option>';
                            select.disabled = true;
                        });
                        return;
                    }
                    loadProcessos(filialId);
                    loadColaboradores(filialId);
                });

                processoSelect.addEventListener('change', function () {
                    const processoId = this.value;
                    if (!processoId) {
                        atividadeSelect.innerHTML = '<option value="">Selecione o processo primeiro</option>';
                        atividadeSelect.disabled = true;
                        return;
                    }
                    loadAtividades(processoId);
                });

                document.getElementById('btnNovaEquipe').addEventListener('click', function () {
                    equipeModalLabel.textContent = 'Nova Equipe';
                    formEquipe.reset();
                    equipeId.value = '';
                    processoSelect.innerHTML = '<option value="">Selecione a filial primeiro</option>';
                    processoSelect.disabled = true;
                    atividadeSelect.innerHTML = '<option value="">Selecione o processo primeiro</option>';
                    atividadeSelect.disabled = true;
                    colaboradorSelects.forEach(select => {
                        select.innerHTML = '<option value="">Selecione a filial primeiro</option>';
                        select.disabled = true;
                    });
                });

                document.querySelectorAll('.btn-editar-equipe').forEach(button => {
                    button.addEventListener('click', function () {
                        equipeModalLabel.textContent = 'Editar Equipe';
                        equipeId.value = this.dataset.id;

                        const filialId = this.dataset.filial || '';
                        const processoId = this.dataset.processo || '';
                        const atividadeId = this.dataset.atividade || '';
                        // const descricao = this.dataset.descricao || '';
                        // const coordenadorId = this.dataset.coordenador || '';
                        // const supervisorId = this.dataset.supervisor || '';
                        // const liderId = this.dataset.lider || '';
                        // No longer using descricao, coordenador, supervisor, lider

                        filialSelect.value = filialId;

                        if (filialId) {
                            loadProcessos(filialId, processoId);
                            // Não há mais colaboradores para carregar
                        } else {
                            processoSelect.innerHTML = '<option value="">Selecione a filial primeiro</option>';
                            processoSelect.disabled = true;
                            atividadeSelect.innerHTML = '<option value="">Selecione o processo primeiro</option>';
                            atividadeSelect.disabled = true;
                        }

                        // O campo de atividades será carregado após o processo ser carregado
                        if (processoId) {
                            loadAtividades(processoId, atividadeId);
                        } else {
                            atividadeSelect.innerHTML = '<option value="">Selecione o processo primeiro</option>';
                            atividadeSelect.disabled = true;
                        }

                        // document.getElementById('descricao').value = descricao;
                        // Não há mais campo descricao

                        // Preencher campo quantidade se for edição
                        // Buscar o valor da quantidade na linha da tabela (se exibido) ou via AJAX, se necessário
                        // Aqui, por simplicidade, não há atribuição. O backend pode preencher via AJAX se necessário.

                        new bootstrap.Modal(equipeModal).show();
                    });
                });

                formEquipe.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const cargos = Array.from(document.querySelectorAll('input[name="cargo[]"]')).map(input => input.value);
                    const quantidades = Array.from(document.querySelectorAll('input[name="quantidade_cargo[]"]')).map(input => input.value);
                    const estrutura = cargos.map((cargo, i) => ({
                        cargo: cargo,
                        quantidade: parseInt(quantidades[i])
                    }));
                    const data = {
                        id: equipeId.value,
                        atividade_id: atividadeSelect.value,
                        quantidade: document.getElementById('quantidade').value,
                        estrutura: estrutura
                    };
                    fetch('api/equipes_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    }).then(res => res.json()).then(result => {
                        showToast(result.message, result.success ? 'success' : 'danger');
                        if (result.success) {
                            bootstrap.Modal.getInstance(equipeModal).hide();
                            setTimeout(() => { location.reload(); }, 1500);
                        }
                    });
                });

                formDelete.addEventListener('submit', function (event) {
                    event.preventDefault();
                    const data = {
                        action: 'delete',
                        id: equipeIdToDelete.value
                    };

                    fetch('api/equipes_api.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(data)
                    }).then(res => res.json()).then(result => {
                        showToast(result.message, result.success ? 'success' : 'danger');
                        if (result.success) {
                            document.getElementById('equipe-row-' + data.id)?.remove();
                            bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
                        }
                    });
                });

                document.querySelectorAll('.btn-excluir-equipe').forEach(button => {
                    button.addEventListener('click', function () {
                        equipeIdToDelete.value = this.dataset.id;
                        new bootstrap.Modal(document.getElementById('confirmDeleteModal')).show();
                    });
                });
                // Estrutura dinâmica de cargos
                const estruturaCargosContainer = document.getElementById('estruturaCargosContainer');
                document.getElementById('btnAddCargo').addEventListener('click', () => {
                    const div = document.createElement('div');
                    div.classList.add('d-flex', 'mb-2', 'gap-2');
                    div.innerHTML = `
                        <input type="text" class="form-control" name="cargo[]" placeholder="Cargo" required>
                        <input type="number" class="form-control" name="quantidade_cargo[]" placeholder="Qtd" min="1" required>
                        <button type="button" class="btn btn-outline-danger btn-sm btnRemoveCargo">×</button>
                    `;
                    estruturaCargosContainer.appendChild(div);
                });

                estruturaCargosContainer.addEventListener('click', (e) => {
                    if (e.target.classList.contains('btnRemoveCargo')) {
                        e.target.parentElement.remove();
                    }
                });
            });
            </script>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Estrutura e status das equipes (ações da tabela)
                const estruturaModal = new bootstrap.Modal(document.getElementById('estruturaModal'));
                const estruturaLista = document.getElementById('estruturaLista');
                const estruturaEquipeId = document.getElementById('estruturaEquipeId');

                document.querySelectorAll('.btn-estrutura').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const equipeId = btn.dataset.id;
                        estruturaEquipeId.value = equipeId;
                        estruturaLista.innerHTML = '';
                        fetch(`api/get_estrutura_por_equipe.php?equipe_id=${equipeId}`)
                            .then(res => res.json())
                            .then(data => {
                                data.forEach(item => {
                                    const div = document.createElement('div');
                                    div.classList.add('d-flex', 'mb-2', 'gap-2');
                                    div.innerHTML = `
                                        <input type="text" class="form-control" name="cargo[]" value="${item.cargo}" required>
                                        <input type="number" class="form-control" name="quantidade[]" value="${item.quantidade}" required>
                                        <button type="button" class="btn btn-outline-danger btn-sm btnRemoveCargo">×</button>
                                    `;
                                    estruturaLista.appendChild(div);
                                });
                                estruturaModal.show();
                            });
                    });
                });

                document.getElementById('btnAddCargoEstrutura').addEventListener('click', () => {
                    const div = document.createElement('div');
                    div.classList.add('d-flex', 'mb-2', 'gap-2');
                    div.innerHTML = `
                        <input type="text" class="form-control" name="cargo[]" placeholder="Cargo" required>
                        <input type="number" class="form-control" name="quantidade[]" placeholder="Qtd" min="1" required>
                        <button type="button" class="btn btn-outline-danger btn-sm btnRemoveCargo">×</button>
                    `;
                    estruturaLista.appendChild(div);
                });

                estruturaLista.addEventListener('click', e => {
                    if (e.target.classList.contains('btnRemoveCargo')) {
                        e.target.parentElement.remove();
                    }
                });

                document.getElementById('formEstrutura').addEventListener('submit', function(e) {
                    e.preventDefault();
                    const equipeId = estruturaEquipeId.value;
                    const cargos = Array.from(document.querySelectorAll('#estruturaLista input[name="cargo[]"]')).map(i => i.value);
                    const quantidades = Array.from(document.querySelectorAll('#estruturaLista input[name="quantidade[]"]')).map(i => parseInt(i.value));
                    const estrutura = cargos.map((cargo, i) => ({
                        cargo: cargo,
                        quantidade: quantidades[i]
                    }));
                    fetch('api/salvar_estrutura.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ equipe_id: equipeId, estrutura: estrutura })
                    }).then(res => res.json()).then(result => {
                        showToast(result.message, result.success ? 'success' : 'danger');
                        if (result.success) estruturaModal.hide();
                    });
                });

                document.querySelectorAll('.btn-status').forEach(btn => {
                    btn.addEventListener('click', () => {
                        const id = btn.dataset.id;
                        const statusAtual = btn.dataset.status;
                        fetch('api/alterar_status_equipe.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: id, status_atual: statusAtual })
                        }).then(res => res.json()).then(result => {
                            showToast(result.message, result.success ? 'success' : 'danger');
                            if (result.success) setTimeout(() => location.reload(), 1000);
                        });
                    });
                });
            });
            </script>
        </div>

        <?php require_once 'includes/footer.php'; ?>
    </div>
</div>
</body>
<script>
  $(document).ready(function() {
    let table = $('#tabela-equipes').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json'
      },
      pageLength: 10,
      orderCellsTop: true,
      fixedHeader: true
    });

    // Aplica filtro por coluna
    $('#tabela-equipes thead tr:eq(1) th').each(function(i) {
      $('input', this).on('keyup change', function() {
        if (table.column(i).search() !== this.value) {
          table.column(i).search(this.value).draw();
        }
      });
    });
  });
</script>
</html>
