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
            $stmt = $conn->prepare("DELETE FROM atividades WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Atividade excluída com sucesso!'];
            } else {
                $response['message'] = 'Erro ao excluir atividade: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'ID da atividade inválido.';
        }
    } else {
        // --- LÓGICA DE CADASTRO E EDIÇÃO ---
        $id = $data['id'] ?? null;
        $titulo = $data['titulo'] ?? '';
        $processo_id = $data['processo_id'] ?? '';
        $meta = $data['meta'] ?? null; // Pode ser null se não fornecido
        $status = $data['status'] ?? '';

        // Validação da Meta
        if (!is_numeric($meta) || $meta < 10 || $meta > 1000000) {
            $response['message'] = 'O valor da meta deve ser um número entre 10 e 1.000.000.';
            echo json_encode($response);
            $conn->close();
            exit(); // Interrompe a execução
        }

        if (empty($id)) {
            // Cadastro
            if (empty($titulo) || empty($status) || $meta === null || empty($processo_id)) {
                $response['message'] = 'Os campos (Título, Processo, Meta, Status) são obrigatórios.';
            } else {
                $stmt = $conn->prepare("INSERT INTO atividades (titulo, processo_id, meta, status) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sids", $titulo, $processo_id, $meta, $status);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Atividade cadastrada com sucesso!'];
                } else {
                    $response['message'] = 'Erro ao salvar atividade: ' . $stmt->error;
                }
                $stmt->close();
            }
        } else {
            // Edição
            if (empty($titulo) || empty($status) || $meta === null) {
                $response['message'] = 'Os campos (Título, Meta, Status) são obrigatórios.';
            } else {
                $stmt = $conn->prepare("UPDATE atividades SET titulo = ?, meta = ?, status = ? WHERE id = ?");
                $stmt->bind_param("sdsi", $titulo, $meta, $status, $id);
                if ($stmt->execute()) {
                    $response = ['success' => true, 'message' => 'Atividade atualizada com sucesso!'];
                } else {
                    $response['message'] = 'Erro ao salvar atividade: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
} else {
    $response['message'] = 'Método de requisição inválido.';
}

$conn->close();
echo json_encode($response);
?>