<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$supplier_id = $_GET['id'] ?? '';

if (empty($supplier_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do fornecedor é obrigatório']);
    exit;
}

try {
    // Buscar dados do fornecedor
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$supplier) {
        echo json_encode(['success' => false, 'message' => 'Fornecedor não encontrado']);
        exit;
    }
    
    // Buscar produtos do fornecedor
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, l.name as location_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE p.supplier_id = ? AND p.active = 1
        ORDER BY p.name
    ");
    $stmt->execute([$supplier_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'supplier' => $supplier,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar produtos: ' . $e->getMessage()]);
}
?>