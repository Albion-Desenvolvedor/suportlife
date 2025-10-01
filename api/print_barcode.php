<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$product_id = $_GET['id'] ?? '';

if (empty($product_id)) {
    die('ID do produto é obrigatório');
}

try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        die('Produto não encontrado');
    }
    
} catch (Exception $e) {
    die('Erro ao buscar produto: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código de Barras - <?= htmlspecialchars($product['name']) ?></title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        
        .barcode-container {
            border: 2px solid #000;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            max-width: 400px;
        }
        
        .product-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .barcode {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ccc;
            background: #f9f9f9;
        }
        
        .product-info {
            font-size: 12px;
            margin-top: 10px;
        }
        
        .print-button {
            background: #007cba;
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
        <h2>Código de Barras</h2>
        <button onclick="window.print()" class="print-button">Imprimir</button>
        <button onclick="window.close()" class="print-button">Fechar</button>
    </div>
    
    <div class="barcode-container">
        <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
        
        <?php if ($product['barcode']): ?>
            <div class="barcode"><?= htmlspecialchars($product['barcode']) ?></div>
        <?php else: ?>
            <div class="barcode">SEM CÓDIGO</div>
        <?php endif; ?>
        
        <div class="product-info">
            <div>ID: <?= $product['id'] ?></div>
            <div>Preço: R$ <?= number_format($product['price'], 2, ',', '.') ?></div>
            <?php if ($product['ca_certificate']): ?>
                <div>CA: <?= htmlspecialchars($product['ca_certificate']) ?></div>
            <?php endif; ?>
        </div>
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