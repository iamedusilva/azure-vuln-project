<?php
/**
 * Página de Login Vulnerável
 * ATENÇÃO: Este código é propositalmente vulnerável para fins educacionais
 */

// Incluir classe de conexão com banco
require_once '../../config/database.php';

// Iniciar sessão se ainda não foi iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verificar se usuário já está logado
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    // Redirecionar para dashboard se já estiver logado
    header('Location: dashboard.php');
    exit;
}

$error_message = '';
$success_message = '';

// Processar login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username']) && isset($_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Usar classe Database vulnerável
    $db = new Database();
    
    try {
        // Autenticação vulnerável (permite SQL Injection)
        $user = $db->authenticateUser($username, $password);
        
        if ($user) {
            $success_message = "Login bem-sucedido! Bem-vindo, " . htmlspecialchars($user['full_name']) . "! (Role: " . htmlspecialchars($user['role']) . ")";
            
            // Definir variáveis de sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Log da tentativa de login (armazena senha!)
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $db->logLoginAttempt($username, $password, $ip, $user_agent, true);
            
            // Redirecionar após 2 segundos
            header("refresh:2;url=dashboard.php");
        } else {
            $error_message = "Usuário ou senha incorretos.";
            
            // Log da tentativa falhada (armazena senha tentada!)
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            $db->logLoginAttempt($username, $password, $ip, $user_agent, false);
        }
    } catch (Exception $e) {
        $error_message = "Erro no sistema: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FinSecure</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CSS Styles -->
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <img src="assets/images/PingPay(img-letrabranca).png" alt="PingPay Logo" style="
                width: 300px; 
                height: 70px; 
                object-fit: contain;
                margin: 0 auto 1rem;
                display: block;
                filter: brightness(1.1) contrast(1.2);
            ">
            <p>Acesse sua conta</p>
        </div>
        
        <?php if ($error_message): ?>
            <div class="error">
                <i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = 'dashboard.php';
                }, 2000);
            </script>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Usuário
                </label>
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                       placeholder="Digite seu usuário" required>
            </div>
            
            <div class="form-group">
                <label for="password">
                    Senha
                </label>
                <div class="password-field" style="position: relative; display: inline-block; width: 100%;">
                    <input type="password" id="password" name="password" 
                           placeholder="Digite sua senha" required style="padding-right: 3rem; padding-left: 1rem; width: 100%;">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('password', this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #00bcd4;"></i>
                </div>
                <small>Qualquer valor funciona com SQL Injection</small>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> Entrar na Plataforma
            </button>
        </form>

        <div class="links">
            <a href="register.php">
                <i class="fas fa-user-plus"></i> Criar Conta
            </a>
            <a href="../../index.php">
                <i class="fas fa-home"></i> Início
            </a>
        </div>
    </div>

    <script>
        function togglePassword(fieldId, icon) {
            const field = document.getElementById(fieldId);
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
