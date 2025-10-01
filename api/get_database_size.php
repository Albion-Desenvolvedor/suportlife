<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
    ");
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $size = $result['size_mb'] ?? 0;
    
    echo json_encode(['success' => true, 'size' => $size . ' MB']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao calcular tamanho: ' . $e->getMessage()]);
}
?>