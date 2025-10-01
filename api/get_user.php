<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$user_id = $_GET['id'] ?? '';

if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, name, email, role, active FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar usuário: ' . $e->getMessage()]);
}
?>