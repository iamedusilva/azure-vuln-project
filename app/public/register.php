<?php
/**
 * Página de Registro Vulnerável
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

// Processar cadastro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validações básicas (propositalmente fracas)
    if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
        $error_message = "Todos os campos são obrigatórios!";
    } elseif ($password !== $confirm_password) {
        $error_message = "As senhas não coincidem!";
    } elseif (strlen($password) < 3) {
        $error_message = "Senha muito curta! (mínimo 3 caracteres - muito fraco!)";
    } else {
        $db = new Database();
        
        try {
            // Verificar se usuário já existe (vulnerável a SQL injection)
            $check_query = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
            $existing_user = $db->query($check_query);
            
            if ($existing_user && $existing_user->num_rows > 0) {
                $error_message = "Usuário ou email já existe!";
            } else {
                // Registrar usuário (VULNERÁVEL - sem sanitização)
                if ($db->registerUser($username, $password, $email, $fullname)) {
                    $success_message = "Cadastro realizado com sucesso! Você será redirecionado para o login.";
                    
                    // Log do cadastro
                    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                    
                    // VULNERÁVEL - loga a senha em texto plano
                    $log_query = "INSERT INTO login_logs (username, password_attempted, ip_address, user_agent, success) 
                                 VALUES ('NEW_USER: $username', '$password', '$ip', '$user_agent', 1)";
                    $db->query($log_query);
                    
                    // Redirecionar após 3 segundos
                    header("refresh:3;url=login.php");
                } else {
                    $error_message = "Erro ao cadastrar usuário. Tente novamente.";
                }
            }
        } catch (Exception $e) {
            $error_message = "Erro no sistema: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - FinSecure</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CSS Styles -->
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <img src="assets/images/PingPay(img-letrabranca).png" alt="PingPay Logo" style="
                width: 300px; 
                height: 70px; 
                object-fit: contain;
                margin: 0 auto 1rem;
                display: block;
                filter: brightness(1.1) contrast(1.2);
            ">
            <p>Crie sua conta</p>
        </div>
        
        <?php if ($error_message): ?>
            <div class="error">
                <i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
            <script>
                setTimeout(() => {
                    window.location.href = 'login.php';
                }, 3000);
            </script>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="fullname">
                    <i class="fas fa-user"></i> Nome Completo
                </label>
                <i class="fas fa-user"></i>
                <input type="text" id="fullname" name="fullname" 
                       value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>" 
                       placeholder="Digite seu nome completo" required>
            </div>

            <div class="form-group">
                <label for="email">
                    <i class="fas fa-envelope"></i> Email
                </label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                       placeholder="Digite seu email" required>
            </div>

            <div class="form-group">
                <label for="username">
                    <i class="fas fa-user"></i> Nome de Usuário
                </label>
                <i class="fas fa-user"></i>
                <input type="text" id="username" name="username" 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" 
                       placeholder="Digite seu nome de usuário" required>
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
                <small>A senha deve ter no mínimo <strong>6 caracteres</strong></small>
            </div>

            <div class="form-group">
                <label for="confirm_password">
                    Confirmar Senha
                </label>
                <div class="password-field" style="position: relative; display: inline-block; width: 100%;">
                    <input type="password" id="confirm_password" name="confirm_password" 
                           placeholder="Confirme sua senha" required style="padding-right: 3rem; padding-left: 1rem; width: 100%;">
                    <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password', this)" style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #00bcd4;"></i>
                </div>
                <small class="warning">As senhas devem coincidir</small>
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-user-plus"></i> Criar Conta
            </button>
        </form>

        <div class="links">
            <a href="login.php">
                <i class="fas fa-sign-in-alt"></i> Já tem conta? Entrar
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