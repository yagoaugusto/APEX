<?php
require_once '../includes/db_connect.php';

$sql = "SELECT colaborador_id FROM equipe_colaboradores";
$result = $conn->query($sql);

$dados = [];
while ($row = $result->fetch_assoc()) {
    $dados[] = $row;
}

echo json_encode($dados);