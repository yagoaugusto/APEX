

<?php
require_once '../includes/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$equipe_id = intval($data['equipe_id'] ?? 0);
$status = $data['novo_status'] ?? '';

$statuses_validos = ['mobilizando', 'ativa', 'finalizada'];

if (!in_array($status, $statuses_validos)) {
    echo json_encode(["success" => false, "message" => "Status inválido."]);
    exit;
}

$stmt = $conn->prepare("UPDATE equipes SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $equipe_id);
$success = $stmt->execute();

echo json_encode([
    "success" => $success,
    "message" => $success ? "Status atualizado com sucesso." : "Erro ao atualizar o status."
]);