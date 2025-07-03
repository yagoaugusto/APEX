<?php
require_once 'includes/db_connect.php';
require_once 'includes/session_check.php'; // Protege a página
$result = $conn->query("SELECT * FROM filiais ORDER BY nome ASC");
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
                    <h1 class="mt-4 mb-0 text-primary">Gerenciar Filiais</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#filialModal" id="btnNovaFilial">
                        <i class="bi bi-plus-circle me-2"></i>Nova Filial
                    </button>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <i class="bi bi-shop-window me-2"></i>Lista de Filiais
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nome da Filial</th>
                                        <th scope="col">Endereço</th>
                                        <th scope="col">Telefone</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr id="filial-row-<?php echo $row['id']; ?>">
                                                <th scope="row"><?php echo $row['id']; ?></th>
                                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                                <td><?php echo htmlspecialchars($row['endereco']); ?></td>
                                                <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info me-2 btn-editar-filial" 
                                                        data-id="<?php echo $row['id']; ?>" 
                                                        data-nome="<?php echo htmlspecialchars($row['nome']); ?>" 
                                                        data-endereco="<?php echo htmlspecialchars($row['endereco']); ?>" 
                                                        data-telefone="<?php echo htmlspecialchars($row['telefone']); ?>">
                                                <i class="bi bi-pencil"></i> Editar
                                            </button>
                                                    <button class="btn btn-sm btn-outline-danger btn-excluir-filial" 
                                                        data-id="<?php echo $row['id']; ?>" 
                                                        data-nome="<?php echo htmlspecialchars($row['nome']); ?>">
                                                <i class="bi bi-trash"></i> Excluir
                                            </button>
                                        </td>
                                    </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Nenhuma filial encontrada.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal para Cadastro/Edição de Filial -->
                <div class="modal fade" id="filialModal" tabindex="-1" aria-labelledby="filialModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="filialModalLabel">Cadastrar Nova Filial</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formFilial">
                                <div class="modal-body">
                                    <input type="hidden" id="filialId" name="id">
                                    <div class="mb-3">
                                        <label for="nomeFilial" class="form-label">Nome da Filial</label>
                                        <input type="text" class="form-control" id="nomeFilial" name="nome" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="enderecoFilial" class="form-label">Endereço</label>
                                        <input type="text" class="form-control" id="enderecoFilial" name="endereco" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="telefoneFilial" class="form-label">Telefone</label>
                                        <input type="text" class="form-control" id="telefoneFilial" name="telefone">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar Filial</button>
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
                                <p>Tem certeza que deseja excluir a filial <strong id="filialNameToDelete"></strong>?</p>
                                <p>Esta ação não pode ser desfeita.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <form id="formDeleteFilial" class="d-inline">
                                    <input type="hidden" id="filialIdToDelete" name="id">
                                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>


                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const filialModal = document.getElementById('filialModal');
                        const filialModalLabel = document.getElementById('filialModalLabel');
                        const formFilial = document.getElementById('formFilial');
                        const filialId = document.getElementById('filialId');
                        const nomeFilial = document.getElementById('nomeFilial');
                        const enderecoFilial = document.getElementById('enderecoFilial');
                        const telefoneFilial = document.getElementById('telefoneFilial');

                        const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                        const formDeleteFilial = document.getElementById('formDeleteFilial');
                        const filialIdToDelete = document.getElementById('filialIdToDelete');
                        const filialNameToDelete = document.getElementById('filialNameToDelete');

                        // Evento para o botão "Nova Filial"
                        document.getElementById('btnNovaFilial').addEventListener('click', function() {
                            filialModalLabel.textContent = 'Cadastrar Nova Filial';
                            formFilial.reset(); // Limpa o formulário
                            filialId.value = ''; // Garante que o ID esteja vazio para cadastro
                        });

                        // Evento para os botões "Editar"
                        document.querySelectorAll('.btn-editar-filial').forEach(button => {
                            button.addEventListener('click', function() {
                                filialModalLabel.textContent = 'Editar Filial';
                                filialId.value = this.dataset.id;
                                nomeFilial.value = this.dataset.nome;
                                enderecoFilial.value = this.dataset.endereco;
                                telefoneFilial.value = this.dataset.telefone;
                                // Abre o modal
                                const modal = new bootstrap.Modal(filialModal);
                                modal.show();
                            });
                        });

                        // Evento para os botões "Excluir"
                        document.querySelectorAll('.btn-excluir-filial').forEach(button => {
                            button.addEventListener('click', function() {
                                filialIdToDelete.value = this.dataset.id;
                                filialNameToDelete.textContent = this.dataset.nome;
                                deleteModal.show();
                            });
                        });

                        // Função para enviar dados via fetch
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

                        // Evento de submit do formulário de Cadastro/Edição
                        formFilial.addEventListener('submit', function(event) {
                            event.preventDefault();
                            const data = {
                                id: filialId.value,
                                nome: nomeFilial.value,
                                endereco: enderecoFilial.value,
                                telefone: telefoneFilial.value
                            };
                            
                            sendData('api/filiais_api.php', data).then(result => {
                                showToast(result.message, result.success ? 'success' : 'danger');
                                if (result.success) {
                                    const modalInstance = bootstrap.Modal.getInstance(filialModal);
                                    if (modalInstance) {
                                        modalInstance.hide();
                                    }
                                    // Atrasar o recarregamento para que o toast seja visível
                                    setTimeout(() => { location.reload(); }, 1500); // 1.5 segundos de atraso
                                }
                            });
                        });

                        // Evento de submit do formulário de Exclusão
                        formDeleteFilial.addEventListener('submit', function(event) {
                            event.preventDefault();
                            const data = {
                                action: 'delete',
                                id: filialIdToDelete.value
                            };

                            sendData('api/filiais_api.php', data).then(result => {
                                showToast(result.message, result.success ? 'success' : 'danger');
                                if (result.success) {
                                    // Remove a linha da tabela sem recarregar a página
                                    const row = document.getElementById('filial-row-' + data.id);
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