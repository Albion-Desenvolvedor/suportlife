<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$product_id = $_GET['id'] ?? '';

if (empty($product_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do produto é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, l.name as location_name, s.name as supplier_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        WHERE p.id = ?
    ");
    
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product) {
        echo json_encode(['success' => true, 'product' => $product]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar produto: ' . $e->getMessage()]);
}
?>