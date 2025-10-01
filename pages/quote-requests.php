<?php
$quoteRequests = getQuoteRequests();
$suppliers = getAllSuppliers();
?>

<div>
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Solicitações de Orçamento</h1>
            <p class="text-gray-600">Gerencie solicitações de orçamento para fornecedores externos</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="openQuoteRequestModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i>
                Nova Solicitação
            </button>
            <button onclick="generatePublicQuoteForm()" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-external-link-alt mr-2"></i>
                Formulário Público
            </button>
        </div>
    </div>

    <!-- Cards de estatísticas -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total de Solicitações</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count($quoteRequests) ?></p>
                </div>
                <i class="fas fa-file-invoice-dollar text-2xl text-blue-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Aguardando Orçamento</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count(array_filter($quoteRequests, function($r) { return $r['status'] === 'sent'; })) ?></p>
                </div>
                <i class="fas fa-clock text-2xl text-yellow-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Orçamentos Recebidos</p>
                    <p class="text-2xl font-bold text-gray-900"><?= count(array_filter($quoteRequests, function($r) { return $r['status'] === 'quoted'; })) ?></p>
                </div>
                <i class="fas fa-file-invoice text-2xl text-green-600"></i>
            </div>
        </div>

        <div class="bg-white p-6 rounded-xl shadow-sm border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Valor Médio</p>
                    <p class="text-2xl font-bold text-gray-900"><?= formatCurrency(getAverageQuoteValue()) ?></p>
                </div>
                <i class="fas fa-chart-line text-2xl text-purple-600"></i>
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
                        id="search-quotes"
                        placeholder="Buscar solicitações..."
                        class="pl-10 pr-4 py-2 border rounded-lg w-full"
                        onkeyup="filterQuoteRequests()"
                    >
                </div>
                <select id="filter-quote-status" class="border rounded-lg px-3 py-2" onchange="filterQuoteRequests()">
                    <option value="">Todos os status</option>
                    <option value="draft">Rascunho</option>
                    <option value="sent">Enviado</option>
                    <option value="quoted">Orçado</option>
                    <option value="approved">Aprovado</option>
                    <option value="rejected">Rejeitado</option>
                </select>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full" id="quote-requests-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fornecedor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Orçado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Envio</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($quoteRequests as $request): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                #<?= str_pad($request['id'], 4, '0', STR_PAD_LEFT) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                                <?= htmlspecialchars($request['product_name']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= htmlspecialchars($request['supplier_name'] ?? 'Não definido') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= $request['quantity'] ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= $request['quoted_price'] ? formatCurrency($request['quoted_price']) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">
                                <?= $request['sent_at'] ? formatDate($request['sent_at']) : '-' ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getQuoteStatusClass($request['status']) ?>">
                                    <?= getQuoteStatusText($request['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex space-x-2">
                                    <button onclick="viewQuoteRequest(<?= $request['id'] ?>)" 
                                            class="text-green-600 hover:text-green-800 text-sm" title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    
                                    <?php if ($request['status'] === 'draft'): ?>
                                        <button onclick="sendQuoteRequest(<?= $request['id'] ?>)" 
                                                class="text-blue-600 hover:text-blue-800 text-sm" title="Enviar">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button onclick="generateQuoteLink(<?= $request['id'] ?>)" 
                                            class="text-purple-600 hover:text-purple-800 text-sm" title="Link Público">
                                        <i class="fas fa-link"></i>
                                    </button>
                                    
                                    <button onclick="deleteQuoteRequest(<?= $request['id'] ?>)" 
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