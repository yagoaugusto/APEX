<?php
require_once '../includes/db_connect.php';

$busca = $_GET['busca'] ?? '';
$atividade_id = intval($_GET['atividade_id'] ?? 0);

$sql = "SELECT id, descricao, status FROM equipes WHERE 1";
$params = [];
$types = '';

if ($atividade_id > 0) {
    $sql .= " AND atividade_id = ?";
    $params[] = $atividade_id;
    $types .= 'i';
}

if (!empty($busca)) {
    $sql .= " AND (descricao LIKE ? OR status LIKE ?)";
    $buscaLike = "%$busca%";
    $params[] = $buscaLike;
    $params[] = $buscaLike;
    $types .= 'ss';
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$equipes = [];
while ($row = $result->fetch_assoc()) {
    $equipes[] = $row;
}

echo json_encode($equipes);