// Main JavaScript file for Support Life Warehouse System

// Initialize application
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Initialize tooltips
    initializeTooltips();
    
    // Initialize file uploads
    initializeFileUploads();
    
    // Initialize form validations
    initializeFormValidations();
    
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Initialize page-specific functionality
    initializePageFunctionality();
}

function initializePageFunctionality() {
    const page = new URLSearchParams(window.location.search).get('page') || 'dashboard';
    
    // Initialize based on current page
    switch (page) {
        case 'dashboard':
            if (typeof Chart !== 'undefined') {
                initializeDashboardCharts();
            }
            break;
        case 'reports':
            if (typeof Chart !== 'undefined') {
                initializeReportsCharts();
            }
            break;
        case 'settings':
            initializeSettingsPage();
            break;
    }
}

// Initialize tooltips
function initializeTooltips() {
    const elements = document.querySelectorAll('[title]');
    elements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip fixed bg-gray-800 text-white px-2 py-1 rounded text-xs z-50';
    tooltip.textContent = e.target.getAttribute('title');
    
    document.body.appendChild(tooltip);
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
    tooltip.style.top = rect.top - tooltip.offsetHeight - 8 + 'px';
    
    // Remove title to prevent browser tooltip
    e.target.setAttribute('data-title', e.target.getAttribute('title'));
    e.target.removeAttribute('title');
}

function hideTooltip(e) {
    const tooltip = document.querySelector('.tooltip');
    if (tooltip) {
        tooltip.remove();
    }
    
    // Restore title
    if (e.target.getAttribute('data-title')) {
        e.target.setAttribute('title', e.target.getAttribute('data-title'));
        e.target.removeAttribute('data-title');
    }
}

// Initialize file uploads
function initializeFileUploads() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', handleFileUpload);
    });
    
    // Drag and drop support
    const uploadAreas = document.querySelectorAll('.file-upload-area');
    uploadAreas.forEach(area => {
        area.addEventListener('dragover', handleDragOver);
        area.addEventListener('dragleave', handleDragLeave);
        area.addEventListener('drop', handleFileDrop);
    });
}

function handleFileUpload(e) {
    const file = e.target.files[0];
    if (file) {
        validateFile(file, e.target);
    }
}

function handleDragOver(e) {
    e.preventDefault();
    e.currentTarget.classList.add('dragover');
}

function handleDragLeave(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('dragover');
}

function handleFileDrop(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const fileInput = e.currentTarget.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.files = files;
            validateFile(files[0], fileInput);
        }
    }
}

// Initialize form validations
function initializeFormValidations() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', validateForm);
    });
}

function validateForm(e) {
    const form = e.target;
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-red-500');
            isValid = false;
        } else {
            field.classList.remove('border-red-500');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        showToast('Por favor, preencha todos os campos obrigatórios.', 'error');
    }
}

// Initialize mobile menu
function initializeMobileMenu() {
    // Create mobile menu toggle if it doesn't exist
    if (!document.querySelector('.mobile-menu-toggle')) {
        const toggle = document.createElement('button');
        toggle.className = 'mobile-menu-toggle';
        toggle.innerHTML = '<i class="fas fa-bars"></i>';
        toggle.onclick = toggleMobileMenu;
        document.body.appendChild(toggle);
    }
}

function toggleMobileMenu() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('open');
}

// Initialize settings page
function initializeSettingsPage() {
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
                    const existingPreview = uploadArea.querySelector('img');
                    if (existingPreview) {
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
        });
    }
}

// Dashboard charts initialization
function initializeDashboardCharts() {
    // Weekly movements chart
    const weeklyCtx = document.getElementById('weeklyMovementsChart');
    if (weeklyCtx) {
        new Chart(weeklyCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'],
                datasets: [{
                    label: 'Entradas',
                    data: [12, 19, 8, 15, 22, 8, 5],
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Saídas',
                    data: [8, 15, 12, 18, 16, 12, 8],
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Category distribution chart
    const categoryCtx = document.getElementById('categoryDistributionChart');
    if (categoryCtx) {
        new Chart(categoryCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['EPI', 'Médico-Hospitalar', 'Escritório', 'Limpeza', 'Ferramentas'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(251, 191, 36)',
                        'rgb(168, 85, 247)',
                        'rgb(239, 68, 68)'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '60%'
            }
        });
    }
}

// Reports charts initialization
function initializeReportsCharts() {
    // Movements chart
    const movementsCtx = document.getElementById('movementsChart');
    if (movementsCtx) {
        new Chart(movementsCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun'],
                datasets: [{
                    label: 'Entradas',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    tension: 0.1
                }, {
                    label: 'Saídas',
                    data: [7, 11, 5, 8, 3, 7],
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Category chart
    const categoryCtx = document.getElementById('categoryChart');
    if (categoryCtx) {
        new Chart(categoryCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['EPI', 'Médico-Hospitalar', 'Escritório', 'Limpeza'],
                datasets: [{
                    data: [30, 25, 20, 25],
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(34, 197, 94)',
                        'rgb(251, 191, 36)',
                        'rgb(168, 85, 247)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
}