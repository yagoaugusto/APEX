<?php
require_once '../includes/db_connect.php';

$filial_id = isset($_GET['filial_id']) ? intval($_GET['filial_id']) : 0;

$sql = "SELECT id, titulo FROM processos WHERE filial_id = ? AND status = 'ativo' ORDER BY titulo ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $filial_id);
$stmt->execute();
$result = $stmt->get_result();

$processos = [];
while ($row = $result->fetch_assoc()) {
    $processos[] = $row;
}

header('Content-Type: application/json');
echo json_encode($processos);