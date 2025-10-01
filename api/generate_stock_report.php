<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$category_id = $_GET['category'] ?? '';

try {
    // Construir query baseada na categoria
    $categoryCondition = '';
    $categoryName = 'Todas as Categorias';
    
    if (!empty($category_id)) {
        $categoryCondition = "AND p.category_id = " . intval($category_id);
        
        // Buscar nome da categoria
        $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
        $stmt->execute([$category_id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        $categoryName = $category ? $category['name'] : 'Categoria Desconhecida';
    }
    
    // Buscar dados do estoque
    $stmt = $pdo->query("
        SELECT p.name as product_name,
               c.name as category_name,
               l.name as location_name,
               s.name as supplier_name,
               p.current_stock,
               p.min_stock,
               p.max_stock,
               p.price,
               (p.current_stock * p.price) as total_value,
               p.condition_status,
               p.ca_certificate,
               p.validity_date,
               p.barcode,
               CASE 
                   WHEN p.current_stock <= p.min_stock THEN 'Baixo'
                   WHEN p.current_stock >= p.max_stock THEN 'Alto'
                   ELSE 'Normal'
               END as stock_status
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        WHERE p.active = 1 $categoryCondition
        ORDER BY p.name
    ");
    
    $stockData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_estoque_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalho do relatório
    fputcsv($output, ['RELATÓRIO DE ESTOQUE ATUAL'], ';');
    fputcsv($output, ['Categoria: ' . $categoryName], ';');
    fputcsv($output, ['Gerado em: ' . date('d/m/Y H:i:s')], ';');
    fputcsv($output, [''], ';'); // Linha em branco
    
    // Cabeçalhos das colunas
    fputcsv($output, [
        'Produto',
        'Categoria',
        'Localização',
        'Fornecedor',
        'Estoque Atual',
        'Estoque Mínimo',
        'Estoque Máximo',
        'Status do Estoque',
        'Preço Unitário',
        'Valor Total',
        'Estado',
        'CA',
        'Validade',
        'Código de Barras'
    ], ';');
    
    // Dados
    $totalItems = 0;
    $totalValue = 0;
    $lowStockCount = 0;
    $highStockCount = 0;
    
    foreach ($stockData as $item) {
        $totalItems += $item['current_stock'];
        $totalValue += $item['total_value'];
        
        if ($item['stock_status'] === 'Baixo') $lowStockCount++;
        if ($item['stock_status'] === 'Alto') $highStockCount++;
        
        fputcsv($output, [
            $item['product_name'],
            $item['category_name'] ?? 'Sem categoria',
            $item['location_name'] ?? 'Sem localização',
            $item['supplier_name'] ?? 'Sem fornecedor',
            $item['current_stock'],
            $item['min_stock'],
            $item['max_stock'],
            $item['stock_status'],
            'R$ ' . number_format($item['price'], 2, ',', '.'),
            'R$ ' . number_format($item['total_value'], 2, ',', '.'),
            $item['condition_status'],
            $item['ca_certificate'] ?? '',
            $item['validity_date'] ? date('d/m/Y', strtotime($item['validity_date'])) : '',
            $item['barcode'] ?? ''
        ], ';');
    }
    
    // Resumo
    fputcsv($output, [''], ';'); // Linha em branco
    fputcsv($output, ['RESUMO DO ESTOQUE'], ';');
    fputcsv($output, ['Total de Produtos:', count($stockData)], ';');
    fputcsv($output, ['Total de Itens em Estoque:', $totalItems], ';');
    fputcsv($output, ['Valor Total do Estoque:', 'R$ ' . number_format($totalValue, 2, ',', '.')], ';');
    fputcsv($output, ['Produtos com Estoque Baixo:', $lowStockCount], ';');
    fputcsv($output, ['Produtos com Estoque Alto:', $highStockCount], ';');
    
    fclose($output);
    
} catch (Exception $e) {
    die('Erro ao gerar relatório: ' . $e->getMessage());
}
?>