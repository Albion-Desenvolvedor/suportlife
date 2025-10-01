<?php
session_start();
require_once '../config/database.php';
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $checks = [];
    
    // Verificar conexão com banco
    try {
        $pdo->query("SELECT 1");
        $checks[] = ['name' => 'Conexão com Banco', 'status' => true, 'message' => 'OK'];
    } catch (Exception $e) {
        $checks[] = ['name' => 'Conexão com Banco', 'status' => false, 'message' => 'Erro: ' . $e->getMessage()];
    }
    
    // Verificar permissões de diretórios
    $directories = ['uploads', 'uploads/products', 'uploads/system', 'backups'];
    foreach ($directories as $dir) {
        if (is_writable($dir)) {
            $checks[] = ['name' => "Permissão $dir", 'status' => true, 'message' => 'Gravável'];
        } else {
            $checks[] = ['name' => "Permissão $dir", 'status' => false, 'message' => 'Sem permissão de escrita'];
        }
    }
    
    // Verificar extensões PHP
    $extensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring'];
    foreach ($extensions as $ext) {
        if (extension_loaded($ext)) {
            $checks[] = ['name' => "Extensão $ext", 'status' => true, 'message' => 'Carregada'];
        } else {
            $checks[] = ['name' => "Extensão $ext", 'status' => false, 'message' => 'Não encontrada'];
        }
    }
    
    // Verificar espaço em disco
    $freeBytes = disk_free_space('.');
    $totalBytes = disk_total_space('.');
    $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;
    
    if ($usedPercent < 90) {
        $checks[] = ['name' => 'Espaço em Disco', 'status' => true, 'message' => sprintf('%.1f%% usado', $usedPercent)];
    } else {
        $checks[] = ['name' => 'Espaço em Disco', 'status' => false, 'message' => sprintf('%.1f%% usado - Crítico', $usedPercent)];
    }
    
    // Verificar versão do PHP
    if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
        $checks[] = ['name' => 'Versão PHP', 'status' => true, 'message' => PHP_VERSION];
    } else {
        $checks[] = ['name' => 'Versão PHP', 'status' => false, 'message' => PHP_VERSION . ' - Atualização necessária'];
    }
    
    echo json_encode(['success' => true, 'checks' => $checks]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao verificar sistema: ' . $e->getMessage()]);
}
?>