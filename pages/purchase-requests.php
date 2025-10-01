<?php
$purchaseRequests = getPurchaseRequests();
$suppliers = getAllSuppliers();
$categories = getAllCategories();
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Solicitações de Compra</h1>
            <p class="text-gray-600">Gerencie solicitações de compra de novos produtos</p>
        </div>
        <button onclick="openPurchaseRequestModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Nova Solicitação de Compra
        </button>
    </div>

    <!-- Cards de estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total de Solicitações</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count($purchaseRequests) ?></p>
                </div>
                <i class="fas fa-shopping-bag text-2xl text-blue-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Pendentes</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count(array_filter($purchaseRequests, function($r) { return $r['status'] === 'pending'; })) ?></p>
                </div>
                <i class="fas fa-clock text-2xl text-yellow-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Aprovadas</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count(array_filter($purchaseRequests, function($r) { return $r['status'] === 'approved'; })) ?></p>
                </div>
                <i class="fas fa-check-circle text-2xl text-green-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Valor Total</p>
                    <p class="text-2xl font-bold text-gray-900"><?= formatCurrency(array_sum(array_map(function($r) { return $r['estimated_cost']; }, $purchaseRequests))) ?></p>
                </div>
                <i class="fas fa-dollar-sign text-2xl text-purple-600"></i>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-sm border mb-6">
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="filter-status" class="w-full border rounded-lg px-3 py-2" onchange="filterPurchaseRequests()">
                        <option value="">Todos</option>
                        <option value="pending">Pendente</option>
                        <option value="approved">Aprovado</option>
                        <option value="ordered">Pedido Feito</option>
                        <option value="received">Recebido</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <select id="filter-category" class="w-full border rounded-lg px-3 py-2" onchange="filterPurchaseRequests()">
                        <option value="">Todas</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
                    <input type="date" id="filter-date-start" class="w-full border rounded-lg px-3 py-2" onchange="filterPurchaseRequests()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
                    <input type="date" id="filter-date-end" class="w-full border rounded-lg px-3 py-2" onchange="filterPurchaseRequests()">
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border">
        <div class="overflow-x-auto">
            <table class="w-full" id="purchase-requests-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Custo Estimado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($purchaseRequests as $request): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?= str_pad($request['id'], 4, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                <?= htmlspecialchars($request['product_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($request['category_name'] ?? 'Sem categoria') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= $request['quantity'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= formatCurrency($request['estimated_cost']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($request['user_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= formatDate($request['created_at']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getPurchaseStatusClass($request['status']) ?>">
                                    <?= getPurchaseStatusText($request['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <?php if ($request['status'] === 'pending' && hasPermission('manager')): ?>
                                        <button onclick="approvePurchaseRequest(<?= $request['id'] ?>)" 
                                                class="text-blue-600 hover:text-blue-800 text-sm" title="Aprovar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button onclick="viewPurchaseRequest(<?= $request['id'] ?>)" 
                                            class="text-green-600 hover:text-green-800 text-sm" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <button onclick="deletePurchaseRequest(<?= $request['id'] ?>)" 
                                            class="text-red-600 hover:text-red-800 text-sm" title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>