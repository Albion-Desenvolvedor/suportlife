<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $stats = getDashboardStats();
    echo json_encode(['success' => true, 'stats' => $stats]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar estatísticas: ' . $e->getMessage()]);
}
?>