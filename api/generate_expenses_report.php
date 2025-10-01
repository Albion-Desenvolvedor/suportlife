<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$period = $_GET['period'] ?? 'month';

try {
    // Definir período
    $dateCondition = '';
    $periodName = '';
    
    switch ($period) {
        case 'month':
            $dateCondition = "AND MONTH(m.created_at) = MONTH(CURDATE()) AND YEAR(m.created_at) = YEAR(CURDATE())";
            $periodName = 'Este Mês';
            break;
        case 'quarter':
            $dateCondition = "AND QUARTER(m.created_at) = QUARTER(CURDATE()) AND YEAR(m.created_at) = YEAR(CURDATE())";
            $periodName = 'Este Trimestre';
            break;
        case 'year':
            $dateCondition = "AND YEAR(m.created_at) = YEAR(CURDATE())";
            $periodName = 'Este Ano';
            break;
        default:
            $dateCondition = "AND MONTH(m.created_at) = MONTH(CURDATE()) AND YEAR(m.created_at) = YEAR(CURDATE())";
            $periodName = 'Este Mês';
    }
    
    // Buscar dados de gastos
    $stmt = $pdo->query("
        SELECT DATE(m.created_at) as date,
               p.name as product_name,
               c.name as category_name,
               s.name as supplier_name,
               m.quantity,
               p.price,
               (m.quantity * p.price) as total_cost,
               m.reason
        FROM movements m
        JOIN products p ON m.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        WHERE m.type = 'saida' $dateCondition
        ORDER BY m.created_at DESC
    ");
    
    $expensesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_gastos_' . strtolower(str_replace(' ', '_', $periodName)) . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalho do relatório
    fputcsv($output, ['RELATÓRIO DE GASTOS - ' . strtoupper($periodName)], ';');
    fputcsv($output, ['Gerado em: ' . date('d/m/Y H:i:s')], ';');
    fputcsv($output, [''], ';'); // Linha em branco
    
    // Cabeçalhos das colunas
    fputcsv($output, [
        'Data',
        'Produto',
        'Categoria',
        'Fornecedor',
        'Quantidade',
        'Preço Unitário',
        'Custo Total',
        'Motivo'
    ], ';');
    
    // Dados
    $totalCost = 0;
    $totalQuantity = 0;
    
    foreach ($expensesData as $item) {
        $totalCost += $item['total_cost'];
        $totalQuantity += $item['quantity'];
        
        fputcsv($output, [
            date('d/m/Y', strtotime($item['date'])),
            $item['product_name'],
            $item['category_name'] ?? 'Sem categoria',
            $item['supplier_name'] ?? 'Sem fornecedor',
            $item['quantity'],
            'R$ ' . number_format($item['price'], 2, ',', '.'),
            'R$ ' . number_format($item['total_cost'], 2, ',', '.'),
            $item['reason'] ?? ''
        ], ';');
    }
    
    // Totais
    fputcsv($output, [''], ';'); // Linha em branco
    fputcsv($output, ['RESUMO'], ';');
    fputcsv($output, ['Total de Itens:', $totalQuantity], ';');
    fputcsv($output, ['Custo Total:', 'R$ ' . number_format($totalCost, 2, ',', '.')], ';');
    fputcsv($output, ['Custo Médio por Item:', 'R$ ' . number_format($totalQuantity > 0 ? $totalCost / $totalQuantity : 0, 2, ',', '.')], ';');
    
    fclose($output);
    
} catch (Exception $e) {
    die('Erro ao gerar relatório: ' . $e->getMessage());
}
?>