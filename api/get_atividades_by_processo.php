<?php
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$processo_id = isset($_GET['processo_id']) ? intval($_GET['processo_id']) : 0;

if ($processo_id <= 0) {
    echo json_encode(['atividades' => []]);
    exit;
}

$stmt = $conn->prepare("SELECT id, titulo FROM atividades WHERE processo_id = ? ORDER BY titulo ASC");
$stmt->bind_param("i", $processo_id);
$stmt->execute();
$result = $stmt->get_result();

$atividades = [];
while ($row = $result->fetch_assoc()) {
    $atividades[] = $row;
}

echo json_encode(['atividades' => $atividades]);
?>
