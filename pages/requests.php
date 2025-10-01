<?php
$requests = getRecentRequests(50); // Pegar mais solicitações para a página
$products = getAllProducts();
$departments = getAllDepartments();
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Solicitações de Material</h1>
            <p class="text-gray-600">Gerencie solicitações e aprovações</p>
        </div>
        <button onclick="openRequestModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
            <i class="fas fa-plus mr-2"></i>
            Nova Solicitação
        </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white rounded-xl shadow-sm border mb-6">
        <div class="p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select class="w-full border rounded-lg px-3 py-2" onchange="filterRequests()">
                        <option value="">Todos</option>
                        <option value="pending">Pendente</option>
                        <option value="approved">Aprovado</option>
                        <option value="delivered">Entregue</option>
                        <option value="returned">Devolvido</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                    <select class="w-full border rounded-lg px-3 py-2" onchange="filterRequests()">
                        <option value="">Todos</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Inicial</label>
                    <input type="date" class="w-full border rounded-lg px-3 py-2" onchange="filterRequests()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Final</label>
                    <input type="date" class="w-full border rounded-lg px-3 py-2" onchange="filterRequests()">
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solicitante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Setor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Solicitação</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Devolução</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($requests as $request): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?= str_pad($request['id'], 4, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                <?= htmlspecialchars($request['product_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($request['user_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($request['department_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= $request['quantity'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= formatDate($request['created_at']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= $request['return_date'] ? formatDate($request['return_date']) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getStatusClass($request['status']) ?>">
                                    <?= getStatusText($request['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <?php if ($request['status'] === 'pending'): ?>
                                        <button onclick="approveRequest(<?= $request['id'] ?>)" 
                                                class="text-blue-600 hover:text-blue-800 text-sm">
                                            <i class="fas fa-check"></i> Aprovar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($request['status'] === 'approved'): ?>
                                        <button onclick="deliverRequest(<?= $request['id'] ?>)" 
                                                class="text-green-600 hover:text-green-800 text-sm">
                                            <i class="fas fa-truck"></i> Entregar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button onclick="generateTerm(<?= $request['id'] ?>)" 
                                            class="text-purple-600 hover:text-purple-800 text-sm">
                                        <i class="fas fa-file-pdf"></i> Termo
                                    </button>
                                    
                                    <button onclick="viewRequest(<?= $request['id'] ?>)" 
                                            class="text-gray-600 hover:text-gray-800 text-sm">
                                        <i class="fas fa-eye"></i>
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