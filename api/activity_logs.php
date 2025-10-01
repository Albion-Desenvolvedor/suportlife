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

try {
    $limit = $_GET['limit'] ?? 100;
    
    // Verificar se a tabela activity_logs existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'activity_logs'");
    if (!$stmt->fetch()) {
        // Criar tabela se não existir
        $pdo->exec("
            CREATE TABLE activity_logs (
                id INT PRIMARY KEY AUTO_INCREMENT,
                user_id INT NOT NULL,
                action VARCHAR(100) NOT NULL,
                details TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id),
                INDEX idx_user_date (user_id, created_at),
                INDEX idx_action_date (action, created_at)
            )
        ");
    }
    
    $stmt = $pdo->prepare("
        SELECT al.*, u.name as user_name
        FROM activity_logs al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([intval($limit)]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'logs' => $logs]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar logs: ' . $e->getMessage()]);
}
?>