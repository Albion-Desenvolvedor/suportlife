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
    $product_id = $_POST['product_id'] ?? '';
    $type = $_POST['type'] ?? '';
    $quantity = $_POST['quantity'] ?? 0;
    $reason = $_POST['reason'] ?? '';
    
    if (empty($product_id) || empty($type) || empty($quantity)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
        exit;
    }
    
    if (!in_array($type, ['entrada', 'saida'])) {
        echo json_encode(['success' => false, 'message' => 'Tipo de movimentação inválido']);
        exit;
    }
    
    // Verificar se o produto existe
    $stmt = $pdo->prepare("SELECT current_stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
        exit;
    }
    
    // Verificar estoque para saídas
    if ($type === 'saida' && $product['current_stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Estoque insuficiente']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO movements (product_id, type, quantity, reason, user_id, reference_type) 
        VALUES (?, ?, ?, ?, ?, 'adjustment')
    ");
    
    $stmt->execute([$product_id, $type, $quantity, $reason, $_SESSION['user_id']]);
    
    echo json_encode(['success' => true, 'message' => 'Movimentação registrada com sucesso']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao registrar movimentação: ' . $e->getMessage()]);
}
?>