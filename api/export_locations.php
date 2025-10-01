<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

try {
    $locations = getAllLocations();
    
    // Definir headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="localizacoes_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Criar output
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalhos
    fputcsv($output, [
        'ID',
        'Nome',
        'Descrição',
        'Produtos',
        'Status',
        'Criado em'
    ], ';');
    
    // Dados
    foreach ($locations as $location) {
        $productCount = getProductCountByLocation($location['id']);
        
        fputcsv($output, [
            $location['id'],
            $location['name'],
            $location['description'] ?? '',
            $productCount,
            $location['active'] ? 'Ativa' : 'Inativa',
            date('d/m/Y H:i', strtotime($location['created_at']))
        ], ';');
    }
    
    fclose($output);
    
} catch (Exception $e) {
    die('Erro ao exportar localizações: ' . $e->getMessage());
}
?>