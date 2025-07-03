<?php
require_once 'includes/db_connect.php';
require_once 'includes/session_check.php'; // Protege a página

// Obter o termo de busca da URL, se existir
$search_term = $_GET['search'] ?? '';

// Removido: Configurações de Paginação para DataTables controlar a paginação

// Preparar parâmetros de busca para consultas
$search_param_like = '%' . $search_term . '%';

// Removido: Lógica de contagem de registros e paginação para DataTables controlar a paginação

// Buscar atividades com o título do processo (usando JOIN)
$sql_atividades = "
    SELECT a.id, a.titulo, a.meta, a.status, a.processo_id, p.titulo AS processo_titulo, p.filial_id, f.nome AS filial_nome
    FROM atividades a
    JOIN processos p ON a.processo_id = p.id
    JOIN filiais f ON p.filial_id = f.id -- Adicionado para obter o nome da filial do processo
";

// Adicionar condição de busca (sem paginação, pois DataTables controla)
$bind_types_atividades = "";
$bind_params_atividades = [];

if (!empty($search_term)) {
    $sql_atividades .= " WHERE a.titulo LIKE ? OR p.titulo LIKE ? OR f.nome LIKE ?";
    $bind_types_atividades .= "sss";
    $bind_params_atividades[] = &$search_param_like;
    $bind_params_atividades[] = &$search_param_like;
    $bind_params_atividades[] = &$search_param_like;
}

$sql_atividades .= " ORDER BY a.titulo ASC";

$stmt_atividades = $conn->prepare($sql_atividades);
if (!empty($bind_types_atividades)) {
    call_user_func_array([$stmt_atividades, 'bind_param'], array_merge([$bind_types_atividades], $bind_params_atividades));
}
$stmt_atividades->execute();
$atividades_result = $stmt_atividades->get_result();

// Buscar todas as filiais para o dropdown do modal
$filiais_result = $conn->query("SELECT id, nome FROM filiais ORDER BY nome ASC");
mysqli_data_seek($filiais_result, 0); // Reseta o ponteiro para reuso no HTML

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <?php require_once 'includes/head.php'; ?>
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
</head>

<body>
    <div class="d-flex" id="wrapper">
        <?php require_once 'includes/sidebar.php'; ?>

        <!-- Page Content Wrapper -->
        <div id="page-content-wrapper" class="flex-grow-1 d-flex flex-column">
            <?php require_once 'includes/navbar.php'; ?>

            <!-- Main Content -->
            <div class="container-fluid py-4 flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mt-4 mb-0 text-primary">Gerenciar Atividades</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#atividadeModal" id="btnNovaAtividade">
                        <i class="bi bi-plus-circle me-2"></i>Nova Atividade
                    </button>
                </div>


                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="filtroFilial" class="form-label">Filial</label>
                        <select id="filtroFilial" class="form-select">
                            <option value="">Todas</option>
                            <?php mysqli_data_seek($filiais_result, 0);
                            while ($filial = $filiais_result->fetch_assoc()): ?>
                                <option value="<?php echo $filial['id']; ?>"><?php echo htmlspecialchars($filial['nome']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filtroProcesso" class="form-label">Processo</label>
                        <select id="filtroProcesso" class="form-select" disabled>
                            <option value="">Selecione uma filial</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filtroAtividade" class="form-label">Atividade</label>
                        <select id="filtroAtividade" class="form-select" disabled>
                            <option value="">Selecione um processo</option>
                        </select>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <i class="bi bi-list-check me-2"></i>Lista de Atividades
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tabelaAtividades">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Filial</th>
                                        <th>Título</th>
                                        <th>Processo</th>
                                        <th>Meta</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                    <tr>
                                        <th></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Buscar Filial"></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Buscar Título"></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Buscar Processo"></th>
                                        <th><input type="text" class="form-control form-control-sm column-search" placeholder="Buscar Meta"></th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($atividades_result->num_rows > 0): ?>
                                        <?php while ($row = $atividades_result->fetch_assoc()): ?>
                                            <tr id="atividade-row-<?php echo $row['id']; ?>">
                                                <th scope="row"><?php echo $row['id']; ?></th>
                                                <td><?php echo htmlspecialchars($row['filial_nome']); ?></td>
                                                <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($row['processo_titulo']); ?></td>
                                                <td>R$ <?php echo number_format($row['meta'], 2, ',', '.'); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = 'bg-secondary';
                                                    if ($row['status'] == 'ativo') $status_class = 'bg-success';
                                                    if ($row['status'] == 'inativo') $status_class = 'bg-secondary';
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="d-flex gap-2">
                                                    <button class="btn btn-sm btn-outline-info btn-editar-atividade"
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-titulo="<?php echo htmlspecialchars($row['titulo']); ?>"
                                                        data-processo-id="<?php echo $row['processo_id']; ?>"
                                                        data-meta="<?php echo htmlspecialchars($row['meta']); ?>"
                                                        data-status="<?php echo $row['status']; ?>"
                                                        data-filial-id-processo="<?php echo $row['filial_id']; ?>"
                                                        title="Editar Atividade">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Nenhuma atividade encontrada.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal para Cadastro de Atividade -->
                <div class="modal fade" id="atividadeModal" tabindex="-1" aria-labelledby="atividadeModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="atividadeModalLabel">Cadastrar Nova Atividade</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formAtividade">
                                <div class="modal-body">
                                    <input type="hidden" id="atividadeId" name="id">
                                    <div class="mb-3">
                                        <label for="tituloAtividade" class="form-label">Título</label>
                                        <input type="text" class="form-control" id="tituloAtividade" name="titulo" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="filialAtividade" class="form-label">Filial</label>
                                        <select class="form-select" id="filialAtividade" required>
                                            <option value="">Selecione a filial</option>
                                            <?php mysqli_data_seek($filiais_result, 0);
                                            while ($filial = $filiais_result->fetch_assoc()): ?>
                                                <option value="<?php echo $filial['id']; ?>"><?php echo htmlspecialchars($filial['nome']); ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="processoAtividade" class="form-label">Processo</label>
                                        <select class="form-select" id="processoAtividade" required disabled>
                                            <option value="">Selecione uma filial primeiro</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="atividadePaiAtividade" class="form-label">Atividade</label>
                                        <select class="form-select" id="atividadePaiAtividade" required disabled>
                                            <option value="">Selecione um processo primeiro</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="metaAtividade" class="form-label">Meta (R$)</label>
                                        <input type="number" class="form-control" id="metaAtividade" name="meta" step="0.01" min="10" max="1000000" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="statusAtividade" class="form-label">Status</label>
                                        <select class="form-select" id="statusAtividade" name="status" required>
                                            <option value="ativo">Ativo</option>
                                            <option value="inativo">Inativo</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar Atividade</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal para Edição de Atividade -->
                <div class="modal fade" id="atividadeModalEditar" tabindex="-1" aria-labelledby="atividadeModalEditarLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="atividadeModalEditarLabel">Editar Atividade</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formAtividadeEditar">
                                <div class="modal-body">
                                    <input type="hidden" id="atividadeIdEditar" name="id">
                                    <div class="mb-3">
                                        <label for="tituloAtividadeEditar" class="form-label">Título</label>
                                        <input type="text" class="form-control" id="tituloAtividadeEditar" name="titulo" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="metaAtividadeEditar" class="form-label">Meta (R$)</label>
                                        <input type="number" class="form-control" id="metaAtividadeEditar" name="meta" step="0.01" min="10" max="1000000" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="statusAtividadeEditar" class="form-label">Status</label>
                                        <select class="form-select" id="statusAtividadeEditar" name="status" required>
                                            <option value="ativo">Ativo</option>
                                            <option value="inativo">Inativo</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>



                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        // Modal Nova Atividade: Filial/Processo dinâmicos
                        $('#filialAtividade').on('change', function() {
                            const filialId = this.value;
                            $('#processoAtividade').html('<option>Carregando...</option>').prop('disabled', true);
                            // Limpa e desabilita o campo de atividade pai ao trocar filial
                            $('#atividadePaiAtividade').html('<option>Selecione um processo primeiro</option>').prop('disabled', true);
                            if (!filialId) {
                                $('#processoAtividade').html('<option>Selecione uma filial primeiro</option>');
                                return;
                            }
                            fetch('api/get_processos_by_filial.php?filial_id=' + filialId)
                                .then(res => res.json())
                                .then(data => {
                                    $('#processoAtividade').html('<option value=\"\">Selecione</option>');
                                    data.processes.forEach(p => {
                                        $('#processoAtividade').append(`<option value=\"${p.id}\">${p.titulo}</option>`);
                                    });
                                    $('#processoAtividade').prop('disabled', false);
                                });
                        });
                        // Carregar atividades quando um processo for selecionado no modal de nova atividade
                        $('#processoAtividade').on('change', function () {
                            const processoId = this.value;
                            $('#atividadePaiAtividade').html('<option>Carregando...</option>').prop('disabled', true);

                            if (!processoId) {
                                $('#atividadePaiAtividade').html('<option>Selecione um processo primeiro</option>');
                                return;
                            }

                            fetch('api/get_atividades_by_processo.php?processo_id=' + processoId)
                                .then(res => res.json())
                                .then(data => {
                                    $('#atividadePaiAtividade').html('<option value="">Selecione</option>');
                                    data.atividades.forEach(a => {
                                        $('#atividadePaiAtividade').append(`<option value="${a.id}">${a.titulo}</option>`);
                                    });
                                    $('#atividadePaiAtividade').prop('disabled', false);
                                });
                        });
                        const filtroFilial = document.getElementById('filtroFilial');
                        const filtroProcesso = document.getElementById('filtroProcesso');
                        const filtroAtividade = document.getElementById('filtroAtividade');

                        let table = $('#tabelaAtividades').DataTable({
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/pt-BR.json'
                            },
                            paging: true,
                            info: false,
                            ordering: true,
                            initComplete: function() {
                                this.api().columns().every(function() {
                                    const that = this;
                                    $('input', this.header()).on('keyup change clear', function() {
                                        if (that.search() !== this.value) {
                                            that.search(this.value).draw();
                                        }
                                    });
                                });
                            }
                        });

                        filtroFilial.addEventListener('change', async function() {
                            const filialId = this.value;
                            filtroProcesso.innerHTML = '<option>Carregando...</option>';
                            filtroAtividade.innerHTML = '<option>Selecione um processo</option>';
                            filtroProcesso.disabled = true;
                            filtroAtividade.disabled = true;

                            if (!filialId) {
                                filtroProcesso.innerHTML = '<option>Selecione uma filial</option>';
                                table.column(1).search('').draw();
                                return;
                            }

                            try {
                                const res = await fetch('api/get_processos_by_filial.php?filial_id=' + filialId);
                                const json = await res.json();
                                filtroProcesso.innerHTML = '<option value="">Todos</option>';
                                json.processes.forEach(p => {
                                    const opt = document.createElement('option');
                                    opt.value = p.id;
                                    opt.textContent = p.titulo;
                                    filtroProcesso.appendChild(opt);
                                });
                                filtroProcesso.disabled = false;
                                table.column(1).search(filtroFilial.options[filtroFilial.selectedIndex].text).draw();
                            } catch (e) {
                                console.error(e);
                            }
                        });

                        filtroProcesso.addEventListener('change', async function() {
                            const processoId = this.value;
                            filtroAtividade.innerHTML = '<option>Carregando...</option>';
                            filtroAtividade.disabled = true;

                            if (!processoId) {
                                filtroAtividade.innerHTML = '<option>Selecione um processo</option>';
                                table.column(3).search('').draw();
                                return;
                            }

                            try {
                                const res = await fetch('api/get_atividades_by_processo.php?processo_id=' + processoId);
                                const json = await res.json();
                                filtroAtividade.innerHTML = '<option value="">Todos</option>';
                                json.atividades.forEach(a => {
                                    const opt = document.createElement('option');
                                    opt.value = a.id;
                                    opt.textContent = a.titulo;
                                    filtroAtividade.appendChild(opt);
                                });
                                filtroAtividade.disabled = false;
                                table.column(3).search(filtroProcesso.options[filtroProcesso.selectedIndex].text).draw();
                            } catch (e) {
                                console.error(e);
                            }
                        });

                        filtroAtividade.addEventListener('change', () => {
                            if (!table) return;
                            table.column(2).search(filtroAtividade.options[filtroAtividade.selectedIndex].text).draw();
                        });

                        // Listener para botão "Editar Atividade" - abre o modal de edição
                        $(document).on('click', '.btn-editar-atividade', function() {
                            const id = $(this).data('id');
                            const titulo = $(this).data('titulo');
                            const meta = $(this).data('meta');
                            const status = $(this).data('status');
                            $('#atividadeModalEditarLabel').text('Editar Atividade');
                            $('#atividadeIdEditar').val(id);
                            $('#tituloAtividadeEditar').val(titulo).prop('readonly', false);
                            $('#metaAtividadeEditar').val(meta).prop('readonly', false);
                            $('#statusAtividadeEditar').val(status).prop('disabled', false);
                            $('#atividadeModalEditar').modal('show');
                        });

                        // Quando abrir o modal para nova atividade, remover restrições
                        $('#btnNovaAtividade').on('click', function() {
                            $('#atividadeModalLabel').text('Cadastrar Nova Atividade');
                            $('#atividadeId').val('');
                            $('#tituloAtividade').val('').prop('readonly', false);
                            $('#metaAtividade').val('').prop('readonly', false);
                            $('#statusAtividade').val('ativo').prop('disabled', false);
                        });

                        // Submissão do formulário de criação de atividade
                        $('#formAtividade').on('submit', function(e) {
                            e.preventDefault();
                            const filialId = $('#filialAtividade').val();
                            const processoId = $('#processoAtividade').val();
                            if (!filialId) {
                                alert('Selecione uma filial para criar a atividade.');
                                return;
                            }
                            if (!processoId) {
                                alert('Selecione um processo para criar a atividade.');
                                return;
                            }
                            const payload = {
                                action: 'salvar',
                                id: $('#atividadeId').val(),
                                titulo: $('#tituloAtividade').val(),
                                meta: $('#metaAtividade').val(),
                                status: $('#statusAtividade').val(),
                                processo_id: processoId
                            };
                            $.ajax({
                                    url: 'api/atividades_api.php',
                                    method: 'POST',
                                    contentType: 'application/json',
                                    data: JSON.stringify(payload),
                                })
                                .then(response => typeof response === 'object' ? response : JSON.parse(response))
                                .then(json => {
                                    if (json.success) {
                                        alert('Atividade cadastrada com sucesso!');
                                        $('#atividadeModal').modal('hide');
                                        location.reload();
                                    } else {
                                        alert('Erro ao salvar: ' + json.message);
                                    }
                                })
                                .catch(err => {
                                    alert('Erro inesperado ao salvar a atividade.');
                                    console.error(err);
                                });
                        });

                        // Submissão do formulário de edição de atividade
                        $('#formAtividadeEditar').on('submit', function(e) {
                            e.preventDefault();
                            const payload = {
                                action: 'salvar',
                                id: $('#atividadeIdEditar').val(),
                                titulo: $('#tituloAtividadeEditar').val(),
                                meta: $('#metaAtividadeEditar').val(),
                                status: $('#statusAtividadeEditar').val()
                                // Não envia processo_id na edição
                            };
                            $.ajax({
                                    url: 'api/atividades_api.php',
                                    method: 'POST',
                                    contentType: 'application/json',
                                    data: JSON.stringify(payload),
                                })
                                .then(response => typeof response === 'object' ? response : JSON.parse(response))
                                .then(json => {
                                    if (json.success) {
                                        alert('Atividade atualizada com sucesso!');
                                        $('#atividadeModalEditar').modal('hide');
                                        location.reload();
                                    } else {
                                        alert('Erro ao salvar: ' + json.message);
                                    }
                                })
                                .catch(err => {
                                    alert('Erro inesperado ao salvar a atividade.');
                                    console.error(err);
                                });
                        });
                    });
                </script>
            </div>
            <!-- /Main Content -->

            <?php require_once 'includes/footer.php'; ?>
        </div>
        <!-- /Page Content Wrapper -->
    </div>
</body>



</html>