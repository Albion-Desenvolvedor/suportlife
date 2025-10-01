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
$supplier_id = $input['supplier_id'] ?? '';

if (empty($supplier_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do fornecedor é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE suppliers SET active = NOT active WHERE id = ?");
    $stmt->execute([$supplier_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Status alterado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fornecedor não encontrado']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao alterar status: ' . $e->getMessage()]);
}
?>