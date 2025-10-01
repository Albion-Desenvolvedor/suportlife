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
    // Limpar cache de sessões antigas
    session_gc();
    
    // Limpar arquivos temporários
    $tempFiles = glob(sys_get_temp_dir() . '/support_life_*');
    foreach ($tempFiles as $file) {
        if (is_file($file) && filemtime($file) < time() - 3600) { // 1 hora
            unlink($file);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Cache limpo com sucesso']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao limpar cache: ' . $e->getMessage()]);
}
?>