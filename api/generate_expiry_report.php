<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$days = $_GET['days'] ?? '30';

try {
    // Buscar produtos vencendo
    $stmt = $pdo->prepare("
        SELECT p.name as product_name,
               c.name as category_name,
               l.name as location_name,
               s.name as supplier_name,
               p.current_stock,
               p.validity_date,
               DATEDIFF(p.validity_date, CURDATE()) as days_to_expire,
               p.ca_certificate,
               p.barcode,
               (p.current_stock * p.price) as total_value
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        WHERE p.validity_date IS NOT NULL 
        AND p.validity_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
        AND p.active = 1
        ORDER BY p.validity_date ASC
    ");
    
    $stmt->execute([$days]);
    $expiryData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_produtos_vencendo_' . $days . '_dias_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalho do relatório
    fputcsv($output, ['RELATÓRIO DE PRODUTOS VENCENDO'], ';');
    fputcsv($output, ['Período: Próximos ' . $days . ' dias'], ';');
    fputcsv($output, ['Gerado em: ' . date('d/m/Y H:i:s')], ';');
    fputcsv($output, [''], ';'); // Linha em branco
    
    // Cabeçalhos das colunas
    fputcsv($output, [
        'Produto',
        'Categoria',
        'Localização',
        'Fornecedor',
        'Estoque Atual',
        'Data de Validade',
        'Dias para Vencer',
        'Status',
        'CA',
        'Código de Barras',
        'Valor Total'
    ], ';');
    
    // Dados
    $totalValue = 0;
    $expiredCount = 0;
    $expiringCount = 0;
    
    foreach ($expiryData as $item) {
        $totalValue += $item['total_value'];
        
        $status = '';
        if ($item['days_to_expire'] < 0) {
            $status = 'VENCIDO';
            $expiredCount++;
        } elseif ($item['days_to_expire'] <= 7) {
            $status = 'CRÍTICO';
            $expiringCount++;
        } elseif ($item['days_to_expire'] <= 30) {
            $status = 'ATENÇÃO';
            $expiringCount++;
        } else {
            $status = 'OK';
        }
        
        fputcsv($output, [
            $item['product_name'],
            $item['category_name'] ?? 'Sem categoria',
            $item['location_name'] ?? 'Sem localização',
            $item['supplier_name'] ?? 'Sem fornecedor',
            $item['current_stock'],
            date('d/m/Y', strtotime($item['validity_date'])),
            $item['days_to_expire'],
            $status,
            $item['ca_certificate'] ?? '',
            $item['barcode'] ?? '',
            'R$ ' . number_format($item['total_value'], 2, ',', '.')
        ], ';');
    }
    
    // Resumo
    fputcsv($output, [''], ';'); // Linha em branco
    fputcsv($output, ['RESUMO'], ';');
    fputcsv($output, ['Total de Produtos:', count($expiryData)], ';');
    fputcsv($output, ['Produtos Vencidos:', $expiredCount], ';');
    fputcsv($output, ['Produtos Vencendo:', $expiringCount], ';');
    fputcsv($output, ['Valor Total Afetado:', 'R$ ' . number_format($totalValue, 2, ',', '.')], ';');
    
    fclose($output);
    
} catch (Exception $e) {
    die('Erro ao gerar relatório: ' . $e->getMessage());
}
?>