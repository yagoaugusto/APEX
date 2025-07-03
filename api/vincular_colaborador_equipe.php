<?php
require_once '../includes/db_connect.php';

$data = json_decode(file_get_contents("php://input"), true);

$equipe_id = intval($data['equipe_id'] ?? 0);
$colaborador_id = intval($data['colaborador_id'] ?? 0);

// Verifica se os dados são válidos
if ($equipe_id <= 0 || $colaborador_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Dados inválidos."
    ]);
    exit;
}

// Verifica se já está vinculado
$check = $conn->prepare("SELECT id FROM equipe_colaboradores WHERE equipe_id = ? AND colaborador_id = ?");
$check->bind_param("ii", $equipe_id, $colaborador_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Colaborador já está vinculado a essa equipe."
    ]);
    exit;
}

// Buscar o cargo atual do colaborador
$cargoStmt = $conn->prepare("SELECT cargo FROM colaboradores WHERE id = ?");
$cargoStmt->bind_param("i", $colaborador_id);
$cargoStmt->execute();
$cargoResult = $cargoStmt->get_result();
$cargoRow = $cargoResult->fetch_assoc();
$cargo = $cargoRow['cargo'] ?? '';

// Inserir vínculo na tabela equipe_colaboradores
$stmt = $conn->prepare("INSERT INTO equipe_colaboradores (equipe_id, colaborador_id, cargo) VALUES (?, ?, ?)");
$stmt->bind_param("iis", $equipe_id, $colaborador_id, $cargo);
$success = $stmt->execute();

echo json_encode([
    "success" => $success,
    "message" => $success ? "Colaborador vinculado com sucesso." : "Erro ao vincular colaborador."
]);