// Reports JavaScript functions

// Generate consumption report
function generateConsumptionReport(format = 'csv') {
    const period = document.getElementById('consumption-period').value;
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando...';
    button.disabled = true;
    
    const url = `api/generate_consumption_report.php?period=${period}&format=${format}`;
    
    setTimeout(() => {
        if (format === 'pdf') {
            generatePDFReport(url, 'relatorio_consumo.pdf');
        } else {
            window.open(url, '_blank');
        }
        
        showToast(`Relatório de consumo (${format.toUpperCase()}) gerado com sucesso!`, 'success');
        
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1000);
}

// Generate expenses report
function generateExpensesReport(format = 'csv') {
    const period = document.getElementById('expenses-period').value;
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando...';
    button.disabled = true;
    
    const url = `api/generate_expenses_report.php?period=${period}&format=${format}`;
    
    setTimeout(() => {
        if (format === 'pdf') {
            generatePDFReport(url, 'relatorio_gastos.pdf');
        } else {
            window.open(url, '_blank');
        }
        
        showToast(`Relatório de gastos (${format.toUpperCase()}) gerado com sucesso!`, 'success');
        
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1000);
}

// Generate movements report
function generateMovementsReport(format = 'csv') {
    const type = document.getElementById('movements-type').value;
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando...';
    button.disabled = true;
    
    const url = `api/generate_movements_report.php?type=${type}&format=${format}`;
    
    setTimeout(() => {
        if (format === 'pdf') {
            generatePDFReport(url, 'relatorio_movimentacoes.pdf');
        } else {
            window.open(url, '_blank');
        }
        
        showToast(`Relatório de movimentações (${format.toUpperCase()}) gerado com sucesso!`, 'success');
        
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1000);
}

// Generate stock report
function generateStockReport(format = 'csv') {
    const category = document.getElementById('stock-category').value;
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando...';
    button.disabled = true;
    
    const url = `api/generate_stock_report.php?category=${category}&format=${format}`;
    
    setTimeout(() => {
        if (format === 'pdf') {
            generatePDFReport(url, 'relatorio_estoque.pdf');
        } else {
            window.open(url, '_blank');
        }
        
        showToast(`Relatório de estoque (${format.toUpperCase()}) gerado com sucesso!`, 'success');
        
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1000);
}

// Generate expiry report
function generateExpiryReport(format = 'csv') {
    const days = document.getElementById('expiry-days').value;
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando...';
    button.disabled = true;
    
    const url = `api/generate_expiry_report.php?days=${days}&format=${format}`;
    
    setTimeout(() => {
        if (format === 'pdf') {
            generatePDFReport(url, 'relatorio_produtos_vencendo.pdf');
        } else {
            window.open(url, '_blank');
        }
        
        showToast(`Relatório de produtos vencendo (${format.toUpperCase()}) gerado com sucesso!`, 'success');
        
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1000);
}

// Generate price comparison report
function generatePriceComparisonReport(format = 'csv') {
    const groupBy = document.getElementById('price-comparison-group').value;
    const button = event.target;
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando...';
    button.disabled = true;
    
    const url = `api/generate_price_comparison_report.php?group_by=${groupBy}&format=${format}`;
    
    setTimeout(() => {
        if (format === 'pdf') {
            generatePDFReport(url, 'comparativo_precos.pdf');
        } else {
            window.open(url, '_blank');
        }
        
        showToast(`Comparativo de preços (${format.toUpperCase()}) gerado com sucesso!`, 'success');
        
        button.innerHTML = originalText;
        button.disabled = false;
    }, 1000);
}

// Generate PDF report using jsPDF
function generatePDFReport(dataUrl, filename) {
    fetch(dataUrl)
        .then(response => response.text())
        .then(data => {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();
            
            // Add title
            doc.setFontSize(16);
            doc.text('Support Life - Relatório', 20, 20);
            
            // Add content (simplified)
            doc.setFontSize(10);
            const lines = data.split('\n').slice(0, 50); // Limit lines for demo
            lines.forEach((line, index) => {
                if (index < 45) { // Prevent overflow
                    doc.text(line.substring(0, 80), 20, 40 + (index * 5));
                }
            });
            
            // Save PDF
            doc.save(filename);
        })
        .catch(error => {
            console.error('Error generating PDF:', error);
            showToast('Erro ao gerar PDF. Baixando CSV...', 'warning');
            window.open(dataUrl, '_blank');
        });
}

// Initialize charts when page loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart !== 'undefined' && window.location.search.includes('page=reports')) {
        initializeReportsCharts();
    }
});

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