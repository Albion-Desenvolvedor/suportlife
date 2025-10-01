<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$groupBy = $_GET['group_by'] ?? 'product';
$format = $_GET['format'] ?? 'csv';

try {
    // Construir query baseada no agrupamento
    $groupField = '';
    $groupName = '';
    
    switch ($groupBy) {
        case 'ca':
            $groupField = 'p.ca_certificate';
            $groupName = 'CA';
            break;
        case 'category':
            $groupField = 'c.name';
            $groupName = 'Categoria';
            break;
        default:
            $groupField = 'p.name';
            $groupName = 'Produto';
    }
    
    // Buscar dados de comparação
    $stmt = $pdo->query("
        SELECT 
            $groupField as group_name,
            p.name as product_name,
            p.ca_certificate,
            c.name as category_name,
            COUNT(DISTINCT s.id) as supplier_count,
            MIN(p.price) as min_price,
            MAX(p.price) as max_price,
            AVG(p.price) as avg_price,
            (MAX(p.price) - MIN(p.price)) as price_difference,
            ((MAX(p.price) - MIN(p.price)) / MAX(p.price)) * 100 as savings_percentage,
            GROUP_CONCAT(DISTINCT CONCAT(s.name, ':', p.price) ORDER BY p.price SEPARATOR '|') as supplier_prices
        FROM products p
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.active = 1 AND s.active = 1 AND p.supplier_id IS NOT NULL
        GROUP BY $groupField
        HAVING supplier_count > 1 AND price_difference > 0
        ORDER BY savings_percentage DESC
    ");
    
    $comparisonData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($format === 'csv') {
        // Headers para download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="comparativo_precos_' . $groupBy . '_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalho do relatório
        fputcsv($output, ['COMPARATIVO DE PREÇOS POR ' . strtoupper($groupName)], ';');
        fputcsv($output, ['Gerado em: ' . date('d/m/Y H:i:s')], ';');
        fputcsv($output, [''], ';');
        
        // Cabeçalhos das colunas
        fputcsv($output, [
            $groupName,
            'Produto',
            'CA',
            'Categoria',
            'Fornecedores',
            'Preço Mínimo',
            'Preço Máximo',
            'Preço Médio',
            'Diferença',
            'Economia %',
            'Detalhes dos Preços'
        ], ';');
        
        // Dados
        foreach ($comparisonData as $item) {
            fputcsv($output, [
                $item['group_name'] ?? 'N/A',
                $item['product_name'],
                $item['ca_certificate'] ?? '',
                $item['category_name'] ?? 'Sem categoria',
                $item['supplier_count'],
                'R$ ' . number_format($item['min_price'], 2, ',', '.'),
                'R$ ' . number_format($item['max_price'], 2, ',', '.'),
                'R$ ' . number_format($item['avg_price'], 2, ',', '.'),
                'R$ ' . number_format($item['price_difference'], 2, ',', '.'),
                number_format($item['savings_percentage'], 1) . '%',
                str_replace('|', ' | ', $item['supplier_prices'])
            ], ';');
        }
        
        fclose($output);
    } else {
        // Return JSON for PDF generation
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $comparisonData,
            'title' => 'Comparativo de Preços por ' . $groupName,
            'generated_at' => date('d/m/Y H:i:s')
        ]);
    }
    
} catch (Exception $e) {
    if ($format === 'csv') {
        die('Erro ao gerar relatório: ' . $e->getMessage());
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao gerar relatório: ' . $e->getMessage()]);
    }
}
?>