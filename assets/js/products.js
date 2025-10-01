// Product management JavaScript functions

// Toggle delete menu
function toggleDeleteMenu(productId) {
    const menu = document.getElementById(`delete-menu-${productId}`);
    
    // Close all other menus
    document.querySelectorAll('[id^="delete-menu-"]').forEach(m => {
        if (m.id !== `delete-menu-${productId}`) {
            m.classList.add('hidden');
        }
    });
    
    menu.classList.toggle('hidden');
    
    // Close menu when clicking outside
    document.addEventListener('click', function closeMenu(e) {
        if (!e.target.closest(`#delete-menu-${productId}`) && !e.target.closest(`button[onclick="toggleDeleteMenu(${productId})"]`)) {
            menu.classList.add('hidden');
            document.removeEventListener('click', closeMenu);
        }
    });
}

// Delete product with options
function deleteProduct(productId, deleteType = 'product') {
    const confirmMessage = deleteType === 'all' 
        ? 'ATENÇÃO: Esta ação irá excluir o produto e TODOS os dados relacionados (solicitações, movimentações, termos, etc.). Esta ação não pode ser desfeita. Tem certeza?'
        : 'Deseja excluir este produto? Se houver histórico, o produto será apenas desativado.';
    
    confirmAction(confirmMessage, () => {
        fetch('api/delete_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ 
                product_id: productId,
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
            showToast('Erro ao excluir produto', 'error');
        });
    });
}

// Open product modal
function openProductModal(productId = null, productData = null) {
    let modalTitle = productId ? 'Editar Produto' : 'Novo Produto';
    
    // Get form data for dropdowns
    fetch('api/get_form_data.php')
        .then(response => response.json())
        .then(data => {
            const modalContent = `
                <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-semibold">${modalTitle}</h3>
                        <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    
                    <form id="product-form" enctype="multipart/form-data">
                        ${productId ? `<input type="hidden" name="product_id" value="${productId}">` : ''}
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="form-label">Nome do Produto *</label>
                                    <input type="text" name="name" class="form-input" required 
                                           value="${productData ? productData.name : ''}">
                                </div>
                                
                                <div>
                                    <label class="form-label">Categoria</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">Selecione uma categoria</option>
                                        ${data.categories.map(cat => 
                                            `<option value="${cat.id}" ${productData && productData.category_id == cat.id ? 'selected' : ''}>${cat.name}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                
                                <div class="grid grid-cols-3 gap-3">
                                    <div>
                                        <label class="form-label">Estoque Atual</label>
                                        <input type="number" name="current_stock" class="form-input" min="0" 
                                               value="${productData ? productData.current_stock : '0'}">
                                    </div>
                                    <div>
                                        <label class="form-label">Estoque Mínimo</label>
                                        <input type="number" name="min_stock" class="form-input" min="0" 
                                               value="${productData ? productData.min_stock : '0'}">
                                    </div>
                                    <div>
                                        <label class="form-label">Estoque Máximo</label>
                                        <input type="number" name="max_stock" class="form-input" min="0" 
                                               value="${productData ? productData.max_stock : '0'}">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="form-label">Localização</label>
                                    <select name="location_id" class="form-select">
                                        <option value="">Selecione uma localização</option>
                                        ${data.locations.map(loc => 
                                            `<option value="${loc.id}" ${productData && productData.location_id == loc.id ? 'selected' : ''}>${loc.name}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="form-label">Estado de Conservação</label>
                                    <select name="condition_status" class="form-select">
                                        <option value="Novo" ${productData && productData.condition_status === 'Novo' ? 'selected' : ''}>Novo</option>
                                        <option value="Usado - Bom" ${productData && productData.condition_status === 'Usado - Bom' ? 'selected' : ''}>Usado - Bom</option>
                                        <option value="Usado - Regular" ${productData && productData.condition_status === 'Usado - Regular' ? 'selected' : ''}>Usado - Regular</option>
                                        <option value="Para Descarte" ${productData && productData.condition_status === 'Para Descarte' ? 'selected' : ''}>Para Descarte</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="form-label">Fornecedor</label>
                                    <select name="supplier_id" class="form-select">
                                        <option value="">Selecione um fornecedor</option>
                                        ${data.suppliers.map(sup => 
                                            `<option value="${sup.id}" ${productData && productData.supplier_id == sup.id ? 'selected' : ''}>${sup.name}</option>`
                                        ).join('')}
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="form-label">Preço Unitário</label>
                                    <input type="number" name="price" class="form-input" step="0.01" min="0" 
                                           value="${productData ? productData.price : '0.00'}">
                                </div>
                                
                                <div>
                                    <label class="form-label">Certificado CA</label>
                                    <input type="text" name="ca_certificate" class="form-input" 
                                           value="${productData ? productData.ca_certificate || '' : ''}">
                                </div>
                                
                                <div>
                                    <label class="form-label">Data de Validade</label>
                                    <input type="date" name="validity_date" class="form-input" 
                                           value="${productData && productData.validity_date ? productData.validity_date : ''}">
                                </div>
                                
                                <div>
                                    <label class="form-label">Código de Barras</label>
                                    <input type="text" name="barcode" class="form-input" 
                                           value="${productData ? productData.barcode || '' : ''}">
                                </div>
                                
                                <div>
                                    <label class="form-label">Foto do Produto</label>
                                    <div class="file-upload-area" onclick="document.getElementById('product-photo').click()">
                                        <i class="fas fa-camera text-2xl text-gray-400 mb-2"></i>
                                        <p class="text-gray-500">Clique para adicionar foto</p>
                                        <input type="file" id="product-photo" name="photo" accept="image/*" class="hidden">
                                    </div>
                                    ${productData && productData.photo ? 
                                        `<div class="mt-2">
                                            <img src="uploads/products/${productData.photo}" alt="Foto atual" class="w-20 h-20 object-cover rounded border">
                                        </div>` : ''
                                    }
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <label class="form-label">Descrição</label>
                            <textarea name="description" class="form-textarea" rows="3">${productData ? productData.description || '' : ''}</textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal()" class="btn btn-outline">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>
                                ${productId ? 'Atualizar' : 'Cadastrar'}
                            </button>
                        </div>
                    </form>
                </div>
            `;
            
            showModal(modalContent);
            
            // Setup form submission
            document.getElementById('product-form').addEventListener('submit', function(e) {
                e.preventDefault();
                saveProduct(this);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar dados do formulário', 'error');
        });
}

// Save product
function saveProduct(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_product.php', {
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
        showToast('Erro ao salvar produto', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Filter products
function filterProducts() {
    const searchTerm = document.getElementById('search-products').value;
    const categoryFilter = document.getElementById('filter-category').value;
    const stockFilter = document.getElementById('filter-stock-status').value;
    
    filterTable('products-table', searchTerm, {
        category: categoryFilter,
        stock: stockFilter
    });
}

// Clear product filters
function clearProductFilters() {
    document.getElementById('search-products').value = '';
    document.getElementById('filter-category').value = '';
    document.getElementById('filter-stock-status').value = '';
    filterProducts();
}

// Export products
function exportProducts() {
    exportData('api/export_products.php', 'produtos.csv');
}

// Import products
function importProducts() {
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Importar Produtos</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="import-form" enctype="multipart/form-data">
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Arquivo CSV *</label>
                        <div class="file-upload-area" onclick="document.getElementById('import-file').click()">
                            <i class="fas fa-file-csv text-2xl text-gray-400 mb-2"></i>
                            <p class="text-gray-500">Clique para selecionar arquivo CSV</p>
                            <input type="file" id="import-file" name="import_file" accept=".csv" class="hidden" required>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-medium text-blue-900 mb-2">Formato do CSV</h4>
                        <p class="text-sm text-blue-700 mb-2">O arquivo deve conter as seguintes colunas:</p>
                        <code class="text-xs bg-blue-100 p-2 rounded block">
                            nome;categoria;estoque_atual;estoque_min;estoque_max;preco;ca;validade;codigo_barras
                        </code>
                        <button type="button" onclick="downloadTemplate()" class="mt-2 text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-download mr-1"></i>
                            Baixar modelo
                        </button>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="btn btn-outline">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload mr-2"></i>
                        Importar
                    </button>
                </div>
            </form>
        </div>
    `;
    
    showModal(modalContent);
    
    // Setup form submission
    document.getElementById('import-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importando...';
        submitBtn.disabled = true;
        
        fetch('api/import_products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(`${data.imported} produto(s) importado(s) com sucesso!`, 'success');
                closeModal();
                location.reload();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao importar produtos', 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
}

// Download CSV template
function downloadTemplate() {
    const csvContent = "nome;categoria;estoque_atual;estoque_min;estoque_max;preco;ca;validade;codigo_barras\n" +
                      "Capacete de Segurança;EPI;10;5;50;45.90;CA-12345;2025-12-31;7891234567890\n" +
                      "Luva de Procedimento;Médico-Hospitalar;100;20;200;0.85;CA-54321;2024-08-15;7891234567891";
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'modelo_importacao_produtos.csv';
    link.click();
}

// Edit product
function editProduct(productId) {
    fetch(`api/get_product.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openProductModal(productId, data.product);
            } else {
                showToast('Erro ao carregar produto: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar produto', 'error');
        });
}

// View product details
function viewProduct(productId) {
    fetch(`api/get_product.php?id=${productId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showProductDetails(data.product);
            } else {
                showToast('Erro ao carregar produto: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar produto', 'error');
        });
}

// Show product details
function showProductDetails(product) {
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Detalhes do Produto</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    ${product.photo ? 
                        `<img src="uploads/products/${product.photo}" alt="${product.name}" class="w-full h-64 object-cover rounded-lg border">` :
                        `<div class="w-full h-64 bg-gray-200 rounded-lg border flex items-center justify-center">
                            <i class="fas fa-box text-4xl text-gray-400"></i>
                        </div>`
                    }
                    ${product.barcode ? 
                        `<div class="mt-4 p-3 bg-gray-50 rounded-lg text-center">
                            <p class="text-sm text-gray-600 mb-1">Código de Barras</p>
                            <p class="font-mono font-bold">${product.barcode}</p>
                        </div>` : ''
                    }
                </div>
                
                <div class="lg:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                <p class="text-lg font-semibold">${product.name}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                                <p>${product.category_name || 'Sem categoria'}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Localização</label>
                                <p><i class="fas fa-map-marker-alt mr-1"></i>${product.location_name || 'Sem localização'}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fornecedor</label>
                                <p>${product.supplier_name || 'Sem fornecedor'}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estado de Conservação</label>
                                <p>${product.condition_status}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Estoque</label>
                                <div class="flex items-center space-x-2">
                                    <span class="text-2xl font-bold">${product.current_stock}</span>
                                    <span class="text-sm text-gray-500">unidades</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Min: ${product.min_stock} | Max: ${product.max_stock}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Preço Unitário</label>
                                <p class="text-lg font-semibold text-green-600">R$ ${parseFloat(product.price).toFixed(2).replace('.', ',')}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Valor Total em Estoque</label>
                                <p class="text-lg font-semibold text-blue-600">R$ ${(product.current_stock * parseFloat(product.price)).toFixed(2).replace('.', ',')}</p>
                            </div>
                            
                            ${product.ca_certificate ? 
                                `<div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">CA</label>
                                    <p>${product.ca_certificate}</p>
                                </div>` : ''
                            }
                            
                            ${product.validity_date ? 
                                `<div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Validade</label>
                                    <p>${new Date(product.validity_date).toLocaleDateString('pt-BR')}</p>
                                </div>` : ''
                            }
                        </div>
                    </div>
                    
                    ${product.description ? 
                        `<div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
                            <p class="text-gray-600 bg-gray-50 p-3 rounded-lg">${product.description}</p>
                        </div>` : ''
                    }
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button onclick="editProduct(${product.id})" class="btn btn-primary">
                            <i class="fas fa-edit mr-2"></i>
                            Editar
                        </button>
                        <button onclick="printBarcode(${product.id})" class="btn btn-outline">
                            <i class="fas fa-print mr-2"></i>
                            Imprimir Código
                        </button>
                        <button onclick="closeModal()" class="btn btn-outline">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    showModal(modalContent);
}

// Duplicate product
function duplicateProduct(productId) {
    confirmAction('Deseja duplicar este produto?', () => {
        fetch('api/duplicate_product.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ product_id: productId })
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
            showToast('Erro ao duplicar produto', 'error');
        });
    });
}

// Print barcode
function printBarcode(productId) {
    window.open(`api/print_barcode.php?id=${productId}`, '_blank');
}