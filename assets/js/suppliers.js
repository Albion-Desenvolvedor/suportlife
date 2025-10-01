// Suppliers management JavaScript functions

// Toggle supplier delete menu
function toggleSupplierDeleteMenu(supplierId) {
    const menu = document.getElementById(`supplier-delete-menu-${supplierId}`);
    
    // Close all other menus
    document.querySelectorAll('[id^="supplier-delete-menu-"]').forEach(m => {
        if (m.id !== `supplier-delete-menu-${supplierId}`) {
            m.classList.add('hidden');
        }
    });
    
    menu.classList.toggle('hidden');
    
    // Close menu when clicking outside
    document.addEventListener('click', function closeMenu(e) {
        if (!e.target.closest(`#supplier-delete-menu-${supplierId}`) && !e.target.closest(`button[onclick="toggleSupplierDeleteMenu(${supplierId})"]`)) {
            menu.classList.add('hidden');
            document.removeEventListener('click', closeMenu);
        }
    });
}

// Delete supplier with options
function deleteSupplier(supplierId, deleteType = 'supplier') {
    const confirmMessage = deleteType === 'all' 
        ? 'ATENÇÃO: Esta ação irá excluir o fornecedor e TODOS os produtos relacionados (incluindo movimentações, solicitações, etc.). Esta ação não pode ser desfeita. Tem certeza?'
        : 'Deseja excluir este fornecedor? Se houver produtos cadastrados, o fornecedor será apenas desativado.';
    
    confirmAction(confirmMessage, () => {
        fetch('api/delete_supplier.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                supplier_id: supplierId,
                delete_type: deleteType
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                location.reload();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao excluir fornecedor', 'error');
        });
    });
}

// Open supplier modal
function openSupplierModal(supplierId = null, supplierData = null) {
    let modalTitle = supplierId ? 'Editar Fornecedor' : 'Novo Fornecedor';
    
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">${modalTitle}</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="supplier-form">
                ${supplierId ? `<input type="hidden" name="supplier_id" value="${supplierId}">` : ''}
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nome da Empresa *</label>
                        <input type="text" name="name" class="form-input" required 
                               value="${supplierData ? supplierData.name : ''}">
                    </div>
                    
                    <div>
                        <label class="form-label">Pessoa de Contato</label>
                        <input type="text" name="contact_person" class="form-input" 
                               value="${supplierData ? supplierData.contact_person || '' : ''}">
                    </div>
                    
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-input" 
                               value="${supplierData ? supplierData.email || '' : ''}">
                    </div>
                    
                    <div>
                        <label class="form-label">Telefone</label>
                        <input type="text" name="phone" class="form-input" 
                               value="${supplierData ? supplierData.phone || '' : ''}"
                               onkeyup="formatPhone(this)">
                    </div>
                    
                    <div>
                        <label class="form-label">CNPJ</label>
                        <input type="text" name="cnpj" class="form-input" 
                               value="${supplierData ? supplierData.cnpj || '' : ''}"
                               onkeyup="formatCNPJ(this)">
                    </div>
                    
                    <div>
                        <label class="form-label">Status</label>
                        <select name="active" class="form-select">
                            <option value="1" ${!supplierData || supplierData.active ? 'selected' : ''}>Ativo</option>
                            <option value="0" ${supplierData && !supplierData.active ? 'selected' : ''}>Inativo</option>
                        </select>
                    </div>
                </div>
                
                <div class="mt-4">
                    <label class="form-label">Endereço</label>
                    <textarea name="address" class="form-textarea" rows="3" 
                              placeholder="Endereço completo do fornecedor...">${supplierData ? supplierData.address || '' : ''}</textarea>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="btn btn-outline">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        ${supplierId ? 'Atualizar' : 'Cadastrar'}
                    </button>
                </div>
            </form>
        </div>
    `;
    
    showModal(modalContent);
    
    // Setup form submission
    document.getElementById('supplier-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveSupplier(this);
    });
}

// Save supplier
function saveSupplier(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_supplier.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeModal();
            location.reload();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Erro ao salvar fornecedor', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Edit supplier
function editSupplier(supplierId) {
    fetch(`api/get_supplier.php?id=${supplierId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openSupplierModal(supplierId, data.supplier);
            } else {
                showToast('Erro ao carregar fornecedor: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar fornecedor', 'error');
        });
}

// View supplier products
function viewSupplierProducts(supplierId) {
    fetch(`api/get_supplier_products.php?id=${supplierId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSupplierProducts(data.supplier, data.products);
            } else {
                showToast('Erro ao carregar produtos: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar produtos', 'error');
        });
}

// Show supplier products
function showSupplierProducts(supplier, products) {
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Produtos - ${supplier.name}</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="font-medium text-blue-900">Total de Produtos</h4>
                    <p class="text-2xl font-bold text-blue-600">${products.length}</p>
                </div>
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="font-medium text-green-900">Itens em Estoque</h4>
                    <p class="text-2xl font-bold text-green-600">${products.reduce((sum, p) => sum + parseInt(p.current_stock), 0)}</p>
                </div>
                <div class="bg-purple-50 p-4 rounded-lg">
                    <h4 class="font-medium text-purple-900">Valor Total</h4>
                    <p class="text-2xl font-bold text-purple-600">R$ ${products.reduce((sum, p) => sum + (parseInt(p.current_stock) * parseFloat(p.price)), 0).toFixed(2).replace('.', ',')}</p>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estoque</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Localização</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preço</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">CA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        ${products.map(product => `
                            <tr>
                                <td class="px-4 py-4 font-medium">${product.name}</td>
                                <td class="px-4 py-4">${product.category_name || 'Sem categoria'}</td>
                                <td class="px-4 py-4">${product.current_stock}</td>
                                <td class="px-4 py-4">${product.location_name || 'Sem localização'}</td>
                                <td class="px-4 py-4">R$ ${parseFloat(product.price).toFixed(2).replace('.', ',')}</td>
                                <td class="px-4 py-4">${product.ca_certificate || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            
            ${products.length === 0 ? 
                `<div class="text-center py-8">
                    <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">Nenhum produto cadastrado para este fornecedor</p>
                </div>` : ''
            }
            
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="editSupplier(${supplier.id})" class="btn btn-primary">
                    <i class="fas fa-edit mr-2"></i>
                    Editar Fornecedor
                </button>
                <button onclick="closeModal()" class="btn btn-outline">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    showModal(modalContent);
}

// Toggle supplier status
function toggleSupplierStatus(supplierId) {
    confirmAction('Deseja alterar o status deste fornecedor?', () => {
        fetch('api/toggle_supplier_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ supplier_id: supplierId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                location.reload();
            } else {
                showToast('Erro ao alterar status: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao alterar status', 'error');
        });
    });
}

// Filter suppliers
function filterSuppliers() {
    const searchTerm = document.getElementById('search-suppliers').value;
    filterTable('suppliers-table', searchTerm);
}

// Export suppliers
function exportSuppliers() {
    exportData('api/export_suppliers.php', 'fornecedores.csv');
}