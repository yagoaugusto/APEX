<?php
require_once '../includes/db_connect.php';

$filial_id = isset($_GET['filial_id']) ? intval($_GET['filial_id']) : 0;

$sql = "SELECT id, nome FROM colaboradores WHERE filial_id = ? AND status = 'ativo' ORDER BY nome ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $filial_id);
$stmt->execute();
$result = $stmt->get_result();

$colaboradores = [];
while ($row = $result->fetch_assoc()) {
    $colaboradores[] = $row;
}

header('Content-Type: application/json');
echo json_encode($colaboradores);