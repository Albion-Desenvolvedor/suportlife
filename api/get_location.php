<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

$location_id = $_GET['id'] ?? '';

if (empty($location_id)) {
    echo json_encode(['success' => false, 'message' => 'ID da localização é obrigatório']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM locations WHERE id = ?");
    $stmt->execute([$location_id]);
    $location = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($location) {
        echo json_encode(['success' => true, 'location' => $location]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Localização não encontrada']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar localização: ' . $e->getMessage()]);
}
?>