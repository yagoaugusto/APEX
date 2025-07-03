<?php
require_once '../includes/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$equipe_id = intval($data['equipe_id'] ?? 0);
$campo = $data['campo'] ?? '';
$valor = intval($data['valor'] ?? 0);

// Verificar se o campo é válido
$campos_validos = ['coordenador_id', 'supervisor_id', 'lider_id'];
if (!in_array($campo, $campos_validos)) {
    echo json_encode(["success" => false, "message" => "Campo inválido."]);
    exit;
}

$sql = "UPDATE equipes SET $campo = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $valor, $equipe_id);
$success = $stmt->execute();

echo json_encode([
    "success" => $success,
    "message" => $success ? "Atualização realizada com sucesso." : "Erro ao atualizar."
]);
