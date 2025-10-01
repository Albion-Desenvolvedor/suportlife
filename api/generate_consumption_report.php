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
    
    // Buscar dados de consumo
    $stmt = $pdo->query("
        SELECT p.name as product_name, c.name as category_name,
               SUM(CASE WHEN m.type = 'saida' THEN m.quantity ELSE 0 END) as consumed,
               SUM(CASE WHEN m.type = 'entrada' THEN m.quantity ELSE 0 END) as received,
               p.price,
               SUM(CASE WHEN m.type = 'saida' THEN m.quantity * p.price ELSE 0 END) as total_cost
        FROM movements m
        JOIN products p ON m.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1 $dateCondition
        GROUP BY p.id, p.name, c.name, p.price
        HAVING consumed > 0
        ORDER BY consumed DESC
    ");
    
    $consumptionData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_consumo_' . strtolower(str_replace(' ', '_', $periodName)) . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalho do relatório
    fputcsv($output, ['RELATÓRIO DE CONSUMO - ' . strtoupper($periodName)], ';');
    fputcsv($output, ['Gerado em: ' . date('d/m/Y H:i:s')], ';');
    fputcsv($output, [''], ';'); // Linha em branco
    
    // Cabeçalhos das colunas
    fputcsv($output, [
        'Produto',
        'Categoria',
        'Quantidade Consumida',
        'Quantidade Recebida',
        'Preço Unitário',
        'Custo Total'
    ], ';');
    
    // Dados
    $totalConsumed = 0;
    $totalCost = 0;
    
    foreach ($consumptionData as $item) {
        $totalConsumed += $item['consumed'];
        $totalCost += $item['total_cost'];
        
        fputcsv($output, [
            $item['product_name'],
            $item['category_name'] ?? 'Sem categoria',
            $item['consumed'],
            $item['received'],
            'R$ ' . number_format($item['price'], 2, ',', '.'),
            'R$ ' . number_format($item['total_cost'], 2, ',', '.')
        ], ';');
    }
    
    // Totais
    fputcsv($output, [''], ';'); // Linha em branco
    fputcsv($output, ['TOTAIS'], ';');
    fputcsv($output, ['Total de Itens Consumidos:', $totalConsumed], ';');
    fputcsv($output, ['Custo Total:', 'R$ ' . number_format($totalCost, 2, ',', '.')], ';');
    
    fclose($output);
    
} catch (Exception $e) {
    die('Erro ao gerar relatório: ' . $e->getMessage());
}
?>