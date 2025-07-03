<?php
require_once '../includes/db_connect.php';

$busca = $_GET['busca'] ?? '';
$filial_id = intval($_GET['filial_id'] ?? 0);

$sql = "SELECT id, nome, cargo FROM colaboradores WHERE status = 'ativo'";
$params = [];
$types = '';

if ($filial_id > 0) {
    $sql .= " AND filial_id = ?";
    $params[] = $filial_id;
    $types .= 'i';
}

if (!empty($busca)) {
    $sql .= " AND (nome LIKE ? OR cargo LIKE ?)";
    $buscaLike = "%$busca%";
    $params[] = $buscaLike;
    $params[] = $buscaLike;
    $types .= 'ss';
}

$sql .= " ORDER BY nome ASC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$colaboradores = [];
while ($row = $result->fetch_assoc()) {
    $colaboradores[] = $row;
}

echo json_encode($colaboradores);