<?php
require_once 'includes/db_connect.php';
require_once 'includes/session_check.php'; // Protege a página

// Buscar processos com o nome da filial (usando JOIN)
$processos_result = $conn->query("
    SELECT p.id, p.titulo, p.status, p.filial_id, f.nome AS filial_nome
    FROM processos p
    JOIN filiais f ON p.filial_id = f.id
    ORDER BY p.titulo ASC
");

// Buscar todas as filiais para o dropdown do modal
$filiais_result = $conn->query("SELECT id, nome FROM filiais ORDER BY nome ASC");
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

            <!-- Main Content -->
            <div class="container-fluid py-4 flex-grow-1">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1 class="mt-4 mb-0 text-primary">Gerenciar Processos</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#processoModal" id="btnNovoProcesso">
                        <i class="bi bi-plus-circle me-2"></i>Novo Processo
                    </button>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <i class="bi bi-diagram-2-fill me-2"></i>Lista de Processos
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Título</th>
                                        <th scope="col">Filial</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($processos_result->num_rows > 0): ?>
                                        <?php while($row = $processos_result->fetch_assoc()): ?>
                                            <tr id="processo-row-<?php echo $row['id']; ?>">
                                                <th scope="row"><?php echo $row['id']; ?></th>
                                                <td><?php echo htmlspecialchars($row['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($row['filial_nome']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo ($row['status'] == 'ativo') ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info me-2 btn-editar-processo" 
                                                        data-id="<?php echo $row['id']; ?>" 
                                                        data-titulo="<?php echo htmlspecialchars($row['titulo']); ?>" 
                                                        data-filial-id="<?php echo $row['filial_id']; ?>" 
                                                        data-status="<?php echo $row['status']; ?>">
                                                        <i class="bi bi-pencil"></i> Editar
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger btn-excluir-processo" 
                                                        data-id="<?php echo $row['id']; ?>" 
                                                        data-titulo="<?php echo htmlspecialchars($row['titulo']); ?>">
                                                        <i class="bi bi-trash"></i> Excluir
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Nenhum processo encontrado.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal para Cadastro/Edição de Processo -->
                <div class="modal fade" id="processoModal" tabindex="-1" aria-labelledby="processoModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="processoModalLabel">Cadastrar Novo Processo</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formProcesso">
                                <div class="modal-body">
                                    <input type="hidden" id="processoId" name="id">
                                    <div class="mb-3">
                                        <label for="tituloProcesso" class="form-label">Título</label>
                                        <input type="text" class="form-control" id="tituloProcesso" name="titulo" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="filialProcesso" class="form-label">Filial</label>
                                        <select class="form-select" id="filialProcesso" name="filial_id" required>
                                            <option value="" disabled selected>Selecione uma filial</option>
                                            <?php mysqli_data_seek($filiais_result, 0); // Reseta o ponteiro do resultado ?>
                                            <?php while($filial = $filiais_result->fetch_assoc()): ?>
                                                <option value="<?php echo $filial['id']; ?>"><?php echo htmlspecialchars($filial['nome']); ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="statusProcesso" class="form-label">Status</label>
                                        <select class="form-select" id="statusProcesso" name="status" required>
                                            <option value="ativo">Ativo</option>
                                            <option value="inativo">Inativo</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar Processo</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal de Confirmação de Exclusão -->
                <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Exclusão</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Tem certeza que deseja excluir o processo <strong id="processoNameToDelete"></strong>?</p>
                                <p>Esta ação não pode ser desfeita.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <form id="formDeleteProcesso" class="d-inline">
                                    <input type="hidden" id="processoIdToDelete" name="id">
                                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const processoModal = document.getElementById('processoModal');
                        const processoModalLabel = document.getElementById('processoModalLabel');
                        const formProcesso = document.getElementById('formProcesso');
                        const processoId = document.getElementById('processoId');
                        const tituloProcesso = document.getElementById('tituloProcesso');
                        const filialProcesso = document.getElementById('filialProcesso');
                        const statusProcesso = document.getElementById('statusProcesso');

                        const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                        const formDeleteProcesso = document.getElementById('formDeleteProcesso');
                        const processoIdToDelete = document.getElementById('processoIdToDelete');
                        const processoNameToDelete = document.getElementById('processoNameToDelete');

                        document.getElementById('btnNovoProcesso').addEventListener('click', function() {
                            processoModalLabel.textContent = 'Cadastrar Novo Processo';
                            formProcesso.reset();
                            processoId.value = '';
                            filialProcesso.selectedIndex = 0; // Reseta o select
                        });

                        document.querySelectorAll('.btn-editar-processo').forEach(button => {
                            button.addEventListener('click', function() {
                                processoModalLabel.textContent = 'Editar Processo';
                                processoId.value = this.dataset.id;
                                tituloProcesso.value = this.dataset.titulo;
                                filialProcesso.value = this.dataset.filialId;
                                statusProcesso.value = this.dataset.status;
                                const modal = new bootstrap.Modal(processoModal);
                                modal.show();
                            });
                        });

                        document.querySelectorAll('.btn-excluir-processo').forEach(button => {
                            button.addEventListener('click', function() {
                                processoIdToDelete.value = this.dataset.id;
                                processoNameToDelete.textContent = this.dataset.titulo;
                                deleteModal.show();
                            });
                        });

                        async function sendData(url, data) {
                            try {
                                const response = await fetch(url, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify(data)
                                });
                                return await response.json();
                            } catch (error) {
                                console.error('Erro na requisição:', error);
                                return { success: false, message: 'Erro de conexão.' };
                            }
                        }

                        formProcesso.addEventListener('submit', function(event) {
                            event.preventDefault();
                            const data = {
                                id: processoId.value,
                                titulo: tituloProcesso.value,
                                filial_id: filialProcesso.value,
                                status: statusProcesso.value
                            };
                            
                            sendData('api/processos_api.php', data).then(result => {
                                showToast(result.message, result.success ? 'success' : 'danger');
                                if (result.success) {
                                    const modalInstance = bootstrap.Modal.getInstance(processoModal);
                                    if (modalInstance) {
                                        modalInstance.hide();
                                    }
                                    setTimeout(() => { location.reload(); }, 1500);
                                }
                            });
                        });

                        formDeleteProcesso.addEventListener('submit', function(event) {
                            event.preventDefault();
                            const data = {
                                action: 'delete',
                                id: processoIdToDelete.value
                            };

                            sendData('api/processos_api.php', data).then(result => {
                                showToast(result.message, result.success ? 'success' : 'danger');
                                if (result.success) {
                                    const row = document.getElementById('processo-row-' + data.id);
                                    if (row) {
                                        row.remove();
                                    }
                                    deleteModal.hide();
                                }
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