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
    $department_id = $_POST['department_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $manager_id = $_POST['manager_id'] ?? null;
    $active = $_POST['active'] ?? '1';
    
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Nome do departamento é obrigatório']);
        exit;
    }
    
    if ($department_id) {
        // Verificar se já existe departamento com este nome (exceto o atual)
        $stmt = $pdo->prepare("SELECT id FROM departments WHERE name = ? AND id != ?");
        $stmt->execute([$name, $department_id]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Já existe um departamento com este nome']);
            exit;
        }
        
        // Atualizar departamento existente
        $stmt = $pdo->prepare("
            UPDATE departments SET 
                name = ?, manager_id = ?, active = ?
            WHERE id = ?
        ");
        
        $stmt->execute([$name, $manager_id ?: null, $active, $department_id]);
        echo json_encode(['success' => true, 'message' => 'Departamento atualizado com sucesso']);
    } else {
        // Verificar se já existe departamento com este nome
        $stmt = $pdo->prepare("SELECT id FROM departments WHERE name = ?");
        $stmt->execute([$name]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Já existe um departamento com este nome']);
            exit;
        }
        
        // Criar novo departamento
        $stmt = $pdo->prepare("
            INSERT INTO departments (name, manager_id, active) 
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$name, $manager_id ?: null, $active]);
        echo json_encode(['success' => true, 'message' => 'Departamento cadastrado com sucesso']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar departamento: ' . $e->getMessage()]);
}
?>