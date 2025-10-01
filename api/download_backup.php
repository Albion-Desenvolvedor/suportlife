<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$filename = $_GET['file'] ?? '';

if (empty($filename)) {
    die('Nome do arquivo é obrigatório');
}

// Validar nome do arquivo
if (!preg_match('/^backup_\d{4}_\d{2}_\d{2}_\d{2}_\d{2}_\d{2}\.sql$/', $filename)) {
    die('Nome de arquivo inválido');
}

$filepath = BACKUP_PATH . $filename;

if (!file_exists($filepath)) {
    die('Arquivo não encontrado');
}

// Headers para download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));

// Enviar arquivo
readfile($filepath);
exit;
?>