<?php
require_once '../includes/db_connect.php';
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["success" => false, "message" => "Dados inválidos."]);
    exit;
}

$action = $data['action'] ?? null;
$id = isset($data['id']) ? intval($data['id']) : null;

if ($action === 'delete' && $id) {
    $stmt = $conn->prepare("UPDATE equipes SET status = 'finalizada' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    echo json_encode([
        "success" => $success,
        "message" => $success ? "Equipe finalizada com sucesso." : "Erro ao finalizar equipe.",
        "id" => $id
    ]);
    exit;
}

// Dados para inserção múltipla de equipes
$atividade_id = intval($data['atividade_id'] ?? 0);
$quantidade = intval($data['quantidade'] ?? 0);

if ($quantidade > 0) {
    $stmt = $conn->prepare("INSERT INTO equipes (atividade_id, status) VALUES (?, 'mobilizando')");
    $stmt->bind_param("i", $atividade_id);

    $estrutura = $data['estrutura'] ?? [];
    $success = true;

    for ($i = 0; $i < $quantidade; $i++) {
        if ($stmt->execute()) {
            $equipe_id = $conn->insert_id;

            if (!empty($estrutura)) {
                $cargoStmt = $conn->prepare("INSERT INTO estrutura_cargos (equipe_id, cargo, quantidade) VALUES (?, ?, ?)");
                foreach ($estrutura as $item) {
                    $cargo = $item['cargo'];
                    $qtd = intval($item['quantidade']);
                    $cargoStmt->bind_param("isi", $equipe_id, $cargo, $qtd);
                    if (!$cargoStmt->execute()) {
                        $success = false;
                        break 2;
                    }
                }
            }
        } else {
            $success = false;
            break;
        }
    }

    echo json_encode([
        "success" => $success,
        "message" => $success ? "Equipes criadas com sucesso." : "Erro ao criar equipes."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Quantidade inválida."
    ]);
}
