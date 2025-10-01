<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

try {
    $data = [];
    
    // Categorias
    $stmt = $pdo->query("SELECT id, name FROM categories WHERE active = 1 ORDER BY name");
    $data['categories'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Localizações
    $stmt = $pdo->query("SELECT id, name FROM locations WHERE active = 1 ORDER BY name");
    $data['locations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Fornecedores
    $stmt = $pdo->query("SELECT id, name FROM suppliers WHERE active = 1 ORDER BY name");
    $data['suppliers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($data);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro interno do servidor']);
}
?>