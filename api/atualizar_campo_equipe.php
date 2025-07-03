<?php
require_once '../includes/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$equipe_id = intval($data['equipe_id'] ?? 0);
$campo = $data['campo'] ?? '';
$valor = $data['valor'] ?? null;

$campos_permitidos = ['descricao', 'coordenador_id', 'supervisor_id', 'lider_id'];

if (!in_array($campo, $campos_permitidos)) {
    echo json_encode(["success" => false, "message" => "Campo não permitido."]);
    exit;
}

$sql = "UPDATE equipes SET $campo = ? WHERE id = ?";
$stmt = $conn->prepare($sql);

if (in_array($campo, ['coordenador_id', 'supervisor_id', 'lider_id'])) {
    $valor = intval($valor);
    $stmt->bind_param("ii", $valor, $equipe_id);
} else {
    $stmt->bind_param("si", $valor, $equipe_id);
}

$success = $stmt->execute();

echo json_encode([
    "success" => $success,
    "message" => $success ? "Campo atualizado com sucesso." : "Erro ao atualizar o campo."
]);
