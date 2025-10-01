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
$supplier_id = $input['supplier_id'] ?? '';
$delete_type = $input['delete_type'] ?? 'supplier'; // 'supplier' or 'all'

if (empty($supplier_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do fornecedor é obrigatório']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    if ($delete_type === 'all') {
        // Excluir tudo relacionado ao fornecedor
        
        // 1. Buscar produtos do fornecedor
        $stmt = $pdo->prepare("SELECT id FROM products WHERE supplier_id = ?");
        $stmt->execute([$supplier_id]);
        $products = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($products as $product_id) {
            // Excluir termos relacionados aos produtos
            $stmt = $pdo->prepare("
                DELETE rt FROM responsibility_terms rt
                INNER JOIN requests r ON rt.request_id = r.id
                WHERE r.product_id = ?
            ");
            $stmt->execute([$product_id]);
            
            // Excluir solicitações dos produtos
            $stmt = $pdo->prepare("DELETE FROM requests WHERE product_id = ?");
            $stmt->execute([$product_id]);
            
            // Excluir movimentações dos produtos
            $stmt = $pdo->prepare("DELETE FROM movements WHERE product_id = ?");
            $stmt->execute([$product_id]);
        }
        
        // 2. Excluir preços do fornecedor
        $stmt = $pdo->prepare("DELETE FROM supplier_prices WHERE supplier_id = ?");
        $stmt->execute([$supplier_id]);
        
        // 3. Excluir produtos do fornecedor
        $stmt = $pdo->prepare("DELETE FROM products WHERE supplier_id = ?");
        $stmt->execute([$supplier_id]);
        
        // 4. Excluir o fornecedor
        $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
        $stmt->execute([$supplier_id]);
        
        $message = 'Fornecedor e todos os dados relacionados foram excluídos com sucesso';
        
    } else {
        // Excluir apenas o fornecedor (verificar se tem produtos)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE supplier_id = ?");
        $stmt->execute([$supplier_id]);
        $products = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($products['count'] > 0) {
            // Se tem produtos, apenas desativar
            $stmt = $pdo->prepare("UPDATE suppliers SET active = 0 WHERE id = ?");
            $stmt->execute([$supplier_id]);
            
            $message = 'Fornecedor desativado com sucesso (possui produtos cadastrados)';
        } else {
            // Se não tem produtos, pode excluir
            $stmt = $pdo->prepare("DELETE FROM suppliers WHERE id = ?");
            $stmt->execute([$supplier_id]);
            
            $message = 'Fornecedor excluído com sucesso';
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir fornecedor: ' . $e->getMessage()]);
}
?>