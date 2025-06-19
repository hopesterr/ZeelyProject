<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            $error = 'Tous les champs sont requis';
        } else {
            $database = new Database();
            $db = $database->getConnection();
            $userModel = new User($db);

            $user = $userModel->login($email, $password);
            
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];

                // Handle remember me
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', time() + REMEMBER_TOKEN_LIFETIME);
                    
                    // Store token in database (you'd need to implement this)
                    setcookie('remember_token', $token, time() + REMEMBER_TOKEN_LIFETIME, '/', '', false, true);
                }

                handleSuccess('Connexion réussie', 'dashboard.php');
            } else {
                $error = 'Email ou mot de passe incorrect';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body style="background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);">
    <?php include 'includes/header.php'; ?>

    <main class="auth-main">
        <div class="container">
            <div class="auth-form fade-in-up">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                        🔐
                    </div>
                    <h1>Bon retour !</h1>
                    <p style="color: var(--gray-600); margin-top: 0.5rem;">Connectez-vous à votre compte pour continuer</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <strong>❌ Erreur :</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <strong>✅ Succès :</strong> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-group">
                        <label for="email">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                📧 Adresse email
                            </span>
                        </label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                               required placeholder="votre@email.com" autocomplete="email">
                    </div>

                    <div class="form-group">
                        <label for="password">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                🔑 Mot de passe
                            </span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="password" name="password" required 
                                   placeholder="Votre mot de passe" autocomplete="current-password">
                            <button type="button" onclick="togglePassword()" 
                                    style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-500);">
                                👁️
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" style="accent-color: var(--primary);">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                💾 Se souvenir de moi
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full" id="submitBtn">
                        <span id="submitText">Se connecter</span>
                        <span id="submitLoader" style="display: none;">⏳ Connexion...</span>
                    </button>
                </form>

                <div class="auth-links">
                    <p style="margin-bottom: 1rem;">
                        <a href="forgot-password.php" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                            🔄 Mot de passe oublié ?
                        </a>
                    </p>
                    <div style="display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0;">
                        <div style="flex: 1; height: 1px; background: var(--gray-300);"></div>
                        <span style="color: var(--gray-500); font-size: 0.875rem;">ou</span>
                        <div style="flex: 1; height: 1px; background: var(--gray-300);"></div>
                    </div>
                    <p>
                        <span style="color: var(--gray-600);">Pas encore de compte ?</span>
                        <a href="register.php" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-left: 0.5rem;">
                            ✨ Créer un compte
                        </a>
                    </p>
                </div>

                <!-- Demo Account Info -->
                <div style="margin-top: 2rem; padding: 1.5rem; background: var(--gray-50); border-radius: 1rem; border: 1px solid var(--gray-200);">
                    <h4 style="color: var(--gray-700); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        🎯 Compte de démonstration
                    </h4>
                    <div style="font-size: 0.875rem; color: var(--gray-600);">
                        <p><strong>Email :</strong> admin@portfolio.com</p>
                        <p><strong>Mot de passe :</strong> password</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Form animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const inputs = form.querySelectorAll('input');
            
            // Add focus animations
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                    this.parentElement.style.transition = 'transform 0.2s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });

            // Form submission with loading state
            form.addEventListener('submit', function() {
                const submitBtn = document.getElementById('submitBtn');
                const submitText = document.getElementById('submitText');
                const submitLoader = document.getElementById('submitLoader');
                
                submitBtn.disabled = true;
                submitText.style.display = 'none';
                submitLoader.style.display = 'inline';
                
                // Re-enable after 3 seconds in case of error
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitText.style.display = 'inline';
                    submitLoader.style.display = 'none';
                }, 3000);
            });
        });

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        }

        // Smooth entrance animation
        document.querySelector('.auth-form').style.opacity = '0';
        document.querySelector('.auth-form').style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            document.querySelector('.auth-form').style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            document.querySelector('.auth-form').style.opacity = '1';
            document.querySelector('.auth-form').style.transform = 'translateY(0)';
        }, 100);
    </script>
</body>
</html>