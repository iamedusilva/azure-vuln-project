-- ================================================
-- SCRIPT DE CRIAÇÃO DO BANCO DE DADOS VULNERÁVEL - AZURE SQL DATABASE
-- ================================================
-- ATENÇÃO: Este banco é propositalmente vulnerável para fins educacionais
-- NÃO USE EM PRODUÇÃO!
-- 
-- Compatível com: Azure SQL Database / SQL Server
-- Sintaxe: T-SQL

-- NOTA: No Azure SQL Database, você não precisa criar o banco de dados via script
-- O banco já deve existir. Este script cria apenas as tabelas e dados.

-- ================================================
-- CONFIGURAÇÕES INICIAIS
-- ================================================

-- Definir collation para UTF-8 (equivalente ao utf8mb4 do MySQL)
-- No Azure SQL Database, usar UTF-8 collation
-- Exemplo: SQL_Latin1_General_CP1_CI_AS ou Latin1_General_100_CI_AS_SC_UTF8

-- ================================================
-- TABELA DE USUÁRIOS
-- ================================================
IF OBJECT_ID('users', 'U') IS NULL
BEGIN
    CREATE TABLE users (
        id INT IDENTITY(1,1) PRIMARY KEY,  -- IDENTITY substitui AUTO_INCREMENT
        username NVARCHAR(50) NOT NULL,    -- NVARCHAR para suporte Unicode completo
        password NVARCHAR(255) NOT NULL,   -- VULNERÁVEL: senhas em texto plano
        email NVARCHAR(100) NULL,          -- NULL explícito (boa prática T-SQL)
        full_name NVARCHAR(100) NULL,
        role NVARCHAR(20) NOT NULL DEFAULT 'user' CHECK (role IN ('admin', 'user', 'guest')), -- CHECK constraint substitui ENUM
        is_active BIT NOT NULL DEFAULT 1,  -- BIT substitui BOOLEAN
        created_at DATETIME2 NOT NULL DEFAULT GETUTCDATE(),    -- DATETIME2 e GETUTCDATE() substitui TIMESTAMP
        updated_at DATETIME2 NOT NULL DEFAULT GETUTCDATE(),
        
        -- Índices básicos (sem considerar segurança)
        INDEX idx_username NONCLUSTERED (username),
        INDEX idx_email NONCLUSTERED (email)
    );
    
    PRINT 'Tabela users criada';
END
ELSE
    PRINT 'Tabela users já existe';

-- ================================================
-- TABELA DE COMENTÁRIOS
-- ================================================
IF OBJECT_ID('comments', 'U') IS NULL
BEGIN
    CREATE TABLE comments (
        id INT IDENTITY(1,1) PRIMARY KEY,
        name NVARCHAR(100) NOT NULL,
        comment NVARCHAR(MAX) NOT NULL,    -- NVARCHAR(MAX) substitui TEXT, VULNERÁVEL: permite qualquer conteúdo (XSS)
        ip_address NVARCHAR(45) NULL,      -- Para demonstrar coleta de IPs (IPv4/IPv6)
        user_agent NVARCHAR(MAX) NULL,     -- Para demonstrar coleta de user agents
        created_at DATETIME2 NOT NULL DEFAULT GETUTCDATE(),
        
        -- Índice para ordenação
        INDEX idx_created_at NONCLUSTERED (created_at)
    );
    
    PRINT 'Tabela comments criada';
END
ELSE
    PRINT 'Tabela comments já existe';

-- ================================================
-- TABELA DE LOGS DE LOGIN (para demonstrar vazamento de dados)
-- ================================================
IF OBJECT_ID('login_logs', 'U') IS NULL
BEGIN
    CREATE TABLE login_logs (
        id INT IDENTITY(1,1) PRIMARY KEY,
        username NVARCHAR(50) NULL,
        password_attempted NVARCHAR(255) NULL, -- MUITO VULNERÁVEL: armazena senhas tentadas
        ip_address NVARCHAR(45) NULL,
        user_agent NVARCHAR(MAX) NULL,
        success BIT NOT NULL DEFAULT 0,        -- BIT substitui BOOLEAN
        created_at DATETIME2 NOT NULL DEFAULT GETUTCDATE(),
        
        INDEX idx_username NONCLUSTERED (username),
        INDEX idx_created_at NONCLUSTERED (created_at)
    );
    
    PRINT 'Tabela login_logs criada';
END
ELSE
    PRINT 'Tabela login_logs já existe';

-- ================================================
-- TABELA DE SESSÕES (implementação vulnerável)
-- ================================================
IF OBJECT_ID('sessions', 'U') IS NULL
BEGIN
    CREATE TABLE sessions (
        id NVARCHAR(128) PRIMARY KEY,
        user_id INT NULL,
        data NVARCHAR(MAX) NULL,           -- VULNERÁVEL: dados da sessão não criptografados
        ip_address NVARCHAR(45) NULL,
        created_at DATETIME2 NOT NULL DEFAULT GETUTCDATE(),
        updated_at DATETIME2 NOT NULL DEFAULT GETUTCDATE(),
        expires_at DATETIME2 NULL,
        
        -- Foreign Key constraint
        CONSTRAINT FK_sessions_users FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_user_id NONCLUSTERED (user_id),
        INDEX idx_expires_at NONCLUSTERED (expires_at)
    );
    
    PRINT 'Tabela sessions criada';
END
ELSE
    PRINT 'Tabela sessions já existe';

-- ================================================
-- TABELA DE ARQUIVOS ENVIADOS
-- ================================================
IF OBJECT_ID('uploaded_files', 'U') IS NULL
BEGIN
    CREATE TABLE uploaded_files (
        id INT IDENTITY(1,1) PRIMARY KEY,
        filename NVARCHAR(255) NOT NULL,
        original_name NVARCHAR(255) NOT NULL,
        file_type NVARCHAR(100) NULL,      -- VULNERÁVEL: confia no tipo enviado pelo cliente
        file_size INT NULL,
        upload_path NVARCHAR(500) NULL,    -- VULNERÁVEL: caminho completo exposto
        uploader_ip NVARCHAR(45) NULL,
        uploaded_by INT NULL,
        created_at DATETIME2 NOT NULL DEFAULT GETUTCDATE(),
        
        CONSTRAINT FK_uploaded_files_users FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL,
        INDEX idx_uploaded_by NONCLUSTERED (uploaded_by),
        INDEX idx_file_type NONCLUSTERED (file_type)
    );
    
    PRINT 'Tabela uploaded_files criada';
END
ELSE
    PRINT 'Tabela uploaded_files já existe';

-- ================================================
-- TABELA DE CONFIGURAÇÕES SENSÍVEIS
-- ================================================
IF OBJECT_ID('config', 'U') IS NULL
BEGIN
    CREATE TABLE config (
        id INT IDENTITY(1,1) PRIMARY KEY,
        config_key NVARCHAR(100) NOT NULL UNIQUE,
        config_value NVARCHAR(MAX) NOT NULL,   -- VULNERÁVEL: valores sensíveis não criptografados
        description NVARCHAR(MAX) NULL,
        created_at DATETIME2 NOT NULL DEFAULT GETUTCDATE(),
        updated_at DATETIME2 NOT NULL DEFAULT GETUTCDATE(),
        
        INDEX idx_config_key NONCLUSTERED (config_key)
    );
    
    PRINT 'Tabela config criada';
END
ELSE
    PRINT 'Tabela config já existe';

-- ================================================
-- TRIGGER PARA UPDATE AUTOMÁTICO (substitui ON UPDATE CURRENT_TIMESTAMP do MySQL)
-- ================================================

-- Trigger para users
IF OBJECT_ID('tr_users_updated_at', 'TR') IS NULL
BEGIN
    EXEC('
    CREATE TRIGGER tr_users_updated_at
    ON users
    AFTER UPDATE
    AS
    BEGIN
        SET NOCOUNT ON;
        UPDATE users 
        SET updated_at = GETUTCDATE()
        FROM users u
        INNER JOIN inserted i ON u.id = i.id;
    END');
    
    PRINT 'Trigger tr_users_updated_at criado';
END

-- Trigger para sessions
IF OBJECT_ID('tr_sessions_updated_at', 'TR') IS NULL
BEGIN
    EXEC('
    CREATE TRIGGER tr_sessions_updated_at
    ON sessions
    AFTER UPDATE
    AS
    BEGIN
        SET NOCOUNT ON;
        UPDATE sessions 
        SET updated_at = GETUTCDATE()
        FROM sessions s
        INNER JOIN inserted i ON s.id = i.id;
    END');
    
    PRINT 'Trigger tr_sessions_updated_at criado';
END

-- Trigger para config
IF OBJECT_ID('tr_config_updated_at', 'TR') IS NULL
BEGIN
    EXEC('
    CREATE TRIGGER tr_config_updated_at
    ON config
    AFTER UPDATE
    AS
    BEGIN
        SET NOCOUNT ON;
        UPDATE config 
        SET updated_at = GETUTCDATE()
        FROM config c
        INNER JOIN inserted i ON c.id = i.id;
    END');
    
    PRINT 'Trigger tr_config_updated_at criado';
END

-- ================================================
-- INSERIR DADOS DE EXEMPLO (VULNERÁVEIS)
-- ================================================

-- Limpar dados existentes para re-inserção (opcional)
-- DELETE FROM uploaded_files;
-- DELETE FROM login_logs;
-- DELETE FROM comments;
-- DELETE FROM config;
-- DELETE FROM users;

-- Usuários com senhas fracas em texto plano
IF NOT EXISTS (SELECT 1 FROM users WHERE username = 'admin')
BEGIN
    INSERT INTO users (username, password, email, full_name, role) VALUES 
    ('admin', 'admin', 'admin@vulnerable-site.com', 'Administrador do Sistema', 'admin'),
    ('root', '123456', 'root@vulnerable-site.com', 'Super Usuario', 'admin'),
    ('user1', 'password', 'user1@email.com', 'João Silva', 'user'),
    ('guest', 'guest', 'guest@email.com', 'Usuário Visitante', 'guest'),
    ('test', 'test123', 'test@email.com', 'Usuário de Teste', 'user'),
    ('demo', 'demo', 'demo@email.com', 'Usuário Demo', 'user'),
    ('manager', 'manager123', 'manager@company.com', 'Gerente', 'user');
    
    PRINT 'Usuários de teste inseridos';
END
ELSE
    PRINT 'Usuários já existem';

-- Comentários com conteúdo XSS
IF NOT EXISTS (SELECT 1 FROM comments WHERE name = 'João')
BEGIN
    INSERT INTO comments (name, comment, ip_address, user_agent) VALUES 
    ('João', 'Ótimo sistema para aprender sobre segurança!', '192.168.1.100', 'Mozilla/5.0'),
    ('Maria', 'As vulnerabilidades estão bem demonstradas', '192.168.1.101', 'Chrome/91.0'),
    ('Hacker', '<script>alert("XSS Básico")</script>', '10.0.0.1', 'curl/7.68'),
    ('Pedro', 'Sistema interessante <img src="x" onerror="alert(''XSS via img'')">', '192.168.1.102', 'Firefox/89.0'),
    ('Ana', 'Bom para estudos <svg onload="alert(''SVG XSS'')">', '172.16.0.1', 'Safari/14.0'),
    ('Carlos', 'Vulnerabilidades: <iframe src="javascript:alert(''Frame XSS'')"></iframe>', '10.0.0.2', 'Opera/76.0');
    
    PRINT 'Comentários de teste inseridos';
END
ELSE
    PRINT 'Comentários já existem';

-- Logs de login (expondo tentativas de senha)
IF NOT EXISTS (SELECT 1 FROM login_logs WHERE username = 'admin')
BEGIN
    INSERT INTO login_logs (username, password_attempted, ip_address, user_agent, success) VALUES 
    ('admin', 'admin', '192.168.1.100', 'Mozilla/5.0', 1),      -- 1 = TRUE no SQL Server
    ('admin', 'password', '10.0.0.1', 'curl/7.68', 0),         -- 0 = FALSE
    ('admin', '123456', '10.0.0.1', 'curl/7.68', 0),
    ('root', 'toor', '172.16.0.1', 'Nmap NSE', 0),
    ('user1', 'user1', '192.168.1.101', 'Chrome/91.0', 0),
    ('guest', 'guest', '192.168.1.102', 'Firefox/89.0', 1);
    
    PRINT 'Logs de login inseridos';
END
ELSE
    PRINT 'Logs já existem';

-- Configurações sensíveis expostas
IF NOT EXISTS (SELECT 1 FROM config WHERE config_key = 'database_password')
BEGIN
    INSERT INTO config (config_key, config_value, description) VALUES 
    ('database_password', 'super_secret_pass', 'Senha do banco de dados principal'),
    ('api_key', 'sk-1234567890abcdef', 'Chave da API externa'),
    ('secret_key', 'my_secret_key_123', 'Chave secreta da aplicação'),
    ('admin_email', 'admin@vulnerable-site.com', 'Email do administrador'),
    ('debug_mode', 'true', 'Modo de debug ativo'),
    ('allow_file_upload', 'true', 'Permite upload de arquivos'),
    ('max_file_size', '10485760', 'Tamanho máximo de arquivo (10MB)'),
    ('encryption_key', 'weak_encryption_key', 'Chave de criptografia fraca');
    
    PRINT 'Configurações inseridas';
END
ELSE
    PRINT 'Configurações já existem';

-- Exemplos de arquivos "enviados"
IF NOT EXISTS (SELECT 1 FROM uploaded_files WHERE filename = 'img_001.jpg')
BEGIN
    INSERT INTO uploaded_files (filename, original_name, file_type, file_size, upload_path, uploader_ip, uploaded_by) VALUES 
    ('img_001.jpg', 'foto.jpg', 'image/jpeg', 245760, '/uploads/img_001.jpg', '192.168.1.100', 1),
    ('doc_001.pdf', 'documento.pdf', 'application/pdf', 1048576, '/uploads/doc_001.pdf', '192.168.1.101', 2),
    ('script.php', 'backdoor.php', 'application/x-php', 2048, '/uploads/script.php', '10.0.0.1', NULL),
    ('shell.jsp', 'webshell.jsp', 'text/plain', 4096, '/uploads/shell.jsp', '172.16.0.1', NULL);
    
    PRINT 'Arquivos de exemplo inseridos';
END
ELSE
    PRINT 'Arquivos já existem';

-- ================================================
-- VIEWS VULNERÁVEIS (expõem dados sensíveis)
-- ================================================

-- View que expõe senhas
IF OBJECT_ID('user_credentials', 'V') IS NULL
BEGIN
    EXEC('
    CREATE VIEW user_credentials AS 
    SELECT id, username, password, email, role 
    FROM users 
    WHERE is_active = 1');  -- 1 substitui TRUE
    
    PRINT 'View user_credentials criada';
END
ELSE
    PRINT 'View user_credentials já existe';

-- View que expõe logs de tentativas de login
IF OBJECT_ID('failed_logins', 'V') IS NULL
BEGIN
    EXEC('
    CREATE VIEW failed_logins AS 
    SELECT username, password_attempted, ip_address, created_at 
    FROM login_logs 
    WHERE success = 0');  -- 0 substitui FALSE
    
    PRINT 'View failed_logins criada';
END
ELSE
    PRINT 'View failed_logins já existe';

-- View que expõe configurações
IF OBJECT_ID('system_config', 'V') IS NULL
BEGIN
    EXEC('
    CREATE VIEW system_config AS 
    SELECT config_key, config_value, description 
    FROM config');
    
    PRINT 'View system_config criada';
END
ELSE
    PRINT 'View system_config já existe';

-- ================================================
-- STORED PROCEDURES VULNERÁVEIS
-- ================================================

-- Procedure vulnerável que permite SQL injection
IF OBJECT_ID('GetUserByName', 'P') IS NULL
BEGIN
    EXEC('
    CREATE PROCEDURE GetUserByName
        @user_name NVARCHAR(50)
    AS
    BEGIN
        DECLARE @sql NVARCHAR(MAX);
        -- VULNERÁVEL: concatenação direta permite SQL injection
        SET @sql = ''SELECT * FROM users WHERE username = '''''' + @user_name + '''''';
        EXEC sp_executesql @sql;
    END');
    
    PRINT 'Procedure GetUserByName criada (VULNERÁVEL)';
END
ELSE
    PRINT 'Procedure GetUserByName já existe';

-- Procedure que expõe informações sensíveis
IF OBJECT_ID('GetSystemInfo', 'P') IS NULL
BEGIN
    EXEC('
    CREATE PROCEDURE GetSystemInfo
    AS
    BEGIN
        SELECT ''Database Version'' as info_type, @@VERSION as info_value
        UNION ALL
        SELECT ''Current User'', SYSTEM_USER
        UNION ALL
        SELECT ''Current Database'', DB_NAME()
        UNION ALL
        SELECT ''Server Name'', @@SERVERNAME
        UNION ALL
        SELECT ''SQL Server Edition'', SERVERPROPERTY(''Edition'');
    END');
    
    PRINT 'Procedure GetSystemInfo criada';
END
ELSE
    PRINT 'Procedure GetSystemInfo já existe';

-- Procedure para "limpeza" que na verdade não limpa nada
IF OBJECT_ID('CleanLogs', 'P') IS NULL
BEGIN
    EXEC('
    CREATE PROCEDURE CleanLogs
        @days_old INT
    AS
    BEGIN
        -- Finge que limpa, mas não faz nada (vulnerabilidade de negação de serviço)
        SELECT CONCAT(''Seria para deletar logs de '', @days_old, '' dias atrás'') as message;
        -- Não faz nada real - apenas simula
    END');
    
    PRINT 'Procedure CleanLogs criada';
END
ELSE
    PRINT 'Procedure CleanLogs já existe';

-- ================================================
-- TRIGGERS PROBLEMÁTICOS
-- ================================================

-- Trigger que loga tentativas de login (inclusive senhas)
IF OBJECT_ID('tr_log_login_attempts', 'TR') IS NULL
BEGIN
    EXEC('
    CREATE TRIGGER tr_log_login_attempts 
    ON login_logs
    AFTER INSERT
    AS
    BEGIN
        SET NOCOUNT ON;
        -- Log adicional que poderia ser explorado
        INSERT INTO comments (name, comment) 
        SELECT ''System'', CONCAT(''Login attempt: '', i.username, '' with password: '', i.password_attempted)
        FROM inserted i;
    END');
    
    PRINT 'Trigger tr_log_login_attempts criado (VULNERÁVEL)';
END
ELSE
    PRINT 'Trigger tr_log_login_attempts já existe';

-- ================================================
-- USUÁRIOS E PERMISSÕES PROBLEMÁTICAS (APENAS PARA REFERÊNCIA)
-- ================================================

/*
NOTA: No Azure SQL Database, a criação de logins e usuários é diferente.
Estes comandos são apenas para referência e podem precisar ser executados
separadamente pelo administrador do banco:

-- Criar login no master database (deve ser executado no master)
CREATE LOGIN webapp_login WITH PASSWORD = 'WeakPassword123!';

-- Criar usuário no banco atual (executado no banco da aplicação)
CREATE USER webapp_user FOR LOGIN webapp_login;

-- Conceder permissões excessivas (VULNERÁVEL)
ALTER ROLE db_owner ADD MEMBER webapp_user; -- MUITO PERIGOSO!

-- Ou permissões mais específicas (ainda vulneráveis)
GRANT SELECT, INSERT, UPDATE, DELETE ON SCHEMA::dbo TO webapp_user;
*/

-- ================================================
-- FUNÇÕES AUXILIARES PARA COMPATIBILIDADE
-- ================================================

-- Função para simular CONCAT do MySQL (se não estiver disponível)
-- No SQL Server 2012+, CONCAT já existe, mas mantemos para compatibilidade
IF OBJECT_ID('fn_mysql_concat', 'FN') IS NULL
BEGIN
    EXEC('
    CREATE FUNCTION fn_mysql_concat(@str1 NVARCHAR(MAX), @str2 NVARCHAR(MAX))
    RETURNS NVARCHAR(MAX)
    AS
    BEGIN
        RETURN ISNULL(@str1, '''') + ISNULL(@str2, '''');
    END');
    
    PRINT 'Função fn_mysql_concat criada para compatibilidade';
END

-- ================================================
-- COMENTÁRIOS SOBRE AS VULNERABILIDADES
-- ================================================

/*
VULNERABILIDADES IMPLEMENTADAS NESTE BANCO (T-SQL / Azure SQL):

1. SENHAS EM TEXTO PLANO
   - Todas as senhas são armazenadas sem hash
   - Facilmente visíveis em dumps do banco

2. SQL INJECTION
   - Stored procedures vulneráveis (GetUserByName)
   - Views que podem ser exploradas
   - Falta de sanitização em queries dinâmicas

3. EXPOSIÇÃO DE DADOS SENSÍVEIS
   - Tabela config com chaves de API
   - Logs de login com senhas tentadas
   - Views que expõem informações críticas

4. XSS (Cross-Site Scripting)
   - Comentários permitem HTML/JavaScript
   - Falta de sanitização na saída

5. CONFIGURAÇÕES INSEGURAS
   - Usuários com senhas fracas
   - Permissões excessivas
   - Triggers que expõem dados

6. INFORMATION DISCLOSURE
   - Stored procedures que expõem info do sistema
   - Triggers que logam dados sensíveis
   - Caminhos completos de arquivos expostos

7. WEAK ACCESS CONTROL
   - Falta de controle de acesso adequado
   - Usuários com privilégios desnecessários

DIFERENÇAS DO MYSQL PARA T-SQL:
- AUTO_INCREMENT → IDENTITY(1,1)
- VARCHAR → NVARCHAR (suporte Unicode)
- TEXT → NVARCHAR(MAX)
- BOOLEAN → BIT
- TIMESTAMP → DATETIME2
- ENUM → CHECK constraints
- CURRENT_TIMESTAMP → GETUTCDATE()
- ON UPDATE triggers → Triggers explícitos
- IF NOT EXISTS → IF OBJECT_ID() IS NULL

NUNCA USE ESTE ESQUEMA EM PRODUÇÃO!
É apenas para fins educacionais e demonstração de vulnerabilidades.
*/

-- ================================================
-- FINALIZAÇÃO E ESTATÍSTICAS
-- ================================================

-- Mostrar estatísticas da criação
PRINT '================================================';
PRINT 'BANCO DE DADOS VULNERÁVEL CRIADO COM SUCESSO!';
PRINT '================================================';

SELECT 'Banco criado com sucesso para Azure SQL Database!' as status;

SELECT 
    'users' as tabela,
    COUNT(*) as total_records
FROM users
UNION ALL
SELECT 
    'comments',
    COUNT(*)
FROM comments
UNION ALL
SELECT 
    'login_logs',
    COUNT(*)
FROM login_logs
UNION ALL
SELECT 
    'config',
    COUNT(*)
FROM config
UNION ALL
SELECT 
    'uploaded_files',
    COUNT(*)
FROM uploaded_files;

PRINT '================================================';
PRINT 'ESTRUTURA PRONTA PARA USO EM AZURE SQL DATABASE';
PRINT '================================================';

-- Fim do script