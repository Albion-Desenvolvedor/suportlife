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
    $location_id = $_POST['location_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Nome da localização é obrigatório']);
        exit;
    }
    
    if ($location_id) {
        // Atualizar localização existente
        $stmt = $pdo->prepare("
            UPDATE locations SET 
                name = ?, description = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$name, $description, $location_id]);
        echo json_encode(['success' => true, 'message' => 'Localização atualizada com sucesso']);
    } else {
        // Verificar se já existe localização com este nome
        $stmt = $pdo->prepare("SELECT id FROM locations WHERE name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Já existe uma localização com este nome']);
            exit;
        }
        
        // Criar nova localização
        $stmt = $pdo->prepare("
            INSERT INTO locations (name, description) 
            VALUES (?, ?)
        ");
        
        $stmt->execute([$name, $description]);
        echo json_encode(['success' => true, 'message' => 'Localização cadastrada com sucesso']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao cadastrar localização: ' . $e->getMessage()]);
}
?>