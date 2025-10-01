<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$department_id = $_GET['id'] ?? '';

if (empty($department_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do departamento é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT d.*, u.name as manager_name
        FROM departments d
        LEFT JOIN users u ON d.manager_id = u.id
        WHERE d.id = ?
    ");
    $stmt->execute([$department_id]);
    $department = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($department) {
        echo json_encode(['success' => true, 'department' => $department]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Departamento não encontrado']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar departamento: ' . $e->getMessage()]);
}
?>