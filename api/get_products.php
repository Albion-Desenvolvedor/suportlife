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
    $data = [];
    
    // Produtos com estoque disponível
    $stmt = $pdo->query("
        SELECT id, name, current_stock, barcode 
        FROM products 
        WHERE active = 1 AND current_stock > 0 
        ORDER BY name
    ");
    $data['products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($data);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>