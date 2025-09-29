<?php
/**
 * Dashboard Vulnerável
 * ATENÇÃO: Este código é propositalmente vulnerável para fins educacionais
 */

// Incluir classe de conexão com banco
require_once '../../config/database.php';

// Verificação de sessão (vulnerável)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$user_logged_in = false;
$current_user = null;

// Verificação muito fraca de autenticação
if (isset($_SESSION['user_id']) || isset($_GET['user_id']) || isset($_POST['user_id'])) {
    $user_logged_in = true;
    $user_id = $_SESSION['user_id'] ?? $_GET['user_id'] ?? $_POST['user_id'];
    
    $db = new Database();
    // VULNERÁVEL - query direta sem sanitização
    $current_user = $db->getUserById($user_id);
    
    if (!$current_user) {
        // Se não encontrou por ID, usa dados da sessão ou valores padrão
        $current_user = [
            'id' => $user_id,
            'username' => $_SESSION['username'] ?? 'unknown',
            'full_name' => $_SESSION['full_name'] ?? 'Usuário Desconhecido',
            'role' => $_SESSION['role'] ?? 'user',
            'email' => 'unknown@email.com'
        ];
    }
}

// Processar comentários via AJAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_comment') {
    $name = $_POST['name'] ?? '';
    $comment = $_POST['comment'] ?? '';
    
    if (!empty($name) && !empty($comment)) {
        $db = new Database();
        // VULNERÁVEL - sem sanitização, permite XSS
        $success = $db->addComment($name, $comment);
        
        if ($success) {
            echo json_encode(['status' => 'success', 'message' => 'Comentário adicionado']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Erro ao adicionar comentário']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Nome e comentário são obrigatórios']);
    }
    exit; // Para requisições AJAX
}

// Processar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Atualizar perfil (vulnerável)
$profile_message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $new_name = $_POST['new_name'] ?? '';
    $new_email = $_POST['new_email'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    
    if (!empty($new_name) || !empty($new_email) || !empty($new_password)) {
        $db = new Database();
        
        // VULNERÁVEL - update sem sanitização
        $updates = [];
        if (!empty($new_name)) $updates[] = "full_name = '$new_name'";
        if (!empty($new_email)) $updates[] = "email = '$new_email'";
        if (!empty($new_password)) $updates[] = "password = '$new_password'";
        
        if (!empty($updates)) {
            $update_query = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = " . $current_user['id'];
            
            if ($db->query($update_query)) {
                $profile_message = "Perfil atualizado com sucesso!";
                // Recarregar dados do usuário
                $current_user = $db->getUserById($current_user['id']);
            } else {
                $profile_message = "Erro ao atualizar perfil.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FinSecure</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- CSS Styles -->
    <link rel="stylesheet" href="assets/css/common.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="../../index.php" class="logo">
                <img src="assets/images/PingPay(img-letrabranca).png" alt="PingPay Logo" style="
                    width: 300px; 
                    height: 70px; 
                    object-fit: contain;
                    margin-right: 0.5rem;
                    vertical-align: middle;
                    filter: brightness(1.1) contrast(1.2);
                ">
            </a>
            <div class="user-info">
                <?php if ($user_logged_in): ?>
                    <div class="user-name" onclick="toggleDropdown()">
                        <i class="fas fa-user-circle"></i>
                        Olá, <strong><?php echo htmlspecialchars($current_user['full_name']); ?></strong>
                        <span style="opacity: 0.7;">(<?php echo htmlspecialchars($current_user['role']); ?>)</span>
                        <i class="fas fa-chevron-down" style="margin-left: 0.5rem; font-size: 0.8rem; transition: transform 0.3s ease;" id="dropdown-arrow"></i>
                        
                        <!-- Dropdown Menu -->
                        <div class="dropdown-menu" id="userDropdown">
                            <a href="#" class="dropdown-item" onclick="openModal('editProfileModal')">
                                <i class="fas fa-user-edit"></i>
                                Editar Perfil
                            </a>
                            <a href="#" class="dropdown-item" onclick="openModal('linksModal')">
                                <i class="fas fa-link"></i>
                                Links Úteis
                            </a>
                            <a href="#" class="dropdown-item" onclick="openModal('commentsModal')">
                                <i class="fas fa-shield-alt"></i>
                                Tentativas de Login
                            </a>
                        </div>
                    </div>
                    <a href="?logout=1" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                <?php else: ?>
                    <a href="login.php" class="logout-btn">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php if (!$user_logged_in): ?>
            <!-- Área não autenticada -->
            <div class="access-denied">
                            <div class="modal-icon">
                <i class="fas fa-user-edit" style="font-size: 4rem; color: #00bcd4; margin-bottom: 1rem;"></i>
            </div>
                <h2 style="color: #00bcd4; margin-bottom: 1rem;">Acesso Não Autorizado</h2>
                <div class="vulnerability-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Vulnerabilidade IDOR Detectada!</strong><br>
                    Você está vendo esta página sem estar logado! Isso é uma falha grave de segurança.
                </div>
                
                <p style="color: #cccccc; margin-bottom: 2rem;">
                    Para demonstrar vulnerabilidades educacionais, tente acessar:
                </p>
                
                <div style="background: rgba(255, 255, 255, 0.05); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
                    <code style="color: #ffc107;">dashboard.php?user_id=1</code> - Ver dados do admin<br>
                    <code style="color: #ffc107;">dashboard.php?user_id=2</code> - Ver dados de outro usuário<br>
                    <code style="color: #ffc107;">dashboard.php?user_id=X</code> - Qualquer ID de usuário existente
                </div>
                
                <a href="login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Fazer Login Agora
                </a>
            </div>
        <?php endif; ?>

        <?php if ($user_logged_in): ?>
            <!-- Dashboard autenticado -->
            <div class="vulnerability-warning">
                <i class="fas fa-bug"></i>
                <strong>Vulnerabilidades Ativas:</strong>
                • Bypass de autenticação via GET/POST • 
                • IDOR (Insecure Direct Object Reference) • 
                • Perfil editável sem validação • 
                • Exposição de dados sensíveis
            </div>

            <div class="dashboard-grid">
                <!-- Informações do Usuário -->
                <div class="card">
                    <h3><i class="fas fa-user-circle"></i> Seus Dados</h3>
                    <table class="user-table">
                        <tr><td>ID:</td><td><?php echo htmlspecialchars($current_user['id']); ?></td></tr>
                        <tr><td>Usuário:</td><td><?php echo htmlspecialchars($current_user['username']); ?></td></tr>
                        <tr><td>Nome:</td><td><?php echo htmlspecialchars($current_user['full_name']); ?></td></tr>
                        <tr><td>Email:</td><td><?php echo htmlspecialchars($current_user['email']); ?></td></tr>
                        <tr><td>Papel:</td><td><?php echo htmlspecialchars($current_user['role']); ?></td></tr>
                        <tr>
                            <td>Senha:</td>
                            <td>
                                <div class="password-container" style="position: relative; background: rgba(220, 53, 69, 0.1); border-radius: 6px; padding: 0.3rem 2.5rem 0.3rem 0.6rem; font-family: 'Courier New', monospace; color: #dc3545; display: inline-block; min-width: 150px; max-width: 100%;">
                                    <span id="user-password" style="display: inline;">
                                        <?php echo str_repeat('•', strlen($current_user['password'] ?? 'N/A')); ?>
                                    </span>
                                    <span id="user-password-hidden" style="display: none;">
                                        <?php echo htmlspecialchars($current_user['password'] ?? 'N/A'); ?>
                                    </span>
                                    <i class="fas fa-eye password-toggle" 
                                       onclick="toggleUserPassword(this)" 
                                       style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #dc3545; transition: all 0.3s ease; font-size: 0.9rem;"
                                       title="Mostrar/Ocultar senha"></i>
                                </div>
                            </td>
                        </tr>
                    </table>
                    
                    <div style="background: rgba(220, 53, 69, 0.1); padding: 1rem; border-radius: 8px; margin-top: 1rem; border: 1px solid rgba(220, 53, 69, 0.3);">
                        <i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i>
                        <strong style="color: #ff5252;">CRÍTICO:</strong> 
                        <span style="color: #cccccc;">Sua senha está visível em texto plano!</span>
                    </div>
                </div>

                <!-- Estatísticas do Sistema -->
                <div class="card">
                    <h3><i class="fas fa-chart-bar"></i> Estatísticas do Sistema</h3>
                    <?php
                    $db = new Database();

                    // Helper to safely extract a single 'total' value from different DB result shapes
                    function extract_total($result) {
                        // Null/false -> zero
                        if ($result === null || $result === false) return 0;

                        // If PDO returned an array of rows
                        if (is_array($result)) {
                            // common shape: [ ['total' => X], ... ]
                            if (isset($result[0]) && is_array($result[0]) && array_key_exists('total', $result[0])) {
                                return $result[0]['total'];
                            }

                            // possible shape: ['total' => X]
                            if (array_key_exists('total', $result)) {
                                return $result['total'];
                            }

                            // fallback: try first numeric value found
                            $first = reset($result);
                            if (is_array($first)) {
                                $val = reset($first);
                                return ($val !== false) ? $val : 0;
                            }
                            return is_numeric($first) ? $first : 0;
                        }

                        // If mysqli_result or similar object
                        if (is_object($result)) {
                            // mysqli_result
                            if (method_exists($result, 'fetch_assoc')) {
                                $row = $result->fetch_assoc();
                                return isset($row['total']) ? $row['total'] : 0;
                            }

                            // PDOStatement fallback
                            if (method_exists($result, 'fetch')) {
                                $row = $result->fetch(PDO::FETCH_ASSOC);
                                return isset($row['total']) ? $row['total'] : 0;
                            }
                        }

                        return 0;
                    }

                    $total_users_result = $db->query("SELECT COUNT(*) as total FROM users");
                    $total_users = extract_total($total_users_result);

                    $total_comments_result = $db->query("SELECT COUNT(*) as total FROM comments");
                    $total_comments = extract_total($total_comments_result);

                    $total_logs_result = $db->query("SELECT COUNT(*) as total FROM login_logs");
                    $total_logs = extract_total($total_logs_result);

                    // FIX: T-SQL usa 0 para FALSE, não a palavra-chave 'FALSE'
                    $failed_logins_result = $db->query("SELECT COUNT(*) as total FROM login_logs WHERE success = 0");
                    $failed_logins = extract_total($failed_logins_result);
                    ?>
                    <div class="stats">
                        <div class="stat-item" onclick="showUsersModal()" style="cursor: pointer;" title="Clique para ver todos os usuários">
                            <div class="stat-number"><?php echo $total_users; ?></div>
                            <div class="stat-label">Usuários</div>
                        </div>
                        <div class="stat-item" onclick="showCommentsModal()" style="cursor: pointer;" title="Clique para ver todos os comentários">
                            <div class="stat-number"><?php echo $total_comments; ?></div>
                            <div class="stat-label">Comentários</div>
                        </div>
                        <div class="stat-item" onclick="showFailedLoginsModal()" style="cursor: pointer;" title="Clique para ver tentativas de login falhadas">
                            <div class="stat-number" style="background: linear-gradient(135deg, #dc3545 0%, #ff6b6b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"><?php echo $failed_logins; ?></div>
                            <div class="stat-label">Logins Falhados</div>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>
    </div>

    <!-- Modais -->
    <!-- Modal Editar Perfil -->
    <div class="modal" id="editProfileModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-user-edit"></i> Editar Perfil</h3>
                <button class="close-modal" onclick="closeModal('editProfileModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <?php if ($profile_message): ?>
                <div class="success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($profile_message); ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="form-group">
                    <label for="new_name"><i class="fas fa-user"></i> Nome Completo:</label>
                    <input type="text" id="new_name" name="new_name" placeholder="Deixe vazio para não alterar">
                </div>
                <div class="form-group">
                    <label for="new_email"><i class="fas fa-envelope"></i> Email:</label>
                    <input type="email" id="new_email" name="new_email" placeholder="Deixe vazio para não alterar">
                </div>
                <div class="form-group">
                    <label for="new_password">Nova Senha:</label>
                    <div style="position: relative; display: inline-block; width: 100%;">
                        <input type="password" id="new_password" name="new_password" placeholder="Deixe vazio para não alterar" style="padding-right: 3rem;">
                        <i class="fas fa-eye password-toggle" onclick="togglePasswordDashboard('new_password', this)" title="Mostrar senha"></i>
                    </div>
                </div>
                <button type="submit" name="update_profile" class="btn btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Links Úteis -->
    <div class="modal" id="linksModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-link"></i> Links Úteis</h3>
                <button class="close-modal" onclick="closeModal('linksModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p style="margin-bottom: 1.5rem; color: #cccccc;">Navegação:</p>
            <a href="../../index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Página Principal
            </a>
            <a href="login.php" class="btn btn-secondary">
                <i class="fas fa-sign-in-alt"></i> Página de Login
            </a>
            <a href="register.php" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i> Cadastro
            </a>
        </div>
    </div>

    <!-- Modal Comentários Recentes -->
    <div class="modal" id="commentsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-shield-alt"></i> Tentativas de Login</h3>
                <button class="close-modal" onclick="closeModal('commentsModal')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <?php
            $comments = $db->getComments();
            $recent_comments = array_slice($comments, 0, 5);
            
            if (!empty($recent_comments)):
                foreach ($recent_comments as $comment): ?>
                    <div class="comment">
                        <div class="comment-date">
                            <i class="fas fa-clock"></i> <?php echo $comment['created_at']; ?>
                        </div>
                        <strong><?php echo $comment['name']; // VULNERÁVEL - sem escape ?></strong>
                        <div class="comment-text"><?php 
                            // Sanitizar apenas alertas para melhorar UX, mantendo outros XSS para fins educacionais
                            $comment_text = $comment['comment'];
                            $comment_text = str_replace(['alert(', 'Alert(', 'ALERT('], 'console.log(', $comment_text);
                            echo $comment_text; // VULNERÁVEL A XSS (exceto alertas)
                        ?></div>
                    </div>
                <?php endforeach;
            else: ?>
                <div class="comment">
                    <i class="fas fa-info-circle"></i> Nenhum comentário encontrado.
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="../../index.php#comments" class="btn btn-primary">
                    <i class="fas fa-eye"></i> Ver Todos os Comentários
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Usuários -->
    <div id="usersModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-users"></i> Todos os Usuários</h2>
                <span class="close" onclick="closeModal('usersModal')">&times;</span>
            </div>
            <div class="modal-body">
                <?php
                $all_users = $db->query("SELECT id, username, full_name, email, role, created_at FROM users ORDER BY created_at DESC");
                if ($all_users && count($all_users) > 0): ?>
                    <div class="users-table">
                        <table id="usersTable" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: rgba(0, 188, 212, 0.1); border-bottom: 2px solid #00bcd4;">
                                    <th style="padding: 0.8rem; text-align: left; color: #00bcd4; cursor: pointer; user-select: none;" onclick="sortTable('usersTable', 0)" title="Clique para ordenar por ID">
                                        ID <i class="fas fa-sort" style="margin-left: 0.5rem; font-size: 0.8rem;"></i>
                                    </th>
                                    <th style="padding: 0.8rem; text-align: left; color: #00bcd4; cursor: pointer; user-select: none;" onclick="sortTable('usersTable', 1)" title="Clique para ordenar por Usuário">
                                        Usuário <i class="fas fa-sort" style="margin-left: 0.5rem; font-size: 0.8rem;"></i>
                                    </th>
                                    <th style="padding: 0.8rem; text-align: left; color: #00bcd4; cursor: pointer; user-select: none;" onclick="sortTable('usersTable', 2)" title="Clique para ordenar por Nome">
                                        Nome <i class="fas fa-sort" style="margin-left: 0.5rem; font-size: 0.8rem;"></i>
                                    </th>
                                    <th style="padding: 0.8rem; text-align: left; color: #00bcd4; cursor: pointer; user-select: none;" onclick="sortTable('usersTable', 3)" title="Clique para ordenar por Email">
                                        Email <i class="fas fa-sort" style="margin-left: 0.5rem; font-size: 0.8rem;"></i>
                                    </th>
                                    <th style="padding: 0.8rem; text-align: left; color: #00bcd4; cursor: pointer; user-select: none;" onclick="sortTable('usersTable', 4)" title="Clique para ordenar por Papel">
                                        Papel <i class="fas fa-sort" style="margin-left: 0.5rem; font-size: 0.8rem;"></i>
                                    </th>
                                    <th style="padding: 0.8rem; text-align: left; color: #00bcd4; cursor: pointer; user-select: none;" onclick="sortTable('usersTable', 5)" title="Clique para ordenar por Data">
                                        Data <i class="fas fa-sort" style="margin-left: 0.5rem; font-size: 0.8rem;"></i>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($all_users as $user): ?>
                                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                        <td style="padding: 0.8rem; color: #cccccc;"><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td style="padding: 0.8rem; color: #ffffff; font-weight: 500;"><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td style="padding: 0.8rem; color: #cccccc;"><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td style="padding: 0.8rem; color: #cccccc;"><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td style="padding: 0.8rem;">
                                            <span style="background: <?php echo $user['role'] === 'admin' ? '#dc3545' : '#00bcd4'; ?>; color: white; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem;">
                                                <?php echo htmlspecialchars($user['role']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 0.8rem; color: #999; font-size: 0.9rem;"><?php echo $user['created_at']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: #cccccc;">
                        <i class="fas fa-user-times" style="font-size: 3rem; margin-bottom: 1rem; color: #666;"></i>
                        <p>Nenhum usuário encontrado.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Comentários Completos -->
    <div id="allCommentsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-comments"></i> Todos os Comentários</h2>
                <span class="close" onclick="closeModal('allCommentsModal')">&times;</span>
            </div>
            <div class="modal-body">
                <?php
                $all_comments = $db->query("SELECT id, name, comment, created_at FROM comments ORDER BY created_at DESC");
                if ($all_comments && count($all_comments) > 0): ?>
                    <?php foreach($all_comments as $comment): ?>
                        <div class="comment" style="margin-bottom: 1rem; background: rgba(255, 255, 255, 0.05); padding: 1rem; border-radius: 8px; border-left: 3px solid #00bcd4;">
                            <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 0.5rem;">
                                <strong style="color: #00bcd4;"><?php echo $comment['name']; // VULNERÁVEL - sem escape ?></strong>
                                <small style="color: #999; margin-left: auto;">
                                    <i class="fas fa-clock"></i> <?php echo $comment['created_at']; ?>
                                </small>
                            </div>
                            <div style="color: #cccccc; line-height: 1.5;">
                                <?php 
                                    // Sanitizar apenas alertas para melhorar UX, mantendo outros XSS para fins educacionais
                                    $comment_text = $comment['comment'];
                                    $comment_text = str_replace(['alert(', 'Alert(', 'ALERT('], 'console.log(', $comment_text);
                                    echo $comment_text; // VULNERÁVEL A XSS (exceto alertas)
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: #cccccc;">
                        <i class="fas fa-comment-slash" style="font-size: 3rem; margin-bottom: 1rem; color: #666;"></i>
                        <p>Nenhum comentário encontrado.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal Login Falhados -->
    <div id="failedLoginsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i> Tentativas de Login Falhadas</h2>
                <span class="close" onclick="closeModal('failedLoginsModal')">&times;</span>
            </div>
            <div class="modal-body">
                <?php
                $failed_login_attempts = $db->query("SELECT TOP 50 username, password_attempted, ip_address, user_agent, created_at FROM login_logs WHERE success = 0 ORDER BY created_at DESC");
                if ($failed_login_attempts && count($failed_login_attempts) > 0): ?>
                    
                    <!-- Filtros -->
                    <div style="background: rgba(255, 255, 255, 0.05); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="color: #cccccc; font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">Filtrar por Usuário:</label>
                                <input type="text" id="filterUsername" placeholder="Digite o usuário..." 
                                       style="width: 100%; padding: 0.5rem; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 4px; background: rgba(255, 255, 255, 0.05); color: #ffffff; font-size: 0.9rem;"
                                       onkeyup="filterFailedLogins()">
                            </div>
                            <div>
                                <label style="color: #cccccc; font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">Filtrar por IP:</label>
                                <input type="text" id="filterIP" placeholder="Digite o IP..." 
                                       style="width: 100%; padding: 0.5rem; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 4px; background: rgba(255, 255, 255, 0.05); color: #ffffff; font-size: 0.9rem;"
                                       onkeyup="filterFailedLogins()">
                            </div>
                            <div>
                                <label style="color: #cccccc; font-size: 0.9rem; margin-bottom: 0.5rem; display: block;">Filtrar por Senha:</label>
                                <input type="text" id="filterPassword" placeholder="Digite a senha..." 
                                       style="width: 100%; padding: 0.5rem; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 4px; background: rgba(255, 255, 255, 0.05); color: #ffffff; font-size: 0.9rem;"
                                       onkeyup="filterFailedLogins()">
                            </div>
                        </div>
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <button onclick="clearFilters()" 
                                    style="padding: 0.5rem 1rem; background: rgba(220, 53, 69, 0.2); color: #dc3545; border: 1px solid #dc3545; border-radius: 4px; cursor: pointer; font-size: 0.9rem;">
                                <i class="fas fa-times"></i> Limpar Filtros
                            </button>
                            <span id="filterResults" style="color: #cccccc; font-size: 0.9rem;"></span>
                        </div>
                    </div>

                    <div class="failed-logins-table">
                        <table id="failedLoginsTable" style="width: 100%; border-collapse: collapse; font-size: 0.9rem; table-layout: fixed;">
                            <colgroup>
                                <col style="width: 15%;">  <!-- Data/Hora -->
                                <col style="width: 15%;">  <!-- Usuário -->
                                <col style="width: 15%;">  <!-- Senha -->
                                <col style="width: 12%;">  <!-- IP -->
                                <col style="width: 43%;">  <!-- User Agent -->
                            </colgroup>
                            <thead>
                                <tr style="background: rgba(220, 53, 69, 0.1); border-bottom: 2px solid #dc3545;">
                                    <th style="padding: 0.6rem; text-align: left; color: #dc3545; cursor: pointer; user-select: none;" onclick="sortFailedLoginsTable(0)" title="Clique para ordenar por Data">
                                        Data/Hora <i class="fas fa-sort" style="margin-left: 0.5rem; font-size: 0.8rem;"></i>
                                    </th>
                                    <th style="padding: 0.6rem; text-align: left; color: #dc3545; cursor: pointer; user-select: none;" onclick="sortFailedLoginsTable(1)" title="Clique para ordenar por Usuário">
                                        Usuário <i class="fas fa-sort" style="margin-left: 0.5rem; font-size: 0.8rem;"></i>
                                    </th>
                                    <th style="padding: 0.6rem; text-align: left; color: #dc3545; cursor: pointer; user-select: none;" onclick="sortFailedLoginsTable(2)" title="Clique para ordenar por Senha">
                                        Senha <i class="fas fa-sort" style="margin-left: 0.5rem; font-size: 0.8rem;"></i>
                                    </th>
                                    <th style="padding: 0.6rem; text-align: left; color: #dc3545; cursor: pointer; user-select: none;" onclick="sortFailedLoginsTable(3)" title="Clique para ordenar por IP">
                                        IP <i class="fas fa-sort" style="margin-left: 0.5rem; font-size: 0.8rem;"></i>
                                    </th>
                                    <th style="padding: 0.6rem; text-align: left; color: #dc3545;">User Agent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($failed_login_attempts as $attempt): ?>
                                    <tr class="login-row" style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">
                                        <td style="padding: 0.6rem; color: #cccccc; white-space: nowrap;" data-date="<?php echo $attempt['created_at']; ?>"><?php echo $attempt['created_at']; ?></td>
                                        <td style="padding: 0.6rem; color: #ffffff; font-weight: 500; word-break: break-word;" data-username="<?php echo strtolower($attempt['username']); ?>"><?php echo htmlspecialchars($attempt['username']); ?></td>
                                        <td style="padding: 0.6rem; color: #ff6b6b; font-family: monospace; word-break: break-all;" data-password="<?php echo strtolower($attempt['password_attempted']); ?>"><?php echo htmlspecialchars($attempt['password_attempted']); ?></td>
                                        <td style="padding: 0.6rem; color: #cccccc; font-family: monospace;" data-ip="<?php echo $attempt['ip_address']; ?>"><?php echo htmlspecialchars($attempt['ip_address']); ?></td>
                                        <td style="padding: 0.6rem; color: #999; word-break: break-word; line-height: 1.3;" title="<?php echo htmlspecialchars($attempt['user_agent']); ?>">
                                            <?php echo htmlspecialchars($attempt['user_agent']); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 2rem; color: #cccccc;">
                        <i class="fas fa-shield-alt" style="font-size: 3rem; margin-bottom: 1rem; color: #28a745;"></i>
                        <p>Nenhuma tentativa de login falhada encontrada! 🎉</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // JavaScript para demonstrar vulnerabilidades
        document.addEventListener('DOMContentLoaded', function() {
            // Comentado: Alertas automáticos de IDOR removidos para melhor UX
            /*
            // Mostrar alerta se estiver acessando dados de outro usuário via IDOR
            const urlParams = new URLSearchParams(window.location.search);
            const userIdParam = urlParams.get('user_id');
            
            if (userIdParam) {
                const currentUserId = <?php echo json_encode($current_user['id'] ?? 'null'); ?>;
                if (userIdParam != currentUserId) {
                    setTimeout(() => {
                        alert('🎯 VULNERABILIDADE IDOR EXPLORADA!\n\n' +
                              'Você está vendo dados do usuário ' + userIdParam + 
                              ' sem ter permissão!\n\n' +
                              'Esta é uma falha grave de segurança.');
                    }, 1000);
                }
            }
            */
            
            // Demonstrar que dados sensíveis estão expostos no JavaScript (apenas no console)
            console.log('🚨 DADOS SENSÍVEIS EXPOSTOS NO JAVASCRIPT:');
            console.log('Usuário atual:', <?php echo json_encode($current_user); ?>);

            // Fechar dropdown ao clicar fora
            document.addEventListener('click', function(event) {
                const dropdown = document.getElementById('userDropdown');
                const userNameElement = document.querySelector('.user-name');
                
                if (!userNameElement.contains(event.target)) {
                    dropdown.classList.remove('show');
                    document.getElementById('dropdown-arrow').style.transform = 'rotate(0deg)';
                }
            });

            // Fechar modal ao clicar fora
            document.addEventListener('click', function(event) {
                if (event.target.classList.contains('modal')) {
                    event.target.classList.remove('show');
                    document.body.style.overflow = 'auto';
                }
            });

            // ESC para fechar modais
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const openModal = document.querySelector('.modal.show');
                    if (openModal) {
                        openModal.classList.remove('show');
                        document.body.style.overflow = 'auto';
                    }
                }
            });
        });

        // Função para toggle do dropdown
        function toggleDropdown() {
            const dropdown = document.getElementById('userDropdown');
            const arrow = document.getElementById('dropdown-arrow');
            
            dropdown.classList.toggle('show');
            
            if (dropdown.classList.contains('show')) {
                arrow.style.transform = 'rotate(180deg)';
            } else {
                arrow.style.transform = 'rotate(0deg)';
            }
        }

        // Função para abrir modal
        function openModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Fechar dropdown
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.remove('show');
            document.getElementById('dropdown-arrow').style.transform = 'rotate(0deg)';
        }

        // Função para fechar modal
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
        }

        // Funções para abrir modais das estatísticas
        function showUsersModal() {
            openModal('usersModal');
        }

        function showCommentsModal() {
            openModal('allCommentsModal');
        }

        function showFailedLoginsModal() {
            openModal('failedLoginsModal');
        }

        // Função para ordenação de tabelas
        function sortTable(tableId, columnIndex) {
            const table = document.getElementById(tableId);
            const tbody = table.getElementsByTagName('tbody')[0];
            const rows = Array.from(tbody.getElementsByTagName('tr'));
            const header = table.getElementsByTagName('th')[columnIndex];
            const icon = header.querySelector('i.fas');
            
            // Determinar direção da ordenação
            let ascending = true;
            if (icon.classList.contains('fa-sort-up')) {
                ascending = false;
                icon.className = 'fas fa-sort-down';
            } else {
                ascending = true;
                icon.className = 'fas fa-sort-up';
            }
            
            // Resetar outros ícones
            const allIcons = table.querySelectorAll('th i.fas');
            allIcons.forEach((otherIcon, index) => {
                if (index !== columnIndex) {
                    otherIcon.className = 'fas fa-sort';
                }
            });
            
            // Ordenar linhas
            rows.sort((a, b) => {
                let aValue = a.getElementsByTagName('td')[columnIndex].textContent.trim();
                let bValue = b.getElementsByTagName('td')[columnIndex].textContent.trim();
                
                // Tratamento especial para números (ID) e datas
                if (columnIndex === 0) { // ID
                    aValue = parseInt(aValue);
                    bValue = parseInt(bValue);
                } else if (columnIndex === 5) { // Data
                    aValue = new Date(aValue);
                    bValue = new Date(bValue);
                }
                
                if (aValue < bValue) {
                    return ascending ? -1 : 1;
                } else if (aValue > bValue) {
                    return ascending ? 1 : -1;
                } else {
                    return 0;
                }
            });
            
            // Reordenar as linhas na tabela
            rows.forEach(row => tbody.appendChild(row));
        }

        // Função para toggle de senha no dashboard
        function togglePasswordDashboard(fieldId, icon) {
            const field = document.getElementById(fieldId);
            
            if (!field) {
                console.error('Campo não encontrado:', fieldId);
                return;
            }

            console.log('Toggle password para:', fieldId, 'Tipo atual:', field.type);
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                icon.setAttribute('title', 'Ocultar senha');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                icon.setAttribute('title', 'Mostrar senha');
            }
        }

        // Função para toggle de senha na seção "Seus Dados"
        function toggleUserPassword(icon) {
            const passwordVisible = document.getElementById('user-password');
            const passwordHidden = document.getElementById('user-password-hidden');
            
            if (passwordVisible.style.display !== 'none') {
                // Mostrar senha real
                passwordVisible.style.display = 'none';
                passwordHidden.style.display = 'inline-block';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                icon.setAttribute('title', 'Ocultar senha');
            } else {
                // Ocultar senha (mostrar pontos)
                passwordVisible.style.display = 'inline-block';
                passwordHidden.style.display = 'none';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                icon.setAttribute('title', 'Mostrar senha');
            }
        }

        // Inicializar tooltips quando a página carregar
        document.addEventListener('DOMContentLoaded', function() {
            // Adicionar título inicial aos ícones de senha
            const passwordToggles = document.querySelectorAll('.password-toggle');
            passwordToggles.forEach(function(toggle) {
                toggle.setAttribute('title', 'Mostrar senha');
            });
            
            // Inicializar contador de resultados se modal existir
            const filterResults = document.getElementById('filterResults');
            if (filterResults) {
                const table = document.getElementById('failedLoginsTable');
                if (table) {
                    const total = table.querySelectorAll('tbody tr').length;
                    filterResults.textContent = `Exibindo ${total} de ${total} tentativas`;
                }
            }
        });

        // Função para filtrar logins falhados
        function filterFailedLogins() {
            const usernameFilter = document.getElementById('filterUsername').value.toLowerCase();
            const ipFilter = document.getElementById('filterIP').value.toLowerCase();
            const passwordFilter = document.getElementById('filterPassword').value.toLowerCase();
            
            const table = document.getElementById('failedLoginsTable');
            const rows = table.querySelectorAll('tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const username = row.querySelector('[data-username]').getAttribute('data-username');
                const ip = row.querySelector('[data-ip]').getAttribute('data-ip').toLowerCase();
                const password = row.querySelector('[data-password]').getAttribute('data-password');
                
                const matchUsername = username.includes(usernameFilter);
                const matchIP = ip.includes(ipFilter);
                const matchPassword = password.includes(passwordFilter);
                
                if (matchUsername && matchIP && matchPassword) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Atualizar contador de resultados
            const total = rows.length;
            const resultText = `Exibindo ${visibleCount} de ${total} tentativas`;
            document.getElementById('filterResults').textContent = resultText;
            
            // Se há filtros ativos, destacar
            const hasFilters = usernameFilter || ipFilter || passwordFilter;
            const filterResultsEl = document.getElementById('filterResults');
            if (hasFilters) {
                filterResultsEl.style.color = '#64b5f6';
                filterResultsEl.style.fontWeight = '500';
            } else {
                filterResultsEl.style.color = '#cccccc';
                filterResultsEl.style.fontWeight = 'normal';
            }
        }

        // Função para limpar todos os filtros
        function clearFilters() {
            document.getElementById('filterUsername').value = '';
            document.getElementById('filterIP').value = '';
            document.getElementById('filterPassword').value = '';
            
            const table = document.getElementById('failedLoginsTable');
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.style.display = '';
            });
            
            // Atualizar contador
            const total = rows.length;
            document.getElementById('filterResults').textContent = `Exibindo ${total} de ${total} tentativas`;
            document.getElementById('filterResults').style.color = '#cccccc';
            document.getElementById('filterResults').style.fontWeight = 'normal';
        }

        // Atualizar função sortTable para suportar tabela de logins falhados
        function sortFailedLoginsTable(columnIndex) {
            const table = document.getElementById('failedLoginsTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr:not([style*="display: none"])'));
            const header = table.querySelectorAll('th')[columnIndex];
            const icon = header.querySelector('i.fas');
            
            if (rows.length === 0) return;
            
            // Determinar direção da ordenação
            let ascending = true;
            if (icon.classList.contains('fa-sort-up')) {
                ascending = false;
                icon.className = 'fas fa-sort-down';
            } else {
                ascending = true;
                icon.className = 'fas fa-sort-up';
            }
            
            // Resetar outros ícones
            const allIcons = table.querySelectorAll('th i.fas');
            allIcons.forEach((otherIcon, index) => {
                if (index !== columnIndex) {
                    otherIcon.className = 'fas fa-sort';
                }
            });
            
            // Ordenar linhas
            rows.sort((a, b) => {
                let aValue = a.cells[columnIndex].textContent.trim();
                let bValue = b.cells[columnIndex].textContent.trim();
                
                // Tratamento especial para datas na primeira coluna
                if (columnIndex === 0) {
                    aValue = new Date(aValue);
                    bValue = new Date(bValue);
                }
                
                if (aValue < bValue) {
                    return ascending ? -1 : 1;
                } else if (aValue > bValue) {
                    return ascending ? 1 : -1;
                } else {
                    return 0;
                }
            });
            
            // Manter linhas ocultas no final
            const hiddenRows = Array.from(tbody.querySelectorAll('tr[style*="display: none"]'));
            
            // Limpar tbody e reordenar
            tbody.innerHTML = '';
            rows.forEach(row => tbody.appendChild(row));
            hiddenRows.forEach(row => tbody.appendChild(row));
        }
    </script>
</body>
</html>
