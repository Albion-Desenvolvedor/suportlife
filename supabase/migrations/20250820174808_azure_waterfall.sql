-- Schema do banco de dados para o sistema de almoxarifado Support Life

CREATE DATABASE IF NOT EXISTS support_life_warehouse;
USE support_life_warehouse;

-- Tabela de usuários
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'manager', 'user') DEFAULT 'user',
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de categorias
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de localizações
CREATE TABLE locations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de fornecedores
CREATE TABLE suppliers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    contact_person VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    cnpj VARCHAR(18),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de departamentos
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    manager_id INT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES users(id)
);

-- Tabela de produtos
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT,
    current_stock INT DEFAULT 0,
    min_stock INT DEFAULT 0,
    max_stock INT DEFAULT 0,
    location_id INT,
    condition_status ENUM('Novo', 'Usado - Bom', 'Usado - Regular', 'Para Descarte') DEFAULT 'Novo',
    supplier_id INT,
    price DECIMAL(10,2) DEFAULT 0.00,
    ca_certificate VARCHAR(50),
    validity_date DATE,
    barcode VARCHAR(50),
    photo VARCHAR(255),
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (location_id) REFERENCES locations(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    INDEX idx_barcode (barcode),
    INDEX idx_name (name),
    INDEX idx_category (category_id)
);

-- Tabela de movimentações de estoque
CREATE TABLE movements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    type ENUM('entrada', 'saida') NOT NULL,
    quantity INT NOT NULL,
    reason VARCHAR(200),
    user_id INT NOT NULL,
    reference_id INT, -- ID da solicitação ou compra relacionada
    reference_type ENUM('request', 'purchase', 'adjustment', 'return'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_product_date (product_id, created_at),
    INDEX idx_type_date (type, created_at)
);

-- Tabela de solicitações de material
CREATE TABLE requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    user_id INT NOT NULL,
    department_id INT NOT NULL,
    quantity INT NOT NULL,
    pickup_date DATE,
    return_date DATE,
    observations TEXT,
    status ENUM('pending', 'approved', 'delivered', 'returned', 'cancelled') DEFAULT 'pending',
    approved_by INT,
    approved_at TIMESTAMP NULL,
    delivered_by INT,
    delivered_at TIMESTAMP NULL,
    returned_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (approved_by) REFERENCES users(id),
    FOREIGN KEY (delivered_by) REFERENCES users(id),
    INDEX idx_status_date (status, created_at),
    INDEX idx_user_date (user_id, created_at)
);

-- Tabela de termos de responsabilidade
CREATE TABLE responsibility_terms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT NOT NULL,
    term_number VARCHAR(20) NOT NULL,
    responsible_person VARCHAR(100) NOT NULL,
    responsible_document VARCHAR(20),
    delivered_by VARCHAR(100) NOT NULL,
    delivery_date DATE NOT NULL,
    return_date DATE,
    observations TEXT,
    signature_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES requests(id),
    UNIQUE KEY unique_term_number (term_number)
);

-- Tabela de preços de fornecedores
CREATE TABLE supplier_prices (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    supplier_id INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    valid_from DATE NOT NULL,
    valid_until DATE,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    INDEX idx_product_supplier (product_id, supplier_id),
    INDEX idx_valid_dates (valid_from, valid_until)
);

-- Tabela de configurações do sistema
CREATE TABLE system_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir dados iniciais

-- Usuário administrador padrão
INSERT INTO users (name, email, password, role) VALUES 
('Administrador', 'admin@supportlife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Categorias padrão
INSERT INTO categories (name, description) VALUES 
('EPI', 'Equipamentos de Proteção Individual'),
('Médico-Hospitalar', 'Materiais médicos e hospitalares'),
('Escritório', 'Materiais de escritório'),
('Limpeza', 'Produtos de limpeza e higiene'),
('Ferramentas', 'Ferramentas e equipamentos'),
('Eletrônicos', 'Equipamentos eletrônicos');

-- Localizações padrão
INSERT INTO locations (name, description) VALUES 
('Prateleira A1', 'Primeira prateleira do corredor A'),
('Prateleira A2', 'Segunda prateleira do corredor A'),
('Armário B1', 'Primeiro armário do setor B'),
('Armário B2', 'Segundo armário do setor B'),
('Geladeira Médica', 'Geladeira para materiais médicos'),
('Depósito Geral', 'Área de depósito geral');

-- Departamentos padrão
INSERT INTO departments (name) VALUES 
('Administração'),
('Recursos Humanos'),
('Manutenção'),
('Limpeza'),
('Segurança'),
('TI');

-- Fornecedores padrão
INSERT INTO suppliers (name, contact_person, email, phone) VALUES 
('EPI Solutions', 'João Silva', 'contato@episolutions.com', '(11) 1234-5678'),
('MedSupply', 'Maria Santos', 'vendas@medsupply.com', '(11) 8765-4321'),
('Office Plus', 'Carlos Oliveira', 'pedidos@officeplus.com', '(11) 5555-0000');

-- Produtos de exemplo
INSERT INTO products (name, description, category_id, current_stock, min_stock, max_stock, location_id, condition_status, supplier_id, price, ca_certificate, validity_date, barcode) VALUES 
('Capacete de Segurança', 'Capacete de segurança classe A', 1, 15, 10, 50, 1, 'Novo', 1, 45.90, 'CA-12345', '2025-12-31', '7891234567890'),
('Luva de Procedimento', 'Luva descartável para procedimentos', 2, 5, 20, 200, 3, 'Novo', 2, 0.85, 'CA-54321', '2024-08-15', '7891234567891'),
('Papel A4', 'Resma de papel A4 75g', 3, 25, 15, 100, 2, 'Novo', 3, 18.50, NULL, NULL, '7891234567892');

-- Configurações do sistema
INSERT INTO system_settings (setting_key, setting_value, description) VALUES 
('company_name', 'Support Life', 'Nome da empresa'),
('company_logo', '', 'Logo da empresa'),
('alert_days_before_expiry', '30', 'Dias antes do vencimento para alertar'),
('term_validity_days', '30', 'Validade padrão dos termos em dias'),
('backup_frequency', 'daily', 'Frequência de backup automático');

-- Criar índices adicionais para performance
CREATE INDEX idx_products_stock ON products(current_stock, min_stock, max_stock);
CREATE INDEX idx_movements_date ON movements(created_at);
CREATE INDEX idx_requests_status ON requests(status);
CREATE INDEX idx_products_validity ON products(validity_date);

-- Views úteis

-- View para produtos com estoque baixo
CREATE VIEW low_stock_products AS
SELECT 
    p.*,
    c.name as category_name,
    l.name as location_name,
    s.name as supplier_name
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN locations l ON p.location_id = l.id
LEFT JOIN suppliers s ON p.supplier_id = s.id
WHERE p.current_stock <= p.min_stock AND p.active = TRUE;

-- View para produtos próximos ao vencimento
CREATE VIEW expiring_products AS
SELECT 
    p.*,
    c.name as category_name,
    l.name as location_name,
    DATEDIFF(p.validity_date, CURDATE()) as days_to_expire
FROM products p
LEFT JOIN categories c ON p.category_id = c.id
LEFT JOIN locations l ON p.location_id = l.id
WHERE p.validity_date IS NOT NULL 
AND p.validity_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
AND p.active = TRUE;

-- View para relatório de movimentações
CREATE VIEW movement_report AS
SELECT 
    m.*,
    p.name as product_name,
    p.barcode,
    c.name as category_name,
    u.name as user_name
FROM movements m
JOIN products p ON m.product_id = p.id
LEFT JOIN categories c ON p.category_id = c.id
JOIN users u ON m.user_id = u.id;

-- Triggers para atualizar estoque automaticamente

DELIMITER //

-- Trigger para atualizar estoque após movimentação
CREATE TRIGGER update_stock_after_movement
AFTER INSERT ON movements
FOR EACH ROW
BEGIN
    IF NEW.type = 'entrada' THEN
        UPDATE products 
        SET current_stock = current_stock + NEW.quantity,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = NEW.product_id;
    ELSEIF NEW.type = 'saida' THEN
        UPDATE products 
        SET current_stock = current_stock - NEW.quantity,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = NEW.product_id;
    END IF;
END//

-- Trigger para criar movimentação quando solicitação é entregue
CREATE TRIGGER create_movement_on_delivery
AFTER UPDATE ON requests
FOR EACH ROW
BEGIN
    IF OLD.status != 'delivered' AND NEW.status = 'delivered' THEN
        INSERT INTO movements (product_id, type, quantity, reason, user_id, reference_id, reference_type)
        VALUES (NEW.product_id, 'saida', NEW.quantity, 'Entrega de solicitação', NEW.delivered_by, NEW.id, 'request');
    END IF;
END//

-- Trigger para criar movimentação quando material é devolvido
CREATE TRIGGER create_movement_on_return
AFTER UPDATE ON requests
FOR EACH ROW
BEGIN
    IF OLD.status != 'returned' AND NEW.status = 'returned' THEN
        INSERT INTO movements (product_id, type, quantity, reason, user_id, reference_id, reference_type)
        VALUES (NEW.product_id, 'entrada', NEW.quantity, 'Devolução de material', NEW.user_id, NEW.id, 'return');
    END IF;
END//

DELIMITER ;