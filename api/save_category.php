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
    $category_id = $_POST['category_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $active = $_POST['active'] ?? '1';
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Nome da categoria é obrigatório']);
        exit;
    }
    
    if ($category_id) {
        // Atualizar categoria existente
        $stmt = $pdo->prepare("
            UPDATE categories SET 
                name = ?, description = ?, active = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $stmt->execute([$name, $description, $active, $category_id]);
        echo json_encode(['success' => true, 'message' => 'Categoria atualizada com sucesso']);
    } else {
        // Verificar se já existe categoria com este nome
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Já existe uma categoria com este nome']);
            exit;
        }
        
        // Criar nova categoria
        $stmt = $pdo->prepare("
            INSERT INTO categories (name, description, active) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$name, $description, $active]);
        echo json_encode(['success' => true, 'message' => 'Categoria cadastrada com sucesso']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar categoria: ' . $e->getMessage()]);
}
?>