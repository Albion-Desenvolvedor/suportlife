<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$location_id = $_GET['id'] ?? '';

if (empty($location_id)) {
    echo json_encode(['success' => false, 'message' => 'ID da localização é obrigatório']);
    exit;
}

try {
    // Buscar dados da localização
    $stmt = $pdo->prepare("SELECT * FROM locations WHERE id = ?");
    $stmt->execute([$location_id]);
    $location = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$location) {
        echo json_encode(['success' => false, 'message' => 'Localização não encontrada']);
        exit;
    }
    
    // Buscar produtos da localização
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.location_id = ? AND p.active = 1
        ORDER BY p.name
    ");
    $stmt->execute([$location_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'location' => $location,
        'products' => $products
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar produtos: ' . $e->getMessage()]);
}
?>