<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$type = $_GET['type'] ?? 'all';

try {
    // Construir condição de tipo
    $typeCondition = '';
    $typeName = 'Todas';
    
    if ($type !== 'all') {
        $typeCondition = "AND m.type = '" . $pdo->quote($type) . "'";
        $typeName = ucfirst($type);
    }
    
    // Buscar dados das movimentações
    $stmt = $pdo->query("
        SELECT m.id,
               m.created_at,
               p.name as product_name,
               p.barcode,
               c.name as category_name,
               m.type,
               m.quantity,
               m.reason,
               u.name as user_name,
               m.reference_type,
               m.reference_id,
               (m.quantity * p.price) as estimated_value
        FROM movements m
        LEFT JOIN products p ON m.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON m.user_id = u.id
        WHERE 1=1 $typeCondition
        ORDER BY m.created_at DESC
    ");
    
    $movementsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_movimentacoes_' . strtolower($typeName) . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalho do relatório
    fputcsv($output, ['RELATÓRIO DE MOVIMENTAÇÕES - ' . strtoupper($typeName)], ';');
    fputcsv($output, ['Gerado em: ' . date('d/m/Y H:i:s')], ';');
    fputcsv($output, [''], ';'); // Linha em branco
    
    // Cabeçalhos das colunas
    fputcsv($output, [
        'ID',
        'Data/Hora',
        'Produto',
        'Categoria',
        'Código de Barras',
        'Tipo',
        'Quantidade',
        'Motivo',
        'Usuário',
        'Referência',
        'Valor Estimado'
    ], ';');
    
    // Dados
    $totalQuantity = 0;
    $totalValue = 0;
    $entradas = 0;
    $saidas = 0;
    
    foreach ($movementsData as $item) {
        $totalQuantity += $item['quantity'];
        $totalValue += $item['estimated_value'];
        
        if ($item['type'] === 'entrada') {
            $entradas += $item['quantity'];
        } else {
            $saidas += $item['quantity'];
        }
        
        $reference = '';
        if ($item['reference_type'] && $item['reference_id']) {
            $reference = ucfirst($item['reference_type']) . ' #' . $item['reference_id'];
        }
        
        fputcsv($output, [
            $item['id'],
            date('d/m/Y H:i', strtotime($item['created_at'])),
            $item['product_name'],
            $item['category_name'] ?? 'Sem categoria',
            $item['barcode'] ?? '',
            ucfirst($item['type']),
            $item['quantity'],
            $item['reason'] ?? '',
            $item['user_name'],
            $reference,
            'R$ ' . number_format($item['estimated_value'], 2, ',', '.')
        ], ';');
    }
    
    // Resumo
    fputcsv($output, [''], ';'); // Linha em branco
    fputcsv($output, ['RESUMO'], ';');
    fputcsv($output, ['Total de Movimentações:', count($movementsData)], ';');
    fputcsv($output, ['Total de Entradas:', $entradas], ';');
    fputcsv($output, ['Total de Saídas:', $saidas], ';');
    fputcsv($output, ['Saldo:', $entradas - $saidas], ';');
    fputcsv($output, ['Valor Total Movimentado:', 'R$ ' . number_format($totalValue, 2, ',', '.')], ';');
    
    fclose($output);
    
} catch (Exception $e) {
    die('Erro ao gerar relatório: ' . $e->getMessage());
}
?>