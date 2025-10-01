// Settings JavaScript functions

// Show settings tab
function showSettingsTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.settings-tab').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.settings-tab-btn').forEach(btn => {
        btn.classList.remove('active', 'bg-blue-100', 'text-blue-700');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Add active class to clicked button
    event.target.classList.add('active', 'bg-blue-100', 'text-blue-700');
    
    // Load specific tab data
    if (tabName === 'backup') {
        loadBackupHistory();
    } else if (tabName === 'system') {
        loadDatabaseSize();
    }
}

// Save general settings with confirmation
function saveGeneralSettings(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...';
    submitBtn.disabled = true;
    
    const formData = new FormData(form);
    
    fetch('api/save_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            showUpdateConfirmation();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Erro ao salvar configurações', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Show update confirmation modal with reload option
function showUpdateConfirmation() {
    const confirmationContent = `
        <div class="bg-white rounded-lg p-8 w-full max-w-md">
            <div class="text-center">
                <div class="mx-auto h-20 w-20 bg-green-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-check-circle text-3xl text-green-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Configurações Salvas!</h3>
                <p class="text-gray-600 mb-6">As configurações foram atualizadas com sucesso. A página será recarregada para aplicar as alterações visuais.</p>
                <div class="flex justify-center space-x-3">
                    <button onclick="closeModal()" class="btn btn-outline">
                        Continuar Editando
                    </button>
                    <button onclick="location.reload()" class="btn btn-primary">
                        <i class="fas fa-refresh mr-2"></i>
                        Recarregar Agora
                    </button>
                </div>
            </div>
        </div>
    `;
    
    showModal(confirmationContent);
    
    // Auto-reload after 10 seconds if user doesn't interact
    setTimeout(() => {
        if (currentModal) {
            location.reload();
        }
    }, 10000);
}

// Generate backup
function generateBackup() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando...';
    button.disabled = true;
    
    fetch('api/generate_backup.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            
            // Auto-download backup
            if (data.backup_url) {
                window.open(data.backup_url, '_blank');
            }
            
            // Reload backup history
            loadBackupHistory();
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Erro ao gerar backup', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Restore backup
function restoreBackup() {
    const fileInput = document.getElementById('backup-file');
    const file = fileInput.files[0];
    
    if (!file) {
        showToast('Selecione um arquivo de backup', 'error');
        return;
    }
    
    confirmAction('ATENÇÃO: Esta operação irá substituir todos os dados atuais. Tem certeza que deseja continuar?', () => {
        const formData = new FormData();
        formData.append('backup_file', file);
        
        const button = event.target;
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Restaurando...';
        button.disabled = true;
        
        fetch('api/restore_backup.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                fileInput.value = '';
                
                // Reload page after successful restore
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao restaurar backup', 'error');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    });
}

// Load backup history
function loadBackupHistory() {
    fetch('api/get_backup_history.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('backup-history');
            if (data.success && data.backups.length > 0) {
                container.innerHTML = data.backups.map(backup => `
                    <div class="flex justify-between items-center py-2 border-b">
                        <div>
                            <span class="text-sm font-medium">${backup.filename}</span>
                            <p class="text-xs text-gray-500">${backup.size} - ${backup.date}</p>
                        </div>
                        <div class="flex space-x-2">
                            <button onclick="downloadBackup('${backup.filename}')" class="text-blue-600 hover:text-blue-800 text-sm" title="Download">
                                <i class="fas fa-download"></i>
                            </button>
                            <button onclick="deleteBackup('${backup.filename}')" class="text-red-600 hover:text-red-800 text-sm" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-sm text-gray-500">Nenhum backup encontrado</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('backup-history').innerHTML = '<p class="text-sm text-red-500">Erro ao carregar histórico</p>';
        });
}

// Load database size
function loadDatabaseSize() {
    fetch('api/get_database_size.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('db-size').textContent = data.size;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('db-size').textContent = 'Erro ao calcular';
        });
}

// Download backup
function downloadBackup(filename) {
    window.open(`api/download_backup.php?file=${encodeURIComponent(filename)}`, '_blank');
}

// Delete backup
function deleteBackup(filename) {
    confirmAction('Tem certeza que deseja excluir este backup?', () => {
        fetch('api/delete_backup.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ filename: filename })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                loadBackupHistory();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Erro ao excluir backup', 'error');
        });
    });
}

// Clear cache
function clearCache() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Limpando...';
    button.disabled = true;
    
    fetch('api/clear_cache.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Erro ao limpar cache', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Optimize database
function optimizeDatabase() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Otimizando...';
    button.disabled = true;
    
    fetch('api/optimize_database.php', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Erro ao otimizar banco', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Check system
function checkSystem() {
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Verificando...';
    button.disabled = true;
    
    fetch('api/check_system.php')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSystemCheckResults(data.checks);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Erro ao verificar sistema', 'error');
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// View activity logs
function viewActivityLogs() {
    fetch('api/activity_logs.php?limit=50')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showActivityLogs(data.logs);
        } else {
            showToast(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Erro ao carregar logs', 'error');
    });
}

// Show activity logs modal
function showActivityLogs(logs) {
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Logs de Atividade</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Usuário</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ação</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detalhes</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        ${logs.map(log => `
                            <tr>
                                <td class="px-4 py-4 text-sm text-gray-900">${formatDateTime(log.created_at)}</td>
                                <td class="px-4 py-4 text-sm text-gray-900">${log.user_name || 'Sistema'}</td>
                                <td class="px-4 py-4 text-sm text-gray-900">${log.action}</td>
                                <td class="px-4 py-4 text-sm text-gray-600">${log.details || '-'}</td>
                                <td class="px-4 py-4 text-sm text-gray-600">${log.ip_address || '-'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
            
            ${logs.length === 0 ? 
                `<div class="text-center py-8">
                    <i class="fas fa-history text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-500">Nenhum log de atividade encontrado</p>
                </div>` : ''
            }
            
            <div class="mt-6 flex justify-end">
                <button onclick="closeModal()" class="btn btn-outline">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    showModal(modalContent);
}

// Show system check results
function showSystemCheckResults(checks) {
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-semibold">Verificação do Sistema</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <div class="space-y-3">
                ${checks.map(check => `
                    <div class="flex items-center justify-between p-3 border rounded-lg ${check.status ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'}">
                        <div class="flex items-center">
                            <i class="fas fa-${check.status ? 'check-circle text-green-600' : 'exclamation-circle text-red-600'} mr-3"></i>
                            <span class="font-medium">${check.name}</span>
                        </div>
                        <span class="text-sm ${check.status ? 'text-green-600' : 'text-red-600'}">${check.message}</span>
                    </div>
                `).join('')}
            </div>
            
            <div class="mt-6 flex justify-end">
                <button onclick="closeModal()" class="btn btn-outline">
                    Fechar
                </button>
            </div>
        </div>
    `;
    
    showModal(modalContent);
}

// Initialize settings page
document.addEventListener('DOMContentLoaded', function() {
    // Setup general settings form
    const generalForm = document.getElementById('general-settings-form');
    if (generalForm) {
        generalForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show confirmation before saving
            showConfirmModal(
                'Salvar Configurações',
                'As alterações serão aplicadas ao sistema e podem afetar a aparência. Deseja continuar?',
                () => saveGeneralSettings(this)
            );
        });
    }
    
    // Setup logo upload preview
    const logoInput = document.getElementById('logo-input');
    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (!validateFile(file, logoInput)) {
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Show preview
                    const preview = document.createElement('img');
                    preview.src = e.target.result;
                    preview.className = 'w-16 h-16 object-contain border rounded mt-2';
                    
                    const uploadArea = logoInput.closest('.file-upload-area');
                    const existingPreview = uploadArea.querySelector('img:last-child');
                    if (existingPreview && existingPreview.className.includes('mt-2')) {
                        existingPreview.remove();
                    }
                    uploadArea.appendChild(preview);
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Setup color picker preview
    const colorInput = document.querySelector('input[name="primary_color"]');
    if (colorInput) {
        colorInput.addEventListener('change', function(e) {
            // Preview color change
            document.documentElement.style.setProperty('--primary-color', e.target.value);
            
            // Calculate darker shade for hover states
            const darkerColor = adjustBrightness(e.target.value, -20);
            document.documentElement.style.setProperty('--primary-dark', darkerColor);
            
            // Convert to RGB for transparency effects
            const rgb = hexToRgb(e.target.value);
            document.documentElement.style.setProperty('--primary-rgb', rgb);
            
            // Update all primary color elements
            const primaryElements = document.querySelectorAll('.btn-primary, .bg-blue-600');
            primaryElements.forEach(el => {
                el.style.backgroundColor = e.target.value;
            });
        });
    }
});

// Helper function to adjust brightness
function adjustBrightness(hex, percent) {
    // Remove # if present
    hex = hex.replace('#', '');
    
    // Convert to RGB
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    
    // Adjust brightness
    const newR = Math.max(0, Math.min(255, r + (r * percent / 100)));
    const newG = Math.max(0, Math.min(255, g + (g * percent / 100)));
    const newB = Math.max(0, Math.min(255, b + (b * percent / 100)));
    
    // Convert back to hex
    return '#' + [newR, newG, newB].map(x => {
        const hex = Math.round(x).toString(16);
        return hex.length === 1 ? '0' + hex : hex;
    }).join('');
}

// Helper function to convert hex to RGB
function hexToRgb(hex) {
    hex = hex.replace('#', '');
    
    const r = parseInt(hex.substr(0, 2), 16);
    const g = parseInt(hex.substr(2, 2), 16);
    const b = parseInt(hex.substr(4, 2), 16);
    
    return `${r}, ${g}, ${b}`;
}