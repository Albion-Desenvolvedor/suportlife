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
    // Verificar se o departamento tem solicitações
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM requests WHERE department_id = ?");
    $stmt->execute([$department_id]);
    $requests = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($requests['count'] > 0) {
        // Se tem solicitações, apenas desativar
        $stmt = $pdo->prepare("UPDATE departments SET active = 0 WHERE id = ?");
        $stmt->execute([$department_id]);
        
        echo json_encode(['success' => true, 'message' => 'Departamento desativado com sucesso']);
    } else {
        // Se não tem solicitações, pode excluir
        $stmt = $pdo->prepare("DELETE FROM departments WHERE id = ?");
        $stmt->execute([$department_id]);
        
        echo json_encode(['success' => true, 'message' => 'Departamento excluído com sucesso']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir departamento: ' . $e->getMessage()]);
}
?>