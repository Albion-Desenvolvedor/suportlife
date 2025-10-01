<?php
require_once 'config/database.php';
require_once 'config.php';

// Função para obter usuário por ID
function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Função para obter todos os produtos
function getAllProducts() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name, l.name as location_name, s.name as supplier_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        WHERE p.active = 1
        ORDER BY p.name
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter produtos com estoque baixo
function getLowStockProducts() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name, l.name as location_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE p.current_stock <= p.min_stock AND p.active = 1
        ORDER BY p.current_stock ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter produtos com estoque alto
function getOverStockProducts() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT p.*, c.name as category_name, l.name as location_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE p.current_stock >= p.max_stock AND p.active = 1
        ORDER BY p.current_stock DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter produtos vencendo
function getExpiringProducts($days = 30) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, l.name as location_name,
               DATEDIFF(p.validity_date, CURDATE()) as days_to_expire
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN locations l ON p.location_id = l.id
        WHERE p.validity_date IS NOT NULL 
        AND p.validity_date <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
        AND p.active = 1
        ORDER BY p.validity_date ASC
    ");
    $stmt->execute([$days]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter estatísticas do dashboard
function getDashboardStats() {
    global $pdo;
    
    $stats = [];
    
    // Total de produtos
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE active = 1");
    $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Movimentações hoje
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM movements WHERE DATE(created_at) = CURDATE()");
    $stats['movements_today'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Última movimentação
    $stmt = $pdo->query("SELECT MAX(created_at) as last_movement FROM movements WHERE DATE(created_at) = CURDATE()");
    $stats['last_movement_time'] = $stmt->fetch(PDO::FETCH_ASSOC)['last_movement'];
    
    // Solicitações pendentes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM requests WHERE status = 'pending'");
    $stats['pending_requests'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Valor total do estoque
    $stmt = $pdo->query("SELECT SUM(current_stock * price) as total FROM products WHERE active = 1");
    $stats['total_stock_value'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Total de estoque para cálculos
    $stmt = $pdo->query("SELECT SUM(current_stock) as total FROM products WHERE active = 1");
    $stats['total_stock'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    return $stats;
}

// Função para obter estatísticas mensais
function getMonthlyStats() {
    global $pdo;
    
    $stats = [];
    
    // Tendência de produtos (comparação com mês anterior)
    $stmt = $pdo->query("
        SELECT 
            COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 END) as current_month,
            COUNT(CASE WHEN MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN 1 END) as last_month
        FROM products WHERE active = 1
    ");
    $productStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['products_trend'] = $productStats['last_month'] > 0 ? 
        (($productStats['current_month'] - $productStats['last_month']) / $productStats['last_month']) * 100 : 0;
    
    // Tendência de valor
    $stmt = $pdo->query("
        SELECT 
            SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN current_stock * price ELSE 0 END) as current_value,
            SUM(CASE WHEN MONTH(created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN current_stock * price ELSE 0 END) as last_value
        FROM products WHERE active = 1
    ");
    $valueStats = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['value_trend'] = $valueStats['last_value'] > 0 ? 
        (($valueStats['current_value'] - $valueStats['last_value']) / $valueStats['last_value']) * 100 : 0;
    
    // Dados para gráfico de movimentações (últimos 7 dias)
    $stmt = $pdo->query("
        SELECT DATE(created_at) as date,
               SUM(CASE WHEN type = 'entrada' THEN quantity ELSE 0 END) as entries,
               SUM(CASE WHEN type = 'saida' THEN quantity ELSE 0 END) as exits
        FROM movements 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(created_at)
        ORDER BY date
    ");
    $movementData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats['movement_labels'] = array_map(function($d) { 
        return date('d/m', strtotime($d['date'])); 
    }, $movementData);
    $stats['entries'] = array_column($movementData, 'entries');
    $stats['exits'] = array_column($movementData, 'exits');
    
    return $stats;
}

// Função para obter top categorias
function getTopCategories() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT c.name, 
               COUNT(p.id) as product_count,
               SUM(p.current_stock) as total_stock,
               SUM(p.current_stock * p.price) as total_value
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id AND p.active = 1
        WHERE c.active = 1
        GROUP BY c.id, c.name
        ORDER BY product_count DESC
        LIMIT 6
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter movimentações recentes
function getRecentMovements($limit = 5) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT m.*, p.name as product_name, u.name as user_name
        FROM movements m
        LEFT JOIN products p ON m.product_id = p.id
        LEFT JOIN users u ON m.user_id = u.id
        ORDER BY m.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter solicitações recentes
function getRecentRequests($limit = 5) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT r.*, p.name as product_name, u.name as user_name, d.name as department_name
        FROM requests r
        LEFT JOIN products p ON r.product_id = p.id
        LEFT JOIN users u ON r.user_id = u.id
        LEFT JOIN departments d ON r.department_id = d.id
        ORDER BY r.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter solicitações de compra
function getPurchaseRequests() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT pr.*, c.name as category_name, u.name as user_name
        FROM purchase_requests pr
        LEFT JOIN categories c ON pr.category_id = c.id
        LEFT JOIN users u ON pr.user_id = u.id
        ORDER BY pr.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter solicitações de orçamento
function getQuoteRequests() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT qr.*, u.name as user_name, s.name as supplier_name
        FROM quote_requests qr
        LEFT JOIN users u ON qr.user_id = u.id
        LEFT JOIN suppliers s ON qr.supplier_id = s.id
        ORDER BY qr.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter ranking de fornecedores
function getSupplierRanking() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT s.*, 
               COUNT(p.id) as product_count,
               SUM(p.current_stock * p.price) as total_value,
               AVG(p.price) as avg_price
        FROM suppliers s
        LEFT JOIN products p ON s.id = p.supplier_id AND p.active = 1
        WHERE s.active = 1
        GROUP BY s.id
        ORDER BY product_count DESC, total_value DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter dados de comparação de preços
function getPriceComparisonData() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT 
            p1.name as product_name,
            p1.ca_certificate,
            MIN(p1.price) as min_price,
            MAX(p1.price) as max_price,
            (MAX(p1.price) - MIN(p1.price)) as price_difference,
            ((MAX(p1.price) - MIN(p1.price)) / MAX(p1.price)) * 100 as savings_percentage,
            (SELECT s1.name FROM suppliers s1 JOIN products p2 ON s1.id = p2.supplier_id 
             WHERE (p2.name = p1.name OR p2.ca_certificate = p1.ca_certificate) 
             AND p2.price = MIN(p1.price) LIMIT 1) as cheapest_supplier,
            (SELECT s2.name FROM suppliers s2 JOIN products p3 ON s2.id = p3.supplier_id 
             WHERE (p3.name = p1.name OR p3.ca_certificate = p1.ca_certificate) 
             AND p3.price = MAX(p1.price) LIMIT 1) as expensive_supplier
        FROM products p1
        WHERE p1.active = 1 AND p1.supplier_id IS NOT NULL
        GROUP BY 
            CASE 
                WHEN p1.ca_certificate IS NOT NULL AND p1.ca_certificate != '' THEN p1.ca_certificate
                ELSE p1.name
            END
        HAVING COUNT(*) > 1 AND price_difference > 0
        ORDER BY savings_percentage DESC
        LIMIT 10
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter valor médio de orçamentos
function getAverageQuoteValue() {
    global $pdo;
    $stmt = $pdo->query("SELECT AVG(quoted_price) as avg_value FROM quote_requests WHERE quoted_price IS NOT NULL");
    return $stmt->fetch(PDO::FETCH_ASSOC)['avg_value'] ?? 0;
}

// Função para obter todas as categorias
function getAllCategories() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM categories WHERE active = 1 ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter todas as localizações
function getAllLocations() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM locations WHERE active = 1 ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter todos os fornecedores
function getAllSuppliers() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM suppliers WHERE active = 1 ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter todos os departamentos
function getAllDepartments() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM departments WHERE active = 1 ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter todos os cargos
function getAllRoles() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM roles WHERE active = 1 ORDER BY access_level DESC, name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para formatar moeda
function formatCurrency($value) {
    return 'R$ ' . number_format($value, 2, ',', '.');
}

// Função para formatar data
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

// Função para formatar data e hora
function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

// Função para obter status em português
function getStatusText($status) {
    $statuses = [
        'pending' => 'Pendente',
        'approved' => 'Aprovado',
        'delivered' => 'Entregue',
        'returned' => 'Devolvido',
        'cancelled' => 'Cancelado'
    ];
    return $statuses[$status] ?? $status;
}

// Função para obter classe CSS do status
function getStatusClass($status) {
    $classes = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-blue-100 text-blue-800',
        'delivered' => 'bg-green-100 text-green-800',
        'returned' => 'bg-gray-100 text-gray-800',
        'cancelled' => 'bg-red-100 text-red-800'
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}

// Função para obter status de compra em português
function getPurchaseStatusText($status) {
    $statuses = [
        'pending' => 'Pendente',
        'approved' => 'Aprovado',
        'ordered' => 'Pedido Feito',
        'received' => 'Recebido',
        'cancelled' => 'Cancelado'
    ];
    return $statuses[$status] ?? $status;
}

// Função para obter classe CSS do status de compra
function getPurchaseStatusClass($status) {
    $classes = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-blue-100 text-blue-800',
        'ordered' => 'bg-purple-100 text-purple-800',
        'received' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800'
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}

// Função para obter status de orçamento em português
function getQuoteStatusText($status) {
    $statuses = [
        'draft' => 'Rascunho',
        'sent' => 'Enviado',
        'quoted' => 'Orçado',
        'approved' => 'Aprovado',
        'rejected' => 'Rejeitado'
    ];
    return $statuses[$status] ?? $status;
}

// Função para obter classe CSS do status de orçamento
function getQuoteStatusClass($status) {
    $classes = [
        'draft' => 'bg-gray-100 text-gray-800',
        'sent' => 'bg-blue-100 text-blue-800',
        'quoted' => 'bg-purple-100 text-purple-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800'
    ];
    return $classes[$status] ?? 'bg-gray-100 text-gray-800';
}

// Função para obter todas as movimentações
function getAllMovements() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT m.*, p.name as product_name, p.barcode, u.name as user_name
        FROM movements m
        LEFT JOIN products p ON m.product_id = p.id
        LEFT JOIN users u ON m.user_id = u.id
        ORDER BY m.created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter preços de fornecedores
function getSupplierPrices() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT p.name as product_name, p.ca_certificate, p.price, s.name as supplier_name
        FROM products p
        LEFT JOIN suppliers s ON p.supplier_id = s.id
        WHERE p.active = 1 AND s.active = 1 AND p.supplier_id IS NOT NULL
        ORDER BY p.name, p.price
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter estatísticas de relatórios
function getReportStats() {
    global $pdo;
    
    $stats = [];
    
    // Movimentações este mês
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM movements WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $stats['movements_this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Gastos este mês (baseado nas saídas)
    $stmt = $pdo->query("
        SELECT SUM(m.quantity * p.price) as total 
        FROM movements m 
        JOIN products p ON m.product_id = p.id 
        WHERE m.type = 'saida' AND MONTH(m.created_at) = MONTH(CURDATE()) AND YEAR(m.created_at) = YEAR(CURDATE())
    ");
    $stats['expenses_this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    
    // Solicitações este mês
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM requests WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())");
    $stats['requests_this_month'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Produtos vencendo
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE validity_date IS NOT NULL AND validity_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) AND active = 1");
    $stats['expiring_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    return $stats;
}

// Função para obter estatísticas de localizações
function getLocationStats() {
    global $pdo;
    
    $stats = [];
    
    // Total de produtos armazenados
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE location_id IS NOT NULL AND active = 1");
    $stats['total_products'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Localizações vazias
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM locations l 
        LEFT JOIN products p ON l.id = p.location_id AND p.active = 1
        WHERE p.id IS NULL AND l.active = 1
    ");
    $stats['empty_locations'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    return $stats;
}

// Função para obter contagem de produtos por localização
function getProductCountByLocation($locationId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE location_id = ? AND active = 1");
    $stmt->execute([$locationId]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Função para obter configurações do sistema
function getSystemSettings() {
    global $pdo;
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM system_settings");
    $settings = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    return $settings;
}

// Função para obter todos os usuários
function getAllUsers() {
    global $pdo;
    $stmt = $pdo->query("SELECT u.*, r.name as role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para obter texto da função do usuário
function getUserRoleText($role) {
    $roles = [
        'admin' => 'Administrador',
        'manager' => 'Gerente',
        'user' => 'Usuário'
    ];
    return $roles[$role] ?? $role;
}

// Função para obter classe CSS da função do usuário
function getUserRoleClass($role) {
    $classes = [
        'admin' => 'bg-red-100 text-red-800',
        'manager' => 'bg-blue-100 text-blue-800',
        'user' => 'bg-green-100 text-green-800'
    ];
    return $classes[$role] ?? 'bg-gray-100 text-gray-800';
}

// Função para obter total de produtos
function getTotalProducts() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE active = 1");
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Função para obter total de movimentações
function getTotalMovements() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM movements");
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

// Função para verificar se o sistema está instalado
function isSystemInstalled() {
    return file_exists(__DIR__ . '/../config/installed.lock');
}

// Função para verificar permissões do usuário
function hasPermission($required_role) {
    if (!isset($_SESSION['user_role'])) {
        return false;
    }
    
    $roles = ['user' => 1, 'manager' => 2, 'admin' => 3];
    $user_level = $roles[$_SESSION['user_role']] ?? 0;
    $required_level = $roles[$required_role] ?? 0;
    
    return $user_level >= $required_level;
}

// Função para sanitizar entrada
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Função para validar email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Função para gerar token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Função para verificar token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Função para log de atividades
function logActivity($action, $details = '') {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    
    try {
        // Verificar se a tabela existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'activity_logs'");
        if (!$stmt->fetch()) {
            // Criar tabela se não existir
            $pdo->exec("
                CREATE TABLE activity_logs (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    user_id INT NOT NULL,
                    action VARCHAR(100) NOT NULL,
                    details TEXT,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id),
                    INDEX idx_user_date (user_id, created_at),
                    INDEX idx_action_date (action, created_at)
                )
            ");
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, details, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $action,
            $details,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    } catch (Exception $e) {
        logError('Erro ao registrar atividade: ' . $e->getMessage());
    }
}

// Função para backup do banco de dados
function createDatabaseBackup() {
    global $pdo;
    
    try {
        $backupDir = __DIR__ . '/../backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $backupFile = $backupDir . 'backup_' . date('Y_m_d_H_i_s') . '.sql';
        
        // Obter todas as tabelas
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $backup = "-- Support Life Warehouse Backup\n";
        $backup .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n\n";
        $backup .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        
        foreach ($tables as $table) {
            // Estrutura da tabela
            $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
            $create = $stmt->fetch(PDO::FETCH_ASSOC);
            $backup .= "\n-- Table: $table\n";
            $backup .= "DROP TABLE IF EXISTS `$table`;\n";
            $backup .= $create['Create Table'] . ";\n\n";
            
            // Dados da tabela
            $stmt = $pdo->query("SELECT * FROM `$table`");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($rows)) {
                $backup .= "-- Data for table: $table\n";
                foreach ($rows as $row) {
                    $values = array_map(function($value) use ($pdo) {
                        return $value === null ? 'NULL' : $pdo->quote($value);
                    }, array_values($row));
                    
                    $backup .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                }
                $backup .= "\n";
            }
        }
        
        $backup .= "SET FOREIGN_KEY_CHECKS = 1;\n";
        
        file_put_contents($backupFile, $backup);
        
        // Limpar backups antigos
        cleanOldBackups();
        
        return $backupFile;
        
    } catch (Exception $e) {
        logError('Erro ao criar backup: ' . $e->getMessage());
        return false;
    }
}

// Função para limpar backups antigos
function cleanOldBackups() {
    $backupDir = __DIR__ . '/../backups/';
    $files = glob($backupDir . 'backup_*.sql');
    $cutoff = time() - (BACKUP_RETENTION_DAYS * 24 * 60 * 60);
    
    foreach ($files as $file) {
        if (filemtime($file) < $cutoff) {
            unlink($file);
        }
    }
}

// Função para otimizar banco de dados
function optimizeDatabase() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($tables as $table) {
            $pdo->exec("OPTIMIZE TABLE `$table`");
        }
        
        return true;
    } catch (Exception $e) {
        logError('Erro ao otimizar banco: ' . $e->getMessage());
        return false;
    }
}

// Função para ajustar brilho de uma cor hex
function adjustBrightness($hex, $percent) {
    // Remove o # se presente
    $hex = ltrim($hex, '#');
    
    // Converte para RGB
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    // Ajusta o brilho
    $r = max(0, min(255, $r + ($r * $percent / 100)));
    $g = max(0, min(255, $g + ($g * $percent / 100)));
    $b = max(0, min(255, $b + ($b * $percent / 100)));
    
    // Converte de volta para hex
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

// Função para converter hex para RGB
function hexToRgb($hex) {
    $hex = ltrim($hex, '#');
    
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    return "$r, $g, $b";
}
?>