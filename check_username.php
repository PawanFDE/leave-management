<?php
require_once 'config.php';

header('Content-Type: application/json');

if (isset($_POST['username'])) {
    $username = trim($_POST['username']);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $count = $stmt->fetchColumn();
    
    echo json_encode(['exists' => $count > 0]);
}
?>
