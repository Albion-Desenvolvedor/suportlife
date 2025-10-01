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

if (!isset($_FILES['backup_file'])) {
    echo json_encode(['success' => false, 'message' => 'Nenhum arquivo selecionado']);
    exit;
}

$file = $_FILES['backup_file'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo']);
    exit;
}

// Verificar extensão
$fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
if (!in_array($fileExtension, ['sql', 'zip'])) {
    echo json_encode(['success' => false, 'message' => 'Formato de arquivo não suportado']);
    exit;
}

try {
    $tempFile = $file['tmp_name'];
    
    if ($fileExtension === 'sql') {
        // Ler conteúdo do arquivo SQL
        $sql = file_get_contents($tempFile);
        
        if (empty($sql)) {
            throw new Exception('Arquivo de backup vazio');
        }
        
        // Executar SQL
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
                $pdo->exec($statement);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Backup restaurado com sucesso']);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Formato ZIP ainda não implementado']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao restaurar backup: ' . $e->getMessage()]);
}
?>