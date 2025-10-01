// Categories management JavaScript functions

// Open category modal
function openCategoryModal(categoryId = null, categoryData = null) {
    let modalTitle = categoryId ? 'Editar Categoria' : 'Nova Categoria';
    
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">${modalTitle}</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="category-form">
                ${categoryId ? `<input type="hidden" name="category_id" value="${categoryId}">` : ''}
                
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Nome da Categoria *</label>
                        <input type="text" name="name" class="form-input" required 
                               value="${categoryData ? categoryData.name : ''}"
                               placeholder="Ex: EPI, Médico-Hospitalar">
                    </div>
                    
                    <div>
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-textarea" rows="3" 
                                  placeholder="Descrição da categoria...">${categoryData ? categoryData.description || '' : ''}</textarea>
                    </div>
                    
                    <div>
                        <label class="form-label">Status</label>
                        <select name="active" class="form-select">
                            <option value="1" ${!categoryData || categoryData.active ? 'selected' : ''}>Ativa</option>
                            <option value="0" ${categoryData && !categoryData.active ? 'selected' : ''}>Inativa</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="btn btn-outline">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        ${categoryId ? 'Atualizar' : 'Cadastrar'}
                    </button>
                </div>
            </form>
        </div>
    `;
    
    showModal(modalContent);
    
    // Setup form submission
    document.getElementById('category-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveCategory(this);
    });
}

// Save category
function saveCategory(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_category.php', {
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
        showToast('Erro ao salvar categoria', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Edit category
function editCategory(categoryId) {
    fetch(`api/get_category.php?id=${categoryId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openCategoryModal(categoryId, data.category);
            } else {
                showToast('Erro ao carregar categoria: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar categoria', 'error');
        });
}

// Toggle category status
function toggleCategoryStatus(categoryId) {
    confirmAction('Deseja alterar o status desta categoria?', () => {
        fetch('api/toggle_category_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ category_id: categoryId })
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

// Delete category
function deleteCategory(categoryId) {
    confirmAction('Tem certeza que deseja excluir esta categoria? Se houver produtos vinculados, a categoria será apenas desativada.', () => {
        fetch('api/delete_category.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ category_id: categoryId })
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
            showToast('Erro ao excluir categoria', 'error');
        });
    });
}