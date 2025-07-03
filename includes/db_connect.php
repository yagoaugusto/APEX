<?php
session_start(); // Inicia a sessão PHP

$servername = "localhost";
$username = "root"; // Usuário padrão do XAMPP
$password = ""; // Senha padrão do XAMPP é vazia
$dbname = "apex"; // O nome do banco de dados que você criou

// Criar a conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checar a conexão
if ($conn->connect_error) {
  die("Falha na conexão: " . $conn->connect_error);
}