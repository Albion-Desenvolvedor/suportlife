<?php
$settings = getSystemSettings();
$users = getAllUsers();
$categories = getAllCategories();
$departments = getAllDepartments();
?>

<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Configurações</h1>
        <p class="text-gray-600">Configurações gerais do sistema</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Menu lateral -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border">
                <nav class="p-4 space-y-2">
                    <button onclick="showSettingsTab('general')" class="settings-tab-btn w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 flex items-center active">
                        <i class="fas fa-cog mr-3"></i>
                        Configurações Gerais
                    </button>
                    <button onclick="showSettingsTab('users')" class="settings-tab-btn w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 flex items-center">
                        <i class="fas fa-users mr-3"></i>
                        Usuários
                    </button>
                    <button onclick="showSettingsTab('categories')" class="settings-tab-btn w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 flex items-center">
                        <i class="fas fa-tags mr-3"></i>
                        Categorias
                    </button>
                    <button onclick="showSettingsTab('departments')" class="settings-tab-btn w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 flex items-center">
                        <i class="fas fa-building mr-3"></i>
                        Departamentos
                    </button>
                    <button onclick="showSettingsTab('backup')" class="settings-tab-btn w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 flex items-center">
                        <i class="fas fa-database mr-3"></i>
                        Backup
                    </button>
                    <button onclick="showSettingsTab('system')" class="settings-tab-btn w-full text-left px-4 py-3 rounded-lg hover:bg-gray-100 flex items-center">
                        <i class="fas fa-server mr-3"></i>
                        Sistema
                    </button>
                </nav>
            </div>
        </div>

        <!-- Conteúdo -->
        <div class="lg:col-span-2">
            <!-- Configurações Gerais -->
            <div id="general-tab" class="settings-tab bg-white rounded-xl shadow-sm border p-6">
                <h3 class="text-lg font-semibold mb-4">Configurações Gerais</h3>
                <form id="general-settings-form" class="space-y-4">
                    <div>
                        <label class="form-label">Nome da Empresa</label>
                        <input type="text" name="company_name" class="form-input" value="<?= htmlspecialchars($settings['company_name'] ?? 'Support Life') ?>">
                    </div>
                    
                    <div>
                        <label class="form-label">Logo da Empresa</label>
                        <div class="space-y-2">
                            <?php if (!empty($settings['company_logo'])): ?>
                                <div class="flex items-center space-x-4 mb-2">
                                    <img src="uploads/system/<?= htmlspecialchars($settings['company_logo']) ?>" 
                                         alt="Logo atual" class="w-16 h-16 object-contain border rounded">
                                    <span class="text-sm text-gray-600">Logo atual</span>
                                </div>
                            <?php endif; ?>
                            <div class="file-upload-area" onclick="document.getElementById('logo-input').click()">
                                <i class="fas fa-image text-2xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500">Clique para <?= !empty($settings['company_logo']) ? 'alterar' : 'adicionar' ?> logo</p>
                                <input type="file" id="logo-input" name="company_logo" accept="image/*" class="hidden">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Cor Principal do Sistema</label>
                        <div class="flex items-center space-x-4">
                            <input type="color" name="primary_color" class="w-12 h-10 border rounded" value="<?= htmlspecialchars($settings['primary_color'] ?? '#3b82f6') ?>">
                            <span class="text-sm text-gray-600">Cor usada nos menus e botões principais</span>
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Dias de Alerta Antes do Vencimento</label>
                        <input type="number" name="alert_days_before_expiry" class="form-input" value="<?= htmlspecialchars($settings['alert_days_before_expiry'] ?? '30') ?>">
                    </div>

                    <div>
                        <label class="form-label">Validade Padrão dos Termos (dias)</label>
                        <input type="number" name="term_validity_days" class="form-input" value="<?= htmlspecialchars($settings['term_validity_days'] ?? '30') ?>">
                    </div>

                    <div>
                        <label class="form-label">Frequência de Backup</label>
                        <select name="backup_frequency" class="form-select">
                            <option value="daily" <?= ($settings['backup_frequency'] ?? '') === 'daily' ? 'selected' : '' ?>>Diário</option>
                            <option value="weekly" <?= ($settings['backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>Semanal</option>
                            <option value="monthly" <?= ($settings['backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>>Mensal</option>
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>
                            Salvar Configurações
                        </button>
                    </div>
                </form>
            </div>

            <!-- Usuários -->
            <div id="users-tab" class="settings-tab bg-white rounded-xl shadow-sm border p-6 hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Usuários</h3>
                    <button onclick="openUserModal()" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        Novo Usuário
                    </button>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Função</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="px-4 py-4 font-medium text-gray-900"><?= htmlspecialchars($user['name']) ?></td>
                                    <td class="px-4 py-4 text-gray-600"><?= htmlspecialchars($user['email']) ?></td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= getUserRoleClass($user['role']) ?>">
                                            <?= getUserRoleText($user['role']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $user['active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                            <?= $user['active'] ? 'Ativo' : 'Inativo' ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex space-x-2">
                                            <button onclick="editUser(<?= $user['id'] ?>)" class="text-blue-600 hover:text-blue-800" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="toggleUserStatus(<?= $user['id'] ?>)" class="text-gray-600 hover:text-gray-800" title="Alterar status">
                                                <i class="fas fa-<?= $user['active'] ? 'ban' : 'check' ?>"></i>
                                            </button>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button onclick="deleteUser(<?= $user['id'] ?>)" class="text-red-600 hover:text-red-800" title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Categorias -->
            <div id="categories-tab" class="settings-tab bg-white rounded-xl shadow-sm border p-6 hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Categorias</h3>
                    <button onclick="openCategoryModal()" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        Nova Categoria
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($categories as $category): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium text-gray-900"><?= htmlspecialchars($category['name']) ?></h4>
                                <div class="flex space-x-2">
                                    <button onclick="editCategory(<?= $category['id'] ?>)" class="text-blue-600 hover:text-blue-800" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleCategoryStatus(<?= $category['id'] ?>)" class="text-gray-600 hover:text-gray-800" title="Alterar status">
                                        <i class="fas fa-<?= $category['active'] ? 'ban' : 'check' ?>"></i>
                                    </button>
                                    <button onclick="deleteCategory(<?= $category['id'] ?>)" class="text-red-600 hover:text-red-800" title="Excluir categoria">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="text-sm text-gray-600"><?= htmlspecialchars($category['description'] ?? 'Sem descrição') ?></p>
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $category['active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $category['active'] ? 'Ativa' : 'Inativa' ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Departamentos -->
            <div id="departments-tab" class="settings-tab bg-white rounded-xl shadow-sm border p-6 hidden">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">Departamentos</h3>
                    <button onclick="openDepartmentModal()" class="btn btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        Novo Departamento
                    </button>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($departments as $department): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium text-gray-900"><?= htmlspecialchars($department['name']) ?></h4>
                                <div class="flex space-x-2">
                                    <button onclick="editDepartment(<?= $department['id'] ?>)" class="text-blue-600 hover:text-blue-800" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="toggleDepartmentStatus(<?= $department['id'] ?>)" class="text-gray-600 hover:text-gray-800" title="Alterar status">
                                        <i class="fas fa-<?= $department['active'] ? 'ban' : 'check' ?>"></i>
                                    </button>
                                    <button onclick="deleteDepartment(<?= $department['id'] ?>)" class="text-red-600 hover:text-red-800" title="Excluir departamento">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $department['active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                    <?= $department['active'] ? 'Ativo' : 'Inativo' ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Backup -->
            <div id="backup-tab" class="settings-tab bg-white rounded-xl shadow-sm border p-6 hidden">
                <h3 class="text-lg font-semibold mb-4">Backup e Restauração</h3>
                
                <div class="space-y-6">
                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-2">Backup Manual</h4>
                        <p class="text-sm text-gray-600 mb-4">Gere um backup completo do sistema</p>
                        <button onclick="generateBackup()" class="btn btn-primary">
                            <i class="fas fa-download mr-2"></i>
                            Gerar Backup
                        </button>
                    </div>

                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-2">Restaurar Backup</h4>
                        <p class="text-sm text-gray-600 mb-4">Restaure o sistema a partir de um backup</p>
                        <div class="flex items-center space-x-4">
                            <input type="file" id="backup-file" accept=".sql,.zip" class="form-input">
                            <button onclick="restoreBackup()" class="btn btn-secondary">
                                <i class="fas fa-upload mr-2"></i>
                                Restaurar
                            </button>
                        </div>
                    </div>

                    <div class="border rounded-lg p-4">
                        <h4 class="font-medium text-gray-900 mb-2">Histórico de Backups</h4>
                        <div class="space-y-2" id="backup-history">
                            <p class="text-sm text-gray-500">Carregando histórico...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sistema -->
            <div id="system-tab" class="settings-tab bg-white rounded-xl shadow-sm border p-6 hidden">
                <h3 class="text-lg font-semibold mb-4">Informações do Sistema</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Versão do Sistema</h4>
                            <p class="text-2xl font-bold text-blue-600">v1.0.0</p>
                        </div>

                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Banco de Dados</h4>
                            <p class="text-sm text-gray-600">MySQL 8.0</p>
                            <p class="text-sm text-gray-600">Tamanho: <span id="db-size">Calculando...</span></p>
                        </div>

                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Servidor</h4>
                            <p class="text-sm text-gray-600">PHP <?= phpversion() ?></p>
                            <p class="text-sm text-gray-600">Apache 2.4</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Estatísticas</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Total de Produtos:</span>
                                    <span class="text-sm font-medium"><?= getTotalProducts() ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Total de Usuários:</span>
                                    <span class="text-sm font-medium"><?= count($users) ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Movimentações:</span>
                                    <span class="text-sm font-medium"><?= getTotalMovements() ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="border rounded-lg p-4">
                            <h4 class="font-medium text-gray-900 mb-2">Manutenção</h4>
                            <div class="space-y-2">
                                <button onclick="clearCache()" class="w-full btn btn-outline">
                                    <i class="fas fa-broom mr-2"></i>
                                    Limpar Cache
                                </button>
                                <button onclick="optimizeDatabase()" class="w-full btn btn-outline">
                                    <i class="fas fa-database mr-2"></i>
                                    Otimizar Banco
                                </button>
                                <button onclick="checkSystem()" class="w-full btn btn-outline">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Verificar Sistema
                                </button>
                                <button onclick="viewActivityLogs()" class="w-full btn btn-outline">
                                    <i class="fas fa-history mr-2"></i>
                                    Logs de Atividade
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>