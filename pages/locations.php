<?php
$locations = getAllLocations();
$locationStats = getLocationStats();
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Localizações</h1>
            <p class="text-gray-600">Gerencie as localizações do almoxarifado</p>
        </div>
        <button onclick="openLocationModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Nova Localização
        </button>
    </div>

    <!-- Cards de estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total de Localizações</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count($locations) ?></p>
                </div>
                <i class="fas fa-map-marker-alt text-2xl text-blue-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Localizações Ativas</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count(array_filter($locations, function($l) { return $l['active']; })) ?></p>
                </div>
                <i class="fas fa-check-circle text-2xl text-green-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Produtos Armazenados</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $locationStats['total_products'] ?></p>
                </div>
                <i class="fas fa-boxes text-2xl text-orange-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Localizações Vazias</p>
                    <p class="text-2xl font-bold text-gray-900"><?= $locationStats['empty_locations'] ?></p>
                </div>
                <i class="fas fa-inbox text-2xl text-gray-600"></i>
            </div>
        </div>
    </div>

    <!-- Mapa de localizações -->
    <div class="bg-white rounded-xl shadow-sm border mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Mapa do Almoxarifado</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <?php foreach ($locations as $location): ?>
                    <?php $productCount = getProductCountByLocation($location['id']); ?>
                    <div class="border-2 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer <?= $productCount > 0 ? 'border-blue-200 bg-blue-50' : 'border-gray-200 bg-gray-50' ?>"
                         onclick="viewLocationDetails(<?= $location['id'] ?>)">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-medium text-gray-900"><?= htmlspecialchars($location['name']) ?></h4>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?= $location['active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $location['active'] ? 'Ativa' : 'Inativa' ?>
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2"><?= htmlspecialchars($location['description'] ?? 'Sem descrição') ?></p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">
                                <i class="fas fa-boxes mr-1"></i>
                                <?= $productCount ?> produtos
                            </span>
                            <div class="flex space-x-1">
                                <button onclick="event.stopPropagation(); editLocation(<?= $location['id'] ?>)" class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="event.stopPropagation(); viewLocationProducts(<?= $location['id'] ?>)" class="text-green-600 hover:text-green-800">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Tabela de localizações -->
    <div class="bg-white rounded-xl shadow-sm border">
        <div class="p-4 border-b">
            <div class="flex items-center space-x-4">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        id="search-locations"
                        placeholder="Buscar localizações..."
                        class="pl-10 pr-4 py-2 border rounded-lg w-full"
                        onkeyup="filterLocations()"
                    >
                </div>
                <button onclick="exportLocations()" class="flex items-center px-3 py-2 border rounded-lg hover:bg-gray-50">
                    <i class="fas fa-download mr-2"></i>
                    Exportar
                </button>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full" id="locations-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produtos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado em</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($locations as $location): ?>
                        <?php $productCount = getProductCountByLocation($location['id']); ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas fa-map-marker-alt text-blue-600 mr-3"></i>
                                    <div class="font-medium text-gray-900"><?= htmlspecialchars($location['name']) ?></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                <?= htmlspecialchars($location['description'] ?? 'Sem descrição') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $productCount > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                                    <?= $productCount ?> produtos
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $location['active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $location['active'] ? 'Ativa' : 'Inativa' ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= formatDate($location['created_at']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <button onclick="editLocation(<?= $location['id'] ?>)" class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="viewLocationProducts(<?= $location['id'] ?>)" class="text-green-600 hover:text-green-800 text-sm">
                                        <i class="fas fa-boxes"></i>
                                    </button>
                                    <button onclick="toggleLocationStatus(<?= $location['id'] ?>)" class="text-gray-600 hover:text-gray-800 text-sm">
                                        <i class="fas fa-<?= $location['active'] ? 'ban' : 'check' ?>"></i>
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