<?php
require_once 'config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$form_data = [
    'username' => '',
    'email' => '',
    'first_name' => '',
    'last_name' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        // Sanitize and validate input
        $form_data['username'] = sanitizeInput($_POST['username'] ?? '');
        $form_data['email'] = sanitizeInput($_POST['email'] ?? '');
        $form_data['first_name'] = sanitizeInput($_POST['first_name'] ?? '');
        $form_data['last_name'] = sanitizeInput($_POST['last_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validation
        if (empty($form_data['username'])) {
            $errors[] = 'Le nom d\'utilisateur est requis';
        } elseif (strlen($form_data['username']) < 3) {
            $errors[] = 'Le nom d\'utilisateur doit contenir au moins 3 caractères';
        }

        if (empty($form_data['email'])) {
            $errors[] = 'L\'email est requis';
        } elseif (!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format d\'email invalide';
        }

        if (empty($form_data['first_name'])) {
            $errors[] = 'Le prénom est requis';
        }

        if (empty($form_data['last_name'])) {
            $errors[] = 'Le nom est requis';
        }

        if (empty($password)) {
            $errors[] = 'Le mot de passe est requis';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
        }

        if ($password !== $confirm_password) {
            $errors[] = 'Les mots de passe ne correspondent pas';
        }

        // Check for existing users
        if (empty($errors)) {
            $database = new Database();
            $db = $database->getConnection();
            $userModel = new User($db);

            if ($userModel->findByUsername($form_data['username'])) {
                $errors[] = 'Ce nom d\'utilisateur est déjà utilisé';
            }

            if ($userModel->findByEmail($form_data['email'])) {
                $errors[] = 'Cet email est déjà utilisé';
            }
        }

        // Register user if no errors
        if (empty($errors)) {
            $registration_data = $form_data;
            $registration_data['password'] = $password;

            if ($userModel->register($registration_data)) {
                handleSuccess('Inscription réussie ! Vous pouvez maintenant vous connecter.', 'login.php');
            } else {
                $errors[] = 'Erreur lors de l\'inscription. Veuillez réessayer.';
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
    <title>Inscription - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body style="background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);">
    <?php include 'includes/header.php'; ?>

    <main class="auth-main">
        <div class="container">
            <div class="auth-form fade-in-up" style="max-width: 500px;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                        ✨
                    </div>
                    <h1>Créer votre compte</h1>
                    <p style="color: var(--gray-600); margin-top: 0.5rem;">Rejoignez notre communauté de développeurs créatifs</p>
                </div>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <strong>❌ Erreurs détectées :</strong>
                        <ul style="margin-top: 0.5rem;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first_name">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    👤 Prénom
                                </span>
                            </label>
                            <input type="text" id="first_name" name="first_name" 
                                   value="<?php echo htmlspecialchars($form_data['first_name']); ?>" 
                                   required placeholder="Votre prénom" autocomplete="given-name">
                        </div>

                        <div class="form-group">
                            <label for="last_name">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    👤 Nom
                                </span>
                            </label>
                            <input type="text" id="last_name" name="last_name" 
                                   value="<?php echo htmlspecialchars($form_data['last_name']); ?>" 
                                   required placeholder="Votre nom" autocomplete="family-name">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                🏷️ Nom d'utilisateur
                            </span>
                        </label>
                        <input type="text" id="username" name="username" 
                               value="<?php echo htmlspecialchars($form_data['username']); ?>" 
                               required placeholder="Nom d'utilisateur unique" autocomplete="username">
                        <small>Au moins 3 caractères, sera visible publiquement</small>
                    </div>

                    <div class="form-group">
                        <label for="email">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                📧 Adresse email
                            </span>
                        </label>
                        <input type="email" id="email" name="email" 
                               value="<?php echo htmlspecialchars($form_data['email']); ?>" 
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
                                   placeholder="Choisissez un mot de passe sécurisé" autocomplete="new-password">
                            <button type="button" onclick="togglePassword('password')" 
                                    style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-500);">
                                👁️
                            </button>
                        </div>
                        <small>Au moins 6 caractères</small>
                        <div id="passwordStrength" style="margin-top: 0.5rem;"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                🔐 Confirmer le mot de passe
                            </span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="confirm_password" name="confirm_password" required 
                                   placeholder="Répétez votre mot de passe" autocomplete="new-password">
                            <button type="button" onclick="togglePassword('confirm_password')" 
                                    style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-500);">
                                👁️
                            </button>
                        </div>
                        <div id="passwordMatch" style="margin-top: 0.5rem;"></div>
                    </div>

                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" required style="accent-color: var(--primary);">
                            <span style="font-size: 0.875rem; color: var(--gray-600);">
                                J'accepte les <a href="#" style="color: var(--primary);">conditions d'utilisation</a> et la <a href="#" style="color: var(--primary);">politique de confidentialité</a>
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full" id="submitBtn">
                        <span id="submitText">🚀 Créer mon compte</span>
                        <span id="submitLoader" style="display: none;">⏳ Création en cours...</span>
                    </button>
                </form>

                <div class="auth-links">
                    <div style="display: flex; align-items: center; gap: 1rem; margin: 1.5rem 0;">
                        <div style="flex: 1; height: 1px; background: var(--gray-300);"></div>
                        <span style="color: var(--gray-500); font-size: 0.875rem;">ou</span>
                        <div style="flex: 1; height: 1px; background: var(--gray-300);"></div>
                    </div>
                    <p>
                        <span style="color: var(--gray-600);">Déjà un compte ?</span>
                        <a href="login.php" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-left: 0.5rem;">
                            🔐 Se connecter
                        </a>
                    </p>
                </div>

                <!-- Benefits -->
                <div style="margin-top: 2rem; padding: 1.5rem; background: var(--gray-50); border-radius: 1rem; border: 1px solid var(--gray-200);">
                    <h4 style="color: var(--gray-700); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                        🎁 Avantages de l'inscription
                    </h4>
                    <ul style="font-size: 0.875rem; color: var(--gray-600); list-style: none; padding: 0;">
                        <li style="margin-bottom: 0.5rem;">✅ Portfolio professionnel personnalisé</li>
                        <li style="margin-bottom: 0.5rem;">✅ Gestion illimitée de projets</li>
                        <li style="margin-bottom: 0.5rem;">✅ Partage facile avec la communauté</li>
                        <li>✅ Interface moderne et sécurisée</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Form animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const inputs = form.querySelectorAll('input');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            
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

            // Password strength indicator
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strengthDiv = document.getElementById('passwordStrength');
                let strength = 0;
                let feedback = '';
                
                if (password.length >= 6) strength++;
                if (password.match(/[a-z]/)) strength++;
                if (password.match(/[A-Z]/)) strength++;
                if (password.match(/[0-9]/)) strength++;
                if (password.match(/[^a-zA-Z0-9]/)) strength++;
                
                switch(strength) {
                    case 0:
                    case 1:
                        feedback = '<span style="color: var(--error);">❌ Faible</span>';
                        break;
                    case 2:
                    case 3:
                        feedback = '<span style="color: var(--warning);">⚠️ Moyen</span>';
                        break;
                    case 4:
                    case 5:
                        feedback = '<span style="color: var(--success);">✅ Fort</span>';
                        break;
                }
                
                strengthDiv.innerHTML = feedback;
            });

            // Password match indicator
            function checkPasswordMatch() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                const matchDiv = document.getElementById('passwordMatch');
                
                if (confirmPassword.length > 0) {
                    if (password === confirmPassword) {
                        matchDiv.innerHTML = '<span style="color: var(--success);">✅ Les mots de passe correspondent</span>';
                    } else {
                        matchDiv.innerHTML = '<span style="color: var(--error);">❌ Les mots de passe ne correspondent pas</span>';
                    }
                } else {
                    matchDiv.innerHTML = '';
                }
            }

            passwordInput.addEventListener('input', checkPasswordMatch);
            confirmPasswordInput.addEventListener('input', checkPasswordMatch);

            // Form submission with loading state
            form.addEventListener('submit', function() {
                const submitBtn = document.getElementById('submitBtn');
                const submitText = document.getElementById('submitText');
                const submitLoader = document.getElementById('submitLoader');
                
                submitBtn.disabled = true;
                submitText.style.display = 'none';
                submitLoader.style.display = 'inline';
                
                // Re-enable after 5 seconds in case of error
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitText.style.display = 'inline';
                    submitLoader.style.display = 'none';
                }, 5000);
            });
        });

        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
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