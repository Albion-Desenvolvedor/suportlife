<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Verificar se é admin
require_once '../config/database.php';
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$filename = $input['filename'] ?? '';

if (empty($filename)) {
    echo json_encode(['success' => false, 'message' => 'Nome do arquivo é obrigatório']);
    exit;
}

// Validar nome do arquivo
if (!preg_match('/^backup_\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_\d{2}\.sql$/', $filename)) {
    echo json_encode(['success' => false, 'message' => 'Nome de arquivo inválido']);
    exit;
}

try {
    $filepath = __DIR__ . '/../backups/' . $filename;
    
    if (!file_exists($filepath)) {
        echo json_encode(['success' => false, 'message' => 'Arquivo não encontrado']);
        exit;
    }
    
    if (unlink($filepath)) {
        echo json_encode(['success' => true, 'message' => 'Backup excluído com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao excluir arquivo']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir backup: ' . $e->getMessage()]);
}
?>