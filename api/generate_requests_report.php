<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$status = $_GET['status'] ?? 'all';

try {
    // Construir condição de status
    $statusCondition = '';
    $statusName = 'Todas';
    
    if ($status !== 'all') {
        $statusCondition = "AND r.status = '" . $pdo->quote($status) . "'";
        $statusName = getStatusText($status);
    }
    
    // Buscar dados das solicitações
    $stmt = $pdo->query("
        SELECT r.id,
               r.created_at,
               p.name as product_name,
               u.name as user_name,
               d.name as department_name,
               r.quantity,
               r.pickup_date,
               r.return_date,
               r.status,
               au.name as approved_by_name,
               r.approved_at,
               du.name as delivered_by_name,
               r.delivered_at,
               r.returned_at,
               r.observations,
               (r.quantity * p.price) as estimated_cost
        FROM requests r
        LEFT JOIN products p ON r.product_id = p.id
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        LEFT JOIN users au ON r.approved_by = au.id
        LEFT JOIN users du ON r.delivered_by = du.id
        WHERE 1=1 $statusCondition
        ORDER BY r.created_at DESC
    ");
    
    $requestsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_solicitacoes_' . strtolower($statusName) . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Cabeçalho do relatório
    fputcsv($output, ['RELATÓRIO DE SOLICITAÇÕES'], ';');
    fputcsv($output, ['Status: ' . $statusName], ';');
    fputcsv($output, ['Gerado em: ' . date('d/m/Y H:i:s')], ';');
    fputcsv($output, [''], ';'); // Linha em branco
    
    // Cabeçalhos das colunas
    fputcsv($output, [
        'ID',
        'Data Solicitação',
        'Produto',
        'Solicitante',
        'Departamento',
        'Quantidade',
        'Data Retirada',
        'Data Devolução',
        'Status',
        'Aprovado por',
        'Data Aprovação',
        'Entregue por',
        'Data Entrega',
        'Data Devolução Real',
        'Custo Estimado',
        'Observações'
    ], ';');
    
    // Dados
    $totalRequests = count($requestsData);
    $totalCost = 0;
    $statusCount = [];
    
    foreach ($requestsData as $item) {
        $totalCost += $item['estimated_cost'];
        
        if (!isset($statusCount[$item['status']])) {
            $statusCount[$item['status']] = 0;
        }
        $statusCount[$item['status']]++;
        
        fputcsv($output, [
            str_pad($item['id'], 4, '0', STR_PAD_LEFT),
            date('d/m/Y H:i', strtotime($item['created_at'])),
            $item['product_name'],
            $item['user_name'],
            $item['department_name'],
            $item['quantity'],
            $item['pickup_date'] ? date('d/m/Y', strtotime($item['pickup_date'])) : '',
            $item['return_date'] ? date('d/m/Y', strtotime($item['return_date'])) : '',
            getStatusText($item['status']),
            $item['approved_by_name'] ?? '',
            $item['approved_at'] ? date('d/m/Y H:i', strtotime($item['approved_at'])) : '',
            $item['delivered_by_name'] ?? '',
            $item['delivered_at'] ? date('d/m/Y H:i', strtotime($item['delivered_at'])) : '',
            $item['returned_at'] ? date('d/m/Y H:i', strtotime($item['returned_at'])) : '',
            'R$ ' . number_format($item['estimated_cost'], 2, ',', '.'),
            $item['observations'] ?? ''
        ], ';');
    }
    
    // Resumo
    fputcsv($output, [''], ';'); // Linha em branco
    fputcsv($output, ['RESUMO'], ';');
    fputcsv($output, ['Total de Solicitações:', $totalRequests], ';');
    fputcsv($output, ['Custo Total Estimado:', 'R$ ' . number_format($totalCost, 2, ',', '.')], ';');
    fputcsv($output, [''], ';');
    
    // Breakdown por status
    fputcsv($output, ['BREAKDOWN POR STATUS'], ';');
    foreach ($statusCount as $status => $count) {
        fputcsv($output, [getStatusText($status) . ':', $count], ';');
    }
    
    fclose($output);
    
} catch (Exception $e) {
    die('Erro ao gerar relatório: ' . $e->getMessage());
}
?>