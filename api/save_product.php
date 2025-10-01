<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    $product_id = $_POST['product_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $current_stock = $_POST['current_stock'] ?? 0;
    $min_stock = $_POST['min_stock'] ?? 0;
    $max_stock = $_POST['max_stock'] ?? 0;
    $location_id = $_POST['location_id'] ?? null;
    $condition_status = $_POST['condition_status'] ?? 'Novo';
    $supplier_id = $_POST['supplier_id'] ?? null;
    $price = $_POST['price'] ?? 0;
    $ca_certificate = $_POST['ca_certificate'] ?? '';
    $validity_date = $_POST['validity_date'] ?? null;
    $barcode = $_POST['barcode'] ?? '';
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Nome do produto é obrigatório']);
        exit;
    }
    
    // Upload da foto se fornecida
    $photo = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
            $photo = $fileName;
        }
    }
    
    if ($product_id) {
        // Atualizar produto existente
        if ($photo) {
            // Se há nova foto, incluir no update
            $stmt = $pdo->prepare("
                UPDATE products SET 
                    name = ?, description = ?, category_id = ?, current_stock = ?, min_stock = ?, max_stock = ?, 
                    location_id = ?, condition_status = ?, supplier_id = ?, price = ?, ca_certificate = ?, 
                    validity_date = ?, barcode = ?, photo = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $name, $description, $category_id, $current_stock, $min_stock, $max_stock,
                $location_id, $condition_status, $supplier_id, $price, $ca_certificate,
                $validity_date ?: null, $barcode, $photo, $product_id
            ]);
        } else {
            // Sem nova foto, não alterar o campo photo
            $stmt = $pdo->prepare("
                UPDATE products SET 
                    name = ?, description = ?, category_id = ?, current_stock = ?, min_stock = ?, max_stock = ?, 
                    location_id = ?, condition_status = ?, supplier_id = ?, price = ?, ca_certificate = ?, 
                    validity_date = ?, barcode = ?, updated_at = NOW()
                WHERE id = ?
            ");
            
            $stmt->execute([
                $name, $description, $category_id, $current_stock, $min_stock, $max_stock,
                $location_id, $condition_status, $supplier_id, $price, $ca_certificate,
                $validity_date ?: null, $barcode, $product_id
            ]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Produto atualizado com sucesso']);
    } else {
        // Criar novo produto
        $stmt = $pdo->prepare("
            INSERT INTO products (name, description, category_id, current_stock, min_stock, max_stock, 
                                 location_id, condition_status, supplier_id, price, ca_certificate, 
                                 validity_date, barcode, photo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $name, $description, $category_id, $current_stock, $min_stock, $max_stock,
            $location_id, $condition_status, $supplier_id, $price, $ca_certificate,
            $validity_date ?: null, $barcode, $photo
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Produto cadastrado com sucesso']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar produto: ' . $e->getMessage()]);
}
?>