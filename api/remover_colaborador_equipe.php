<?php
require_once '../includes/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$equipe_id = intval($data['equipe_id'] ?? 0);
$colaborador_id = intval($data['colaborador_id'] ?? 0);

if ($equipe_id > 0 && $colaborador_id > 0) {
    $stmt = $conn->prepare("DELETE FROM equipe_colaboradores WHERE equipe_id = ? AND colaborador_id = ?");
    $stmt->bind_param("ii", $equipe_id, $colaborador_id);
    $success = $stmt->execute();

    echo json_encode([
        "success" => $success,
        "message" => $success ? "Colaborador removido da equipe." : "Erro ao remover colaborador."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Dados inválidos."
    ]);
}