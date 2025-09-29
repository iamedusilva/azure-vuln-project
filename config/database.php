<?php
/**
 * Configuração de Conexão com Azure SQL Database
 * ATENÇÃO: Este código é propositalmente vulnerável para fins educacionais
 */

class Database {
    // Configurações do banco de dados - apenas Azure SQL
    private $db_type = "azure"; // Forçado para Azure SQL Database
    
    // Configurações Azure SQL Database
    private $azure_server = "tcp:server-db-vuln.database.windows.net,1433";
    private $azure_username = "eduardo";
    private $azure_password = "081187Melo"; // VULNERÁVEL - senha exposta no código
    private $azure_database = "vulnerable_db";
    
    public $connection;
    private $connection_type;
    
    /**
     * Retorna o tipo de conexão atual
     * @return string
     */
    public function getConnectionType() {
        return $this->connection_type;
    }
    
    /**
     * Construtor - conecta automaticamente ao Azure SQL Database
     */
    public function __construct() {
        $this->connect();
    }

    /**
     * Conecta ao Azure SQL Database
     * @return PDO|resource|null
     */
    public function connect() {
        try {
            return $this->connectAzure();
        } catch (Exception $e) {
            die("Erro de conexão com o banco: " . $e->getMessage());
        }
    }
    
    /**
     * Conecta ao Azure SQL Database usando PDO
     * @return PDO|null
     */
    private function connectAzure() {
        try {
            // Opção 1: PDO (recomendado)
            $dsn = "sqlsrv:server={$this->azure_server};Database={$this->azure_database};LoginTimeout=30;Encrypt=1;TrustServerCertificate=0";
            
            $this->connection = new PDO($dsn, $this->azure_username, $this->azure_password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            $this->connection_type = "azure_pdo";
            return $this->connection;
            
        } catch (PDOException $e) {
            // Fallback para SQL Server extension
            try {
                return $this->connectAzureSqlsrv();
            } catch (Exception $e2) {
                throw new Exception("Erro Azure SQL (PDO): " . $e->getMessage() . " | Erro sqlsrv: " . $e2->getMessage());
            }
        }
    }
    
    /**
     * Conecta ao Azure SQL Database usando SQL Server extension
     * @return resource|null
     */
    private function connectAzureSqlsrv() {
        if (!function_exists('sqlsrv_connect')) {
            throw new Exception("Extensão SQL Server não está instalada");
        }
        
        $connectionInfo = [
            "UID" => $this->azure_username,
            "pwd" => $this->azure_password,
            "Database" => $this->azure_database,
            "LoginTimeout" => 30,
            "Encrypt" => 1,
            "TrustServerCertificate" => 0
        ];
        
        $this->connection = sqlsrv_connect($this->azure_server, $connectionInfo);
        
        if (!$this->connection) {
            $errors = sqlsrv_errors();
            $errorMsg = "Falha na conexão Azure SQL: ";
            foreach ($errors as $error) {
                $errorMsg .= $error['message'] . " ";
            }
            throw new Exception($errorMsg);
        }
        
        $this->connection_type = "azure_sqlsrv";
        return $this->connection;
    }
    
    /**
     * Executa uma query vulnerável (sem prepared statements)
     * @param string $query
     * @return array|bool
     */
    public function query($query) {
        return $this->queryAzure($query);
    }
    
    /**
     * Executa query no Azure SQL
     * @param string $query
     * @return array|bool
     */
    private function queryAzure($query) {
        try {
            if ($this->connection_type == "azure_pdo") {
                return $this->queryAzurePDO($query);
            } else {
                return $this->queryAzureSqlsrv($query);
            }
        } catch (Exception $e) {
            echo "Erro Azure query: " . $e->getMessage() . "<br>";
            echo "Query executada: " . $query . "<br>";
            return false;
        }
    }
    
    /**
     * Executa query usando PDO
     * @param string $query
     * @return array|bool
     */
    private function queryAzurePDO($query) {
        $stmt = $this->connection->query($query);
        
        if (!$stmt) {
            return false;
        }
        
        // Para SELECT
        if (stripos($query, 'SELECT') === 0) {
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return true; // Para INSERT, UPDATE, DELETE
    }
    
    /**
     * Executa query usando SQL Server extension
     * @param string $query
     * @return array|bool
     */
    private function queryAzureSqlsrv($query) {
        $result = sqlsrv_query($this->connection, $query);
        
        if (!$result) {
            $errors = sqlsrv_errors();
            $errorMsg = "Erro SQL Server: ";
            foreach ($errors as $error) {
                $errorMsg .= $error['message'] . " ";
            }
            echo $errorMsg . "<br>";
            return false;
        }
        
        // Para SELECT
        if (stripos($query, 'SELECT') === 0) {
            $data = [];
            while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
                $data[] = $row;
            }
            return $data;
        }
        
        return true; // Para INSERT, UPDATE, DELETE
    }
    
    /**
     * Método vulnerável para autenticação
     * @param string $username
     * @param string $password
     * @return array|null
     */
    public function authenticateUser($username, $password) {
        // VULNERÁVEL A SQL INJECTION - concatenação direta
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        
        // Debug - mostra a query (NUNCA fazer em produção)
        echo "<!-- Query executada: $query -->";
        
        $result = $this->query($query);
        
        // Azure retorna array
        if ($result && is_array($result) && count($result) > 0) {
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Registra um novo usuário (método vulnerável)
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $fullName
     * @return bool
     */
    public function registerUser($username, $password, $email, $fullName) {
        // VULNERÁVEL - sem sanitização e senha em texto plano
        $query = "INSERT INTO users (username, password, email, full_name) 
                  VALUES ('$username', '$password', '$email', '$fullName')";
        
        return $this->query($query) ? true : false;
    }
    
    /**
     * Adiciona comentário (vulnerável a XSS)
     * @param string $name
     * @param string $comment
     * @return bool
     */
    public function addComment($name, $comment) {
        // VULNERÁVEL - sem sanitização para XSS
        $query = "INSERT INTO comments (name, comment) VALUES ('$name', '$comment')";
        
        return $this->query($query) ? true : false;
    }
    
    /**
     * Busca comentários (retorna dados não sanitizados)
     * @return array
     */
    public function getComments() {
        $query = "SELECT * FROM comments ORDER BY created_at DESC";
        $result = $this->query($query);
        
        $comments = [];
        
        // Azure retorna array diretamente
        if ($result && is_array($result)) {
            $comments = $result;
        }
        
        return $comments;
    }
    
    /**
     * Busca todos os usuários (VULNERÁVEL - expõe senhas)
     * @return array
     */
    public function getAllUsers() {
        // PERIGOSO - expõe senhas em texto plano
        $query = "SELECT id, username, password, email, full_name, created_at FROM users";
        $result = $this->query($query);
        
        $users = [];
        if ($result && is_array($result)) {
            $users = $result;
        }
        
        return $users;
    }
    
    /**
     * Busca usuário por ID (vulnerável a SQL injection)
     * @param string $id
     * @return array|null
     */
    public function getUserById($id) {
        // VULNERÁVEL - sem validação de entrada
        $query = "SELECT * FROM users WHERE id = $id";
        $result = $this->query($query);
        
        // Azure retorna array
        if ($result && is_array($result) && count($result) > 0) {
            return $result[0];
        }
        
        return null;
    }
    
    /**
     * Executa query personalizada (MUITO PERIGOSO)
     * @param string $customQuery
     * @return array|bool
     */
    public function executeCustomQuery($customQuery) {
        // EXTREMAMENTE VULNERÁVEL - permite qualquer query
        echo "<!-- Executando query personalizada: $customQuery -->";
        return $this->query($customQuery);
    }
    
    /**
     * Método para contar registros (com possível injection)
     * @param string $table
     * @return int
     */
    public function countRecords($table) {
        // VULNERÁVEL - permite especificar qualquer tabela
        $query = "SELECT COUNT(*) as total FROM $table";
        $result = $this->query($query);
        
        // Azure retorna array
        if ($result && is_array($result) && count($result) > 0) {
            return $result[0]['total'] ?? 0;
        }
        
        return 0;
    }
    
    /**
     * Busca configurações (expõe dados sensíveis)
     * @return array
     */
    public function getConfigurations() {
        $query = "SELECT config_key, config_value, description FROM config";
        $result = $this->query($query);
        
        return $result ? $result : [];
    }
    
    /**
     * Busca logs de login (expõe senhas)
     * @param int $limit
     * @return array
     */
    public function getLoginLogs($limit = 20) {
        $query = "SELECT TOP $limit * FROM login_logs ORDER BY created_at DESC";
        $result = $this->query($query);
        
        return $result ? $result : [];
    }
    
    /**
     * Insere log de login (armazena senha!)
     * @param string $username
     * @param string $password
     * @param string $ip
     * @param string $userAgent
     * @param bool $success
     */
    public function logLoginAttempt($username, $password, $ip, $userAgent, $success) {
        // MUITO VULNERÁVEL - armazena senhas em texto plano
        $successValue = $success ? 1 : 0;
        $query = "INSERT INTO login_logs (username, password_attempted, ip_address, user_agent, success) 
                  VALUES ('$username', '$password', '$ip', '$userAgent', $successValue)";
        
        $this->query($query);
    }
    
    /**
     * Update de perfil vulnerável
     * @param int $userId
     * @param array $data
     * @return bool
     */
    public function updateUserProfile($userId, $data) {
        // VULNERÁVEL - update sem sanitização
        $updates = [];
        
        foreach ($data as $field => $value) {
            $updates[] = "$field = '$value'";
        }
        
        $query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = $userId";
        
        return $this->query($query) ? true : false;
    }
    
    /**
     * Busca com filtro vulnerável
     * @param string $table
     * @param string $where
     * @return array
     */
    public function searchRecords($table, $where) {
        // EXTREMAMENTE VULNERÁVEL - permite qualquer condição WHERE
        $query = "SELECT * FROM $table WHERE $where";
        $result = $this->query($query);
        
        return $result ? $result : [];
    }
    
    /**
     * Fecha a conexão
     */
    public function close() {
        if ($this->connection) {
            if ($this->connection_type == "azure_pdo") {
                // PDO: define como null para fechar
                $this->connection = null;
            } elseif ($this->connection_type == "azure_sqlsrv") {
                // SQL Server extension: fecha conexão
                sqlsrv_close($this->connection);
            }
        }
    }
    
    /**
     * Destrutor - fecha conexão automaticamente
     */
    public function __destruct() {
        $this->close();
    }
}

// Função global para obter conexão (prática ruim - variável global)
function getDatabase() {
    static $database = null;
    
    if ($database === null) {
        // SEMPRE USA AZURE SQL DATABASE
        $database = new Database();
    }
    
    return $database;
}

?>