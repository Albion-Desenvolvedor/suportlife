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
$delete_type = $input['delete_type'] ?? 'product'; // 'product' or 'all'

if (empty($product_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do produto é obrigatório']);
    exit;
}

try {
    $pdo->beginTransaction();
    
    if ($delete_type === 'all') {
        // Excluir tudo relacionado ao produto
        
        // 1. Excluir termos de responsabilidade relacionados
        $stmt = $pdo->prepare("
            DELETE rt FROM responsibility_terms rt
            INNER JOIN requests r ON rt.request_id = r.id
            WHERE r.product_id = ?
        ");
        $stmt->execute([$product_id]);
        
        // 2. Excluir solicitações
        $stmt = $pdo->prepare("DELETE FROM requests WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // 3. Excluir movimentações
        $stmt = $pdo->prepare("DELETE FROM movements WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // 4. Excluir preços de fornecedores
        $stmt = $pdo->prepare("DELETE FROM supplier_prices WHERE product_id = ?");
        $stmt->execute([$product_id]);
        
        // 5. Excluir o produto
        $stmt = $pdo->prepare("SELECT photo FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product && $product['photo']) {
            $photoPath = '../uploads/products/' . $product['photo'];
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        
        $message = 'Produto e todos os dados relacionados foram excluídos com sucesso';
        
    } else {
        // Excluir apenas o produto (verificar se tem histórico)
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM movements WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $movements = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM requests WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $requests = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($movements['count'] > 0 || $requests['count'] > 0) {
            // Se tem histórico, apenas desativar
            $stmt = $pdo->prepare("UPDATE products SET active = 0, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$product_id]);
            
            $message = 'Produto desativado com sucesso (possui histórico)';
        } else {
            // Se não tem histórico, pode excluir fisicamente
            $stmt = $pdo->prepare("SELECT photo FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product && $product['photo']) {
                $photoPath = '../uploads/products/' . $product['photo'];
                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
            }
            
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            
            $message = 'Produto excluído com sucesso';
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $message]);
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Erro ao excluir produto: ' . $e->getMessage()]);
}
?>