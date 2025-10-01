<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

try {
    $suppliers = getAllSuppliers();
    
    // Definir headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="fornecedores_' . date('Y-m-d_H-i-s') . '.csv"');
    
    // Criar output
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalhos
    fputcsv($output, [
        'ID',
        'Nome',
        'Pessoa de Contato',
        'Email',
        'Telefone',
        'CNPJ',
        'Endereço',
        'Status',
        'Criado em'
    ], ';');
    
    // Dados
    foreach ($suppliers as $supplier) {
        fputcsv($output, [
            $supplier['id'],
            $supplier['name'],
            $supplier['contact_person'] ?? '',
            $supplier['email'] ?? '',
            $supplier['phone'] ?? '',
            $supplier['cnpj'] ?? '',
            $supplier['address'] ?? '',
            $supplier['active'] ? 'Ativo' : 'Inativo',
            date('d/m/Y H:i', strtotime($supplier['created_at']))
        ], ';');
    }
    
    fclose($output);
    
} catch (Exception $e) {
    die('Erro ao exportar fornecedores: ' . $e->getMessage());
}
?>