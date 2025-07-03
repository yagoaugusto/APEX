<?php
require_once 'includes/db_connect.php'; // ajuste o caminho se necessário

$email = 'yagoacp@gmail.com';
$novaSenha = '123';
$hash = password_hash($novaSenha, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE usuarios SET senha = ?, primeiro_acesso = 1 WHERE email = ?");
$stmt->bind_param("ss", $hash, $email);

if ($stmt->execute()) {
    echo "Senha redefinida com sucesso!";
} else {
    echo "Erro ao atualizar senha: " . $stmt->error;
}
?>