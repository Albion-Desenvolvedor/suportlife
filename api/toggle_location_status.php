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
$location_id = $input['location_id'] ?? '';

if (empty($location_id)) {
    echo json_encode(['success' => false, 'message' => 'ID da localização é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE locations SET active = NOT active WHERE id = ?");
    $stmt->execute([$location_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Status alterado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Localização não encontrada']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao alterar status: ' . $e->getMessage()]);
}
?>