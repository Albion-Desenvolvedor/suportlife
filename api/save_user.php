<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

// Verificar se é admin
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

try {
    $user_id = $_POST['user_id'] ?? null;
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $active = $_POST['active'] ?? '1';
    
    if (empty($name) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Nome e email são obrigatórios']);
        exit;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit;
    }
    
    if ($user_id) {
        // Verificar se email já existe em outro usuário
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email já está em uso por outro usuário']);
            exit;
        }
        
        // Atualizar usuário existente
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                UPDATE users SET 
                    name = ?, email = ?, password = ?, role = ?, active = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $hashed_password, $role, $active, $user_id]);
        } else {
            $stmt = $pdo->prepare("
                UPDATE users SET 
                    name = ?, email = ?, role = ?, active = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$name, $email, $role, $active, $user_id]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso']);
    } else {
        if (empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Senha é obrigatória para novos usuários']);
            exit;
        }
        
        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email já está em uso']);
            exit;
        }
        
        // Criar novo usuário
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password, role, active) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$name, $email, $hashed_password, $role, $active]);
        echo json_encode(['success' => true, 'message' => 'Usuário cadastrado com sucesso']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar usuário: ' . $e->getMessage()]);
}
?>