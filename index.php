<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Verificar se o sistema está instalado
if (!isSystemInstalled()) {
    header('Location: install.php');
    exit;
}

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Obter página atual
$page = $_GET['page'] ?? 'dashboard';
$allowedPages = [
    'dashboard', 'products', 'movements', 'requests', 'purchase-requests', 
    'quote-requests', 'suppliers', 'reports', 'locations', 'settings'
];

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

// Obter dados do usuário
$user = getUserById($_SESSION['user_id']);
if (!$user || !$user['active']) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Obter configurações do sistema
$settings = getSystemSettings();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($page) ?> - Support Life Almoxarifado</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        :root {
            --primary-color: <?= $settings['primary_color'] ?? '#3b82f6' ?>;
            --primary-dark: <?= adjustBrightness($settings['primary_color'] ?? '#3b82f6', -20) ?>;
            --primary-rgb: <?= hexToRgb($settings['primary_color'] ?? '#3b82f6') ?>;
            --logo-size: <?= $settings['logo_size'] ?? '40' ?>px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="main-layout">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="p-6 border-b">
                <div class="flex items-center">
                    <?php if (!empty($settings['company_logo'])): ?>
                        <img src="uploads/system/<?= htmlspecialchars($settings['company_logo']) ?>" 
                             alt="Logo" class="object-contain mr-3" 
                             style="width: var(--logo-size); height: var(--logo-size);">
                    <?php else: ?>
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-boxes text-white"></i>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($settings['company_name'] ?? 'Support Life') ?></h2>
                        <p class="text-sm text-gray-600">Almoxarifado</p>
                    </div>
                </div>
            </div>
            
            <nav class="p-4">
                <a href="?page=dashboard" class="nav-item <?= $page === 'dashboard' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="?page=products" class="nav-item <?= $page === 'products' ? 'active' : '' ?>">
                    <i class="fas fa-boxes"></i>
                    Produtos
                </a>
                <a href="?page=movements" class="nav-item <?= $page === 'movements' ? 'active' : '' ?>">
                    <i class="fas fa-exchange-alt"></i>
                    Movimentações
                </a>
                <a href="?page=requests" class="nav-item <?= $page === 'requests' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-cart"></i>
                    Solicitações
                </a>
                <a href="?page=purchase-requests" class="nav-item <?= $page === 'purchase-requests' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-bag"></i>
                    Solicitações de Compra
                </a>
                <a href="?page=quote-requests" class="nav-item <?= $page === 'quote-requests' ? 'active' : '' ?>">
                    <i class="fas fa-file-invoice-dollar"></i>
                    Solicitações de Orçamento
                </a>
                <a href="?page=suppliers" class="nav-item <?= $page === 'suppliers' ? 'active' : '' ?>">
                    <i class="fas fa-truck"></i>
                    Fornecedores
                </a>
                <a href="?page=reports" class="nav-item <?= $page === 'reports' ? 'active' : '' ?>">
                    <i class="fas fa-chart-bar"></i>
                    Relatórios
                </a>
                <a href="?page=locations" class="nav-item <?= $page === 'locations' ? 'active' : '' ?>">
                    <i class="fas fa-map-marker-alt"></i>
                    Localizações
                </a>
                <?php if (hasPermission('admin')): ?>
                    <a href="?page=settings" class="nav-item <?= $page === 'settings' ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i>
                        Configurações
                    </a>
                <?php endif; ?>
            </nav>
            
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-user text-gray-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></p>
                            <p class="text-xs text-gray-600"><?= getUserRoleText($user['role']) ?></p>
                        </div>
                    </div>
                    <a href="logout.php" class="text-gray-400 hover:text-gray-600" title="Sair">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <?php include "pages/{$page}.php"; ?>
        </div>
    </div>

    <!-- Modal Overlay -->
    <div id="modal-overlay" class="hidden">
        <div id="modal-content"></div>
    </div>

    <!-- Scripts -->
    <script src="assets/js/utils.js"></script>
    <script src="assets/js/modals.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/products.js"></script>
    <script src="assets/js/requests.js"></script>
    <script src="assets/js/movements.js"></script>
    <script src="assets/js/locations.js"></script>
    <script src="assets/js/suppliers.js"></script>
    <script src="assets/js/categories.js"></script>
    <script src="assets/js/users.js"></script>
    <script src="assets/js/departments.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/reports.js"></script>
    <script src="assets/js/purchase-requests.js"></script>
    <script src="assets/js/quote-requests.js"></script>
    <script src="assets/js/roles.js"></script>
</body>
</html>