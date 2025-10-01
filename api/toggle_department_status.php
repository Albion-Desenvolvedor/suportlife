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
$department_id = $input['department_id'] ?? '';

if (empty($department_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do departamento é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE departments SET active = NOT active WHERE id = ?");
    $stmt->execute([$department_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Status alterado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Departamento não encontrado']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao alterar status: ' . $e->getMessage()]);
}
?>