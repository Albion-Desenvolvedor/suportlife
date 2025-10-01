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
    $stmt = $pdo->prepare("SELECT * FROM requests WHERE id = ? AND status = 'delivered'");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        throw new Exception('Solicitação não encontrada ou não entregue');
    }
    
    // Atualizar status da solicitação
    $stmt = $pdo->prepare("
        UPDATE requests 
        SET status = 'returned', returned_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$request_id]);
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Material devolvido com sucesso']);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erro ao devolver material: ' . $e->getMessage()]);
}
?>