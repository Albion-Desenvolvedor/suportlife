# Sistema de Gestão de Almoxarifado - Support Life

## 📋 Descrição
Sistema completo de gestão de almoxarifado desenvolvido para a Support Life, oferecendo controle total sobre estoque, movimentações, solicitações e relatórios.

## 🚀 Funcionalidades

### 📊 Dashboard
- Visão geral com métricas em tempo real
- Alertas de estoque baixo e alto
- Gráficos de movimentações e categorias
- Solicitações recentes

### 📦 Gestão de Produtos
- Cadastro completo com foto e código de barras
- Controle de estoque (mínimo, máximo, atual)
- Localização e estado de conservação
- Certificados CA e validade
- Categorização e fornecedores

### 🔄 Movimentações
- Registro de entradas e saídas
- Histórico completo com filtros
- Atualização automática de estoque
- Rastreabilidade por usuário

### 📋 Solicitações
- Sistema de solicitação de materiais
- Fluxo de aprovação
- Termos de responsabilidade
- Controle de devoluções

### 📈 Relatórios
- Relatórios de consumo
- Análise de gastos
- Movimentações por período
- Produtos vencendo
- Exportação em PDF/Excel

### ⚙️ Configurações
- Gestão de usuários e permissões
- Categorias e departamentos
- Fornecedores e localizações
- Backup e restauração
- Configurações do sistema

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP 7.4+
- **Banco de Dados:** MySQL 8.0
- **Frontend:** HTML5, CSS3, JavaScript
- **Frameworks CSS:** Tailwind CSS
- **Ícones:** Font Awesome
- **Gráficos:** Chart.js
- **Servidor:** Apache/Nginx

## 📋 Requisitos do Sistema

- PHP 7.4 ou superior
- MySQL 8.0 ou superior
- Apache/Nginx
- Extensões PHP: PDO, GD, mbstring
- Mínimo 512MB RAM
- 1GB espaço em disco

## 🔧 Instalação

### 1. Clone o repositório
```bash
git clone https://github.com/supportlife/almoxarifado.git
cd almoxarifado
```

### 2. Configure o banco de dados
```bash
# Importe o arquivo SQL
mysql -u root -p < supabase/migrations/20250820174808_azure_waterfall.sql
```

### 3. Configure a conexão
Edite o arquivo `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'support_life_warehouse';
private $username = 'seu_usuario';
private $password = 'sua_senha';
```

### 4. Configure permissões
```bash
chmod 755 uploads/
chmod 755 uploads/products/
chmod 755 uploads/system/
```

### 5. Acesse o sistema
- URL: `http://localhost/almoxarifado`
- Usuário: `admin@supportlife.com`
- Senha: `password`

## 👥 Usuários Padrão

| Usuário | Email | Senha | Função |
|---------|-------|-------|---------|
| Administrador | admin@supportlife.com | password | Admin |

## 📁 Estrutura do Projeto

```
almoxarifado/
├── api/                    # APIs REST
├── assets/                 # CSS, JS, imagens
├── config/                 # Configurações
├── includes/              # Funções auxiliares
├── pages/                 # Páginas do sistema
├── uploads/               # Arquivos enviados
├── supabase/migrations/   # Scripts SQL
├── index.php             # Página principal
├── login.php             # Página de login
└── logout.php            # Logout
```

## 🔐 Segurança

- Autenticação por sessão
- Senhas criptografadas (bcrypt)
- Proteção contra SQL Injection
- Validação de uploads
- Controle de acesso por função

## 📊 Funcionalidades Avançadas

### Alertas Automáticos
- Estoque baixo/alto
- Produtos vencendo
- Solicitações pendentes

### Relatórios Inteligentes
- Análise de consumo
- Previsão de reposição
- Comparativo de fornecedores
- Custos por departamento

### Mobile Responsivo
- Interface adaptável
- Menu mobile
- Touch-friendly

## 🔄 Backup e Restauração

### Backup Manual
1. Acesse Configurações > Backup
2. Clique em "Gerar Backup"
3. Download automático do arquivo

### Backup Automático
- Configurável (diário/semanal/mensal)
- Armazenamento local
- Limpeza automática de backups antigos

## 🐛 Solução de Problemas

### Erro de Conexão
- Verifique as credenciais do banco
- Confirme se o MySQL está rodando
- Teste a conexão manualmente

### Problemas de Upload
- Verifique permissões das pastas
- Confirme limites do PHP (upload_max_filesize)
- Verifique espaço em disco

### Performance Lenta
- Otimize o banco de dados
- Verifique índices das tabelas
- Limpe logs antigos

## 📞 Suporte

Para suporte técnico:
- Email: suporte@supportlife.com
- Telefone: (11) 1234-5678
- Documentação: [Wiki do Projeto]

## 📄 Licença

Este projeto está licenciado sob a Licença MIT - veja o arquivo [LICENSE](LICENSE) para detalhes.

## 🤝 Contribuição

1. Fork o projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## 📝 Changelog

### v1.0.0 (2024-01-20)
- Lançamento inicial
- Sistema completo de almoxarifado
- Dashboard interativo
- Gestão de produtos e estoque
- Sistema de solicitações
- Relatórios avançados

---

**Support Life** - Sistema de Gestão de Almoxarifado
© 2024 Support Life. Todos os direitos reservados.