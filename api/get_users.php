<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT id, name, email, role, active FROM users WHERE active = 1 ORDER BY name");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['users' => $users]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>