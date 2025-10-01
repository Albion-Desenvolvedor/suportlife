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
$request_id = $input['request_id'] ?? '';

if (empty($request_id)) {
    echo json_encode(['success' => false, 'message' => 'ID da solicitação é obrigatório']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    // Buscar dados da solicitação
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ? AND status = 'approved'");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        throw new Exception('Solicitação não encontrada ou não aprovada');
    }
    
    // Verificar estoque
    $stmt = $pdo->prepare("SELECT current_stock FROM products WHERE id = ?");
    $stmt->execute([$request['product_id']]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($product['current_stock'] < $request['quantity']) {
        throw new Exception('Estoque insuficiente');
    }
    
    // Atualizar status da solicitação
    $stmt = $pdo->prepare("
        UPDATE requests 
        SET status = 'delivered', delivered_by = ?, delivered_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $request_id]);
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Material entregue com sucesso']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erro ao entregar material: ' . $e->getMessage()]);
}
?>