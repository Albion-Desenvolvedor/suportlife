<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $alerts = [];
    
    // Verificar produtos com estoque baixo
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM products 
        WHERE current_stock <= min_stock AND active = 1
    ");
    $lowStock = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($lowStock > 0) {
        $alerts[] = [
            'type' => 'warning',
            'message' => "$lowStock produto(s) com estoque baixo"
        ];
    }
    
    // Verificar produtos vencendo
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM products 
        WHERE validity_date IS NOT NULL 
        AND validity_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        AND active = 1
    ");
    $expiring = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($expiring > 0) {
        $alerts[] = [
            'type' => 'error',
            'message' => "$expiring produto(s) vencendo em 7 dias"
        ];
    }
    
    // Verificar solicitações pendentes há mais de 24h
    $stmt = $pdo->query("
        SELECT COUNT(*) as count 
        FROM requests 
        WHERE status = 'pending' 
        AND created_at <= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $pendingOld = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if ($pendingOld > 0) {
        $alerts[] = [
            'type' => 'info',
            'message' => "$pendingOld solicitação(ões) pendente(s) há mais de 24h"
        ];
    }
    
    echo json_encode(['success' => true, 'alerts' => $alerts]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao verificar alertas: ' . $e->getMessage()]);
}
?>