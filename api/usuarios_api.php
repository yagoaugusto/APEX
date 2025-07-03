<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$response = ['success' => false, 'message' => 'Ocorreu um erro.'];

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'delete') {
        // --- LÓGICA DE EXCLUSÃO ---
        $id = $data['id'] ?? 0;
        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $response = ['success' => true, 'message' => 'Usuário excluído com sucesso!'];
            } else {
                $response['message'] = 'Erro ao excluir usuário: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'ID do usuário inválido.';
        }
    } elseif ($action === 'reset_password') {
        // --- LÓGICA DE RESET DE SENHA ---
        $id = $data['id'] ?? 0;
        if ($id > 0) {
            $new_password = bin2hex(random_bytes(4)); // Gera uma senha aleatória de 8 caracteres
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Reseta a senha e força o usuário a trocá-la no próximo login
            $stmt = $conn->prepare("UPDATE usuarios SET senha = ?, primeiro_acesso = 1 WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $id);

            if ($stmt->execute()) {
                $response = [
                    'success' => true, 
                    'message' => 'Senha resetada! A nova senha temporária é: ' . $new_password,
                    'new_password' => $new_password // Retorna a senha para o admin
                ];
            } else {
                $response['message'] = 'Erro ao resetar a senha: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'ID do usuário inválido.';
        }
    } else {
        // --- LÓGICA DE CADASTRO E EDIÇÃO ---
        $id = $data['id'] ?? null;
        $nome = $data['nome'] ?? '';
        $email = $data['email'] ?? '';
        $telefone = $data['telefone'] ?? '';

        // Validações básicas
        if (empty($nome) || empty($email)) {
            $response['message'] = 'Nome e E-mail são obrigatórios.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $response['message'] = 'Formato de e-mail inválido.';
        } else {
            // Checar se o e-mail já existe (em caso de cadastro ou mudança de e-mail na edição)
            $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $current_id = $id ?? 0;
            $stmt_check->bind_param("si", $email, $current_id);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                $response['message'] = 'Este e-mail já está em uso por outro usuário.';
            } else {
                if (empty($id)) {
                    // CADASTRO
                    $password = bin2hex(random_bytes(4)); // Senha aleatória de 8 caracteres
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, telefone, senha, primeiro_acesso) VALUES (?, ?, ?, ?, 1)");
                    $stmt->bind_param("ssss", $nome, $email, $telefone, $hashed_password);
                    
                    if ($stmt->execute()) {
                        $response = [
                            'success' => true, 
                            'message' => 'Usuário cadastrado! A senha temporária é: ' . $password,
                            'new_password' => $password // Retorna a senha para o admin
                        ];
                    } else {
                        $response['message'] = 'Erro ao cadastrar usuário: ' . $stmt->error;
                    }
                } else {
                    // EDIÇÃO
                    $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ? WHERE id = ?");
                    $stmt->bind_param("sssi", $nome, $email, $telefone, $id);
                    
                    if ($stmt->execute()) {
                        $response = ['success' => true, 'message' => 'Usuário atualizado com sucesso!'];
                    } else {
                        $response['message'] = 'Erro ao atualizar usuário: ' . $stmt->error;
                    }
                }
                if (isset($stmt)) $stmt->close();
            }
            $stmt_check->close();
        }
    }
} else {
    $response['message'] = 'Método de requisição inválido.';
}

$conn->close();
echo json_encode($response);
?>