<?php
require_once 'includes/db_connect.php';
require_once 'includes/session_check.php'; // Protege a página

// Buscar colaboradores com o nome da filial (usando JOIN)
$colaboradores_result = $conn->query("
    SELECT c.id, c.nome, c.status, c.filial_id, c.cargo, c.cpf, c.matricula, f.nome AS filial_nome
    FROM colaboradores c
    JOIN filiais f ON c.filial_id = f.id
    ORDER BY c.nome ASC
");

// Buscar todas as filiais para o dropdown do modal
$filiais_result = $conn->query("SELECT id, nome FROM filiais ORDER BY nome ASC");
mysqli_data_seek($filiais_result, 0); // Reseta o ponteiro para reuso no HTML
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
                    <h1 class="mt-4 mb-0 text-primary">Gerenciar Colaboradores</h1>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#colaboradorModal" id="btnNovoColaborador">
                        <i class="bi bi-plus-circle me-2"></i>Novo Colaborador
                    </button>
                    <button type="button" class="btn btn-outline-success ms-2" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="bi bi-upload me-2"></i>Importar Colaboradores
                    </button>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <i class="bi bi-person-lines-fill me-2"></i>Lista de Colaboradores
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Filial</th>
                                        <th scope="col">Cargo</th>
                                        <th scope="col">CPF</th>
                                        <th scope="col">Matrícula</th>
                                        <th scope="col">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($colaboradores_result->num_rows > 0): ?>
                                        <?php while($row = $colaboradores_result->fetch_assoc()): ?>
                                            <tr id="colaborador-row-<?php echo $row['id']; ?>">
                                                <th scope="row"><?php echo $row['id']; ?></th>
                                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                                <td>
                                                    <?php
                                                        $status_class = 'bg-secondary';
                                                        switch ($row['status']) {
                                                            case 'ativo': $status_class = 'bg-success'; break;
                                                            case 'ferias': $status_class = 'bg-info'; break;
                                                            case 'desligado': $status_class = 'bg-danger'; break;
                                                            case 'afastado': $status_class = 'bg-warning text-dark'; break;
                                                        }
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>">
                                                        <?php echo ucfirst($row['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($row['filial_nome']); ?></td>
                                                <td><?php echo htmlspecialchars($row['cargo']); ?></td>
                                                <td><?php echo htmlspecialchars($row['cpf']); ?></td>
                                                <td><?php echo htmlspecialchars($row['matricula']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info me-2 btn-editar-colaborador" 
                                                        data-id="<?php echo $row['id']; ?>" 
                                                        data-nome="<?php echo htmlspecialchars($row['nome']); ?>" 
                                                        data-status="<?php echo $row['status']; ?>" 
                                                        data-filial-id="<?php echo $row['filial_id']; ?>" 
                                                        data-cargo="<?php echo htmlspecialchars($row['cargo']); ?>" 
                                                        data-cpf="<?php echo htmlspecialchars($row['cpf']); ?>" 
                                                        data-matricula="<?php echo htmlspecialchars($row['matricula']); ?>">
                                                        <i class="bi bi-pencil"></i> Editar
                                                    </button>
                                                    <?php if ($row['status'] != 'desligado'): ?>
                                                    <button class="btn btn-sm btn-outline-danger btn-desligar-colaborador" 
                                                        data-id="<?php echo $row['id']; ?>" 
                                                        data-nome="<?php echo htmlspecialchars($row['nome']); ?>">
                                                        <i class="bi bi-person-x"></i> Desligar
                                                    </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Nenhum colaborador encontrado.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Modal para Cadastro/Edição de Colaborador -->
                <div class="modal fade" id="colaboradorModal" tabindex="-1" aria-labelledby="colaboradorModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="colaboradorModalLabel">Cadastrar Novo Colaborador</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formColaborador">
                                <div class="modal-body">
                                    <input type="hidden" id="colaboradorId" name="id">
                                    <div class="mb-3">
                                        <label for="nomeColaborador" class="form-label">Nome</label>
                                        <input type="text" class="form-control" id="nomeColaborador" name="nome" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="statusColaborador" class="form-label">Status</label>
                                        <select class="form-select" id="statusColaborador" name="status" required>
                                            <option value="ativo">Ativo</option>
                                            <option value="ferias">Férias</option>
                                            <option value="desligado">Desligado</option>
                                            <option value="afastado">Afastado</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="filialColaborador" class="form-label">Filial</label>
                                        <select class="form-select" id="filialColaborador" name="filial_id" required>
                                            <option value="" disabled selected>Selecione uma filial</option>
                                            <?php mysqli_data_seek($filiais_result, 0); // Reseta o ponteiro do resultado ?>
                                            <?php while($filial = $filiais_result->fetch_assoc()): ?>
                                                <option value="<?php echo $filial['id']; ?>"><?php echo htmlspecialchars($filial['nome']); ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cargoColaborador" class="form-label">Cargo</label>
                                        <input type="text" class="form-control" id="cargoColaborador" name="cargo" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="cpfColaborador" class="form-label">CPF</label>
                                        <input type="text" class="form-control" id="cpfColaborador" name="cpf" required pattern="\d{3}\.\d{3}\.\d{3}-\d{2}" placeholder="000.000.000-00">
                                    </div>
                                    <div class="mb-3">
                                        <label for="matriculaColaborador" class="form-label">Matrícula</label>
                                        <input type="text" class="form-control" id="matriculaColaborador" name="matricula" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-primary">Salvar Colaborador</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal de Confirmação de Desligamento -->
                <div class="modal fade" id="confirmDesligarModal" tabindex="-1" aria-labelledby="confirmDesligarModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmDesligarModalLabel">Confirmar Desligamento</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Tem certeza que deseja desligar o colaborador <strong id="colaboradorNameToDesligar"></strong>?</p>
                                <p>Esta ação irá alterar o status do colaborador para "desligado".</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <form id="formDesligarColaborador" class="d-inline">
                                    <input type="hidden" id="colaboradorIdToDesligar" name="id">
                                    <button type="submit" class="btn btn-danger">Confirmar Desligamento</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal de Importação de Colaboradores -->
                <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="importModalLabel">Importar Colaboradores</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formImportColaboradores" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <p>Para importar colaboradores, baixe o template, preencha os dados e faça o upload do arquivo.</p>
                                    <a href="api/download_template.php" class="btn btn-outline-primary mb-3">
                                        <i class="bi bi-download me-2"></i>Baixar Template
                                    </a>
                                    <div class="mb-3">
                                        <label for="excelFile" class="form-label">Selecione o arquivo Excel (.xls ou .xlsx)</label>
                                        <input class="form-control" type="file" id="excelFile" name="excel_file" accept=".xls,.xlsx" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Importar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const colaboradorModal = document.getElementById('colaboradorModal');
                        const colaboradorModalLabel = document.getElementById('colaboradorModalLabel');
                        const formColaborador = document.getElementById('formColaborador');
                        const colaboradorId = document.getElementById('colaboradorId');
                        const nomeColaborador = document.getElementById('nomeColaborador');
                        const statusColaborador = document.getElementById('statusColaborador');
                        const filialColaborador = document.getElementById('filialColaborador');
                        const cargoColaborador = document.getElementById('cargoColaborador');
                        const cpfColaborador = document.getElementById('cpfColaborador');
                        const matriculaColaborador = document.getElementById('matriculaColaborador');

                        const desligarModal = new bootstrap.Modal(document.getElementById('confirmDesligarModal'));
                        const formDesligarColaborador = document.getElementById('formDesligarColaborador');
                        const colaboradorIdToDesligar = document.getElementById('colaboradorIdToDesligar');
                        const colaboradorNameToDesligar = document.getElementById('colaboradorNameToDesligar');

                        const importModal = document.getElementById('importModal');
                        const formImportColaboradores = document.getElementById('formImportColaboradores');

                        // Máscara de CPF
                        cpfColaborador.addEventListener('input', function (e) {
                            let value = e.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito
                            if (value.length > 11) value = value.slice(0, 11);
                            if (value.length > 9) {
                                value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
                            } else if (value.length > 6) {
                                value = value.replace(/^(\d{3})(\d{3})(\d{3})$/, '$1.$2.$3');
                            } else if (value.length > 3) {
                                value = value.replace(/^(\d{3})(\d{3})$/, '$1.$2');
                            }
                            e.target.value = value;
                        });

                        document.getElementById('btnNovoColaborador').addEventListener('click', function() {
                            colaboradorModalLabel.textContent = 'Cadastrar Novo Colaborador';
                            formColaborador.reset();
                            colaboradorId.value = '';
                            filialColaborador.selectedIndex = 0; // Reseta o select da filial
                        });

                        document.querySelectorAll('.btn-editar-colaborador').forEach(button => {
                            button.addEventListener('click', function() {
                                colaboradorModalLabel.textContent = 'Editar Colaborador';
                                colaboradorId.value = this.dataset.id;
                                nomeColaborador.value = this.dataset.nome;
                                statusColaborador.value = this.dataset.status;
                                filialColaborador.value = this.dataset.filialId;
                                cargoColaborador.value = this.dataset.cargo;
                                cpfColaborador.value = this.dataset.cpf;
                                matriculaColaborador.value = this.dataset.matricula;
                                const modal = new bootstrap.Modal(colaboradorModal);
                                modal.show();
                            });
                        });

                        document.querySelectorAll('.btn-desligar-colaborador').forEach(button => {
                            button.addEventListener('click', function() {
                                colaboradorIdToDesligar.value = this.dataset.id;
                                colaboradorNameToDesligar.textContent = this.dataset.nome;
                                desligarModal.show();
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

                        formColaborador.addEventListener('submit', function(event) {
                            event.preventDefault();
                            const data = {
                                id: colaboradorId.value,
                                nome: nomeColaborador.value,
                                status: statusColaborador.value,
                                filial_id: filialColaborador.value,
                                cargo: cargoColaborador.value,
                                cpf: cpfColaborador.value,
                                matricula: matriculaColaborador.value
                            };
                            
                            sendData('api/colaboradores_api.php', data).then(result => {
                                showToast(result.message, result.success ? 'success' : 'danger');
                                if (result.success) {
                                    const modalInstance = bootstrap.Modal.getInstance(colaboradorModal);
                                    if (modalInstance) {
                                        modalInstance.hide();
                                    }
                                    setTimeout(() => { location.reload(); }, 1500);
                                }
                            });
                        });

                        formDesligarColaborador.addEventListener('submit', function(event) {
                            event.preventDefault();
                            const data = {
                                action: 'delete', // Usamos 'delete' na API para mudar o status para 'desligado'
                                id: colaboradorIdToDesligar.value
                            };

                            sendData('api/colaboradores_api.php', data).then(result => {
                                showToast(result.message, result.success ? 'success' : 'danger');
                                if (result.success) {
                                    // Recarrega a página para refletir a mudança de status
                                    setTimeout(() => { location.reload(); }, 1500);
                                }
                                desligarModal.hide();
                            });
                        });

                        // Evento de submit do formulário de Importação
                        formImportColaboradores.addEventListener('submit', async function(event) {
                            event.preventDefault();
                            const formData = new FormData(this); // Cria um FormData com o arquivo

                            try {
                                const response = await fetch('api/import_colaboradores.php', {
                                    method: 'POST',
                                    body: formData // FormData não precisa de Content-Type header
                                });
                                const result = await response.json();
                                showToast(result.message, result.success ? 'success' : 'danger');
                                if (result.success) {
                                    bootstrap.Modal.getInstance(importModal).hide();
                                    setTimeout(() => { location.reload(); }, 1500);
                                }
                            } catch (error) {
                                console.error('Erro na importação:', error);
                                showToast('Erro na importação: ' + error.message, 'danger');
                            }
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