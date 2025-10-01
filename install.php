<?php
/**
 * Script de Instalação do Sistema Support Life
 * Execute este arquivo apenas uma vez para configurar o sistema
 */
 file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
// Verificar se já foi instalado
if (file_exists('config/installed.lock')) {
    die('Sistema já foi instalado. Remova o arquivo config/installed.lock para reinstalar.');
}

$errors = [];
$success = [];

// Verificar requisitos do sistema
function checkRequirements() {
    global $errors, $success;
    
    // Verificar versão do PHP
    if (version_compare(PHP_VERSION, '7.4.0', '<')) {
        $errors[] = 'PHP 7.4 ou superior é necessário. Versão atual: ' . PHP_VERSION;
    } else {
        $success[] = 'PHP ' . PHP_VERSION . ' ✓';
    }
    
    // Verificar extensões necessárias
    $required_extensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'json'];
    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $errors[] = "Extensão PHP '$ext' não encontrada";
        } else {
            $success[] = "Extensão '$ext' ✓";
        }
    }
    
    // Verificar permissões de diretórios
    $directories = ['uploads', 'uploads/products', 'uploads/system', 'config'];
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        if (!is_writable($dir)) {
            $errors[] = "Diretório '$dir' não tem permissão de escrita";
        } else {
            $success[] = "Diretório '$dir' ✓";
        }
    }
}

// Processar instalação
if ($_POST && isset($_POST['install'])) {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_name = $_POST['db_name'] ?? 'support_life_warehouse';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';
    
    $admin_name = $_POST['admin_name'] ?? 'Administrador';
    $admin_email = $_POST['admin_email'] ?? 'admin@supportlife.com';
    $admin_password = $_POST['admin_password'] ?? '';
    
    try {
        // Testar conexão com o banco
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Criar banco de dados se não existir
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");
        
        // Executar script SQL
        $sql = file_get_contents('supabase/migrations/20250820174808_azure_waterfall.sql');
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
                $pdo->exec($statement);
            }
        }
        
        // Atualizar usuário administrador
        if (!empty($admin_password)) {
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE role = 'admin' LIMIT 1");
            $stmt->execute([$admin_name, $admin_email, $hashed_password]);
        }
        
        // Criar arquivo de configuração do banco
        $config_content = "<?php
class Database {
    private \$host = '$db_host';
    private \$db_name = '$db_name';
    private \$username = '$db_user';
    private \$password = '$db_pass';
    private \$conn;

    public function getConnection() {
        \$this->conn = null;
        
        try {
            \$this->conn = new PDO(
                \"mysql:host=\" . \$this->host . \";dbname=\" . \$this->db_name . \";charset=utf8mb4\",
                \$this->username,
                \$this->password
            );
            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException \$exception) {
            echo \"Erro de conexão: \" . \$exception->getMessage();
        }
        
        return \$this->conn;
    }
}

// Instância global da conexão
\$database = new Database();
\$pdo = \$database->getConnection();
?>";
        
        file_put_contents('config/database.php', $config_content);
        
        // Criar arquivo de lock
        file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
        
        $success[] = 'Sistema instalado com sucesso!';
        $success[] = 'Você pode fazer login com: ' . $admin_email;
        
    } catch (Exception $e) {
        $errors[] = 'Erro na instalação: ' . $e->getMessage();
    }
}

checkRequirements();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Support Life Almoxarifado</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-2xl w-full mx-4">
        <div class="bg-white rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="mx-auto h-16 w-16 bg-blue-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-boxes text-2xl text-white"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900">Support Life</h1>
                <p class="text-gray-600 mt-2">Instalação do Sistema de Almoxarifado</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle text-red-400"></i>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Erros encontrados:</h3>
                            <ul class="mt-2 text-sm text-red-700">
                                <?php foreach ($errors as $error): ?>
                                    <li>• <?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex">
                        <i class="fas fa-check-circle text-green-400"></i>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-green-800">Verificações:</h3>
                            <ul class="mt-2 text-sm text-green-700">
                                <?php foreach ($success as $item): ?>
                                    <li>• <?= htmlspecialchars($item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($errors) && !file_exists('config/installed.lock')): ?>
                <form method="POST" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Configuração do Banco de Dados</h3>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Host do Banco</label>
                            <input type="text" name="db_host" value="localhost" class="w-full border rounded-lg px-3 py-2" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome do Banco</label>
                            <input type="text" name="db_name" value="support_life_warehouse" class="w-full border rounded-lg px-3 py-2" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Usuário</label>
                            <input type="text" name="db_user" value="root" class="w-full border rounded-lg px-3 py-2" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                            <input type="password" name="db_pass" class="w-full border rounded-lg px-3 py-2">
                        </div>
                        
                        <div class="md:col-span-2">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 mt-6">Administrador do Sistema</h3>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nome</label>
                            <input type="text" name="admin_name" value="Administrador" class="w-full border rounded-lg px-3 py-2" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="admin_email" value="admin@supportlife.com" class="w-full border rounded-lg px-3 py-2" required>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                            <input type="password" name="admin_password" class="w-full border rounded-lg px-3 py-2" required>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" name="install" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium">
                            <i class="fas fa-rocket mr-2"></i>
                            Instalar Sistema
                        </button>
                    </div>
                </form>
            <?php elseif (file_exists('config/installed.lock')): ?>
                <div class="text-center">
                    <div class="bg-green-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check text-2xl text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Sistema Instalado!</h3>
                    <p class="text-gray-600 mb-6">O sistema foi instalado com sucesso.</p>
                    <a href="index.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Acessar Sistema
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-6 text-sm text-gray-600">
            <p>© 2024 Support Life. Sistema de Gestão de Almoxarifado v1.0.0</p>
        </div>
    </div>
</body>
</html>