<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$response = ['success' => false, 'message' => 'Ocorreu um erro.'];

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'delete') {
        // --- LÓGICA DE EXCLUSÃO ---
        $id = $data['id'] ?? 0;
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM processos WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Processo excluído com sucesso!'];
            } else {
                $response['message'] = 'Erro ao excluir processo: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'ID do processo inválido.';
        }
    } else {
        // --- LÓGICA DE CADASTRO E EDIÇÃO ---
        $id = $data['id'] ?? null;
        $titulo = $data['titulo'] ?? '';
        $filial_id = $data['filial_id'] ?? '';
        $status = $data['status'] ?? '';

        if (empty($titulo) || empty($filial_id) || empty($status)) {
            $response['message'] = 'Todos os campos (Título, Filial, Status) são obrigatórios.';
        } else {
            if (empty($id)) {
                // Cadastro (INSERT)
                $stmt = $conn->prepare("INSERT INTO processos (titulo, filial_id, status) VALUES (?, ?, ?)");
                $stmt->bind_param("sis", $titulo, $filial_id, $status);
                $message = 'Processo cadastrado com sucesso!';
            } else {
                // Edição (UPDATE)
                $stmt = $conn->prepare("UPDATE processos SET titulo = ?, filial_id = ?, status = ? WHERE id = ?");
                $stmt->bind_param("sisi", $titulo, $filial_id, $status, $id);
                $message = 'Processo atualizado com sucesso!';
            }

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => $message];
            } else {
                $response['message'] = 'Erro ao salvar processo: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
} else {
    $response['message'] = 'Método de requisição inválido.';
}

$conn->close();
echo json_encode($response);
?>