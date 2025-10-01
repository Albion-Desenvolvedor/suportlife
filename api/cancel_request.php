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
    // Verificar se a solicitação pode ser cancelada
    $stmt = $pdo->prepare("SELECT status, user_id FROM requests WHERE id = ?");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
        exit;
    }
    
    // Verificar permissões
    if ($request['user_id'] != $_SESSION['user_id'] && !hasPermission('manager')) {
        echo json_encode(['success' => false, 'message' => 'Sem permissão para cancelar esta solicitação']);
        exit;
    }
    
    if (!in_array($request['status'], ['pending', 'approved'])) {
        echo json_encode(['success' => false, 'message' => 'Solicitação não pode ser cancelada neste status']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        UPDATE requests 
        SET status = 'cancelled', updated_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$request_id]);
    
    echo json_encode(['success' => true, 'message' => 'Solicitação cancelada com sucesso']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao cancelar solicitação: ' . $e->getMessage()]);
}
?>