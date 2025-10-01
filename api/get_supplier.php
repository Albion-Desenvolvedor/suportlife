<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$supplier_id = $_GET['id'] ?? '';

if (empty($supplier_id)) {
    echo json_encode(['success' => false, 'message' => 'ID do fornecedor é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$supplier_id]);
    $supplier = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($supplier) {
        echo json_encode(['success' => true, 'supplier' => $supplier]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Fornecedor não encontrado']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar fornecedor: ' . $e->getMessage()]);
}
?>