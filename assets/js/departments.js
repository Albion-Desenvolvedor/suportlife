// Departments management JavaScript functions

// Open department modal
function openDepartmentModal(departmentId = null, departmentData = null) {
    let modalTitle = departmentId ? 'Editar Departamento' : 'Novo Departamento';
    
    // Get users for manager selection
    fetch('api/get_users.php')
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
                    
                    <form id="department-form">
                        ${departmentId ? `<input type="hidden" name="department_id" value="${departmentId}">` : ''}
                        
                        <div class="space-y-4">
                            <div>
                                <label class="form-label">Nome do Departamento *</label>
                                <input type="text" name="name" class="form-input" required 
                                       value="${departmentData ? departmentData.name : ''}"
                                       placeholder="Ex: Recursos Humanos, TI">
                            </div>
                            
                            <div>
                                <label class="form-label">Gerente Responsável</label>
                                <select name="manager_id" class="form-select">
                                    <option value="">Selecione um gerente</option>
                                    ${data.users.filter(u => u.role === 'manager' || u.role === 'admin').map(user => 
                                        `<option value="${user.id}" ${departmentData && departmentData.manager_id == user.id ? 'selected' : ''}>${user.name}</option>`
                                    ).join('')}
                                </select>
                            </div>
                            
                            <div>
                                <label class="form-label">Status</label>
                                <select name="active" class="form-select">
                                    <option value="1" ${!departmentData || departmentData.active ? 'selected' : ''}>Ativo</option>
                                    <option value="0" ${departmentData && !departmentData.active ? 'selected' : ''}>Inativo</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" onclick="closeModal()" class="btn btn-outline">
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>
                                ${departmentId ? 'Atualizar' : 'Cadastrar'}
                            </button>
                        </div>
                    </form>
                </div>
            `;
            
            showModal(modalContent);
            
            // Setup form submission
            document.getElementById('department-form').addEventListener('submit', function(e) {
                e.preventDefault();
                saveDepartment(this);
            });
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar usuários', 'error');
        });
}

// Save department
function saveDepartment(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_department.php', {
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
        showToast('Erro ao salvar departamento', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Edit department
function editDepartment(departmentId) {
    fetch(`api/get_department.php?id=${departmentId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openDepartmentModal(departmentId, data.department);
            } else {
                showToast('Erro ao carregar departamento: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar departamento', 'error');
        });
}

// Toggle department status
function toggleDepartmentStatus(departmentId) {
    confirmAction('Deseja alterar o status deste departamento?', () => {
        fetch('api/toggle_department_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ department_id: departmentId })
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

// Delete department
function deleteDepartment(departmentId) {
    confirmAction('Tem certeza que deseja excluir este departamento? Se houver solicitações vinculadas, o departamento será apenas desativado.', () => {
        fetch('api/delete_department.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ department_id: departmentId })
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
            showToast('Erro ao excluir departamento', 'error');
        });
    });
}