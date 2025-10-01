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
    $quantity = $_POST['quantity'] ?? 0;
    $department_id = $_POST['department_id'] ?? '';
    $pickup_date = $_POST['pickup_date'] ?? '';
    $return_date = $_POST['return_date'] ?? null;
    $observations = $_POST['observations'] ?? '';
    
    if (empty($product_id) || empty($quantity) || empty($department_id)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos obrigatórios devem ser preenchidos']);
        exit;
    }
    
    // Verificar se há estoque suficiente
    $stmt = $pdo->prepare("SELECT current_stock FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
        exit;
    }
    
    if ($product['current_stock'] < $quantity) {
        echo json_encode(['success' => false, 'message' => 'Estoque insuficiente']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO requests (product_id, user_id, department_id, quantity, pickup_date, return_date, observations) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $product_id, $_SESSION['user_id'], $department_id, $quantity, 
        $pickup_date, $return_date ?: null, $observations
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Solicitação criada com sucesso']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao criar solicitação: ' . $e->getMessage()]);
}
?>