// Requests management JavaScript functions

// Open request modal
function openRequestModal(requestId = null, requestData = null) {
    let modalTitle = requestId ? 'Editar Solicitação' : 'Nova Solicitação';
    
    // Get form data for dropdowns
    fetch('api/get_request_form_data.php')
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
                    
                    <form id="request-form">
                        ${requestId ? `<input type="hidden" name="request_id" value="${requestId}">` : ''}
                        
                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Produto *</label>
                                <select name="product_id" class="form-select" required onchange="updateProductInfo(this.value)">
                                    <option value="">Selecione um produto</option>
                                    ${data.products.map(product => 
                                        `<option value="${product.id}" ${requestData && requestData.product_id == product.id ? 'selected' : ''}>${product.name} (Estoque: ${product.current_stock})</option>`
                                    ).join('')}
                                </select>
                            </div>
                            
                            <div id="product-info" class="hidden bg-blue-50 p-3 rounded-lg">
                                <p class="text-sm text-blue-800">Estoque disponível: <span id="available-stock">0</span> unidades</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Quantidade *</label>
                                    <input type="number" name="quantity" class="form-input" min="1" required 
                                           value="${requestData ? requestData.quantity : '1'}">
                                </div>
                                
                                <div>
                                    <label class="form-label">Departamento *</label>
                                    <select name="department_id" class="form-select" required>
                                        <option value="">Selecione um departamento</option>
                                        ${data.departments.map(dept => 
                                            `<option value="${dept.id}" ${requestData && requestData.department_id == dept.id ? 'selected' : ''}>${dept.name}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Data de Retirada</label>
                                    <input type="date" name="pickup_date" class="form-input" 
                                           value="${requestData && requestData.pickup_date ? requestData.pickup_date : new Date().toISOString().split('T')[0]}">
                                </div>
                                
                                <div>
                                    <label class="form-label">Data de Devolução</label>
                                    <input type="date" name="return_date" class="form-input" 
                                           value="${requestData && requestData.return_date ? requestData.return_date : ''}">
                                </div>
                            </div>
                            
                            <div>
                                <label class="form-label">Observações</label>
                                <textarea name="observations" class="form-textarea" rows="3" 
                                          placeholder="Observações sobre a solicitação...">${requestData ? requestData.observations || '' : ''}</textarea>
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
            document.getElementById('request-form').addEventListener('submit', function(e) {
                e.preventDefault();
                saveRequest(this);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar dados do formulário', 'error');
        });
}

// Update product info when product is selected
function updateProductInfo(productId) {
    if (!productId) {
        document.getElementById('product-info').classList.add('hidden');
        return;
    }
    
    fetch(`api/get_product.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const productInfo = document.getElementById('product-info');
                const stockSpan = document.getElementById('available-stock');
                
                stockSpan.textContent = data.product.current_stock;
                productInfo.classList.remove('hidden');
                
                // Update quantity input max value
                const quantityInput = document.querySelector('input[name="quantity"]');
                quantityInput.max = data.product.current_stock;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Save request
function saveRequest(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_request.php', {
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

// Approve request
function approveRequest(requestId) {
    confirmAction('Deseja aprovar esta solicitação?', () => {
        fetch('api/approve_request.php', {
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

// Deliver request
function deliverRequest(requestId) {
    confirmAction('Confirma a entrega deste material?', () => {
        fetch('api/deliver_request.php', {
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
            showToast('Erro ao entregar material', 'error');
        });
    });
}

// Return request
function returnRequest(requestId) {
    confirmAction('Confirma a devolução deste material?', () => {
        fetch('api/return_request.php', {
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
            showToast('Erro ao devolver material', 'error');
        });
    });
}

// Cancel request
function cancelRequest(requestId) {
    confirmAction('Deseja cancelar esta solicitação?', () => {
        fetch('api/cancel_request.php', {
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
            showToast('Erro ao cancelar solicitação', 'error');
        });
    });
}

// Generate responsibility term
function generateTerm(requestId) {
    window.open(`api/generate_term.php?request_id=${requestId}`, '_blank');
}

// View request details
function viewRequest(requestId) {
    fetch(`api/get_request_details.php?id=${requestId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showRequestDetails(data.request);
            } else {
                showToast('Erro ao carregar solicitação: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar solicitação', 'error');
        });
}

// Show request details
function showRequestDetails(request) {
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-3xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Detalhes da Solicitação #${String(request.id).padStart(4, '0')}</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Produto</label>
                        <div class="flex items-center">
                            ${request.product_photo ? 
                                `<img src="uploads/products/${request.product_photo}" alt="${request.product_name}" class="w-12 h-12 object-cover rounded mr-3">` :
                                `<div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center mr-3">
                                    <i class="fas fa-box text-gray-400"></i>
                                </div>`
                            }
                            <div>
                                <p class="font-semibold">${request.product_name}</p>
                                ${request.barcode ? `<p class="text-xs text-gray-500">${request.barcode}</p>` : ''}
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Solicitante</label>
                        <p>${request.user_name}</p>
                        <p class="text-sm text-gray-500">${request.user_email}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Departamento</label>
                        <p>${request.department_name}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantidade</label>
                        <p class="text-lg font-semibold">${request.quantity} unidades</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${getStatusClass(request.status)}">
                            ${getStatusText(request.status)}
                        </span>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data da Solicitação</label>
                        <p>${formatDateTime(request.created_at)}</p>
                    </div>
                    
                    ${request.pickup_date ? 
                        `<div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Retirada</label>
                            <p>${formatDate(request.pickup_date)}</p>
                        </div>` : ''
                    }
                    
                    ${request.return_date ? 
                        `<div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Data de Devolução</label>
                            <p>${formatDate(request.return_date)}</p>
                        </div>` : ''
                    }
                    
                    ${request.approved_by_name ? 
                        `<div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Aprovado por</label>
                            <p>${request.approved_by_name}</p>
                            <p class="text-sm text-gray-500">${formatDateTime(request.approved_at)}</p>
                        </div>` : ''
                    }
                    
                    ${request.delivered_by_name ? 
                        `<div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Entregue por</label>
                            <p>${request.delivered_by_name}</p>
                            <p class="text-sm text-gray-500">${formatDateTime(request.delivered_at)}</p>
                        </div>` : ''
                    }
                </div>
            </div>
            
            ${request.observations ? 
                `<div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                    <p class="text-gray-600 bg-gray-50 p-3 rounded-lg">${request.observations}</p>
                </div>` : ''
            }
            
            <div class="mt-6 flex justify-end space-x-3">
                ${request.status === 'pending' ? 
                    `<button onclick="approveRequest(${request.id})" class="btn btn-success">
                        <i class="fas fa-check mr-2"></i>
                        Aprovar
                    </button>` : ''
                }
                
                ${request.status === 'approved' ? 
                    `<button onclick="deliverRequest(${request.id})" class="btn btn-success">
                        <i class="fas fa-truck mr-2"></i>
                        Entregar
                    </button>` : ''
                }
                
                ${request.status === 'delivered' ? 
                    `<button onclick="returnRequest(${request.id})" class="btn btn-secondary">
                        <i class="fas fa-undo mr-2"></i>
                        Devolver
                    </button>` : ''
                }
                
                <button onclick="generateTerm(${request.id})" class="btn btn-outline">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Gerar Termo
                </button>
                
                <button onclick="closeModal()" class="btn btn-outline">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    showModal(modalContent);
}

// Filter requests
function filterRequests() {
    const statusFilter = document.querySelector('select[onchange="filterRequests()"]').value;
    const departmentFilter = document.querySelectorAll('select[onchange="filterRequests()"]')[1].value;
    const dateStart = document.querySelectorAll('input[onchange="filterRequests()"]')[0].value;
    const dateEnd = document.querySelectorAll('input[onchange="filterRequests()"]')[1].value;
    
    const table = document.querySelector('table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        let show = true;
        
        // Status filter
        if (statusFilter) {
            const statusCell = row.cells[7].querySelector('span');
            if (statusCell && !statusCell.textContent.toLowerCase().includes(getStatusText(statusFilter).toLowerCase())) {
                show = false;
            }
        }
        
        // Department filter
        if (departmentFilter && show) {
            const departmentCell = row.cells[3].textContent;
            // Would need department mapping logic here
        }
        
        // Date filters
        if ((dateStart || dateEnd) && show) {
            const dateCell = row.cells[5].textContent;
            const rowDate = new Date(dateCell.split('/').reverse().join('-'));
            
            if (dateStart && rowDate < new Date(dateStart)) show = false;
            if (dateEnd && rowDate > new Date(dateEnd)) show = false;
        }
        
        row.style.display = show ? '' : 'none';
    }
}