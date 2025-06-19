<?php
require_once 'config/config.php';
require_once 'utils/FileUpload.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$projectModel = new Project($db);

$errors = [];
$success = '';
$project = null;
$is_edit = false;

// Check if editing existing project
if (isset($_GET['id'])) {
    $project_id = (int)$_GET['id'];
    $project = $projectModel->getProjectById($project_id);
    
    if (!$project || $project['user_id'] != $_SESSION['user_id']) {
        handleError('Projet non trouvé ou accès non autorisé', 'projects.php');
    }
    $is_edit = true;
}

// Initialize form data
$form_data = [
    'title' => $project['title'] ?? '',
    'description' => $project['description'] ?? '',
    'external_link' => $project['external_link'] ?? '',
    'github_link' => $project['github_link'] ?? '',
    'technologies' => $project['technologies'] ?? '',
    'status' => $project['status'] ?? 'draft'
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        // Sanitize and validate input
        $form_data['title'] = sanitizeInput($_POST['title'] ?? '');
        $form_data['description'] = sanitizeInput($_POST['description'] ?? '');
        $form_data['external_link'] = sanitizeInput($_POST['external_link'] ?? '');
        $form_data['github_link'] = sanitizeInput($_POST['github_link'] ?? '');
        $form_data['technologies'] = sanitizeInput($_POST['technologies'] ?? '');
        $form_data['status'] = sanitizeInput($_POST['status'] ?? 'draft');

        // Validation
        if (empty($form_data['title'])) {
            $errors[] = 'Le titre est requis';
        } elseif (strlen($form_data['title']) < 3) {
            $errors[] = 'Le titre doit contenir au moins 3 caractères';
        }

        if (empty($form_data['description'])) {
            $errors[] = 'La description est requise';
        } elseif (strlen($form_data['description']) < 10) {
            $errors[] = 'La description doit contenir au moins 10 caractères';
        }

        if (!empty($form_data['external_link']) && !filter_var($form_data['external_link'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Le lien externe doit être une URL valide';
        }

        if (!empty($form_data['github_link']) && !filter_var($form_data['github_link'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Le lien GitHub doit être une URL valide';
        }

        if (!in_array($form_data['status'], ['draft', 'published', 'archived'])) {
            $errors[] = 'Statut invalide';
        }

        // Handle image upload
        $image_path = $project['image_path'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $fileUpload = new FileUpload();
                $new_image_path = $fileUpload->uploadImage($_FILES['image'], 'project_');
                
                // Delete old image if exists
                if ($image_path && file_exists($image_path)) {
                    unlink($image_path);
                }
                
                $image_path = $new_image_path;
            } catch (Exception $e) {
                $errors[] = 'Erreur lors de l\'upload de l\'image : ' . $e->getMessage();
            }
        }

        // Save project if no errors
        if (empty($errors)) {
            $project_data = $form_data;
            $project_data['image_path'] = $image_path;
            
            if ($is_edit) {
                if ($projectModel->updateProject($project_id, $project_data)) {
                    handleSuccess('Projet modifié avec succès !', 'projects.php');
                } else {
                    $errors[] = 'Erreur lors de la modification du projet';
                }
            } else {
                $project_data['user_id'] = $_SESSION['user_id'];
                if ($projectModel->createProject($project_data)) {
                    handleSuccess('Projet créé avec succès !', 'projects.php');
                } else {
                    $errors[] = 'Erreur lors de la création du projet';
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
    <title><?php echo $is_edit ? 'Modifier' : 'Nouveau'; ?> Projet - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="dashboard-main">
        <div class="container">
            <!-- Form Header -->
            <div class="dashboard-header fade-in-up">
                <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                        <?php echo $is_edit ? '✏️' : '🎨'; ?>
                    </div>
                    <div>
                        <h1 style="margin: 0;"><?php echo $is_edit ? 'Modifier le projet' : 'Nouveau projet'; ?></h1>
                        <p style="margin: 0; color: var(--gray-600);">
                            <?php echo $is_edit ? 'Mettez à jour les informations de votre projet' : 'Créez un nouveau projet pour votre portfolio'; ?>
                        </p>
                    </div>
                </div>
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

            <!-- Project Form -->
            <div class="dashboard-card fade-in-up" style="animation-delay: 0.1s; max-width: 800px; margin: 0 auto;">
                <form method="POST" action="" enctype="multipart/form-data" id="projectForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    
                    <!-- Basic Information -->
                    <div style="margin-bottom: 3rem;">
                        <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem; color: var(--gray-800);">
                            <span style="font-size: 1.5rem;">📝</span>
                            Informations de base
                        </h3>
                        
                        <div class="form-group">
                            <label for="title">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    🏷️ Titre du projet *
                                </span>
                            </label>
                            <input type="text" id="title" name="title" 
                                   value="<?php echo htmlspecialchars($form_data['title']); ?>" 
                                   required placeholder="Ex: Application de gestion de tâches" maxlength="200">
                            <small>Un titre accrocheur qui décrit votre projet</small>
                        </div>

                        <div class="form-group">
                            <label for="description">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    📄 Description détaillée *
                                </span>
                            </label>
                            <textarea id="description" name="description" rows="6" required 
                                      placeholder="Décrivez votre projet : objectifs, fonctionnalités, défis relevés, technologies utilisées..."
                                      style="width: 100%; padding: 0.875rem 1rem; border: 1px solid var(--gray-300); border-radius: 0.75rem; font-size: 1rem; transition: all 0.2s ease; background: var(--white); resize: vertical; min-height: 150px; font-family: inherit;"><?php echo htmlspecialchars($form_data['description']); ?></textarea>
                            <small>Minimum 10 caractères. Soyez précis et détaillé !</small>
                        </div>

                        <div class="form-group">
                            <label for="technologies">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    🛠️ Technologies utilisées
                                </span>
                            </label>
                            <input type="text" id="technologies" name="technologies" 
                                   value="<?php echo htmlspecialchars($form_data['technologies']); ?>" 
                                   placeholder="Ex: PHP, MySQL, JavaScript, HTML, CSS">
                            <small>Séparez les technologies par des virgules</small>
                        </div>
                    </div>

                    <!-- Links -->
                    <div style="margin-bottom: 3rem;">
                        <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem; color: var(--gray-800);">
                            <span style="font-size: 1.5rem;">🔗</span>
                            Liens du projet
                        </h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="external_link">
                                    <span style="display: flex; align-items: center; gap: 0.5rem;">
                                        🌐 Lien du site web
                                    </span>
                                </label>
                                <input type="url" id="external_link" name="external_link" 
                                       value="<?php echo htmlspecialchars($form_data['external_link']); ?>" 
                                       placeholder="https://monprojet.com">
                                <small>URL de votre projet en ligne</small>
                            </div>

                            <div class="form-group">
                                <label for="github_link">
                                    <span style="display: flex; align-items: center; gap: 0.5rem;">
                                        💻 Lien GitHub
                                    </span>
                                </label>
                                <input type="url" id="github_link" name="github_link" 
                                       value="<?php echo htmlspecialchars($form_data['github_link']); ?>" 
                                       placeholder="https://github.com/username/projet">
                                <small>Lien vers le code source</small>
                            </div>
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div style="margin-bottom: 3rem;">
                        <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem; color: var(--gray-800);">
                            <span style="font-size: 1.5rem;">🖼️</span>
                            Image du projet
                        </h3>
                        
                        <?php if ($project && $project['image_path']): ?>
                            <div style="margin-bottom: 1.5rem;">
                                <p style="color: var(--gray-600); margin-bottom: 1rem;">Image actuelle :</p>
                                <div style="max-width: 300px; border-radius: 1rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                                    <img src="<?php echo htmlspecialchars($project['image_path']); ?>" 
                                         alt="Image actuelle" style="width: 100%; height: auto; display: block;">
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="image">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    📸 <?php echo $project && $project['image_path'] ? 'Changer l\'image' : 'Ajouter une image'; ?>
                                </span>
                            </label>
                            <input type="file" id="image" name="image" accept="image/*"
                                   style="width: 100%; padding: 0.875rem 1rem; border: 2px dashed var(--gray-300); border-radius: 0.75rem; font-size: 1rem; transition: all 0.2s ease; background: var(--gray-50);">
                            <small>Formats acceptés : JPEG, PNG, GIF, WebP. Taille max : 5MB</small>
                        </div>
                    </div>

                    <!-- Status -->
                    <div style="margin-bottom: 3rem;">
                        <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem; color: var(--gray-800);">
                            <span style="font-size: 1.5rem;">📊</span>
                            Statut de publication
                        </h3>
                        
                        <div class="form-group">
                            <label for="status">
                                <span style="display: flex; align-items: center; gap: 0.5rem;">
                                    🎯 Statut du projet
                                </span>
                            </label>
                            <select id="status" name="status" required
                                    style="width: 100%; padding: 0.875rem 1rem; border: 1px solid var(--gray-300); border-radius: 0.75rem; font-size: 1rem; transition: all 0.2s ease; background: var(--white);">
                                <option value="draft" <?php echo $form_data['status'] === 'draft' ? 'selected' : ''; ?>>
                                    📝 Brouillon - Non visible publiquement
                                </option>
                                <option value="published" <?php echo $form_data['status'] === 'published' ? 'selected' : ''; ?>>
                                    ✅ Publié - Visible par tous
                                </option>
                                <option value="archived" <?php echo $form_data['status'] === 'archived' ? 'selected' : ''; ?>>
                                    📦 Archivé - Projet terminé
                                </option>
                            </select>
                            <small>Choisissez la visibilité de votre projet</small>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div style="display: flex; gap: 1rem; justify-content: center; padding-top: 2rem; border-top: 1px solid var(--gray-200);">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span id="submitText">
                                <?php echo $is_edit ? '💾 Sauvegarder les modifications' : '🚀 Créer le projet'; ?>
                            </span>
                            <span id="submitLoader" style="display: none;">⏳ Sauvegarde...</span>
                        </button>
                        <a href="projects.php" class="btn btn-secondary">
                            ❌ Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Project form interactions
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('projectForm');
            const inputs = form.querySelectorAll('input, textarea, select');
            
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
                
                // Re-enable after 5 seconds in case of error
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitText.style.display = 'inline';
                    submitLoader.style.display = 'none';
                }, 5000);
            });

            // Auto-resize textarea
            const description = document.getElementById('description');
            description.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = this.scrollHeight + 'px';
            });

            // File input styling
            const fileInput = document.getElementById('image');
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    this.style.borderColor = 'var(--success)';
                    this.style.backgroundColor = 'rgba(16, 185, 129, 0.05)';
                } else {
                    this.style.borderColor = 'var(--gray-300)';
                    this.style.backgroundColor = 'var(--gray-50)';
                }
            });

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

        // Character counter for title
        document.getElementById('title').addEventListener('input', function() {
            const maxLength = 200;
            const currentLength = this.value.length;
            const remaining = maxLength - currentLength;
            
            let small = this.parentElement.querySelector('small');
            if (remaining < 20) {
                small.style.color = remaining < 0 ? 'var(--error)' : 'var(--warning)';
                small.textContent = `${remaining} caractères restants`;
            } else {
                small.style.color = 'var(--gray-500)';
                small.textContent = 'Un titre accrocheur qui décrit votre projet';
            }
        });

        // Character counter for description
        document.getElementById('description').addEventListener('input', function() {
            const minLength = 10;
            const currentLength = this.value.length;
            
            let small = this.parentElement.querySelector('small');
            if (currentLength < minLength) {
                small.style.color = 'var(--warning)';
                small.textContent = `${minLength - currentLength} caractères minimum requis`;
            } else {
                small.style.color = 'var(--success)';
                small.textContent = '✅ Description suffisante. Continuez à détailler !';
            }
        });
    </script>
</body>
</html>