// Utility functions

// Toast notification system
let toastTimeout = null;

function showToast(message, type = 'info', duration = 5000) {
    // Remove existing toast
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Clear existing timeout
    if (toastTimeout) {
        clearTimeout(toastTimeout);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    
    const icon = getToastIcon(type);
    
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas ${icon}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Auto-hide toast
    toastTimeout = setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentElement) {
                toast.remove();
            }
        }, 300);
    }, duration);
}

function getToastIcon(type) {
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };
    return icons[type] || icons.info;
}

// Utility functions
function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString('pt-BR');
}

function formatDateTime(datetime) {
    return new Date(datetime).toLocaleString('pt-BR');
}

// Loading state management
function showLoading(element) {
    element.classList.add('loading');
    element.style.pointerEvents = 'none';
}

function hideLoading(element) {
    element.classList.remove('loading');
    element.style.pointerEvents = '';
}

// Confirmation dialogs
function confirmAction(message, callback) {
    showConfirmModal('Confirmação', message, () => callback());
}

// Export functions
function exportData(url, filename) {
    showToast('Gerando arquivo...', 'info');
    
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    link.style.display = 'none';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    setTimeout(() => {
        showToast('Arquivo exportado com sucesso!', 'success');
    }, 1000);
}

// Generic API call function
async function apiCall(url, data = null, method = 'GET') {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, options);
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// Form data to FormData converter
function formToFormData(form) {
    const formData = new FormData();
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        if (input.type === 'file') {
            if (input.files.length > 0) {
                formData.append(input.name, input.files[0]);
            }
        } else if (input.type === 'checkbox') {
            formData.append(input.name, input.checked ? '1' : '0');
        } else if (input.value) {
            formData.append(input.name, input.value);
        }
    });
    
    return formData;
}

// Debounce function for search inputs
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Generic table filter function
function filterTable(tableId, searchTerm, filters = {}) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        let show = true;
        
        // Text search
        if (searchTerm) {
            const text = row.textContent.toLowerCase();
            if (!text.includes(searchTerm.toLowerCase())) {
                show = false;
            }
        }
        
        // Additional filters
        for (const [filterKey, filterValue] of Object.entries(filters)) {
            if (filterValue && show) {
                // Custom filter logic based on filter key
                switch (filterKey) {
                    case 'status':
                        const statusCell = row.querySelector('.inline-flex');
                        if (statusCell && !statusCell.textContent.toLowerCase().includes(filterValue.toLowerCase())) {
                            show = false;
                        }
                        break;
                    case 'category':
                        // Category filter logic
                        break;
                    case 'stock':
                        // Stock filter logic
                        break;
                }
            }
        }
        
        row.style.display = show ? '' : 'none';
    }
}

// Print function
function printElement(elementId) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Impressão</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                table { width: 100%; border-collapse: collapse; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            ${element.innerHTML}
        </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
    printWindow.close();
}

// File validation
function validateFile(file, input) {
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (file.size > maxSize) {
        showToast('Arquivo muito grande. Máximo 10MB.', 'error');
        input.value = '';
        return false;
    }
    
    if (input.accept && !allowedTypes.includes(file.type)) {
        showToast('Tipo de arquivo não permitido.', 'error');
        input.value = '';
        return false;
    }
    
    return true;
}

// Format functions
function formatCNPJ(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length <= 14) {
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    }
    
    input.value = value;
}

function formatPhone(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length <= 11) {
        if (value.length <= 10) {
            value = value.replace(/^(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        } else {
            value = value.replace(/^(\d{2})(\d)/, '($1) $2');
            value = value.replace(/(\d{5})(\d)/, '$1-$2');
        }
    }
    
    input.value = value;
}

// Status helper functions
function getStatusText(status) {
    const statuses = {
        'pending': 'Pendente',
        'approved': 'Aprovado',
        'delivered': 'Entregue',
        'returned': 'Devolvido',
        'cancelled': 'Cancelado'
    };
    return statuses[status] || status;
}

function getStatusClass(status) {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'approved': 'bg-blue-100 text-blue-800',
        'delivered': 'bg-green-100 text-green-800',
        'returned': 'bg-gray-100 text-gray-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
    if (typeof showToast === 'function') {
        showToast('Ocorreu um erro inesperado. Tente novamente.', 'error');
    }
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Unhandled Promise Rejection:', e.reason);
    if (typeof showToast === 'function') {
        showToast('Erro de conexão. Verifique sua internet.', 'error');
    }
});