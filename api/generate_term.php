<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$request_id = $_GET['request_id'] ?? '';

if (empty($request_id)) {
    die('ID da solicitação é obrigatório');
}

try {
    // Buscar dados da solicitação
    $stmt = $pdo->prepare("
        SELECT r.*, p.name as product_name, u.name as user_name, u.email as user_email,
               d.name as department_name, du.name as delivered_by_name
        FROM requests r
        LEFT JOIN products p ON r.product_id = p.id
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        LEFT JOIN users du ON r.delivered_by = du.id
        WHERE r.id = ?
    ");
    $stmt->execute([$request_id]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request) {
        die('Solicitação não encontrada');
    }
    
    // Gerar número do termo se não existir
    $stmt = $pdo->prepare("SELECT term_number FROM responsibility_terms WHERE request_id = ?");
    $stmt->execute([$request_id]);
    $term = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$term) {
        $term_number = 'TR-' . date('Y') . '-' . str_pad($request_id, 6, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("
            INSERT INTO responsibility_terms (request_id, term_number, responsible_person, delivered_by, delivery_date)
            VALUES (?, ?, ?, ?, CURDATE())
        ");
        $stmt->execute([$request_id, $term_number, $request['user_name'], $request['delivered_by_name'] ?? 'Sistema']);
    } else {
        $term_number = $term['term_number'];
    }
    
} catch (Exception $e) {
    die('Erro ao gerar termo: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termo de Responsabilidade - <?= $term_number ?></title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        
        .document-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .term-number {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .content {
            margin: 30px 0;
        }
        
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .info-table th,
        .info-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .info-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        
        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 45%;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 10px;
        }
        
        .footer {
            margin-top: 50px;
            font-size: 12px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .print-button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="print-button">Imprimir Termo</button>
        <button onclick="window.close()" class="print-button">Fechar</button>
    </div>
    
    <div class="header">
        <div class="company-name">Support Life</div>
        <div class="document-title">TERMO DE RESPONSABILIDADE</div>
        <div class="term-number">Número: <?= htmlspecialchars($term_number) ?></div>
    </div>
    
    <div class="content">
        <p>Por meio deste termo, declaro ter recebido o(s) material(is) abaixo relacionado(s), comprometendo-me a utilizá-lo(s) adequadamente e devolvê-lo(s) nas condições em que foi(ram) entregue(s).</p>
        
        <table class="info-table">
            <tr>
                <th>Produto</th>
                <td><?= htmlspecialchars($request['product_name']) ?></td>
            </tr>
            <tr>
                <th>Quantidade</th>
                <td><?= $request['quantity'] ?> unidade(s)</td>
            </tr>
            <tr>
                <th>Data de Retirada</th>
                <td><?= formatDate($request['pickup_date']) ?></td>
            </tr>
            <?php if ($request['return_date']): ?>
            <tr>
                <th>Data Prevista para Devolução</th>
                <td><?= formatDate($request['return_date']) ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Departamento</th>
                <td><?= htmlspecialchars($request['department_name']) ?></td>
            </tr>
            <?php if ($request['observations']): ?>
            <tr>
                <th>Observações</th>
                <td><?= htmlspecialchars($request['observations']) ?></td>
            </tr>
            <?php endif; ?>
        </table>
        
        <p><strong>RESPONSABILIDADES:</strong></p>
        <ul>
            <li>Utilizar o material exclusivamente para as atividades profissionais;</li>
            <li>Manter o material em boas condições de uso e conservação;</li>
            <li>Comunicar imediatamente qualquer dano, perda ou furto;</li>
            <li>Devolver o material na data prevista ou quando solicitado;</li>
            <li>Arcar com os custos de reposição em caso de dano ou perda por negligência.</li>
        </ul>
    </div>
    
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <strong><?= htmlspecialchars($request['user_name']) ?></strong><br>
                Responsável pelo Material<br>
                Data: <?= date('d/m/Y') ?>
            </div>
        </div>
        
        <div class="signature-box">
            <div class="signature-line">
                <strong><?= htmlspecialchars($request['delivered_by_name'] ?? 'Almoxarifado') ?></strong><br>
                Entregue por<br>
                Data: <?= date('d/m/Y') ?>
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>Este documento foi gerado automaticamente pelo Sistema de Gestão de Almoxarifado - Support Life</p>
        <p>Data de emissão: <?= date('d/m/Y H:i:s') ?></p>
    </div>
    
    <script>
        // Auto-imprimir quando a página carregar
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>