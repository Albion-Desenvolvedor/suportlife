<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$request_id = $_GET['id'] ?? '';

if (empty($request_id)) {
    echo json_encode(['success' => false, 'message' => 'ID da solicitação é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT r.*, p.name as product_name, p.photo as product_photo, p.barcode,
               u.name as user_name, u.email as user_email,
               d.name as department_name,
               au.name as approved_by_name,
               du.name as delivered_by_name
        FROM requests r
        LEFT JOIN products p ON r.product_id = p.id
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        LEFT JOIN users au ON r.approved_by = au.id
        LEFT JOIN users du ON r.delivered_by = du.id
        WHERE r.id = ?
    ");
    
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($request) {
        echo json_encode(['success' => true, 'request' => $request]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Solicitação não encontrada']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar solicitação: ' . $e->getMessage()]);
}
?>