// Quote Requests management JavaScript functions

// Open quote request modal
function openQuoteRequestModal(requestId = null, requestData = null) {
    let modalTitle = requestId ? 'Editar Solicitação de Orçamento' : 'Nova Solicitação de Orçamento';
    
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
                    
                    <form id="quote-request-form">
                        ${requestId ? `<input type="hidden" name="request_id" value="${requestId}">` : ''}
                        
                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Nome do Produto *</label>
                                <input type="text" name="product_name" class="form-input" required 
                                       value="${requestData ? requestData.product_name : ''}"
                                       placeholder="Nome do produto para orçamento">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Categoria</label>
                                    <select name="category_id" class="form-select">
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
                            
                            <div>
                                <label class="form-label">Fornecedores para Orçamento</label>
                                <div class="space-y-2 max-h-32 overflow-y-auto border rounded-lg p-3">
                                    ${data.suppliers.map(supplier => `
                                        <label class="flex items-center">
                                            <input type="checkbox" name="supplier_ids[]" value="${supplier.id}" 
                                                   class="mr-2" ${requestData && requestData.supplier_ids && requestData.supplier_ids.includes(supplier.id.toString()) ? 'checked' : ''}>
                                            <span class="text-sm">${supplier.name}</span>
                                        </label>
                                    `).join('')}
                                </div>
                            </div>
                            
                            <div>
                                <label class="form-label">Especificações Técnicas *</label>
                                <textarea name="specifications" class="form-textarea" rows="4" required 
                                          placeholder="Descreva as especificações técnicas detalhadas do produto...">${requestData ? requestData.specifications || '' : ''}</textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="form-label">Data Limite para Orçamento</label>
                                    <input type="date" name="deadline" class="form-input" 
                                           value="${requestData && requestData.deadline ? requestData.deadline : ''}">
                                </div>
                                
                                <div>
                                    <label class="form-label">Orçamento Máximo</label>
                                    <input type="number" name="max_budget" class="form-input" step="0.01" min="0" 
                                           value="${requestData ? requestData.max_budget || '' : ''}"
                                           placeholder="Valor máximo aceitável">
                                </div>
                            </div>
                            
                            <div>
                                <label class="form-label">Observações</label>
                                <textarea name="observations" class="form-textarea" rows="3" 
                                          placeholder="Observações adicionais para os fornecedores...">${requestData ? requestData.observations || '' : ''}</textarea>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal()" class="btn btn-outline">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>
                                ${requestId ? 'Atualizar' : 'Criar Solicitação'}
                            </button>
                        </div>
                    </form>
                </div>
            `;
            
            showModal(modalContent);
            
            // Setup form submission
            document.getElementById('quote-request-form').addEventListener('submit', function(e) {
                e.preventDefault();
                saveQuoteRequest(this);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar dados do formulário', 'error');
        });
}

// Save quote request
function saveQuoteRequest(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_quote_request.php', {
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

// Send quote request to suppliers
function sendQuoteRequest(requestId) {
    confirmAction('Deseja enviar esta solicitação para os fornecedores selecionados?', () => {
        fetch('api/send_quote_request.php', {
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
            showToast('Erro ao enviar solicitação', 'error');
        });
    });
}

// Generate public quote form
function generatePublicQuoteForm() {
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Formulário Público de Orçamento</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="font-medium text-blue-900 mb-2">Link do Formulário Público</h4>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="public-form-link" class="form-input text-sm" 
                               value="${window.location.origin}/public-quote-form.php" readonly>
                        <button onclick="copyToClipboard('public-form-link')" class="btn btn-outline">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p class="text-sm text-blue-700 mt-2">
                        Compartilhe este link com fornecedores para que possam enviar orçamentos diretamente.
                    </p>
                </div>
                
                <div class="bg-green-50 p-4 rounded-lg">
                    <h4 class="font-medium text-green-900 mb-2">QR Code</h4>
                    <div class="text-center">
                        <div id="qr-code" class="inline-block p-4 bg-white rounded border"></div>
                        <p class="text-sm text-green-700 mt-2">
                            Escaneie para acessar o formulário
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end mt-6">
                <button onclick="closeModal()" class="btn btn-outline">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    showModal(modalContent);
    
    // Generate QR Code (simplified version)
    generateQRCode();
}

// Generate quote link for specific request
function generateQuoteLink(requestId) {
    const link = `${window.location.origin}/quote-form.php?token=${btoa(requestId)}`;
    
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Link de Orçamento</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="form-label">Link para Fornecedores</label>
                    <div class="flex items-center space-x-2">
                        <input type="text" id="quote-link" class="form-input text-sm" value="${link}" readonly>
                        <button onclick="copyToClipboard('quote-link')" class="btn btn-outline">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">
                        Envie este link para fornecedores específicos para esta solicitação.
                    </p>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6">
                <button onclick="sendQuoteByEmail(${requestId})" class="btn btn-primary">
                    <i class="fas fa-envelope mr-2"></i>
                    Enviar por Email
                </button>
                <button onclick="closeModal()" class="btn btn-outline">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    showModal(modalContent);
}

// Filter quote requests
function filterQuoteRequests() {
    const searchTerm = document.getElementById('search-quotes').value;
    const statusFilter = document.getElementById('filter-quote-status').value;
    
    filterTable('quote-requests-table', searchTerm, {
        status: statusFilter
    });
}

// Copy to clipboard utility
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    document.execCommand('copy');
    showToast('Link copiado para a área de transferência!', 'success');
}

// Generate simple QR code placeholder
function generateQRCode() {
    const qrContainer = document.getElementById('qr-code');
    qrContainer.innerHTML = `
        <div class="w-32 h-32 bg-gray-200 border-2 border-dashed border-gray-400 flex items-center justify-center">
            <i class="fas fa-qrcode text-4xl text-gray-400"></i>
        </div>
        <p class="text-xs text-gray-500 mt-2">QR Code seria gerado aqui</p>
    `;
}