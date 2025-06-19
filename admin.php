<?php
require_once 'config/config.php';
requireAdmin(); // Only admins can access this page

$database = new Database();
$db = $database->getConnection();
$skillModel = new Skill($db);

$all_skills = $skillModel->getAllSkills();
$errors = [];
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'add_skill') {
            // Add new skill
            $name = sanitizeInput($_POST['name'] ?? '');
            $category = sanitizeInput($_POST['category'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            
            // Validation
            if (empty($name)) {
                $errors[] = 'Le nom de la compétence est requis';
            } elseif (strlen($name) < 2) {
                $errors[] = 'Le nom doit contenir au moins 2 caractères';
            }
            
            if (empty($category)) {
                $errors[] = 'La catégorie est requise';
            }
            
            if (empty($errors)) {
                $skill_data = [
                    'name' => $name,
                    'category' => $category,
                    'description' => $description
                ];
                
                if ($skillModel->createSkill($skill_data)) {
                    $success = 'Compétence ajoutée avec succès !';
                    $all_skills = $skillModel->getAllSkills(); // Refresh list
                } else {
                    $errors[] = 'Erreur lors de l\'ajout de la compétence';
                }
            }
        } elseif ($action === 'edit_skill') {
            // Edit existing skill
            $skill_id = (int)($_POST['skill_id'] ?? 0);
            $name = sanitizeInput($_POST['name'] ?? '');
            $category = sanitizeInput($_POST['category'] ?? '');
            $description = sanitizeInput($_POST['description'] ?? '');
            
            // Validation
            if (empty($name)) {
                $errors[] = 'Le nom de la compétence est requis';
            } elseif (strlen($name) < 2) {
                $errors[] = 'Le nom doit contenir au moins 2 caractères';
            }
            
            if (empty($category)) {
                $errors[] = 'La catégorie est requise';
            }
            
            if (empty($errors)) {
                $skill_data = [
                    'name' => $name,
                    'category' => $category,
                    'description' => $description
                ];
                
                if ($skillModel->updateSkill($skill_id, $skill_data)) {
                    $success = 'Compétence modifiée avec succès !';
                    $all_skills = $skillModel->getAllSkills(); // Refresh list
                } else {
                    $errors[] = 'Erreur lors de la modification de la compétence';
                }
            }
        } elseif ($action === 'delete_skill') {
            // Delete skill
            $skill_id = (int)($_POST['skill_id'] ?? 0);
            
            if ($skillModel->deleteSkill($skill_id)) {
                $success = 'Compétence supprimée avec succès !';
                $all_skills = $skillModel->getAllSkills(); // Refresh list
            } else {
                $errors[] = 'Erreur lors de la suppression de la compétence';
            }
        }
    }
}

// Group skills by category for display
$skills_by_category = [];
foreach ($all_skills as $skill) {
    $skills_by_category[$skill['category']][] = $skill;
}
ksort($skills_by_category);

// Get categories for the form
$categories = [
    'Frontend' => 'Frontend',
    'Backend' => 'Backend', 
    'Database' => 'Database',
    'Tools' => 'Tools',
    'DevOps' => 'DevOps',
    'Mobile' => 'Mobile',
    'Framework' => 'Framework',
    'Language' => 'Language',
    'Cloud' => 'Cloud',
    'Testing' => 'Testing',
    'Design' => 'Design',
    'Security' => 'Security'
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Compétences - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="dashboard-main">
        <div class="container">
            <!-- Admin Header -->
            <div class="dashboard-header fade-in-up">
                <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--warning), #f97316); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; position: relative;">
                        ⚙️
                        <div style="position: absolute; bottom: -5px; right: -5px; width: 30px; height: 30px; background: var(--error); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; border: 3px solid white;">
                            👑
                        </div>
                    </div>
                    <div>
                        <h1 style="margin: 0;">Administration - Compétences</h1>
                        <p style="margin: 0; color: var(--gray-600);">
                            <strong><?php echo count($all_skills); ?></strong> compétence<?php echo count($all_skills) > 1 ? 's' : ''; ?> au total
                        </p>
                    </div>
                </div>
                <p style="color: var(--gray-600); max-width: 600px; margin: 0 auto;">Gérez les compétences disponibles pour tous les utilisateurs de la plateforme.</p>
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

            <!-- Quick Actions Bar -->
            <div class="dashboard-card fade-in-up" style="animation-delay: 0.1s; margin-bottom: 2rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <h3 style="margin: 0; display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.2rem;">⚡</span>
                            Actions administrateur
                        </h3>
                    </div>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <button onclick="showAddSkillModal()" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.1rem;">➕</span>
                            Nouvelle compétence
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.1rem;">📊</span>
                            Tableau de bord
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="dashboard-card fade-in-up" style="animation-delay: 0.2s; margin-bottom: 2rem;">
                <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                    <span style="font-size: 1.5rem;">📊</span>
                    Statistiques des compétences
                </h3>
                
                <?php 
                $category_counts = [];
                foreach ($all_skills as $skill) {
                    $category_counts[$skill['category']] = ($category_counts[$skill['category']] ?? 0) + 1;
                }
                ?>
                
                <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                    <div class="stat-item interactive">
                        <span class="stat-number" style="color: var(--primary);"><?php echo count($all_skills); ?></span>
                        <span class="stat-label">Total compétences</span>
                    </div>
                    <div class="stat-item interactive">
                        <span class="stat-number" style="color: var(--accent);"><?php echo count($skills_by_category); ?></span>
                        <span class="stat-label">Catégories</span>
                    </div>
                    <div class="stat-item interactive">
                        <span class="stat-number" style="color: var(--success);"><?php echo max($category_counts ?: [0]); ?></span>
                        <span class="stat-label">Plus grande catégorie</span>
                    </div>
                </div>
            </div>

            <!-- Skills Management -->
            <?php if (empty($all_skills)): ?>
                <div class="empty-state fade-in-up" style="animation-delay: 0.3s;">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem;">🛠️</div>
                    <h3 style="color: var(--gray-700); margin-bottom: 1rem;">Aucune compétence créée</h3>
                    <p>Commencez par ajouter des compétences techniques pour vos utilisateurs.</p>
                    <p style="font-size: 1rem; color: var(--gray-400); margin-bottom: 2rem;">Les utilisateurs pourront ensuite sélectionner leurs compétences et niveaux.</p>
                    <button onclick="showAddSkillModal()" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.2rem;">🚀</span>
                        Créer la première compétence
                    </button>
                </div>
            <?php else: ?>
                <!-- Skills by Category -->
                <?php 
                $category_icons = [
                    'Frontend' => '🎨',
                    'Backend' => '⚙️',
                    'Database' => '🗄️',
                    'Tools' => '🔧',
                    'DevOps' => '🚀',
                    'Mobile' => '📱',
                    'Framework' => '🏗️',
                    'Language' => '💻',
                    'Cloud' => '☁️',
                    'Testing' => '🧪',
                    'Design' => '🎭',
                    'Security' => '🔒'
                ];
                
                $delay = 0.3;
                foreach ($skills_by_category as $category => $skills):
                ?>
                    <div class="dashboard-section fade-in-up" style="animation-delay: <?php echo $delay; ?>s; margin-bottom: 3rem;">
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                            <h2 style="display: flex; align-items: center; gap: 0.75rem; margin: 0;">
                                <span style="font-size: 1.5rem;"><?php echo $category_icons[$category] ?? '💡'; ?></span>
                                <?php echo htmlspecialchars($category); ?>
                                <span style="background: var(--gray-100); color: var(--gray-600); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                    <?php echo count($skills); ?>
                                </span>
                            </h2>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
                            <?php foreach ($skills as $index => $skill): ?>
                                <div class="dashboard-card interactive" style="animation-delay: <?php echo $delay + ($index * 0.1); ?>s; position: relative;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                        <h4 style="margin: 0; color: var(--gray-900); font-size: 1.1rem;">
                                            <?php echo htmlspecialchars($skill['name']); ?>
                                        </h4>
                                        <div class="skill-menu" style="position: relative;">
                                            <button onclick="toggleSkillMenu(<?php echo $skill['id']; ?>)" 
                                                    style="background: none; border: none; cursor: pointer; padding: 0.5rem; border-radius: 0.5rem; color: var(--gray-500); transition: all 0.2s ease;">
                                                ⋮
                                            </button>
                                            <div id="skill-menu-<?php echo $skill['id']; ?>" class="skill-dropdown" 
                                                 style="display: none; position: absolute; right: 0; top: 100%; background: white; border: 1px solid var(--gray-200); border-radius: 0.75rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); z-index: 10; min-width: 180px;">
                                                <button onclick="editSkill(<?php echo $skill['id']; ?>, '<?php echo htmlspecialchars($skill['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($skill['category'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($skill['description'] ?? '', ENT_QUOTES); ?>')" 
                                                        style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: none; border: none; color: var(--gray-700); cursor: pointer; width: 100%; text-align: left; border-bottom: 1px solid var(--gray-100);">
                                                    ✏️ Modifier
                                                </button>
                                                <button onclick="confirmDeleteSkill(<?php echo $skill['id']; ?>, '<?php echo htmlspecialchars($skill['name'], ENT_QUOTES); ?>')" 
                                                        style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: none; border: none; color: var(--error); cursor: pointer; width: 100%; text-align: left;">
                                                    🗑️ Supprimer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div style="margin-bottom: 1rem;">
                                        <span style="background: var(--gray-100); color: var(--gray-700); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
                                            <?php echo htmlspecialchars($category); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($skill['description']): ?>
                                        <p style="color: var(--gray-600); font-size: 0.875rem; line-height: 1.5; margin-bottom: 1rem;">
                                            <?php echo htmlspecialchars($skill['description']); ?>
                                        </p>
                                    <?php endif; ?>
                                    
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--gray-200); font-size: 0.875rem; color: var(--gray-500);">
                                        <span>ID: <?php echo $skill['id']; ?></span>
                                        <span><?php echo date('d/m/Y', strtotime($skill['created_at'])); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php 
                    $delay += 0.1;
                endforeach; 
                ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add/Edit Skill Modal -->
    <div id="skillModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; backdrop-filter: blur(5px);">
        <div style="display: flex; align-items: center; justify-content: center; height: 100%; padding: 2rem;">
            <div style="background: white; border-radius: 1.5rem; padding: 2rem; max-width: 600px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); max-height: 90vh; overflow-y: auto;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                        🛠️
                    </div>
                    <h3 style="color: var(--gray-900); margin-bottom: 0.5rem;" id="modalTitle">Ajouter une compétence</h3>
                    <p style="color: var(--gray-600);">Remplissez les informations de la compétence</p>
                </div>
                
                <form method="POST" id="skillForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" id="formAction" value="add_skill">
                    <input type="hidden" name="skill_id" id="skillId">
                    
                    <div class="form-group">
                        <label for="skillName">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                🏷️ Nom de la compétence *
                            </span>
                        </label>
                        <input type="text" id="skillName" name="name" required 
                               placeholder="Ex: JavaScript, PHP, React..." maxlength="100">
                        <small>Nom unique et descriptif de la compétence</small>
                    </div>

                    <div class="form-group">
                        <label for="skillCategory">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                📂 Catégorie *
                            </span>
                        </label>
                        <select id="skillCategory" name="category" required>
                            <option value="">Sélectionner une catégorie</option>
                            <?php foreach ($categories as $value => $label): ?>
                                <option value="<?php echo htmlspecialchars($value); ?>">
                                    <?php echo $category_icons[$value] ?? '💡'; ?> <?php echo htmlspecialchars($label); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Catégorie pour organiser les compétences</small>
                    </div>

                    <div class="form-group">
                        <label for="skillDescription">
                            <span style="display: flex; align-items: center; gap: 0.5rem;">
                                📄 Description (optionnelle)
                            </span>
                        </label>
                        <textarea id="skillDescription" name="description" rows="3" 
                                  placeholder="Description courte de la compétence..."
                                  style="width: 100%; padding: 0.875rem 1rem; border: 1px solid var(--gray-300); border-radius: 0.75rem; font-size: 1rem; transition: all 0.2s ease; background: var(--white); resize: vertical; font-family: inherit;"></textarea>
                        <small>Explication optionnelle de la compétence</small>
                    </div>

                    <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                        <button type="submit" class="btn btn-primary" id="modalSubmitBtn">
                            <span id="modalSubmitText">💾 Ajouter la compétence</span>
                            <span id="modalSubmitLoader" style="display: none;">⏳ Sauvegarde...</span>
                        </button>
                        <button type="button" onclick="closeSkillModal()" class="btn btn-secondary">
                            ❌ Annuler
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; backdrop-filter: blur(5px);">
        <div style="display: flex; align-items: center; justify-content: center; height: 100%; padding: 2rem;">
            <div style="background: white; border-radius: 1.5rem; padding: 2rem; max-width: 500px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                        🗑️
                    </div>
                    <h3 style="color: var(--gray-900); margin-bottom: 0.5rem;">Confirmer la suppression</h3>
                    <p style="color: var(--gray-600);">Êtes-vous sûr de vouloir supprimer la compétence :</p>
                    <p style="font-weight: 600; color: var(--error);" id="skillToDelete"></p>
                    <p style="color: var(--gray-500); font-size: 0.875rem;">Cette action supprimera aussi toutes les associations utilisateur-compétence.</p>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button onclick="closeDeleteModal()" class="btn btn-secondary">
                        ❌ Annuler
                    </button>
                    <form method="POST" style="display: inline;" id="deleteForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="delete_skill">
                        <input type="hidden" name="skill_id" id="deleteSkillId">
                        <button type="submit" class="btn" style="background: var(--error); color: white;">
                            🗑️ Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Admin page interactions
        document.addEventListener('DOMContentLoaded', function() {
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

            // Interactive hover effects
            document.querySelectorAll('.interactive').forEach(el => {
                el.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.transition = 'transform 0.2s ease';
                });
                
                el.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.skill-menu')) {
                    document.querySelectorAll('.skill-dropdown').forEach(dropdown => {
                        dropdown.style.display = 'none';
                    });
                }
            });

            // Form submission with loading state
            document.getElementById('skillForm').addEventListener('submit', function() {
                const submitBtn = document.getElementById('modalSubmitBtn');
                const submitText = document.getElementById('modalSubmitText');
                const submitLoader = document.getElementById('modalSubmitLoader');
                
                submitBtn.disabled = true;
                submitText.style.display = 'none';
                submitLoader.style.display = 'inline';
                
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitText.style.display = 'inline';
                    submitLoader.style.display = 'none';
                }, 3000);
            });

            // Animate stats numbers
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers.forEach(stat => {
                const finalValue = parseInt(stat.textContent);
                let currentValue = 0;
                const increment = Math.ceil(finalValue / 20);
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(timer);
                    }
                    stat.textContent = currentValue;
                }, 50);
            });
        });

        function toggleSkillMenu(skillId) {
            const menu = document.getElementById('skill-menu-' + skillId);
            const isVisible = menu.style.display === 'block';
            
            // Close all other menus
            document.querySelectorAll('.skill-dropdown').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
            
            // Toggle current menu
            menu.style.display = isVisible ? 'none' : 'block';
        }

        function showAddSkillModal() {
            document.getElementById('modalTitle').textContent = 'Ajouter une compétence';
            document.getElementById('formAction').value = 'add_skill';
            document.getElementById('skillId').value = '';
            document.getElementById('skillName').value = '';
            document.getElementById('skillCategory').value = '';
            document.getElementById('skillDescription').value = '';
            document.getElementById('modalSubmitText').textContent = '💾 Ajouter la compétence';
            
            document.getElementById('skillModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Focus on name field
            setTimeout(() => {
                document.getElementById('skillName').focus();
            }, 100);
        }

        function editSkill(id, name, category, description) {
            document.getElementById('modalTitle').textContent = 'Modifier la compétence';
            document.getElementById('formAction').value = 'edit_skill';
            document.getElementById('skillId').value = id;
            document.getElementById('skillName').value = name;
            document.getElementById('skillCategory').value = category;
            document.getElementById('skillDescription').value = description;
            document.getElementById('modalSubmitText').textContent = '💾 Modifier la compétence';
            
            document.getElementById('skillModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Close the dropdown menu
            document.querySelectorAll('.skill-dropdown').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
            
            // Focus on name field
            setTimeout(() => {
                document.getElementById('skillName').focus();
            }, 100);
        }

        function closeSkillModal() {
            document.getElementById('skillModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function confirmDeleteSkill(skillId, skillName) {
            document.getElementById('deleteSkillId').value = skillId;
            document.getElementById('skillToDelete').textContent = skillName;
            document.getElementById('deleteModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Close the dropdown menu
            document.querySelectorAll('.skill-dropdown').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modals with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSkillModal();
                closeDeleteModal();
            }
        });

        // Auto-resize textarea
        document.getElementById('skillDescription').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    </script>
</body>
</html>