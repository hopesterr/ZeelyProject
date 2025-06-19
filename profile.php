<?php
require_once 'config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$userModel = new User($db);

$user = $userModel->findById($_SESSION['user_id']);
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            // Update profile information
            $first_name = sanitizeInput($_POST['first_name'] ?? '');
            $last_name = sanitizeInput($_POST['last_name'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            $bio = sanitizeInput($_POST['bio'] ?? '');
            
            // Validation
            if (empty($first_name)) {
                $errors[] = 'Le prénom est requis';
            }
            if (empty($last_name)) {
                $errors[] = 'Le nom est requis';
            }
            if (empty($email)) {
                $errors[] = 'L\'email est requis';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format d\'email invalide';
            }
            
            // Check if email is already used by another user
            if (empty($errors) && $email !== $user['email']) {
                $existing_user = $userModel->findByEmail($email);
                if ($existing_user && $existing_user['id'] !== $_SESSION['user_id']) {
                    $errors[] = 'Cet email est déjà utilisé par un autre utilisateur';
                }
            }
            
            if (empty($errors)) {
                $profile_data = [
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'bio' => $bio
                ];
                
                if ($userModel->updateProfile($_SESSION['user_id'], $profile_data)) {
                    // Update session data
                    $_SESSION['first_name'] = $first_name;
                    $_SESSION['last_name'] = $last_name;
                    $_SESSION['email'] = $email;
                    
                    $success = 'Profil mis à jour avec succès !';
                    $user = $userModel->findById($_SESSION['user_id']); // Refresh user data
                } else {
                    $errors[] = 'Erreur lors de la mise à jour du profil';
                }
            }
        } elseif ($action === 'change_password') {
            // Change password
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            // Validation
            if (empty($current_password)) {
                $errors[] = 'Le mot de passe actuel est requis';
            } elseif (!password_verify($current_password, $user['password_hash'])) {
                $errors[] = 'Mot de passe actuel incorrect';
            }
            
            if (empty($new_password)) {
                $errors[] = 'Le nouveau mot de passe est requis';
            } elseif (strlen($new_password) < 6) {
                $errors[] = 'Le nouveau mot de passe doit contenir au moins 6 caractères';
            }
            
            if ($new_password !== $confirm_password) {
                $errors[] = 'Les nouveaux mots de passe ne correspondent pas';
            }
            
            if (empty($errors)) {
                if ($userModel->updatePassword($_SESSION['user_id'], $new_password)) {
                    $success = 'Mot de passe modifié avec succès !';
                } else {
                    $errors[] = 'Erreur lors de la modification du mot de passe';
                }
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
    <title>Mon Profil - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="dashboard-main">
        <div class="container">
            <!-- Profile Header -->
            <div class="dashboard-header fade-in-up">
                <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; position: relative;">
                        👤
                        <div style="position: absolute; bottom: -5px; right: -5px; width: 30px; height: 30px; background: var(--success); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; border: 3px solid white;">
                            ✏️
                        </div>
                    </div>
                    <div>
                        <h1 style="margin: 0;">Mon Profil</h1>
                        <p style="margin: 0; color: var(--gray-600);">
                            <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span style="display: inline-block; margin-left: 0.5rem; background: linear-gradient(135deg, var(--warning), #f97316); color: white; padding: 0.125rem 0.5rem; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 600;">👑 Admin</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <p style="color: var(--gray-600); max-width: 600px; margin: 0 auto;">Gérez vos informations personnelles et paramètres de sécurité.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error fade-in">
                    <strong>❌ Erreurs détectées :</strong>
                    <ul style="margin-top: 0.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success fade-in">
                    <strong>✅ Succès :</strong> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Content Grid -->
            <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr;">
                <!-- Profile Information Form -->
                <div class="dashboard-card fade-in-up" style="animation-delay: 0.1s;">
                    <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem;">
                        <span style="font-size: 1.5rem;">📝</span>
                        Informations personnelles
                    </h3>

                    <form method="POST" action="" id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">
                                    <span style="display: flex; align-items: center; gap: 0.5rem;">
                                        👤 Prénom
                                    </span>
                                </label>
                                <input type="text" id="first_name" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" 
                                       required placeholder="Votre prénom">
                            </div>

                            <div class="form-group">
                                <label for="last_name">
                                    <span style="display: flex; align-items: center; gap: 0.5rem;">
                                        👤 Nom
                                    </span>
                                </label>
                                <input type="text" id="last_name" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" 
                                       required placeholder="Votre nom">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    📧 Adresse email
                                </span>
                            </label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" 
                                   required placeholder="votre@email.com">
                        </div>

                        <div class="form-group">
                            <label for="bio">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    📄 Biographie
                                </span>
                            </label>
                            <textarea id="bio" name="bio" rows="4" 
                                      placeholder="Parlez-nous de vous, vos passions, votre parcours..."
                                      style="width: 100%; padding: 0.875rem 1rem; border: 1px solid var(--gray-300); border-radius: 0.75rem; font-size: 1rem; transition: all 0.2s ease; background: var(--white); resize: vertical; min-height: 100px; font-family: inherit;"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                            <small>Décrivez-vous en quelques mots (optionnel)</small>
                        </div>

                        <button type="submit" class="btn btn-primary" id="profileSubmitBtn">
                            <span id="profileSubmitText">💾 Sauvegarder les modifications</span>
                            <span id="profileSubmitLoader" style="display: none;">⏳ Sauvegarde...</span>
                        </button>
                    </form>
                </div>

                <!-- Account Information & Security -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <!-- Account Info Card -->
                    <div class="dashboard-card fade-in-up" style="animation-delay: 0.2s;">
                        <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                            <span style="font-size: 1.2rem;">ℹ️</span>
                            Informations du compte
                        </h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div class="stat-item" style="text-align: left; padding: 1rem; background: var(--gray-50); border-radius: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <span style="font-size: 1rem;">🏷️</span>
                                    <span style="font-size: 0.875rem; color: var(--gray-600);">Nom d'utilisateur</span>
                                </div>
                                <span style="font-weight: 600; color: var(--gray-800);"><?php echo htmlspecialchars($user['username']); ?></span>
                            </div>
                            
                            <div class="stat-item" style="text-align: left; padding: 1rem; background: var(--gray-50); border-radius: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <span style="font-size: 1rem;">👑</span>
                                    <span style="font-size: 0.875rem; color: var(--gray-600);">Rôle</span>
                                </div>
                                <span style="font-weight: 600; color: var(--primary);">
                                    <?php echo $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur'; ?>
                                </span>
                            </div>
                            
                            <div class="stat-item" style="text-align: left; padding: 1rem; background: var(--gray-50); border-radius: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <span style="font-size: 1rem;">📅</span>
                                    <span style="font-size: 0.875rem; color: var(--gray-600);">Membre depuis</span>
                                </div>
                                <span style="font-weight: 600; color: var(--gray-800);">
                                    <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="dashboard-card fade-in-up" style="animation-delay: 0.3s;">
                        <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                            <span style="font-size: 1.2rem;">⚡</span>
                            Actions rapides
                        </h3>
                        
                        <div class="quick-actions">
                            <button onclick="togglePasswordForm()" class="btn btn-secondary" style="justify-content: flex-start; gap: 0.75rem;">
                                <span style="font-size: 1.2rem;">🔑</span>
                                Changer le mot de passe
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary" style="justify-content: flex-start; gap: 0.75rem;">
                                <span style="font-size: 1.2rem;">📊</span>
                                Retour au tableau de bord
                            </a>
                            <a href="my-projects.php" class="btn btn-secondary" style="justify-content: flex-start; gap: 0.75rem;">
                                <span style="font-size: 1.2rem;">💼</span>
                                Mes projets
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Change Form (Hidden by default) -->
            <div id="passwordSection" class="dashboard-card fade-in-up" style="display: none; margin-top: 2rem; animation-delay: 0.4s;">
                <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem;">
                    <span style="font-size: 1.5rem;">🔐</span>
                    Changer le mot de passe
                </h3>

                <form method="POST" action="" id="passwordForm" style="max-width: 500px;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                🔓 Mot de passe actuel
                            </span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="current_password" name="current_password" 
                                   required placeholder="Votre mot de passe actuel">
                            <button type="button" onclick="togglePassword('current_password')" 
                                    style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-500);">
                                👁️
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="new_password">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                🔑 Nouveau mot de passe
                            </span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="new_password" name="new_password" 
                                   required placeholder="Choisissez un nouveau mot de passe">
                            <button type="button" onclick="togglePassword('new_password')" 
                                    style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-500);">
                                👁️
                            </button>
                        </div>
                        <small>Au moins 6 caractères</small>
                        <div id="newPasswordStrength" style="margin-top: 0.5rem;"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                🔐 Confirmer le nouveau mot de passe
                            </span>
                        </label>
                        <div style="position: relative;">
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   required placeholder="Répétez le nouveau mot de passe">
                            <button type="button" onclick="togglePassword('confirm_password')" 
                                    style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--gray-500);">
                                👁️
                            </button>
                        </div>
                        <div id="passwordMatch" style="margin-top: 0.5rem;"></div>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary" id="passwordSubmitBtn">
                            <span id="passwordSubmitText">🔐 Changer le mot de passe</span>
                            <span id="passwordSubmitLoader" style="display: none;">⏳ Modification...</span>
                        </button>
                        <button type="button" onclick="togglePasswordForm()" class="btn btn-secondary">
                            ❌ Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Form animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate form inputs
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'translateY(-2px)';
                    this.parentElement.style.transition = 'transform 0.2s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'translateY(0)';
                });
            });

            // Profile form submission
            document.getElementById('profileForm').addEventListener('submit', function() {
                const submitBtn = document.getElementById('profileSubmitBtn');
                const submitText = document.getElementById('profileSubmitText');
                const submitLoader = document.getElementById('profileSubmitLoader');
                
                submitBtn.disabled = true;
                submitText.style.display = 'none';
                submitLoader.style.display = 'inline';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitText.style.display = 'inline';
                    submitLoader.style.display = 'none';
                }, 3000);
            });

            // Password form submission
            document.getElementById('passwordForm').addEventListener('submit', function() {
                const submitBtn = document.getElementById('passwordSubmitBtn');
                const submitText = document.getElementById('passwordSubmitText');
                const submitLoader = document.getElementById('passwordSubmitLoader');
                
                submitBtn.disabled = true;
                submitText.style.display = 'none';
                submitLoader.style.display = 'inline';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitText.style.display = 'inline';
                    submitLoader.style.display = 'none';
                }, 3000);
            });

            // Password strength indicator
            document.getElementById('new_password').addEventListener('input', function() {
                const password = this.value;
                const strengthDiv = document.getElementById('newPasswordStrength');
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
                const newPassword = document.getElementById('new_password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                const matchDiv = document.getElementById('passwordMatch');
                
                if (confirmPassword.length > 0) {
                    if (newPassword === confirmPassword) {
                        matchDiv.innerHTML = '<span style="color: var(--success);">✅ Les mots de passe correspondent</span>';
                    } else {
                        matchDiv.innerHTML = '<span style="color: var(--error);">❌ Les mots de passe ne correspondent pas</span>';
                    }
                } else {
                    matchDiv.innerHTML = '';
                }
            }

            document.getElementById('new_password').addEventListener('input', checkPasswordMatch);
            document.getElementById('confirm_password').addEventListener('input', checkPasswordMatch);

            // Smooth scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all animated elements
            document.querySelectorAll('.fade-in, .fade-in-up').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                observer.observe(el);
            });
        });

        function togglePasswordForm() {
            const passwordSection = document.getElementById('passwordSection');
            if (passwordSection.style.display === 'none' || passwordSection.style.display === '') {
                passwordSection.style.display = 'block';
                passwordSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                passwordSection.style.display = 'none';
            }
        }

        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
        }

        // Auto-resize textarea
        document.getElementById('bio').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    </script>
</body>
</html>