<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

try {
    $movements = getAllMovements();
    
    // Definir headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="movimentacoes_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Criar output
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalhos
    fputcsv($output, [
        'ID',
        'Data/Hora',
        'Produto',
        'Código de Barras',
        'Tipo',
        'Quantidade',
        'Motivo',
        'Usuário',
        'Referência'
    ], ';');
    
    // Dados
    foreach ($movements as $movement) {
        $reference = '';
        if ($movement['reference_type'] && $movement['reference_id']) {
            $reference = ucfirst($movement['reference_type']) . ' #' . $movement['reference_id'];
        }
        
        fputcsv($output, [
            $movement['id'],
            date('d/m/Y H:i', strtotime($movement['created_at'])),
            $movement['product_name'],
            $movement['barcode'] ?? '',
            ucfirst($movement['type']),
            $movement['quantity'],
            $movement['reason'] ?? '',
            $movement['user_name'],
            $reference
        ], ';');
    }
    
    fclose($output);
    
} catch (Exception $e) {
    die('Erro ao exportar movimentações: ' . $e->getMessage());
}
?>