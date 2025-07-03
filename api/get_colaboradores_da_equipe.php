<?php
require_once '../includes/db_connect.php';

$equipe_id = intval($_GET['equipe_id'] ?? 0);

if ($equipe_id <= 0) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT c.id, c.nome, c.cargo
        FROM equipe_colaboradores ec
        JOIN colaboradores c ON ec.colaborador_id = c.id
        WHERE ec.equipe_id = ?
        ORDER BY c.nome";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $equipe_id);
$stmt->execute();
$result = $stmt->get_result();

$colaboradores = [];
while ($row = $result->fetch_assoc()) {
    $colaboradores[] = $row;
}

echo json_encode($colaboradores);
