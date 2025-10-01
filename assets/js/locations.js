// Locations management JavaScript functions

// Open location modal
function openLocationModal(locationId = null, locationData = null) {
    let modalTitle = locationId ? 'Editar Localização' : 'Nova Localização';
    
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">${modalTitle}</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="location-form">
                ${locationId ? `<input type="hidden" name="location_id" value="${locationId}">` : ''}
                
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Nome da Localização *</label>
                        <input type="text" name="name" class="form-input" required 
                               value="${locationData ? locationData.name : ''}"
                               placeholder="Ex: Prateleira A1, Armário B2">
                    </div>
                    
                    <div>
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-textarea" rows="3" 
                                  placeholder="Descrição detalhada da localização...">${locationData ? locationData.description || '' : ''}</textarea>
                    </div>
                    
                    <div>
                        <label class="form-label">Status</label>
                        <select name="active" class="form-select">
                            <option value="1" ${!locationData || locationData.active ? 'selected' : ''}>Ativa</option>
                            <option value="0" ${locationData && !locationData.active ? 'selected' : ''}>Inativa</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="btn btn-outline">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        ${locationId ? 'Atualizar' : 'Cadastrar'}
                    </button>
                </div>
            </form>
        </div>
    `;
    
    showModal(modalContent);
    
    // Setup form submission
    document.getElementById('location-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveLocation(this);
    });
}

// Save location
function saveLocation(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_location.php', {
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
        showToast('Erro ao salvar localização', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Edit location
function editLocation(locationId) {
    fetch(`api/get_location.php?id=${locationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openLocationModal(locationId, data.location);
            } else {
                showToast('Erro ao carregar localização: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar localização', 'error');
        });
}

// View location details
function viewLocationDetails(locationId) {
    fetch(`api/get_location_products.php?id=${locationId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showLocationDetails(data.location, data.products);
            } else {
                showToast('Erro ao carregar detalhes: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar detalhes', 'error');
        });
}

// Show location details
function showLocationDetails(location, products) {
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Localização: ${location.name}</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                
                ${location.description ? 
                    `<div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-gray-600">${location.description}</p>
                    </div>` : ''
                }
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Categoria</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estoque</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preço</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        ${products.map(product => `
                            <tr>
                                <td class="px-4 py-4 font-medium">${product.name}</td>
                                <td class="px-4 py-4">${product.category_name || 'Sem categoria'}</td>
                                <td class="px-4 py-4">${product.current_stock}</td>
                                <td class="px-4 py-4">R$ ${parseFloat(product.price).toFixed(2).replace('.', ',')}</td>
                                <td class="px-4 py-4">R$ ${(parseInt(product.current_stock) * parseFloat(product.price)).toFixed(2).replace('.', ',')}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            
            ${products.length === 0 ? 
                `<div class="text-center py-8">
                    <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">Nenhum produto nesta localização</p>
                </div>` : ''
            }
            
            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="editLocation(${location.id})" class="btn btn-primary">
                    <i class="fas fa-edit mr-2"></i>
                    Editar Localização
                </button>
                <button onclick="closeModal()" class="btn btn-outline">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    showModal(modalContent);
}

// View location products (alias for compatibility)
function viewLocationProducts(locationId) {
    viewLocationDetails(locationId);
}

// Toggle location status
function toggleLocationStatus(locationId) {
    confirmAction('Deseja alterar o status desta localização?', () => {
        fetch('api/toggle_location_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ location_id: locationId })
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

// Filter locations
function filterLocations() {
    const searchTerm = document.getElementById('search-locations').value;
    filterTable('locations-table', searchTerm);
}

// Export locations
function exportLocations() {
    exportData('api/export_locations.php', 'localizacoes.csv');
}