<?php
session_start();
require_once 'includes/functions.php';

// Log da atividade antes de destruir a sessão
if (isset($_SESSION['user_id'])) {
    logActivity('logout', 'Usuário fez logout do sistema');
}

session_destroy();
header('Location: login.php');
exit;
?>