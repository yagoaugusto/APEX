<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$response = ['success' => false, 'message' => ''];
$processes = [];

// Pega o filial_id da requisição GET
$filial_id = $_GET['filial_id'] ?? null;

if ($filial_id === null || !is_numeric($filial_id)) {
    $response['message'] = 'ID da filial inválido ou não fornecido.';
} else {
    $stmt = $conn->prepare("SELECT id, titulo FROM processos WHERE filial_id = ? ORDER BY titulo ASC");
    $stmt->bind_param("i", $filial_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $processes[] = $row;
        }
        $response['success'] = true;
        $response['processes'] = $processes;
    } else {
        $response['success'] = true; // Ainda é sucesso, apenas não há processos
        $response['message'] = 'Nenhum processo encontrado para esta filial.';
        $response['processes'] = [];
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>