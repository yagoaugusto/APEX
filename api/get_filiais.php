<?php
require_once '../includes/db_connect.php';

$sql = "SELECT id, nome FROM filiais ORDER BY nome";
$result = $conn->query($sql);

$filiais = [];
while ($row = $result->fetch_assoc()) {
    $filiais[] = $row;
}

echo json_encode($filiais);