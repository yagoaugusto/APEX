<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$response = ['success' => false, 'message' => 'Ocorreu um erro.'];

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'delete') {
        // --- LÓGICA DE EXCLUSÃO (Lógica, não física) ---
        // Para colaboradores, geralmente se usa inativação ou status 'desligado'
        // Aqui, vamos mudar o status para 'desligado'
        $id = $data['id'] ?? 0;
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE colaboradores SET status = 'desligado' WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Colaborador desligado com sucesso!'];
            } else {
                $response['message'] = 'Erro ao desligar colaborador: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'ID do colaborador inválido.';
        }
    } else {
        // --- LÓGICA DE CADASTRO E EDIÇÃO ---
        $id = $data['id'] ?? null;
        $nome = $data['nome'] ?? '';
        $status = $data['status'] ?? '';
        $filial_id = $data['filial_id'] ?? '';
        $cargo = $data['cargo'] ?? '';
        $cpf = $data['cpf'] ?? '';
        $matricula = $data['matricula'] ?? '';

        // Validações
        if (empty($nome) || empty($status) || empty($filial_id) || empty($cargo) || empty($cpf) || empty($matricula)) {
            $response['message'] = 'Todos os campos são obrigatórios.';
            echo json_encode($response);
            $conn->close();
            exit();
        }

        // Remover caracteres não numéricos do CPF
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        if (strlen($cpf) != 11) {
            $response['message'] = 'CPF inválido. Deve conter 11 dígitos.';
            echo json_encode($response);
            $conn->close();
            exit();
        }

        // Checar unicidade de CPF e Matrícula
        $stmt_check = $conn->prepare("SELECT id FROM colaboradores WHERE (cpf = ? OR matricula = ?) AND id != ?");
        $current_id = (int)($id ?? 0);
        $stmt_check->bind_param("ssi", $cpf, $matricula, $current_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $response['message'] = 'CPF ou Matrícula já cadastrados para outro colaborador.';
        } else {
            if (empty($id)) {
                // Cadastro (INSERT)
                $stmt = $conn->prepare("INSERT INTO colaboradores (nome, status, filial_id, cargo, cpf, matricula) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssisss", $nome, $status, $filial_id, $cargo, $cpf, $matricula);
                $message = 'Colaborador cadastrado com sucesso!';
            } else {
                // Edição (UPDATE)
                $stmt = $conn->prepare("UPDATE colaboradores SET nome = ?, status = ?, filial_id = ?, cargo = ?, cpf = ?, matricula = ? WHERE id = ?");
                $stmt->bind_param("ssisssi", $nome, $status, $filial_id, $cargo, $cpf, $matricula, $id);
                $message = 'Colaborador atualizado com sucesso!';
            }

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => $message];
            } else {
                // Erro de execução (ex: erro de chave estrangeira, se o RESTRICT não for suficiente)
                $response['message'] = 'Erro ao salvar colaborador: ' . $stmt->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
} else {
    $response['message'] = 'Método de requisição inválido.';
}

$conn->close();
echo json_encode($response);
?>