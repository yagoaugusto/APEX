<?php
require_once '../includes/db_connect.php';

$processo_id = isset($_GET['processo_id']) ? intval($_GET['processo_id']) : 0;

$sql = "SELECT id, titulo FROM atividades WHERE processo_id = ? AND status = 'ativo' ORDER BY titulo ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $processo_id);
$stmt->execute();
$result = $stmt->get_result();

$atividades = [];
while ($row = $result->fetch_assoc()) {
    $atividades[] = $row;
}

header('Content-Type: application/json');
echo json_encode($atividades);