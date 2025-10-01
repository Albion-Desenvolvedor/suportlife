<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$category_id = $_GET['id'] ?? '';

if (empty($category_id)) {
    echo json_encode(['success' => false, 'message' => 'ID da categoria é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($category) {
        echo json_encode(['success' => true, 'category' => $category]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Categoria não encontrada']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar categoria: ' . $e->getMessage()]);
}
?>