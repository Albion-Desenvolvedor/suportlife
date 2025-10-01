// Users management JavaScript functions

// Open user modal
function openUserModal(userId = null, userData = null) {
    let modalTitle = userId ? 'Editar Usuário' : 'Novo Usuário';
    
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-lg">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">${modalTitle}</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="user-form">
                ${userId ? `<input type="hidden" name="user_id" value="${userId}">` : ''}
                
                <div class="space-y-4">
                    <div>
                        <label class="form-label">Nome Completo *</label>
                        <input type="text" name="name" class="form-input" required 
                               value="${userData ? userData.name : ''}">
                    </div>
                    
                    <div>
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-input" required 
                               value="${userData ? userData.email : ''}">
                    </div>
                    
                    <div>
                        <label class="form-label">Senha ${userId ? '(deixe em branco para manter atual)' : '*'}</label>
                        <input type="password" name="password" class="form-input" ${!userId ? 'required' : ''}>
                    </div>
                    
                    <div>
                        <label class="form-label">Função</label>
                        <select name="role" class="form-select">
                            <option value="user" ${userData && userData.role === 'user' ? 'selected' : ''}>Usuário</option>
                            <option value="manager" ${userData && userData.role === 'manager' ? 'selected' : ''}>Gerente</option>
                            <option value="admin" ${userData && userData.role === 'admin' ? 'selected' : ''}>Administrador</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="form-label">Status</label>
                        <select name="active" class="form-select">
                            <option value="1" ${!userData || userData.active ? 'selected' : ''}>Ativo</option>
                            <option value="0" ${userData && !userData.active ? 'selected' : ''}>Inativo</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" class="btn btn-outline">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        ${userId ? 'Atualizar' : 'Cadastrar'}
                    </button>
                </div>
            </form>
        </div>
    `;
    
    showModal(modalContent);
    
    // Setup form submission
    document.getElementById('user-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveUser(this);
    });
}

// Save user
function saveUser(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_user.php', {
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
        showToast('Erro ao salvar usuário', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Edit user
function editUser(userId) {
    fetch(`api/get_user.php?id=${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                openUserModal(userId, data.user);
            } else {
                showToast('Erro ao carregar usuário: ' + data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao carregar usuário', 'error');
        });
}

// Toggle user status
function toggleUserStatus(userId) {
    confirmAction('Deseja alterar o status deste usuário?', () => {
        fetch('api/toggle_user_status.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
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

// Delete user
function deleteUser(userId) {
    confirmAction('Tem certeza que deseja excluir este usuário? Se houver histórico de atividades, o usuário será apenas desativado.', () => {
        fetch('api/delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
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
            showToast('Erro ao excluir usuário', 'error');
        });
    });
}