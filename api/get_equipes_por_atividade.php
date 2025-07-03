

<?php
require_once '../includes/db_connect.php';

$atividade_id = isset($_GET['atividade_id']) ? intval($_GET['atividade_id']) : 0;

$sql = "
    SELECT e.id, e.atividade_id, e.coordenador_id, e.supervisor_id, e.lider_id, e.status,
           a.processo_id, p.filial_id
    FROM equipes e
    INNER JOIN atividades a ON a.id = e.atividade_id
    INNER JOIN processos p ON p.id = a.processo_id
    WHERE e.atividade_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $atividade_id);
$stmt->execute();
$result = $stmt->get_result();

$dados = [];
while ($row = $result->fetch_assoc()) {
    $dados[] = $row;
}

echo json_encode($dados);