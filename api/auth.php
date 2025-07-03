<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php'; // db_connect.php já inicia a sessão

$response = ['success' => false, 'message' => ''];

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    $response['message'] = 'Por favor, preencha todos os campos.';
} else {
    $stmt = $conn->prepare("SELECT id, nome, email, senha, primeiro_acesso FROM usuarios WHERE email = ? AND status = 'ativo'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['senha'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nome'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['primeiro_acesso'] = $user['primeiro_acesso'];

            $response['success'] = true;
            $response['message'] = 'Login realizado com sucesso!';
        } else {
            $response['message'] = 'Email ou senha incorretos.';
        }
    } else {
        $response['message'] = 'Email ou senha incorretos, ou usuário inativo.';
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>