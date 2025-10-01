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

// Não permitir excluir o próprio usuário
if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Você não pode excluir sua própria conta']);
    exit;
}

try {
    // Verificar se o usuário tem atividades
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM movements WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $movements = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM requests WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $requests = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($movements['count'] > 0 || $requests['count'] > 0) {
        // Se tem histórico, apenas desativar
        $stmt = $pdo->prepare("UPDATE users SET active = 0 WHERE id = ?");
        $stmt->execute([$user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Usuário desativado com sucesso (possui histórico)']);
    } else {
        // Se não tem histórico, pode excluir
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Usuário excluído com sucesso']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir usuário: ' . $e->getMessage()]);
}
?>