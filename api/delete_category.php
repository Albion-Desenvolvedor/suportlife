<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$category_id = $input['category_id'] ?? '';

if (empty($category_id)) {
    echo json_encode(['success' => false, 'message' => 'ID da categoria é obrigatório']);
    exit;
}

try {
    // Verificar se a categoria tem produtos
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $products = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($products['count'] > 0) {
        // Se tem produtos, apenas desativar
        $stmt = $pdo->prepare("UPDATE categories SET active = 0 WHERE id = ?");
        $stmt->execute([$category_id]);
        
        echo json_encode(['success' => true, 'message' => 'Categoria desativada com sucesso']);
    } else {
        // Se não tem produtos, pode excluir
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        
        echo json_encode(['success' => true, 'message' => 'Categoria excluída com sucesso']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir categoria: ' . $e->getMessage()]);
}
?>