// Movements management JavaScript functions

// Open movement modal
function openMovementModal(movementId = null, movementData = null) {
    let modalTitle = movementId ? 'Editar Movimentação' : 'Nova Movimentação';
    
    // Get products for dropdown
    fetch('api/get_products.php')
        .then(response => response.json())
        .then(data => {
            const modalContent = `
                <div class="bg-white rounded-lg p-6 w-full max-w-lg">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold">${modalTitle}</h3>
                        <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form id="movement-form">
                        ${movementId ? `<input type="hidden" name="movement_id" value="${movementId}">` : ''}
                        
                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Produto *</label>
                                <select name="product_id" class="form-select" required>
                                    <option value="">Selecione um produto</option>
                                    ${data.products.map(product => 
                                        `<option value="${product.id}" ${movementData && movementData.product_id == product.id ? 'selected' : ''}>${product.name} (Estoque: ${product.current_stock})</option>`
                                    ).join('')}
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Tipo *</label>
                                    <select name="type" class="form-select" required>
                                        <option value="">Selecione o tipo</option>
                                        <option value="entrada" ${movementData && movementData.type === 'entrada' ? 'selected' : ''}>Entrada</option>
                                        <option value="saida" ${movementData && movementData.type === 'saida' ? 'selected' : ''}>Saída</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="form-label">Quantidade *</label>
                                    <input type="number" name="quantity" class="form-input" min="1" required 
                                           value="${movementData ? movementData.quantity : '1'}">
                                </div>
                            </div>
                            
                            <div>
                                <label class="form-label">Motivo</label>
                                <textarea name="reason" class="form-textarea" rows="3" 
                                          placeholder="Motivo da movimentação...">${movementData ? movementData.reason || '' : ''}</textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal()" class="btn btn-outline">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>
                                Registrar
                            </button>
                        </div>
                    </form>
                </div>
            `;
            
            showModal(modalContent);
            
            // Setup form submission
            document.getElementById('movement-form').addEventListener('submit', function(e) {
                e.preventDefault();
                saveMovement(this);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar produtos', 'error');
        });
}

// Save movement
function saveMovement(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Registrando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_movement.php', {
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
        showToast('Erro ao registrar movimentação', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Filter movements
function filterMovements() {
    const typeFilter = document.getElementById('filter-type').value;
    const productFilter = document.getElementById('filter-product').value;
    const dateStart = document.getElementById('filter-date-start').value;
    const dateEnd = document.getElementById('filter-date-end').value;
    
    const table = document.querySelector('table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        let show = true;
        
        // Type filter
        if (typeFilter) {
            const typeCell = row.cells[2].querySelector('span');
            if (typeCell && !typeCell.textContent.toLowerCase().includes(typeFilter)) {
                show = false;
            }
        }
        
        // Product filter
        if (productFilter && show) {
            const productCell = row.cells[1].textContent;
            // Would need product mapping logic here
        }
        
        // Date filters
        if ((dateStart || dateEnd) && show) {
            const dateCell = row.cells[0].textContent;
            const rowDate = new Date(dateCell.split(' ')[0].split('/').reverse().join('-'));
            
            if (dateStart && rowDate < new Date(dateStart)) show = false;
            if (dateEnd && rowDate > new Date(dateEnd)) show = false;
        }
        
        row.style.display = show ? '' : 'none';
    }
}

// Clear filters
function clearFilters() {
    document.getElementById('filter-type').value = '';
    document.getElementById('filter-product').value = '';
    document.getElementById('filter-date-start').value = '';
    document.getElementById('filter-date-end').value = '';
    filterMovements();
}

// Export movements
function exportMovements() {
    exportData('api/export_movements.php', 'movimentacoes.csv');
}