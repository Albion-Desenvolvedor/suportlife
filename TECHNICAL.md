# Documentação Técnica - Support Life Almoxarifado

## 🏗️ Arquitetura do Sistema

### Estrutura de Diretórios
```
support-life-almoxarifado/
├── api/                    # Endpoints da API REST
│   ├── save_product.php
│   ├── save_request.php
│   ├── approve_request.php
│   └── ...
├── assets/                 # Recursos estáticos
│   ├── css/
│   │   └── style.css      # Estilos principais
│   └── js/
│       └── main.js        # JavaScript principal
├── config/                 # Configurações
│   ├── database.php       # Configuração do banco
│   └── installed.lock     # Flag de instalação
├── includes/              # Funções auxiliares
│   └── functions.php      # Funções globais
├── pages/                 # Páginas do sistema
│   ├── dashboard.php
│   ├── products.php
│   ├── requests.php
│   └── ...
├── supabase/migrations/   # Scripts SQL
│   └── 20250820174808_azure_waterfall.sql
├── uploads/               # Arquivos enviados
│   ├── products/          # Fotos de produtos
│   └── system/           # Arquivos do sistema
├── index.php             # Página principal
├── login.php             # Autenticação
├── install.php           # Instalador
└── config.php            # Configurações gerais
```

## 🗄️ Banco de Dados

### Tabelas Principais

#### users
- **Propósito**: Gerenciamento de usuários do sistema
- **Campos principais**: id, name, email, password, role, active
- **Índices**: email (UNIQUE)

#### products
- **Propósito**: Cadastro de produtos do almoxarifado
- **Campos principais**: id, name, current_stock, min_stock, max_stock, price
- **Relacionamentos**: category_id, location_id, supplier_id
- **Índices**: name, barcode, category_id

#### movements
- **Propósito**: Histórico de movimentações de estoque
- **Campos principais**: id, product_id, type, quantity, user_id
- **Tipos**: 'entrada', 'saida'
- **Índices**: product_id + created_at, type + created_at

#### requests
- **Propósito**: Solicitações de material
- **Campos principais**: id, product_id, user_id, quantity, status
- **Status**: 'pending', 'approved', 'delivered', 'returned', 'cancelled'
- **Índices**: status + created_at, user_id + created_at

### Triggers Automáticos

#### update_stock_after_movement
- **Ação**: Atualiza estoque após inserção em movements
- **Lógica**: Soma/subtrai quantidade baseado no tipo

#### create_movement_on_delivery
- **Ação**: Cria movimentação quando solicitação é entregue
- **Trigger**: UPDATE em requests com status = 'delivered'

#### create_movement_on_return
- **Ação**: Cria movimentação quando material é devolvido
- **Trigger**: UPDATE em requests com status = 'returned'

## 🔐 Segurança

### Autenticação
- **Método**: Sessões PHP com cookies httponly
- **Senhas**: Hash bcrypt com salt automático
- **Timeout**: Configurável via php.ini

### Autorização
- **Níveis**: user (1), manager (2), admin (3)
- **Verificação**: Função `hasPermission($role)`
- **Middleware**: Verificação em cada página

### Proteção CSRF
- **Token**: Gerado por sessão
- **Validação**: Em formulários críticos
- **Funções**: `generateCSRFToken()`, `verifyCSRFToken()`

### Sanitização
- **Entrada**: `sanitizeInput()` para todos os dados
- **SQL**: Prepared statements obrigatórios
- **XSS**: htmlspecialchars com ENT_QUOTES

## 📡 API REST

### Estrutura de Resposta
```json
{
    "success": true|false,
    "message": "Mensagem de retorno",
    "data": {} // Opcional
}
```

### Endpoints Principais

#### POST /api/save_product.php
- **Função**: Cadastrar/editar produto
- **Parâmetros**: name, category_id, current_stock, etc.
- **Validação**: Campos obrigatórios, tipos de dados
- **Upload**: Suporte a foto do produto

#### POST /api/save_request.php
- **Função**: Criar solicitação de material
- **Parâmetros**: product_id, quantity, department_id
- **Validação**: Estoque disponível, dados válidos

#### POST /api/approve_request.php
- **Função**: Aprovar solicitação
- **Parâmetros**: request_id
- **Autorização**: Apenas managers/admins

## 🎨 Frontend

### CSS Framework
- **Base**: Tailwind CSS via CDN
- **Customização**: assets/css/style.css
- **Responsividade**: Mobile-first approach
- **Tema**: Azul corporativo (#3b82f6)

### JavaScript
- **Biblioteca**: Vanilla JS + Chart.js
- **Padrão**: Event delegation
- **AJAX**: Fetch API para comunicação
- **Modais**: Sistema próprio sem dependências

### Componentes Principais

#### Dashboard Cards
```css
.dashboard-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}
```

#### Navigation Menu
```css
.nav-item.active {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}
```

## 🔧 Configuração

### Variáveis de Ambiente
```php
// config.php
define('ENVIRONMENT', 'development'); // production
define('DEBUG_MODE', true);
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ITEMS_PER_PAGE', 20);
```

### Configuração do Banco
```php
// config/database.php
private $host = 'localhost';
private $db_name = 'support_life_warehouse';
private $username = 'root';
private $password = '';
```

### Apache (.htaccess)
- **Segurança**: Headers de proteção
- **Performance**: Compressão e cache
- **Rewrite**: URLs amigáveis (opcional)

## 📊 Performance

### Otimizações Implementadas
- **Índices**: Campos frequentemente consultados
- **Prepared Statements**: Todas as queries
- **Lazy Loading**: Carregamento sob demanda
- **Compressão**: Gzip para assets
- **Cache**: Headers de cache para recursos estáticos

### Monitoramento
- **Logs**: Sistema de log de erros
- **Atividades**: Log de ações dos usuários
- **Performance**: Tempo de execução das queries

## 🔄 Backup e Manutenção

### Backup Automático
```php
function createDatabaseBackup() {
    // Gera backup completo do banco
    // Inclui estrutura e dados
    // Limpa backups antigos automaticamente
}
```

### Otimização do Banco
```php
function optimizeDatabase() {
    // Executa OPTIMIZE TABLE em todas as tabelas
    // Melhora performance das consultas
}
```

### Limpeza de Logs
- **Rotação**: Logs antigos são arquivados
- **Retenção**: Configurável via BACKUP_RETENTION_DAYS
- **Compressão**: Logs antigos são comprimidos

## 🚀 Deploy

### Requisitos do Servidor
- **PHP**: 7.4+ com extensões PDO, GD, mbstring
- **MySQL**: 8.0+ ou MariaDB 10.4+
- **Apache/Nginx**: Com mod_rewrite
- **Espaço**: Mínimo 1GB para uploads e backups

### Processo de Deploy
1. **Upload**: Arquivos via FTP/SFTP
2. **Permissões**: chmod 755 em diretórios
3. **Banco**: Importar SQL ou usar install.php
4. **Configuração**: Ajustar config/database.php
5. **Teste**: Verificar funcionalidades principais

### Monitoramento Pós-Deploy
- **Logs de Erro**: Verificar regularmente
- **Performance**: Monitorar tempo de resposta
- **Backup**: Confirmar execução automática
- **Segurança**: Verificar tentativas de acesso

## 🐛 Troubleshooting

### Problemas Comuns

#### Erro de Conexão com Banco
```
Solução:
1. Verificar credenciais em config/database.php
2. Confirmar se MySQL está rodando
3. Testar conexão manual via mysql client
```

#### Upload de Arquivos Falha
```
Solução:
1. Verificar permissões da pasta uploads/
2. Conferir php.ini: upload_max_filesize, post_max_size
3. Verificar espaço em disco disponível
```

#### Performance Lenta
```
Solução:
1. Executar OPTIMIZE TABLE nas tabelas principais
2. Verificar índices nas consultas lentas
3. Aumentar memory_limit no PHP
4. Limpar logs antigos
```

### Debug Mode
```php
// Ativar em config.php para desenvolvimento
define('DEBUG_MODE', true);

// Mostra erros detalhados
// Logs completos de SQL
// Informações de performance
```

## 📈 Escalabilidade

### Otimizações Futuras
- **Cache**: Redis/Memcached para sessões
- **CDN**: Servir assets estáticos
- **Load Balancer**: Múltiplos servidores web
- **Replicação**: Master/slave no MySQL

### Métricas de Monitoramento
- **Usuários Simultâneos**: Sessões ativas
- **Queries/Segundo**: Performance do banco
- **Tempo de Resposta**: Páginas principais
- **Uso de Memória**: PHP e MySQL

---

**Versão**: 1.0.0  
**Última Atualização**: Janeiro 2024  
**Autor**: Equipe Support Life