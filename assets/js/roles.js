// Roles management JavaScript functions

// Open role modal
function openRoleModal(roleId = null, roleData = null) {
    let modalTitle = roleId ? 'Editar Cargo' : 'Novo Cargo';
    
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">${modalTitle}</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="role-form">
                ${roleId ? `<input type="hidden" name="role_id" value="${roleId}">` : ''}
                
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Nome do Cargo *</label>
                        <input type="text" name="name" class="form-input" required 
                               value="${roleData ? roleData.name : ''}"
                               placeholder="Ex: Supervisor, Coordenador">
                    </div>
                    
                    <div>
                        <label class="form-label">Descrição</label>
                        <textarea name="description" class="form-textarea" rows="3" 
                                  placeholder="Descrição das responsabilidades do cargo...">${roleData ? roleData.description || '' : ''}</textarea>
                    </div>
                    
                    <div>
                        <label class="form-label">Nível de Acesso</label>
                        <select name="access_level" class="form-select" required>
                            <option value="1" ${roleData && roleData.access_level == 1 ? 'selected' : ''}>Básico (1)</option>
                            <option value="2" ${roleData && roleData.access_level == 2 ? 'selected' : ''}>Intermediário (2)</option>
                            <option value="3" ${roleData && roleData.access_level == 3 ? 'selected' : ''}>Avançado (3)</option>
                            <option value="4" ${roleData && roleData.access_level == 4 ? 'selected' : ''}>Administrador (4)</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="form-label">Permissões</label>
                        <div class="space-y-3 max-h-48 overflow-y-auto border rounded-lg p-4">
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="view_products" class="mr-2">
                                    <span class="text-sm">Visualizar Produtos</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="manage_products" class="mr-2">
                                    <span class="text-sm">Gerenciar Produtos</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="view_requests" class="mr-2">
                                    <span class="text-sm">Visualizar Solicitações</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="approve_requests" class="mr-2">
                                    <span class="text-sm">Aprovar Solicitações</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="view_reports" class="mr-2">
                                    <span class="text-sm">Visualizar Relatórios</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="manage_suppliers" class="mr-2">
                                    <span class="text-sm">Gerenciar Fornecedores</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="manage_locations" class="mr-2">
                                    <span class="text-sm">Gerenciar Localizações</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="system_settings" class="mr-2">
                                    <span class="text-sm">Configurações Sistema</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="user_management" class="mr-2">
                                    <span class="text-sm">Gerenciar Usuários</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="permissions[]" value="backup_restore" class="mr-2">
                                    <span class="text-sm">Backup/Restauração</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="btn btn-outline">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        ${roleId ? 'Atualizar' : 'Criar Cargo'}
                    </button>
                </div>
            </form>
        </div>
    `;
    
    showModal(modalContent);
    
    // Setup form submission
    document.getElementById('role-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveRole(this);
    });
    
    // Pre-select permissions if editing
    if (roleData && roleData.permissions) {
        setTimeout(() => {
            const permissions = roleData.permissions.split(',');
            permissions.forEach(permission => {
                const checkbox = document.querySelector(`input[value="${permission}"]`);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }, 100);
    }
}

// Save role
function saveRole(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_role.php', {
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
        showToast('Erro ao salvar cargo', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Edit role
function editRole(roleId) {
    fetch(`api/get_role.php?id=${roleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openRoleModal(roleId, data.role);
            } else {
                showToast('Erro ao carregar cargo: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar cargo', 'error');
        });
}

// Delete role
function deleteRole(roleId) {
    confirmAction('Tem certeza que deseja excluir este cargo? Usuários com este cargo serão convertidos para "Usuário".', () => {
        fetch('api/delete_role.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ role_id: roleId })
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
            showToast('Erro ao excluir cargo', 'error');
        });
    });
}

// Toggle role status
function toggleRoleStatus(roleId) {
    confirmAction('Deseja alterar o status deste cargo?', () => {
        fetch('api/toggle_role_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ role_id: roleId })
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