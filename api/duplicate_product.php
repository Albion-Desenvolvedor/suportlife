<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$product_id = $input['product_id'] ?? '';

if (empty($product_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do produto é obrigatório']);
    exit;
}

try {
    // Buscar produto original
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produto não encontrado']);
        exit;
    }
    
    // Criar cópia do produto
    $stmt = $pdo->prepare("
        INSERT INTO products (name, description, category_id, current_stock, min_stock, max_stock, 
                             location_id, condition_status, supplier_id, price, ca_certificate, 
                             validity_date, barcode, photo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    // Adicionar " - Cópia" ao nome
    $newName = $product['name'] . ' - Cópia';
    
    // Limpar código de barras para evitar duplicatas
    $newBarcode = '';
    
    // Zerar estoque atual
    $newCurrentStock = 0;
    
    $stmt->execute([
        $newName, 
        $product['description'], 
        $product['category_id'], 
        $newCurrentStock, 
        $product['min_stock'], 
        $product['max_stock'],
        $product['location_id'], 
        $product['condition_status'], 
        $product['supplier_id'], 
        $product['price'], 
        $product['ca_certificate'],
        $product['validity_date'], 
        $newBarcode, 
        $product['photo']
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Produto duplicado com sucesso']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao duplicar produto: ' . $e->getMessage()]);
}
?>