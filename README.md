# 🛡️ Azure Vuln Project

# Instruções de Instalação e Configuração


> **⚠️ ATENÇÃO:** Este projeto demonstra vulnerabilidades comuns em aplicações web hospedadas no Azure e apresenta como implementar um Web Application Firewall (WAF) para mitigar ataques. O objetivo é educacional, fazendo parte de um projeto de Segurança da Informação, mostrando na prática como identificar, explorar e proteger aplicações contra ameaças.. **NÃO USE EM PRODUÇÃO!**## 📋 Pré-requisitos



## 📋 Índice- XAMPP instalado e funcionando

- PHP 7.4+ 

- [Visão Geral](#-visão-geral)- MySQL/MariaDB ativo

- [Tecnologias Utilizadas](#️-tecnologias-utilizadas)- Navegador web

- [Estrutura do Projeto](#-estrutura-do-projeto)

- [Páginas do Sistema](#-páginas-do-sistema)## 🗄️ Configuração do Banco de Dados

- [Vulnerabilidades Educacionais](#-vulnerabilidades-educacionais)

- [Configuração e Instalação](#️-configuração-e-instalação)### 1. Executar o Script SQL

- [Banco de Dados](#️-banco-de-dados)

- [Design e UI/UX](#-design-e-uiux)Há duas formas de executar o script de criação do banco:

- [Funcionalidades](#-funcionalidades)

- [Equipe de Desenvolvimento](#-equipe-de-desenvolvimento)#### Opção A: Via phpMyAdmin

- [Licença e Uso](#-licençe-e-uso)1. Abra o phpMyAdmin: `http://localhost/phpmyadmin`

2. Clique em "SQL" no menu superior

## 🎯 Visão Geral3. Copie todo o conteúdo do arquivo `database/create_database.sql`

4. Cole no campo de texto e clique em "Executar"

Este projeto é uma aplicação web educacional que simula vulnerabilidades comuns encontradas em sistemas web reais. Desenvolvido para o ensino de segurança cibernética, demonstra falhas de segurança de forma controlada e educativa.

#### Opção B: Via Linha de Comando

### Principais Características:```bash

- **🏦 FinTech Simulada:** Interface de sistema bancário/financeiro# Navegue até o diretório do projeto

- **🔓 Vulnerabilidades Intencionais:** SQL Injection, XSS, CSRF, etc.cd c:\xampp\htdocs\Azure-Web-App---Vuln

- **☁️ Azure SQL Database:** Hospedagem em nuvem

- **🎨 Design Moderno:** Interface responsiva com PingPay branding# Execute o script SQL

- **👥 Sistema de Usuários:** Autenticação e controle de acessomysql -u root -p < database/create_database.sql

```

## 🛠️ Tecnologias Utilizadas

### 2. Verificar a Instalação

### Backend

- **PHP 8.x** - Linguagem principalApós executar o script, você deve ter:

- **Azure SQL Database** - Banco de dados em nuvem- ✅ Banco `vulnerable_db` criado

- **PDO + SQLSRV** - Conectores de banco de dados- ✅ 6 tabelas criadas (users, comments, login_logs, sessions, uploaded_files, config)

- ✅ Dados de exemplo inseridos

### Frontend- ✅ 7 usuários de teste

- **HTML5** - Estrutura das páginas- ✅ Comentários com XSS

- **CSS3** - Estilização com glass effects e gradientes- ✅ Configurações sensíveis

- **JavaScript (Vanilla)** - Interatividade

- **Font Awesome 6.0** - Ícones## 🚀 Executando a Aplicação



### Infraestrutura1. Certifique-se de que o Apache e MySQL estão rodando no XAMPP

- **Azure Cloud** - Hospedagem do banco de dados2. Acesse: `http://localhost/Azure-Web-App---Vuln/index.php`

- **XAMPP** - Ambiente de desenvolvimento local3. A aplicação deve carregar e mostrar os comentários existentes



## 📁 Estrutura do Projeto## 🔐 Usuários de Teste



```Utilize estes usuários para testar as funcionalidades:

Azure-Web-App---Vuln/

├── 📁 app/| Username | Password | Role  | Descrição |

│   └── 📁 public/                    # Páginas públicas da aplicação|----------|----------|-------|-----------|

│       ├── 📁 assets/                # Recursos estáticos| admin    | admin    | admin | Administrador |

│       │   ├── 📁 css/              # Arquivos de estilo| root     | 123456   | admin | Super usuário |

│       │   │   ├── auth.css         # Estilos para login/registro| user1    | password | user  | Usuário comum |

│       │   │   ├── common.css       # Estilos globais| guest    | guest    | guest | Visitante |

│       │   │   ├── dashboard.css    # Estilos do dashboard| test     | test123  | user  | Para testes |

│       │   │   ├── index.css        # Estilos da página inicial| demo     | demo     | user  | Demonstração |

│       │   │   └── README.md        # Documentação CSS| manager  | manager123| user | Gerente |

│       │   └── 📁 images/           # Imagens do projeto

│       │       ├── PingPay(img).png## 🔍 Testando as Vulnerabilidades

│       │       ├── PingPay(img-letrabranca).png

│       │       └── PingPay(Img-Maior).png### SQL Injection no Login

│       ├── dashboard.php            # Painel principal do usuárioTente estes payloads no campo de usuário:

│       ├── index.php               # Página inicial pública```sql

│       ├── login.php               # Página de autenticaçãoadmin' OR '1'='1'--

│       └── register.php            # Página de cadastro' OR 1=1#

├── 📁 config/admin' OR '1'='1' LIMIT 1--

│   └── database.php                # Configuração de conexão DB```

├── 📁 database/

│   ├── create_database.sql         # Script MySQL (legacy)### XSS nos Comentários

│   └── create_database_azure.sql   # Script Azure SQL DatabaseTente estes payloads no campo de comentário:

├── 📁 .git/                       # Controle de versão Git```html

└── 📁 .github/                    # Configurações GitHub<script>alert('XSS')</script>

```<img src="x" onerror="alert('XSS')">

<svg onload="alert('XSS')">

## 🌐 Páginas do Sistema```



### 1. **Página Inicial** (`app/public/index.php`)### Informações Sensíveis Expostas

- **Função:** Landing page do sistema- Senhas em texto plano na tabela `users`

- **Características:**- Logs de tentativas de login na tabela `login_logs`

  - Design responsivo com efeitos parallax- Configurações sensíveis na tabela `config`

  - Seções: Hero, Serviços, Sobre, Contato

  - Detecção de usuário logado## 📁 Estrutura dos Arquivos

  - Navegação suave entre seções

```

### 2. **Login** (`app/public/login.php`)c:\xampp\htdocs\Azure-Web-App---Vuln\

- **Função:** Autenticação de usuários├── index.php                  # Página principal (vulnerável)

- **Características:**├── config/

  - Formulário com toggle de senha│   └── database.php          # Classe de conexão PHP (vulnerável)

  - Design glass effect├── database/

  - **Vulnerabilidades:** SQL Injection, armazenamento inseguro│   └── create_database.sql   # Script de criação do banco

  - Redirecionamento pós-login├── app/

│   ├── public/

### 3. **Registro** (`app/public/register.php`)│   │   ├── dashboard.html

- **Função:** Cadastro de novos usuários│   │   ├── index.html

- **Características:**│   │   ├── login.html

  - Validação client-side básica│   │   └── register.html

  - Campos: nome, email, senha, telefone│   ├── src/

  - **Vulnerabilidades:** XSS stored, validação inadequada│   │   ├── db.js            # Conexão Node.js (existente)

  - Design consistente com login│   │   ├── routes.js

│   │   ├── server.js

### 4. **Dashboard** (`app/public/dashboard.php`)│   │   └── utils.js

- **Função:** Painel principal do usuário│   └── tests/

- **Características:**│       └── xss-tests.md

  - Estatísticas em tempo real└── README.md                # Este arquivo

  - Modais interativos para dados detalhados```

  - Sistema de comentários

  - Edição de perfil## ⚠️ Vulnerabilidades Implementadas

  - **Vulnerabilidades:** XSS, CSRF, exposição de dados

### 1. SQL Injection

#### Modais do Dashboard:- **Localização**: Login e comentários

- **📊 Estatísticas de Usuários:** Lista completa com sorting- **Como testar**: Use payloads SQL nos campos de entrada

- **💬 Comentários:** Exibição de todos os comentários- **Impacto**: Bypass de autenticação, extração de dados

- **🚫 Tentativas de Login Falhadas:** Com filtros em tempo real

### 2. Cross-Site Scripting (XSS)

## 🔓 Vulnerabilidades Educacionais- **Localização**: Sistema de comentários

- **Como testar**: Insira código JavaScript nos comentários

### SQL Injection- **Impacto**: Execução de código no navegador da vítima

- **Localização:** Login, consultas de usuário

- **Impacto:** Acesso não autorizado ao banco de dados### 3. Exposição de Dados Sensíveis

- **Exemplo:** `' OR '1'='1`- **Localização**: Banco de dados

- **Como verificar**: Consulte as tabelas diretamente

### Cross-Site Scripting (XSS)- **Impacto**: Vazamento de senhas, tokens, configurações

- **Tipos:** Stored XSS nos comentários

- **Impacto:** Execução de scripts maliciosos### 4. Logging Excessivo

- **Exemplo:** `<script>alert('XSS')</script>`- **Localização**: Tabela `login_logs`

- **Problema**: Armazena senhas tentadas

### Exposição de Dados Sensíveis- **Impacto**: Exposição de credenciais em logs

- **Senhas em texto plano** no log de tentativas

- **Informações de configuração** expostas### 5. Configurações Inseguras

- **Dados de usuários** acessíveis- **Localização**: Usuários do banco, permissões

- **Problema**: Permissões excessivas, senhas fracas

### Controle de Acesso Inadequado- **Impacato**: Escalação de privilégios

- **Consultas diretas** ao banco

- **Ausência de sanitização** de entrada## 🛡️ IMPORTANTE - AVISO DE SEGURANÇA

- **Privilege escalation**

**⚠️ ESTA APLICAÇÃO É PROPOSITALMENTE VULNERÁVEL ⚠️**

## ⚙️ Configuração e Instalação

- **NÃO use em produção**

### Pré-requisitos- **NÃO exponha na internet**

- PHP 8.x- **Use apenas para aprendizado**

- XAMPP ou servidor similar- **Mantenha em ambiente isolado**

- Azure SQL Database configurado

- Extensões PHP: PDO, SQLSRV## 📚 Recursos Educacionais



### Passos de Instalação### Para Estudar SQL Injection:

1. Tente diferentes payloads de SQL injection

1. **Clone o repositório:**2. Observe as queries geradas nos comentários HTML

   ```bash3. Use ferramentas como Burp Suite ou OWASP ZAP

   git clone [URL_DO_REPOSITORIO]

   cd Azure-Web-App---Vuln### Para Estudar XSS:

   ```1. Experimente diferentes tipos de XSS

2. Observe como o código é executado

2. **Configure o banco de dados:**3. Teste filtros de XSS básicos

   - Execute `database/create_database_azure.sql` no Azure SQL Database

   - Ajuste as credenciais em `config/database.php`### Para Estudar Exposição de Dados:

1. Examine as tabelas do banco de dados

3. **Configuração do Azure SQL:**2. Veja como informações sensíveis são armazenadas

   ```php3. Analise os logs gerados pela aplicação

   private $azure_server = "tcp:seu-server.database.windows.net,1433";

   private $azure_username = "seu_usuario";## 🔧 Troubleshooting

   private $azure_password = "sua_senha";

   private $azure_database = "nome_do_banco";### Erro de Conexão com Banco

   ```- Verifique se o MySQL está rodando no XAMPP

- Confirme se as credenciais no `config/database.php` estão corretas

4. **Inicie o servidor:**- Execute o script SQL novamente se necessário

   ```bash

   # Com XAMPP### Página não Carrega

   # Coloque o projeto em C:/xampp/htdocs/- Verifique se o Apache está rodando

   # Acesse: http://localhost/Azure-Web-App---Vuln/app/public/- Confirme o caminho da aplicação

   ```- Verifique logs de erro do Apache



## 🗄️ Banco de Dados### Erro PHP

- Verifique se a versão do PHP é 7.4+

### Estrutura Principal- Confirme se as extensões mysqli estão habilitadas

- Verifique logs de erro do PHP

#### Tabela `users`

```sql## 📞 Suporte

- id (int, PK, AUTO_INCREMENT)

- username (varchar 50)Este é um projeto educacional. Para questões:

- email (varchar 100)1. Revise a documentação

- password_hash (varchar 255)2. Verifique a configuração do XAMPP

- full_name (varchar 100)3. Consulte logs de erro do sistema

- phone (varchar 20)

- role (varchar 20)---

- created_at (datetime)

```**Lembre-se: Esta aplicação é vulnerável POR DESIGN. Use com responsabilidade!**

#### Tabela `comments`
```sql
- id (int, PK, AUTO_INCREMENT)
- user_id (int, FK)
- content (text)
- created_at (datetime)
```

#### Tabela `login_logs`
```sql
- id (int, PK, AUTO_INCREMENT)
- username (varchar 50)
- password_attempted (varchar 255) -- ⚠️ Vulnerável
- ip_address (varchar 45)
- user_agent (text)
- success (bit)
- created_at (datetime)
```

### Conexão
- **Primária:** Azure SQL Database via PDO
- **Fallback:** SQL Server extension (SQLSRV)
- **Sintaxe:** T-SQL (Azure/SQL Server)

## 🎨 Design e UI/UX

### Paleta de Cores
- **Primária:** `#667eea` (Azul)
- **Secundária:** `#764ba2` (Roxo)
- **Background:** Gradientes escuros
- **Texto:** Branco/Cinza claro

### Características Visuais
- **Glass Effect:** Fundos translúcidos com blur
- **Gradientes:** Transições suaves entre cores
- **Animações:** Hover effects e transições CSS
- **Responsivo:** Mobile-first design
- **Tipografia:** Segoe UI, Tahoma, Geneva

### Componentes
- **Modais:** Sistema de popups informativos
- **Cards:** Layout de informações em cartões
- **Botões:** Efeitos hover e loading states
- **Formulários:** Validação visual e toggle de senha

## ✨ Funcionalidades

### Sistema de Autenticação
- ✅ Login/Logout de usuários
- ✅ Registro de novos usuários
- ✅ Controle de sessões
- ⚠️ Armazenamento inseguro (educacional)

### Dashboard Interativo
- ✅ Estatísticas em tempo real
- ✅ Sistema de comentários
- ✅ Edição de perfil
- ✅ Modais com dados detalhados
- ✅ Filtros e sorting

### Recursos Administrativos
- ✅ Visualização de usuários
- ✅ Log de tentativas de login
- ✅ Exportação de dados (simulada)

### Interface Responsiva
- ✅ Design mobile-first
- ✅ Breakpoints múltiplos
- ✅ Navegação touch-friendly
- ✅ Imagens otimizadas

## 👥 Equipe de Desenvolvimento

Somos um time de 5 desenvolvedores especializados em segurança cibernética:

### 🧑‍💻 Ana Silva
**Especialista em Frontend & UX**
- Responsável pelo design responsivo
- Implementação de animações CSS
- Otimização de performance

### 👨‍💻 Bruno Costa  
**Desenvolvedor Backend Senior**
- Arquitetura do sistema
- Integração com Azure SQL Database
- Implementação de vulnerabilidades educacionais

### 👩‍💻 Carla Moreira
**Especialista em Segurança Cibernética**
- Análise de vulnerabilidades
- Documentação de falhas de segurança
- Testes de penetração

### 🧑‍💻 Diego Fernandes
**DevOps & Infraestrutura**
- Configuração Azure Cloud
- CI/CD pipelines
- Monitoramento de sistema

### 👩‍💻 Elena Rodriguez
**QA & Documentação**
- Testes de qualidade
- Documentação técnica
- Validação de funcionalidades

## 📄 Licença e Uso

### ⚠️ IMPORTANTE - Uso Educacional Apenas

Este projeto foi desenvolvido **exclusivamente para fins educacionais** no ensino de segurança cibernética. 

### Restrições de Uso:
- ❌ **NÃO usar em ambiente de produção**
- ❌ **NÃO hospedar publicamente sem medidas de segurança**
- ❌ **NÃO usar para atividades maliciosas**
- ✅ **USO PERMITIDO:** Ensino, pesquisa, laboratórios controlados

### Disclaimer Legal:
Os desenvolvedores não se responsabilizam pelo uso inadequado desta aplicação. O projeto contém vulnerabilidades intencionais e não deve ser utilizado em ambientes reais sem as devidas correções de segurança.

---

**📧 Contato:** contato@finsecure-edu.com  
**🌐 Projeto Educacional** - Universidade/Instituição de Ensino  
**📅 Última Atualização:** Dezembro 2024

---

> *"A melhor defesa é conhecer o ataque"* - Equipe FinSecure Educational
