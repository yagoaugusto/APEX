<?php
// Este arquivo deve ser incluído APÓS db_connect.php, que já chama session_start()

if (!isset($_SESSION['user_id'])) {
    // Se o usuário não estiver logado, redireciona para a página de login
    header("Location: login.php");
    exit();
}
// Opcional: Você pode adicionar mais verificações aqui, como tempo de inatividade da sessão.
?>