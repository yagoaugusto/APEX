<?php
require_once 'includes/db_connect.php';
require_once 'includes/session_check.php'; // Protege a página
$result = $conn->query("SELECT id, nome, email, telefone FROM usuarios ORDER BY nome ASC");
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
                    <h1 class="mt-4 mb-0 text-primary">Gerenciar Usuários</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#usuarioModal" id="btnNovoUsuario">
                        <i class="bi bi-plus-circle me-2"></i>Novo Usuário
                    </button>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <i class="bi bi-people-fill me-2"></i>Lista de Usuários
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Telefone</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                            <tr id="usuario-row-<?php echo $row['id']; ?>">
                                                <th scope="row"><?php echo $row['id']; ?></th>
                                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['telefone']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info me-2 btn-editar-usuario"
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-nome="<?php echo htmlspecialchars($row['nome']); ?>"
                                                        data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                        data-telefone="<?php echo htmlspecialchars($row['telefone']); ?>">
                                                        <i class="bi bi-pencil"></i> Editar
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-warning me-2 btn-reset-senha"
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-nome="<?php echo htmlspecialchars($row['nome']); ?>">
                                                        <i class="bi bi-key"></i> Resetar Senha
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-danger btn-excluir-usuario"
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-nome="<?php echo htmlspecialchars($row['nome']); ?>">
                                                        <i class="bi bi-trash"></i> Excluir
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Nenhum usuário encontrado.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal para Cadastro/Edição de Usuário -->
                <div class="modal fade" id="usuarioModal" tabindex="-1" aria-labelledby="usuarioModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="usuarioModalLabel">Cadastrar Novo Usuário</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formUsuario">
                                <div class="modal-body">
                                    <input type="hidden" id="usuarioId" name="id">
                                    <div class="mb-3">
                                        <label for="nomeUsuario" class="form-label">Nome</label>
                                        <input type="text" class="form-control" id="nomeUsuario" name="nome" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="emailUsuario" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="emailUsuario" name="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="telefoneUsuario" class="form-label">Telefone</label>
                                        <input type="text" class="form-control" id="telefoneUsuario" name="telefone">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar Usuário</button>
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
                                <p>Tem certeza que deseja excluir o usuário <strong id="userNameToDelete"></strong>?</p>
                                <p>Esta ação não pode ser desfeita.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <form id="formDeleteUsuario" class="d-inline">
                                    <input type="hidden" id="usuarioIdToDelete" name="id">
                                    <button type="submit" class="btn btn-danger">Confirmar Exclusão</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal de Confirmação de Reset de Senha -->
                <div class="modal fade" id="confirmResetModal" tabindex="-1" aria-labelledby="confirmResetModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmResetModalLabel">Confirmar Reset de Senha</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Tem certeza que deseja resetar a senha do usuário <strong id="userNameToReset"></strong>?</p>
                                <p>Uma nova senha temporária será gerada e a antiga será invalidada.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-warning" id="confirmResetBtn">Sim, Resetar Senha</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const usuarioModal = document.getElementById('usuarioModal');
                        const usuarioModalLabel = document.getElementById('usuarioModalLabel');
                        const formUsuario = document.getElementById('formUsuario');
                        const usuarioId = document.getElementById('usuarioId');
                        const nomeUsuario = document.getElementById('nomeUsuario');
                        const emailUsuario = document.getElementById('emailUsuario');
                        const telefoneUsuario = document.getElementById('telefoneUsuario');

                        const deleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                        const formDeleteUsuario = document.getElementById('formDeleteUsuario');
                        const usuarioIdToDelete = document.getElementById('usuarioIdToDelete');
                        const userNameToDelete = document.getElementById('userNameToDelete');

                        const resetModal = new bootstrap.Modal(document.getElementById('confirmResetModal'));
                        const userNameToReset = document.getElementById('userNameToReset');
                        const confirmResetBtn = document.getElementById('confirmResetBtn');
                        let userIdToReset = null;

                        // Evento para o botão "Novo Usuário"
                        document.getElementById('btnNovoUsuario').addEventListener('click', function() {
                            usuarioModalLabel.textContent = 'Cadastrar Novo Usuário';
                            formUsuario.reset(); // Limpa o formulário
                            usuarioId.value = ''; // Garante que o ID esteja vazio para cadastro
                        });

                        // Evento para os botões "Editar"
                        document.querySelectorAll('.btn-editar-usuario').forEach(button => {
                            button.addEventListener('click', function() {
                                usuarioModalLabel.textContent = 'Editar Usuário';
                                usuarioId.value = this.dataset.id;
                                nomeUsuario.value = this.dataset.nome;
                                emailUsuario.value = this.dataset.email;
                                telefoneUsuario.value = this.dataset.telefone;
                                // Abre o modal
                                const modal = new bootstrap.Modal(usuarioModal);
                                modal.show();
                            });
                        });

                        // Evento para os botões "Excluir"
                        document.querySelectorAll('.btn-excluir-usuario').forEach(button => {
                            button.addEventListener('click', function() {
                                usuarioIdToDelete.value = this.dataset.id;
                                userNameToDelete.textContent = this.dataset.nome;
                                deleteModal.show();
                            });
                        });

                        // Evento para os botões "Resetar Senha"
                        document.querySelectorAll('.btn-reset-senha').forEach(button => {
                            button.addEventListener('click', function() {
                                userIdToReset = this.dataset.id;
                                userNameToReset.textContent = this.dataset.nome;
                                resetModal.show();
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
                        formUsuario.addEventListener('submit', function(event) {
                            event.preventDefault();
                            const data = {
                                id: usuarioId.value,
                                nome: nomeUsuario.value,
                                email: emailUsuario.value,
                                telefone: telefoneUsuario.value
                            };
                            
                            sendData('api/usuarios_api.php', data).then(result => {
                                showToast(result.message, result.success ? 'success' : 'danger');
                                if (result.success) {
                                    bootstrap.Modal.getInstance(usuarioModal).hide();
                                    setTimeout(() => { location.reload(); }, 1500);
                                }
                            });
                        });

                        // Evento de submit do formulário de Exclusão
                        formDeleteUsuario.addEventListener('submit', function(event) {
                            event.preventDefault();
                            const data = { action: 'delete', id: usuarioIdToDelete.value };
                            sendData('api/usuarios_api.php', data).then(result => {
                                showToast(result.message, result.success ? 'success' : 'danger');
                                if (result.success) {
                                    document.getElementById('usuario-row-' + data.id)?.remove();
                                    deleteModal.hide();
                                }
                            });
                        });

                        // Evento de clique no botão de confirmação de reset de senha
                        confirmResetBtn.addEventListener('click', function() {
                            const data = { action: 'reset_password', id: userIdToReset };
                            sendData('api/usuarios_api.php', data).then(result => {
                                showToast(result.message, result.success ? 'success' : 'warning');
                                if (result.success) {
                                    resetModal.hide();
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