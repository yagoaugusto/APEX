

<?php
require_once '../includes/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);
$equipe_id = intval($data['equipe_id'] ?? 0);
$estrutura = $data['estrutura'] ?? [];

if (!$equipe_id || !is_array($estrutura)) {
    echo json_encode(["success" => false, "message" => "Dados inválidos."]);
    exit;
}

// Remove estrutura antiga
$deleteStmt = $conn->prepare("DELETE FROM estrutura_cargos WHERE equipe_id = ?");
$deleteStmt->bind_param("i", $equipe_id);
$deleteStmt->execute();

// Insere nova estrutura
$insertStmt = $conn->prepare("INSERT INTO estrutura_cargos (equipe_id, cargo, quantidade) VALUES (?, ?, ?)");
$success = true;

foreach ($estrutura as $item) {
    $cargo = $item['cargo'] ?? '';
    $quantidade = intval($item['quantidade'] ?? 0);
    if (!$cargo || $quantidade <= 0) continue;

    $insertStmt->bind_param("isi", $equipe_id, $cargo, $quantidade);
    if (!$insertStmt->execute()) {
        $success = false;
        break;
    }
}

echo json_encode([
    "success" => $success,
    "message" => $success ? "Estrutura salva com sucesso." : "Erro ao salvar estrutura."
]);