<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

try {
    $products = getAllProducts();
    
    // Definir headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="produtos_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Criar output
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalhos
    fputcsv($output, [
        'ID',
        'Nome',
        'Descrição',
        'Categoria',
        'Estoque Atual',
        'Estoque Mínimo',
        'Estoque Máximo',
        'Localização',
        'Estado',
        'Fornecedor',
        'Preço',
        'CA',
        'Validade',
        'Código de Barras',
        'Criado em'
    ], ';');
    
    // Dados
    foreach ($products as $product) {
        fputcsv($output, [
            $product['id'],
            $product['name'],
            $product['description'],
            $product['category_name'] ?? '',
            $product['current_stock'],
            $product['min_stock'],
            $product['max_stock'],
            $product['location_name'] ?? '',
            $product['condition_status'],
            $product['supplier_name'] ?? '',
            number_format($product['price'], 2, ',', '.'),
            $product['ca_certificate'] ?? '',
            $product['validity_date'] ? date('d/m/Y', strtotime($product['validity_date'])) : '',
            $product['barcode'] ?? '',
            date('d/m/Y H:i', strtotime($product['created_at']))
        ], ';');
    }
    
    fclose($output);
    
} catch (Exception $e) {
    die('Erro ao exportar produtos: ' . $e->getMessage());
}
?>