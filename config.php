<?php
/**
 * Configurações Gerais do Sistema Support Life
 * Sistema de Gestão de Almoxarifado
 */

// Configurações de ambiente
define('ENVIRONMENT', 'development'); // development, production
define('DEBUG_MODE', ENVIRONMENT === 'development');

// Configurações de timezone
date_default_timezone_set('America/Sao_Paulo');

// Configurações de sessão
// ini_set('session.cookie_httponly', 1);
// ini_set('session.use_only_cookies', 1);
// ini_set('session.cookie_secure', 0); // Mude para 1 se usar HTTPS

// Configurações de erro
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/logs/error.log');
}

// Configurações de upload
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('UPLOAD_PATH', __DIR__ . '/uploads/');

// Configurações do sistema
define('SYSTEM_NAME', 'Support Life - Almoxarifado');
define('SYSTEM_VERSION', '1.0.0');
define('COMPANY_NAME', 'Support Life');

// Configurações de paginação
define('ITEMS_PER_PAGE', 20);

// Configurações de backup
define('BACKUP_PATH', __DIR__ . '/backups/');
define('BACKUP_RETENTION_DAYS', 30);

// Configurações de email (se necessário)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@supportlife.com');
define('FROM_NAME', 'Support Life Sistema');

// Funções auxiliares globais
function isProduction() {
    return ENVIRONMENT === 'production';
}

function isDevelopment() {
    return ENVIRONMENT === 'development';
}

function logError($message, $context = []) {
    if (!DEBUG_MODE) {
        $logMessage = date('Y-m-d H:i:s') . ' - ' . $message;
        if (!empty($context)) {
            $logMessage .= ' - Context: ' . json_encode($context);
        }
        error_log($logMessage . PHP_EOL, 3, __DIR__ . '/logs/error.log');
    }
}

// Criar diretórios necessários
$directories = [
    __DIR__ . '/uploads',
    __DIR__ . '/uploads/products',
    __DIR__ . '/uploads/system',
    __DIR__ . '/backups',
    __DIR__ . '/logs'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Autoload de classes (se necessário)
spl_autoload_register(function ($className) {
    $file = __DIR__ . '/classes/' . $className . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
?>