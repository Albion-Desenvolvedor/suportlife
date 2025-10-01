<?php
$stats = getDashboardStats();
$lowStockProducts = getLowStockProducts();
$overStockProducts = getOverStockProducts();
$recentRequests = getRecentRequests();
$recentMovements = getRecentMovements(5);
$expiringProducts = getExpiringProducts();
$topCategories = getTopCategories();
$monthlyStats = getMonthlyStats();
?>

<div>
    <div class="dashboard-header">
        <div class="dashboard-meta">
            <div>
                <h1 class="dashboard-title">Dashboard - Support Life</h1>
                <p class="dashboard-subtitle">Visão geral do almoxarifado em tempo real</p>
            </div>
            <div class="last-update">
                <span class="last-update-label">Última atualização</span>
                <span class="last-update-time"><?= date('d/m/Y H:i') ?></span>
                <button onclick="refreshDashboard()" class="ml-2 text-blue-600 hover:text-blue-800" title="Atualizar">
                    <i class="fas fa-sync-alt"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Alertas Críticos -->
    <?php if (count($lowStockProducts) > 0 || count($overStockProducts) > 0 || count($expiringProducts) > 0): ?>
        <div class="mb-6">
            <?php if (count($lowStockProducts) > 0): ?>
                <div class="dashboard-alert dashboard-alert-critical">
                    <div class="alert-content">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle alert-icon text-red-400"></i>
                            </div>
                            <div class="ml-3 flex-1">
                                <p class="alert-title text-red-800">
                                    <strong>Alerta de Estoque Baixo:</strong> <?= count($lowStockProducts) ?> produto(s) abaixo do estoque mínimo
                                </p>
                                <ul class="alert-list">
                                    <?php foreach (array_slice($lowStockProducts, 0, 3) as $product): ?>
                                        <li class="alert-list-item">
                                            <i class="fas fa-dot-circle mr-2 text-xs"></i>
                                            <span><?= htmlspecialchars($product['name']) ?> - Atual: <?= $product['current_stock'] ?> | Mínimo: <?= $product['min_stock'] ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php if (count($lowStockProducts) > 3): ?>
                                        <li class="alert-list-item text-red-600">
                                            <i class="fas fa-plus mr-2 text-xs"></i>
                                            <span>E mais <?= count($lowStockProducts) - 3 ?> produto(s)</span>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                                <button onclick="viewLowStockProducts()" class="mt-2 text-red-600 hover:text-red-800 text-sm font-medium">
                                    Ver todos os produtos <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (count($expiringProducts) > 0): ?>
                <div class="dashboard-alert dashboard-alert-warning">
                    <div class="alert-content">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-calendar-times alert-icon text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="alert-title text-yellow-800">
                                    <strong>Produtos Vencendo:</strong> <?= count($expiringProducts) ?> produto(s) vencendo nos próximos 30 dias
                                </p>
                                <button onclick="viewExpiringProducts()" class="mt-2 text-yellow-600 hover:text-yellow-800 text-sm font-medium">
                                    Ver produtos vencendo <i class="fas fa-arrow-right ml-1"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Cards de Métricas Principais -->
    <div class="dashboard-grid mb-8">
        <div class="dashboard-card">
            <div class="dashboard-card-content">
                <div class="dashboard-card-info">
                    <div class="dashboard-metric-label">Total de Produtos</div>
                    <p class="dashboard-metric"><?= number_format($stats['total_products']) ?></p>
                    <div class="dashboard-metric-trend <?= $monthlyStats['products_trend'] >= 0 ? 'positive' : 'negative' ?>">
                        <i class="fas fa-arrow-<?= $monthlyStats['products_trend'] >= 0 ? 'up' : 'down' ?> mr-1"></i>
                        <?= abs($monthlyStats['products_trend']) ?>% este mês
                    </div>
                </div>
                <div class="dashboard-card-icon icon-blue">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card-content">
                <div class="dashboard-card-info">
                    <div class="dashboard-metric-label">Movimentações Hoje</div>
                    <p class="dashboard-metric"><?= $stats['movements_today'] ?></p>
                    <div class="dashboard-metric-trend neutral">
                        <i class="fas fa-clock mr-1"></i>
                        <?= $stats['last_movement_time'] ? 'Última: ' . date('H:i', strtotime($stats['last_movement_time'])) : 'Nenhuma hoje' ?>
                    </div>
                </div>
                <div class="dashboard-card-icon icon-green">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card-content">
                <div class="dashboard-card-info">
                    <div class="dashboard-metric-label">Solicitações Pendentes</div>
                    <p class="dashboard-metric"><?= $stats['pending_requests'] ?></p>
                    <div class="dashboard-metric-trend <?= $stats['pending_requests'] > 5 ? 'negative' : 'neutral' ?>">
                        <i class="fas fa-hourglass-half mr-1"></i>
                        <?= $stats['pending_requests'] > 5 ? 'Atenção necessária' : 'Sob controle' ?>
                    </div>
                </div>
                <div class="dashboard-card-icon icon-orange">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="dashboard-card-content">
                <div class="dashboard-card-info">
                    <div class="dashboard-metric-label">Valor Total Estoque</div>
                    <p class="dashboard-metric"><?= formatCurrency($stats['total_stock_value']) ?></p>
                    <div class="dashboard-metric-trend <?= $monthlyStats['value_trend'] >= 0 ? 'positive' : 'negative' ?>">
                        <i class="fas fa-chart-bar mr-1"></i>
                        <?= abs($monthlyStats['value_trend']) ?>% vs mês anterior
                    </div>
                </div>
                <div class="dashboard-card-icon icon-purple">
                    <i class="fas fa-chart-bar"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Conteúdo Principal do Dashboard -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Produtos com Estoque Baixo -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Estoque Baixo</h3>
                <span class="dashboard-status-badge bg-red-100 text-red-800">
                    <?= count($lowStockProducts) ?> itens
                </span>
            </div>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                <?php if (count($lowStockProducts) > 0): ?>
                    <?php foreach ($lowStockProducts as $product): ?>
                        <div class="dashboard-list-item dashboard-list-item-critical">
                            <div class="dashboard-list-item-info">
                                <div class="dashboard-list-item-title">
                                    <div class="status-indicator status-critical"></div>
                                    <?= htmlspecialchars($product['name']) ?>
                                </div>
                                <div class="dashboard-list-item-subtitle">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?= htmlspecialchars($product['location_name'] ?? 'Sem localização') ?>
                                </div>
                            </div>
                            <div class="dashboard-list-item-meta">
                                <p class="text-sm font-bold text-red-600"><?= $product['current_stock'] ?></p>
                                <p class="text-xs text-gray-500">de <?= $product['min_stock'] ?> mín</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle text-green-500 text-3xl mb-2"></i>
                        <h3>Tudo certo!</h3>
                        <p>Todos os produtos com estoque adequado</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Solicitações Recentes -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Solicitações Recentes</h3>
                <a href="?page=requests" class="action-link">
                    Ver todas <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                <?php if (count($recentRequests) > 0): ?>
                    <?php foreach ($recentRequests as $request): ?>
                        <div class="dashboard-list-item dashboard-list-item-normal">
                            <div class="dashboard-list-item-info">
                                <div class="dashboard-list-item-title">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-user text-blue-600 text-xs"></i>
                                    </div>
                                    <?= htmlspecialchars($request['product_name']) ?>
                                </div>
                                <div class="dashboard-list-item-subtitle">
                                    <?= htmlspecialchars($request['user_name']) ?> - <?= htmlspecialchars($request['department_name']) ?>
                                </div>
                            </div>
                            <div class="dashboard-list-item-meta">
                                <span class="dashboard-status-badge <?= getStatusClass($request['status']) ?>">
                                    <?= getStatusText($request['status']) ?>
                                </span>
                                <div class="text-xs text-gray-600 mt-1">
                                    <i class="fas fa-cube mr-1"></i>
                                    Qtd: <?= $request['quantity'] ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox text-gray-400 text-3xl mb-2"></i>
                        <h3>Nenhuma solicitação</h3>
                        <p>Não há solicitações recentes</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Movimentações Recentes -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Movimentações Recentes</h3>
                <a href="?page=movements" class="action-link">
                    Ver todas <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="space-y-3 max-h-80 overflow-y-auto">
                <?php if (count($recentMovements) > 0): ?>
                    <?php foreach ($recentMovements as $movement): ?>
                        <div class="dashboard-list-item dashboard-list-item-normal">
                            <div class="dashboard-list-item-info">
                                <div class="dashboard-list-item-title">
                                    <div class="w-8 h-8 <?= $movement['type'] === 'entrada' ? 'bg-green-100' : 'bg-red-100' ?> rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-arrow-<?= $movement['type'] === 'entrada' ? 'down' : 'up' ?> <?= $movement['type'] === 'entrada' ? 'text-green-600' : 'text-red-600' ?> text-xs"></i>
                                    </div>
                                    <?= htmlspecialchars($movement['product_name']) ?>
                                </div>
                                <div class="dashboard-list-item-subtitle">
                                    <?= htmlspecialchars($movement['user_name']) ?> - <?= formatDateTime($movement['created_at']) ?>
                                </div>
                            </div>
                            <div class="dashboard-list-item-meta">
                                <span class="dashboard-status-badge <?= $movement['type'] === 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $movement['quantity'] ?> un.
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-exchange-alt text-gray-400 text-3xl mb-2"></i>
                        <h3>Nenhuma movimentação</h3>
                        <p>Não há movimentações recentes</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gráficos de Resumo -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="dashboard-chart-container">
            <div class="flex items-center justify-between mb-4">
                <h3 class="chart-title">Movimentações dos Últimos 7 Dias</h3>
                <div class="flex space-x-2">
                    <button onclick="changeChartPeriod('weekly')" class="chart-period-btn active" data-period="weekly">7 dias</button>
                    <button onclick="changeChartPeriod('monthly')" class="chart-period-btn" data-period="monthly">30 dias</button>
                </div>
            </div>
            <canvas id="weeklyMovementsChart" width="400" height="200"></canvas>
        </div>

        <div class="dashboard-chart-container">
            <h3 class="chart-title">Distribuição por Categoria</h3>
            <canvas id="categoryDistributionChart" width="400" height="200"></canvas>
        </div>
    </div>

    <!-- Resumo por Categorias -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php foreach ($topCategories as $category): ?>
            <div class="bg-white p-6 rounded-xl shadow-sm border hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($category['name']) ?></h4>
                    <i class="fas fa-tag text-blue-600"></i>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Produtos:</span>
                        <span class="text-sm font-medium"><?= $category['product_count'] ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Estoque:</span>
                        <span class="text-sm font-medium"><?= $category['total_stock'] ?> un.</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Valor:</span>
                        <span class="text-sm font-medium"><?= formatCurrency($category['total_value']) ?></span>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: <?= min(100, ($category['total_stock'] / max(1, $stats['total_stock'])) * 100) ?>%"></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// Inicializar dashboard
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined') {
        initializeDashboardCharts();
    }
    
    // Auto-refresh dashboard every 5 minutes
    setInterval(refreshDashboard, 300000);
});

function initializeDashboardCharts() {
    // Gráfico de movimentações semanais
    const weeklyCtx = document.getElementById('weeklyMovementsChart');
    if (weeklyCtx) {
        window.weeklyChart = new Chart(weeklyCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: <?= json_encode($monthlyStats['movement_labels']) ?>,
                datasets: [{
                    label: 'Entradas',
                    data: <?= json_encode($monthlyStats['entries']) ?>,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Saídas',
                    data: <?= json_encode($monthlyStats['exits']) ?>,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Gráfico de distribuição por categoria
    const categoryCtx = document.getElementById('categoryDistributionChart');
    if (categoryCtx) {
        new Chart(categoryCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($topCategories, 'name')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($topCategories, 'product_count')) ?>,
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(251, 191, 36)',
                        'rgb(168, 85, 247)',
                        'rgb(239, 68, 68)',
                        'rgb(6, 182, 212)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '60%'
            }
        });
    }
}

function refreshDashboard() {
    showToast('Atualizando dashboard...', 'info');
    location.reload();
}

function changeChartPeriod(period) {
    // Update chart period buttons
    document.querySelectorAll('.chart-period-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Fetch new data and update chart
    fetch(`api/get_chart_data.php?period=${period}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && window.weeklyChart) {
                window.weeklyChart.data.labels = data.labels;
                window.weeklyChart.data.datasets[0].data = data.entries;
                window.weeklyChart.data.datasets[1].data = data.exits;
                window.weeklyChart.update();
            }
        })
        .catch(error => {
            console.error('Error updating chart:', error);
        });
}

function viewLowStockProducts() {
    window.location.href = '?page=products&filter=low-stock';
}

function viewExpiringProducts() {
    window.location.href = '?page=products&filter=expiring';
}
</script>