<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

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
    $backupFile = createDatabaseBackup();
    
    if ($backupFile) {
        $filename = basename($backupFile);
        echo json_encode([
            'success' => true, 
            'message' => 'Backup gerado com sucesso',
            'backup_url' => 'api/download_backup.php?file=' . urlencode($filename),
            'filename' => $filename
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao gerar backup']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao gerar backup: ' . $e->getMessage()]);
}
?>