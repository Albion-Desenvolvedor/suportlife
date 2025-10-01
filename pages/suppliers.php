<?php
$suppliers = getAllSuppliers();
$supplierRanking = getSupplierRanking();
$supplierPrices = getSupplierPrices();
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Fornecedores</h1>
            <p class="text-gray-600">Cadastro, ranking e comparativo de preços</p>
        </div>
        <button onclick="openSupplierModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Novo Fornecedor
        </button>
    </div>

    <!-- Cards de estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total de Fornecedores</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count($suppliers) ?></p>
                </div>
                <i class="fas fa-truck text-2xl text-blue-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Fornecedores Ativos</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count(array_filter($suppliers, function($s) { return $s['active']; })) ?></p>
                </div>
                <i class="fas fa-check-circle text-2xl text-green-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Produtos Fornecidos</p>
                    <p class="text-2xl font-bold text-gray-900"><?= array_sum(array_column($supplierRanking, 'product_count')) ?></p>
                </div>
                <i class="fas fa-boxes text-2xl text-orange-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Melhor Fornecedor</p>
                    <p class="text-lg font-bold text-gray-900"><?= htmlspecialchars($supplierRanking[0]['name'] ?? 'N/A') ?></p>
                </div>
                <i class="fas fa-crown text-2xl text-yellow-600"></i>
            </div>
        </div>
    </div>

    <!-- Ranking de Fornecedores -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Ranking de Fornecedores</h3>
                <div class="space-y-3">
                    <?php foreach ($supplierRanking as $index => $supplier): ?>
                        <div class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50">
                            <div class="flex items-center">
                                <div class="w-8 h-8 <?= $index === 0 ? 'bg-yellow-100 text-yellow-600' : ($index === 1 ? 'bg-gray-100 text-gray-600' : ($index === 2 ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600')) ?> rounded-full flex items-center justify-center mr-3">
                                    <?php if ($index === 0): ?>
                                        <i class="fas fa-crown"></i>
                                    <?php elseif ($index === 1): ?>
                                        <i class="fas fa-medal"></i>
                                    <?php elseif ($index === 2): ?>
                                        <i class="fas fa-award"></i>
                                    <?php else: ?>
                                        <?= $index + 1 ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="font-medium text-gray-900"><?= htmlspecialchars($supplier['name']) ?></p>
                                    <p class="text-sm text-gray-600"><?= $supplier['product_count'] ?> produtos cadastrados</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900"><?= formatCurrency($supplier['total_value']) ?></p>
                                <p class="text-xs text-gray-500">Valor total</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Comparativo de Preços</h3>
                <div class="space-y-4 max-h-80 overflow-y-auto">
                    <?php 
                    $productPrices = [];
                    foreach ($supplierPrices as $price) {
                        $key = $price['product_name'] . '|' . ($price['ca_certificate'] ?? '');
                        $productPrices[$key][] = $price;
                    }
                    ?>
                    
                    <?php foreach ($productPrices as $key => $prices): ?>
                        <?php 
                        $productInfo = explode('|', $key);
                        $productName = $productInfo[0];
                        $ca = $productInfo[1];
                        ?>
                        <div class="p-4 border rounded-lg">
                            <div class="flex items-center justify-between mb-3">
                                <p class="font-medium"><?= htmlspecialchars($productName) ?></p>
                                <?php if ($ca): ?>
                                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">CA: <?= htmlspecialchars($ca) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="space-y-2">
                                <?php 
                                usort($prices, function($a, $b) {
                                    return $a['price'] <=> $b['price'];
                                });
                                ?>
                                <?php foreach ($prices as $index => $price): ?>
                                    <div class="flex justify-between items-center <?= $index === 0 ? 'text-green-600 font-medium' : '' ?>">
                                        <span class="text-sm flex items-center">
                                            <?= htmlspecialchars($price['supplier_name']) ?>
                                            <?php if ($index === 0): ?>
                                                <i class="fas fa-crown ml-1 text-yellow-500" title="Melhor preço"></i>
                                            <?php elseif ($index === count($prices) - 1 && count($prices) > 1): ?>
                                                <i class="fas fa-exclamation-triangle ml-1 text-red-500" title="Preço mais alto"></i>
                                            <?php endif; ?>
                                        </span>
                                        <div class="text-right">
                                            <span class="text-sm font-medium"><?= formatCurrency($price['price']) ?></span>
                                            <?php if ($index > 0): ?>
                                                <div class="text-xs text-red-500">
                                                    +<?= number_format((($price['price'] - $prices[0]['price']) / $prices[0]['price']) * 100, 1) ?>%
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (empty($productPrices)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-chart-line text-gray-400 text-3xl mb-2"></i>
                            <p class="text-gray-500">Nenhum comparativo disponível</p>
                            <p class="text-sm text-gray-400">Cadastre preços para diferentes fornecedores</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista completa de fornecedores -->
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-4 border-b">
            <div class="flex items-center space-x-4">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        id="search-suppliers"
                        placeholder="Buscar fornecedores..."
                        class="pl-10 pr-4 py-2 border rounded-lg w-full"
                        onkeyup="filterSuppliers()"
                    >
                </div>
                <button onclick="exportSuppliers()" class="flex items-center px-3 py-2 border rounded-lg hover:bg-gray-50">
                    <i class="fas fa-download mr-2"></i>
                    Exportar
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full" id="suppliers-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ranking</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contato</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produtos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($supplierRanking as $index => $supplier): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 <?= $index === 0 ? 'bg-yellow-100 text-yellow-600' : ($index === 1 ? 'bg-gray-100 text-gray-600' : ($index === 2 ? 'bg-orange-100 text-orange-600' : 'bg-blue-100 text-blue-600')) ?> rounded-full flex items-center justify-center">
                                        <?php if ($index === 0): ?>
                                            <i class="fas fa-crown"></i>
                                        <?php elseif ($index === 1): ?>
                                            <i class="fas fa-medal"></i>
                                        <?php elseif ($index === 2): ?>
                                            <i class="fas fa-award"></i>
                                        <?php else: ?>
                                            <?= $index + 1 ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                <?= htmlspecialchars($supplier['name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($supplier['contact_person'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($supplier['email'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($supplier['phone'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= $supplier['product_count'] ?> produtos
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $supplier['active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $supplier['active'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <button onclick="editSupplier(<?= $supplier['id'] ?>)" 
                                            class="text-blue-600 hover:text-blue-800 text-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="viewSupplierProducts(<?= $supplier['id'] ?>)" 
                                            class="text-green-600 hover:text-green-800 text-sm" title="Ver produtos">
                                        <i class="fas fa-boxes"></i>
                                    </button>
                                    <button onclick="toggleSupplierStatus(<?= $supplier['id'] ?>)" 
                                            class="text-gray-600 hover:text-gray-800 text-sm" title="Alterar status">
                                        <i class="fas fa-<?= $supplier['active'] ? 'ban' : 'check' ?>"></i>
                                    </button>
                                    <div class="relative inline-block">
                                        <button onclick="toggleSupplierDeleteMenu(<?= $supplier['id'] ?>)" 
                                                class="text-red-600 hover:text-red-800 text-sm" title="Opções de exclusão">
                                            <i class="fas fa-trash"></i>
                                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                        </button>
                                        <div id="supplier-delete-menu-<?= $supplier['id'] ?>" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                                            <div class="py-1">
                                                <button onclick="deleteSupplier(<?= $supplier['id'] ?>, 'supplier')" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i class="fas fa-truck mr-2"></i>
                                                    Excluir apenas fornecedor
                                                </button>
                                                <button onclick="deleteSupplier(<?= $supplier['id'] ?>, 'all')" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                    <i class="fas fa-trash-alt mr-2"></i>
                                                    Excluir tudo relacionado
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>