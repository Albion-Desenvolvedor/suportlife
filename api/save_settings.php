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
    $settings = [
        'company_name' => $_POST['company_name'] ?? '',
        'primary_color' => $_POST['primary_color'] ?? '#3b82f6',
        'alert_days_before_expiry' => $_POST['alert_days_before_expiry'] ?? '30',
        'term_validity_days' => $_POST['term_validity_days'] ?? '30',
        'backup_frequency' => $_POST['backup_frequency'] ?? 'daily'
    ];
    
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("
            INSERT INTO system_settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$key, $value]);
    }
    
    // Upload do logo se fornecido
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/system/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
        $fileName = 'logo.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $uploadPath)) {
            $stmt = $pdo->prepare("
                INSERT INTO system_settings (setting_key, setting_value) 
                VALUES ('company_logo', ?) 
                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
            ");
            $stmt->execute([$fileName]);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Configurações salvas com sucesso']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar configurações: ' . $e->getMessage()]);
}
?>