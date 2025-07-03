<?php
require_once '../includes/db_connect.php';

$equipe_id = isset($_GET['equipe_id']) ? intval($_GET['equipe_id']) : 0;

if (!$equipe_id) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("SELECT cargo, quantidade FROM estrutura_cargos WHERE equipe_id = ?");
$stmt->bind_param("i", $equipe_id);
$stmt->execute();
$result = $stmt->get_result();

$estrutura = [];
while ($row = $result->fetch_assoc()) {
    $estrutura[] = $row;
}

echo json_encode($estrutura);
?>