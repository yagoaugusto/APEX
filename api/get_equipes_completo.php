<?php
require_once '../includes/db_connect.php';

$busca = $_GET['busca'] ?? '';
$filial_id = $_GET['filial_id'] ?? '';
$processo_id = $_GET['processo_id'] ?? '';
$atividade_id = $_GET['atividade_id'] ?? '';

$sql = "
    SELECT 
        e.id,
        e.descricao,
        e.status,
        e.coordenador_id,
        e.supervisor_id,
        e.lider_id,
        f.id AS filial_id,
        f.nome AS filial,
        p.titulo AS processo,
        a.titulo AS atividade
    FROM equipes e
    INNER JOIN atividades a ON e.atividade_id = a.id
    INNER JOIN processos p ON a.processo_id = p.id
    INNER JOIN filiais f ON p.filial_id = f.id
    WHERE 1
";

$params = [];
$types = '';

if (!empty($busca)) {
    $sql .= " AND e.descricao LIKE ?";
    $params[] = '%' . $busca . '%';
    $types .= 's';
}

if (!empty($filial_id)) {
    $sql .= " AND f.id = ?";
    $params[] = $filial_id;
    $types .= 'i';
}

if (!empty($processo_id)) {
    $sql .= " AND p.id = ?";
    $params[] = $processo_id;
    $types .= 'i';
}

if (!empty($atividade_id)) {
    $sql .= " AND a.id = ?";
    $params[] = $atividade_id;
    $types .= 'i';
}

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
