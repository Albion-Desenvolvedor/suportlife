// Purchase Requests management JavaScript functions

// Open purchase request modal
function openPurchaseRequestModal(requestId = null, requestData = null) {
    let modalTitle = requestId ? 'Editar Solicitação de Compra' : 'Nova Solicitação de Compra';
    
    // Get form data for dropdowns
    fetch('api/get_form_data.php')
        .then(response => response.json())
        .then(data => {
            const modalContent = `
                <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold">${modalTitle}</h3>
                        <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form id="purchase-request-form">
                        ${requestId ? `<input type="hidden" name="request_id" value="${requestId}">` : ''}
                        
                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Nome do Produto *</label>
                                <input type="text" name="product_name" class="form-input" required 
                                       value="${requestData ? requestData.product_name : ''}"
                                       placeholder="Nome do produto a ser comprado">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Categoria *</label>
                                    <select name="category_id" class="form-select" required>
                                        <option value="">Selecione uma categoria</option>
                                        ${data.categories.map(cat => 
                                            `<option value="${cat.id}" ${requestData && requestData.category_id == cat.id ? 'selected' : ''}>${cat.name}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="form-label">Quantidade *</label>
                                    <input type="number" name="quantity" class="form-input" min="1" required 
                                           value="${requestData ? requestData.quantity : '1'}">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Custo Estimado</label>
                                    <input type="number" name="estimated_cost" class="form-input" step="0.01" min="0" 
                                           value="${requestData ? requestData.estimated_cost : '0.00'}">
                                </div>
                                
                                <div>
                                    <label class="form-label">Urgência</label>
                                    <select name="urgency" class="form-select">
                                        <option value="low" ${requestData && requestData.urgency === 'low' ? 'selected' : ''}>Baixa</option>
                                        <option value="medium" ${requestData && requestData.urgency === 'medium' ? 'selected' : 'selected'}>Média</option>
                                        <option value="high" ${requestData && requestData.urgency === 'high' ? 'selected' : ''}>Alta</option>
                                        <option value="critical" ${requestData && requestData.urgency === 'critical' ? 'selected' : ''}>Crítica</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label class="form-label">Fornecedor Sugerido</label>
                                <select name="suggested_supplier_id" class="form-select">
                                    <option value="">Selecione um fornecedor</option>
                                    ${data.suppliers.map(sup => 
                                        `<option value="${sup.id}" ${requestData && requestData.suggested_supplier_id == sup.id ? 'selected' : ''}>${sup.name}</option>`
                                    ).join('')}
                                </select>
                            </div>
                            
                            <div>
                                <label class="form-label">Justificativa *</label>
                                <textarea name="justification" class="form-textarea" rows="3" required 
                                          placeholder="Justifique a necessidade desta compra...">${requestData ? requestData.justification || '' : ''}</textarea>
                            </div>
                            
                            <div>
                                <label class="form-label">Especificações Técnicas</label>
                                <textarea name="specifications" class="form-textarea" rows="3" 
                                          placeholder="Especificações técnicas do produto...">${requestData ? requestData.specifications || '' : ''}</textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal()" class="btn btn-outline">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>
                                ${requestId ? 'Atualizar' : 'Solicitar'}
                            </button>
                        </div>
                    </form>
                </div>
            `;
            
            showModal(modalContent);
            
            // Setup form submission
            document.getElementById('purchase-request-form').addEventListener('submit', function(e) {
                e.preventDefault();
                savePurchaseRequest(this);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar dados do formulário', 'error');
        });
}

// Save purchase request
function savePurchaseRequest(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_purchase_request.php', {
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
        showToast('Erro ao salvar solicitação', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Approve purchase request
function approvePurchaseRequest(requestId) {
    confirmAction('Deseja aprovar esta solicitação de compra?', () => {
        fetch('api/approve_purchase_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ request_id: requestId })
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
            showToast('Erro ao aprovar solicitação', 'error');
        });
    });
}

// View purchase request
function viewPurchaseRequest(requestId) {
    fetch(`api/get_purchase_request.php?id=${requestId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showPurchaseRequestDetails(data.request);
            } else {
                showToast('Erro ao carregar solicitação: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar solicitação', 'error');
        });
}

// Delete purchase request
function deletePurchaseRequest(requestId) {
    confirmAction('Tem certeza que deseja excluir esta solicitação de compra?', () => {
        fetch('api/delete_purchase_request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ request_id: requestId })
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
            showToast('Erro ao excluir solicitação', 'error');
        });
    });
}

// Filter purchase requests
function filterPurchaseRequests() {
    const statusFilter = document.getElementById('filter-status').value;
    const categoryFilter = document.getElementById('filter-category').value;
    const dateStart = document.getElementById('filter-date-start').value;
    const dateEnd = document.getElementById('filter-date-end').value;
    
    filterTable('purchase-requests-table', '', {
        status: statusFilter,
        category: categoryFilter,
        dateStart: dateStart,
        dateEnd: dateEnd
    });
}