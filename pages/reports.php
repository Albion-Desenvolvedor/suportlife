<?php
$reportStats = getReportStats();
$priceComparison = getPriceComparisonData();
?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Relatórios</h1>
        <p class="text-gray-600">Relatórios completos de consumo, gastos, comparativos e análises</p>
    </div>

    <!-- Cards de estatísticas rápidas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Movimentações Este Mês</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $reportStats['movements_this_month'] ?></p>
                </div>
                <i class="fas fa-exchange-alt text-2xl text-blue-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Gastos Este Mês</p>
                    <p class="text-2xl font-bold text-gray-900"><?= formatCurrency($reportStats['expenses_this_month']) ?></p>
                </div>
                <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Solicitações Este Mês</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $reportStats['requests_this_month'] ?></p>
                </div>
                <i class="fas fa-shopping-cart text-2xl text-orange-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Produtos Vencendo</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $reportStats['expiring_products'] ?></p>
                </div>
                <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
        </div>
    </div>

    <!-- Relatórios disponíveis -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Relatório de Consumo</h3>
                <i class="fas fa-chart-bar text-2xl text-blue-600"></i>
            </div>
            <p class="text-gray-600 mb-4">Análise de consumo por período e categoria</p>
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span>Período:</span>
                    <select id="consumption-period" class="border rounded px-2 py-1">
                        <option value="month">Este mês</option>
                        <option value="quarter">Trimestre</option>
                        <option value="year">Ano</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="generateConsumptionReport('csv')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-csv mr-2"></i>
                    CSV
                </button>
                <button onclick="generateConsumptionReport('pdf')" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-pdf mr-2"></i>
                    PDF
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Comparativo de Preços</h3>
                <i class="fas fa-balance-scale text-2xl text-purple-600"></i>
            </div>
            <p class="text-gray-600 mb-4">Compare preços entre fornecedores por produto/CA</p>
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span>Agrupamento:</span>
                    <select id="price-comparison-group" class="border rounded px-2 py-1">
                        <option value="product">Por Produto</option>
                        <option value="ca">Por CA</option>
                        <option value="category">Por Categoria</option>
                    </select>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="generatePriceComparisonReport('csv')" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-csv mr-2"></i>
                    CSV
                </button>
                <button onclick="generatePriceComparisonReport('pdf')" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-pdf mr-2"></i>
                    PDF
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Relatório de Gastos</h3>
                <i class="fas fa-chart-line text-2xl text-green-600"></i>
            </div>
            <p class="text-gray-600 mb-4">Análise financeira de compras e custos</p>
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span>Período:</span>
                    <select id="expenses-period" class="border rounded px-2 py-1">
                        <option value="month">Este mês</option>
                        <option value="quarter">Trimestre</option>
                        <option value="year">Ano</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="generateExpensesReport('csv')" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-csv mr-2"></i>
                    CSV
                </button>
                <button onclick="generateExpensesReport('pdf')" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-pdf mr-2"></i>
                    PDF
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Movimentações</h3>
                <i class="fas fa-exchange-alt text-2xl text-purple-600"></i>
            </div>
            <p class="text-gray-600 mb-4">Histórico de entradas e saídas</p>
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span>Tipo:</span>
                    <select id="movements-type" class="border rounded px-2 py-1">
                        <option value="all">Todos</option>
                        <option value="entrada">Entradas</option>
                        <option value="saida">Saídas</option>
                    </select>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="generateMovementsReport('csv')" class="flex-1 bg-purple-600 hover:bg-purple-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-csv mr-2"></i>
                    CSV
                </button>
                <button onclick="generateMovementsReport('pdf')" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-pdf mr-2"></i>
                    PDF
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Estoque Atual</h3>
                <i class="fas fa-boxes text-2xl text-indigo-600"></i>
            </div>
            <p class="text-gray-600 mb-4">Relatório completo do estoque atual</p>
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span>Categoria:</span>
                    <select id="stock-category" class="border rounded px-2 py-1">
                        <option value="all">Todas</option>
                        <?php foreach (getAllCategories() as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="generateStockReport('csv')" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-csv mr-2"></i>
                    CSV
                </button>
                <button onclick="generateStockReport('pdf')" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-pdf mr-2"></i>
                    PDF
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Produtos Vencendo</h3>
                <i class="fas fa-calendar-times text-2xl text-red-600"></i>
            </div>
            <p class="text-gray-600 mb-4">Produtos próximos ao vencimento</p>
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm">
                    <span>Prazo:</span>
                    <select id="expiry-days" class="border rounded px-2 py-1">
                        <option value="30">30 dias</option>
                        <option value="60">60 dias</option>
                        <option value="90">90 dias</option>
                    </select>
                </div>
            </div>
            <div class="flex space-x-2">
                <button onclick="generateExpiryReport('csv')" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-csv mr-2"></i>
                    CSV
                </button>
                <button onclick="generateExpiryReport('pdf')" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-pdf mr-2"></i>
                    PDF
                </button>
            </div>
        </div>
    </div>

    <!-- Comparativo de Preços em Tempo Real -->
    <div class="bg-white rounded-xl shadow-sm border mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Comparativo de Preços - Produtos Similares</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produto/CA</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fornecedor Mais Barato</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preço Mais Baixo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fornecedor Mais Caro</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preço Mais Alto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diferença</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Economia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($priceComparison as $comparison): ?>
                            <tr>
                                <td class="px-4 py-4 font-medium">
                                    <?= htmlspecialchars($comparison['product_name']) ?>
                                    <?php if ($comparison['ca_certificate']): ?>
                                        <div class="text-xs text-blue-600">CA: <?= htmlspecialchars($comparison['ca_certificate']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-green-600 font-medium">
                                    <?= htmlspecialchars($comparison['cheapest_supplier']) ?>
                                </td>
                                <td class="px-4 py-4 text-green-600 font-bold">
                                    <?= formatCurrency($comparison['min_price']) ?>
                                </td>
                                <td class="px-4 py-4 text-red-600">
                                    <?= htmlspecialchars($comparison['expensive_supplier']) ?>
                                </td>
                                <td class="px-4 py-4 text-red-600 font-bold">
                                    <?= formatCurrency($comparison['max_price']) ?>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <?= formatCurrency($comparison['price_difference']) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <?= number_format($comparison['savings_percentage'], 1) ?>%
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Movimentações por Mês</h3>
                <canvas id="movementsChart" width="400" height="200"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Consumo por Categoria</h3>
                <canvas id="categoryChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>