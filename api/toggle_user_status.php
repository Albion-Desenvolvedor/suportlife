<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Verificar se é admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? '';

if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário é obrigatório']);
    exit;
}

// Não permitir desativar o próprio usuário
if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Você não pode alterar seu próprio status']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET active = NOT active WHERE id = ?");
    $stmt->execute([$user_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Status do usuário alterado com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuário não encontrado']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao alterar status: ' . $e->getMessage()]);
}
?>