<?php
$movements = getAllMovements();
$products = getAllProducts();
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Movimentações de Estoque</h1>
            <p class="text-gray-600">Histórico de entradas e saídas</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="openMovementModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Nova Movimentação
            </button>
            <button onclick="exportMovements()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-download mr-2"></i>
                Exportar
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-sm border mb-6">
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select id="filter-type" class="w-full border rounded-lg px-3 py-2" onchange="filterMovements()">
                        <option value="">Todos</option>
                        <option value="entrada">Entrada</option>
                        <option value="saida">Saída</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Produto</label>
                    <select id="filter-product" class="w-full border rounded-lg px-3 py-2" onchange="filterMovements()">
                        <option value="">Todos</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>"><?= htmlspecialchars($product['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
                    <input type="date" id="filter-date-start" class="w-full border rounded-lg px-3 py-2" onchange="filterMovements()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
                    <input type="date" id="filter-date-end" class="w-full border rounded-lg px-3 py-2" onchange="filterMovements()">
                </div>
                <div class="flex items-end">
                    <button onclick="clearFilters()" class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg">
                        Limpar Filtros
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border">
        <div class="overflow-x-auto">
            <table class="w-full" id="movements-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data/Hora</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referência</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($movements as $movement): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= formatDateTime($movement['created_at']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($movement['product_name']) ?></div>
                                <?php if ($movement['barcode']): ?>
                                    <div class="text-xs text-gray-500"><?= htmlspecialchars($movement['barcode']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $movement['type'] === 'entrada' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <i class="fas fa-<?= $movement['type'] === 'entrada' ? 'arrow-down' : 'arrow-up' ?> mr-1"></i>
                                    <?= ucfirst($movement['type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                <?= $movement['quantity'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= htmlspecialchars($movement['reason'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= htmlspecialchars($movement['user_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?php if ($movement['reference_type'] && $movement['reference_id']): ?>
                                    <span class="text-blue-600">
                                        <?= ucfirst($movement['reference_type']) ?> #<?= $movement['reference_id'] ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>