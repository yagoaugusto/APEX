<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$response = ['success' => false, 'message' => 'Ocorreu um erro.'];

// Decodifica o corpo da requisição JSON
$data = json_decode(file_get_contents('php://input'), true);

// Verifica a ação a ser executada
$action = $data['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'delete') {
        // --- LÓGICA DE EXCLUSÃO ---
        $id = $data['id'] ?? 0;

        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM filiais WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Filial excluída com sucesso!'];
            } else {
                $response['message'] = 'Erro ao excluir filial: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'ID da filial inválido.';
        }
    } else {
        // --- LÓGICA DE CADASTRO E EDIÇÃO ---
        $id = $data['id'] ?? null;
        $nome = $data['nome'] ?? '';
        $endereco = $data['endereco'] ?? '';
        $telefone = $data['telefone'] ?? '';

        if (empty($nome)) {
            $response['message'] = 'O nome da filial é obrigatório.';
        } else {
            if (empty($id)) {
                // Cadastro (INSERT)
                $stmt = $conn->prepare("INSERT INTO filiais (nome, endereco, telefone) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $nome, $endereco, $telefone);
                $message = 'Filial cadastrada com sucesso!';
            } else {
                // Edição (UPDATE)
                $stmt = $conn->prepare("UPDATE filiais SET nome = ?, endereco = ?, telefone = ? WHERE id = ?");
                $stmt->bind_param("sssi", $nome, $endereco, $telefone, $id);
                $message = 'Filial atualizada com sucesso!';
            }

            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => $message];
            } else {
                $response['message'] = 'Erro ao salvar filial: ' . $stmt->error;
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