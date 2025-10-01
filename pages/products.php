<?php
$products = getAllProducts();
$categories = getAllCategories();
$locations = getAllLocations();
$suppliers = getAllSuppliers();
$totalProducts = count($products);
$lowStockCount = count(getLowStockProducts());
$overStockCount = count(getOverStockProducts());
$totalValue = array_sum(array_map(function($p) { return $p['current_stock'] * $p['price']; }, $products));

// Apply filters if any
$filter = $_GET['filter'] ?? '';
if ($filter === 'low-stock') {
    $products = getLowStockProducts();
} elseif ($filter === 'expiring') {
    $products = getExpiringProducts();
}
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Produtos</h1>
            <p class="text-gray-600">Gerencie o cadastro de produtos do almoxarifado</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="openProductModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Novo Produto
            </button>
            <button onclick="importProducts()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-upload mr-2"></i>
                Importar
            </button>
        </div>
    </div>

    <!-- Cards de estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total de Produtos</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $totalProducts ?></p>
                </div>
                <i class="fas fa-boxes text-2xl text-blue-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Estoque Baixo</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $lowStockCount ?></p>
                </div>
                <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Estoque Alto</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $overStockCount ?></p>
                </div>
                <i class="fas fa-arrow-up text-2xl text-yellow-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Valor Total</p>
                    <p class="text-2xl font-bold text-gray-900"><?= formatCurrency($totalValue) ?></p>
                </div>
                <i class="fas fa-dollar-sign text-2xl text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-4 border-b">
            <div class="flex items-center space-x-4">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        id="search-products"
                        placeholder="Buscar produtos..."
                        class="pl-10 pr-4 py-2 border rounded-lg w-full"
                        onkeyup="filterProducts()"
                    >
                </div>
                <select id="filter-category" class="border rounded-lg px-3 py-2" onchange="filterProducts()">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filter-stock-status" class="border rounded-lg px-3 py-2" onchange="filterProducts()">
                    <option value="">Todos os status</option>
                    <option value="low">Estoque baixo</option>
                    <option value="normal">Estoque normal</option>
                    <option value="high">Estoque alto</option>
                    <option value="expiring">Vencendo</option>
                </select>
                <button onclick="clearProductFilters()" class="flex items-center px-3 py-2 border rounded-lg hover:bg-gray-50">
                    <i class="fas fa-filter mr-2"></i>
                    Limpar
                </button>
                <button onclick="exportProducts()" class="flex items-center px-3 py-2 border rounded-lg hover:bg-gray-50">
                    <i class="fas fa-download mr-2"></i>
                    Exportar
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full" id="products-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estoque</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localização</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preço</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CA</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <?php if ($product['photo']): ?>
                                        <img src="uploads/products/<?= htmlspecialchars($product['photo']) ?>" 
                                             alt="<?= htmlspecialchars($product['name']) ?>" 
                                             class="w-10 h-10 rounded-lg object-cover mr-3">
                                    <?php else: ?>
                                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-box text-gray-400"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-medium text-gray-900"><?= htmlspecialchars($product['name']) ?></div>
                                        <?php if ($product['barcode']): ?>
                                            <div class="text-xs text-gray-500"><?= htmlspecialchars($product['barcode']) ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($product['category_name'] ?? 'Sem categoria') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $stockClass = 'bg-green-100 text-green-800';
                                if ($product['current_stock'] <= $product['min_stock']) {
                                    $stockClass = 'bg-red-100 text-red-800';
                                } elseif ($product['current_stock'] >= $product['max_stock']) {
                                    $stockClass = 'bg-yellow-100 text-yellow-800';
                                }
                                ?>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $stockClass ?>">
                                    <?= $product['current_stock'] ?>
                                </span>
                                <div class="text-xs text-gray-500 mt-1">
                                    Min: <?= $product['min_stock'] ?> | Max: <?= $product['max_stock'] ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?= htmlspecialchars($product['location_name'] ?? 'Sem localização') ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($product['condition_status']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= formatCurrency($product['price']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($product['ca_certificate'] ?? '-') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?php if ($product['validity_date']): ?>
                                    <?= formatDate($product['validity_date']) ?>
                                    <?php if (strtotime($product['validity_date']) < strtotime('+30 days')): ?>
                                        <i class="fas fa-exclamation-triangle text-yellow-500 ml-1" title="Vence em breve"></i>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <button onclick="editProduct(<?= $product['id'] ?>)" 
                                            class="text-blue-600 hover:text-blue-800 text-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="viewProduct(<?= $product['id'] ?>)" 
                                            class="text-green-600 hover:text-green-800 text-sm" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="duplicateProduct(<?= $product['id'] ?>)" 
                                            class="text-purple-600 hover:text-purple-800 text-sm" title="Duplicar">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button onclick="printBarcode(<?= $product['id'] ?>)" 
                                            class="text-gray-600 hover:text-gray-800 text-sm" title="Imprimir código">
                                        <i class="fas fa-print"></i>
                                    </button>
                                    <div class="relative inline-block">
                                        <button onclick="toggleDeleteMenu(<?= $product['id'] ?>)" 
                                                class="text-red-600 hover:text-red-800 text-sm" title="Opções de exclusão">
                                            <i class="fas fa-trash"></i>
                                            <i class="fas fa-chevron-down ml-1 text-xs"></i>
                                        </button>
                                        <div id="delete-menu-<?= $product['id'] ?>" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border">
                                            <div class="py-1">
                                                <button onclick="deleteProduct(<?= $product['id'] ?>, 'product')" 
                                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                    <i class="fas fa-box mr-2"></i>
                                                    Excluir apenas produto
                                                </button>
                                                <button onclick="deleteProduct(<?= $product['id'] ?>, 'all')" 
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