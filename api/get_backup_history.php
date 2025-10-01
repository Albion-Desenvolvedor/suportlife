<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $backupPath = __DIR__ . '/../backups/';
    
    if (!is_dir($backupPath)) {
        echo json_encode(['success' => true, 'backups' => []]);
        exit;
    }
    
    $files = glob($backupPath . 'backup_*.sql');
    $backups = [];
    
    foreach ($files as $file) {
        $filename = basename($file);
        $size = formatBytes(filesize($file));
        $date = date('d/m/Y H:i', filemtime($file));
        
        $backups[] = [
            'filename' => $filename,
            'size' => $size,
            'date' => $date,
            'timestamp' => filemtime($file)
        ];
    }
    
    // Ordenar por data (mais recente primeiro)
    usort($backups, function($a, $b) {
        return $b['timestamp'] - $a['timestamp'];
    });
    
    echo json_encode(['success' => true, 'backups' => $backups]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao listar backups: ' . $e->getMessage()]);
}

function formatBytes($size, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    
    for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
        $size /= 1024;
    }
    
    return round($size, $precision) . ' ' . $units[$i];
}
?>