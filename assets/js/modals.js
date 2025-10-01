// Modal management system

let currentModal = null;

// Modal system
function showModal(content) {
    const overlay = document.getElementById('modal-overlay');
    const modalContent = document.getElementById('modal-content');
    
    modalContent.innerHTML = content;
    overlay.classList.remove('hidden');
    currentModal = overlay;
    
    // Add fade-in animation
    setTimeout(() => {
        overlay.style.opacity = '1';
        const firstChild = modalContent.firstElementChild;
        if (firstChild) {
            firstChild.classList.add('fade-in');
        }
    }, 10);
    
    // Close modal on overlay click
    overlay.onclick = function(e) {
        if (e.target === overlay) {
            closeModal();
        }
    };
    
    // Close modal on ESC key
    document.addEventListener('keydown', handleEscapeKey);
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    const overlay = document.getElementById('modal-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        
        setTimeout(() => {
            overlay.classList.add('hidden');
            overlay.style.opacity = '';
            currentModal = null;
            
            // Restore body scroll
            document.body.style.overflow = '';
        }, 300);
    }
    
    // Remove ESC key listener
    document.removeEventListener('keydown', handleEscapeKey);
}

function handleEscapeKey(e) {
    if (e.key === 'Escape' && currentModal) {
        closeModal();
    }
}

// Confirmation modal
function showConfirmModal(title, message, onConfirm, onCancel = null) {
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-2xl text-yellow-600"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">${title}</h3>
                <p class="text-gray-600 mb-6">${message}</p>
                <div class="flex justify-center space-x-3">
                    <button onclick="closeModal(); ${onCancel ? onCancel + '()' : ''}" class="btn btn-outline">
                        Cancelar
                    </button>
                    <button onclick="closeModal(); ${onConfirm}()" class="btn btn-primary">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    `;
    
    showModal(modalContent);
}

// Loading modal
function showLoadingModal(message = 'Carregando...') {
    const modalContent = `
        <div class="bg-white rounded-lg p-8 w-full max-w-sm">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-gray-600">${message}</p>
            </div>
        </div>
    `;
    
    showModal(modalContent);
}

// Success modal
function showSuccessModal(title, message, onClose = null) {
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">${title}</h3>
                <p class="text-gray-600 mb-6">${message}</p>
                <button onclick="closeModal(); ${onClose ? onClose + '()' : ''}" class="btn btn-primary">
                    OK
                </button>
            </div>
        </div>
    `;
    
    showModal(modalContent);
}

// Error modal
function showErrorModal(title, message) {
    const modalContent = `
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-circle text-2xl text-red-600"></i>
                </div>
                <h3 class="text-lg font-semibold mb-2">${title}</h3>
                <p class="text-gray-600 mb-6">${message}</p>
                <button onclick="closeModal()" class="btn btn-primary">
                    OK
                </button>
            </div>
        </div>
    `;
    
    showModal(modalContent);
}