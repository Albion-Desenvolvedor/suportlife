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
    $stmt = $pdo->prepare("
        UPDATE requests 
        SET status = 'approved', approved_by = ?, approved_at = NOW() 
        WHERE id = ? AND status = 'pending'
    ");
    
    $stmt->execute([$_SESSION['user_id'], $request_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Solicitação aprovada com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada ou já processada']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao aprovar solicitação: ' . $e->getMessage()]);
}
?>