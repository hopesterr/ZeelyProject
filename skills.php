<?php
require_once 'config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$skillModel = new Skill($db);

$all_skills = $skillModel->getAllSkills();
$user_skills = $skillModel->getUserSkills($_SESSION['user_id']);
$errors = [];
$success = '';

// Create arrays for easier processing
$user_skill_ids = array_column($user_skills, 'id');
$user_skills_data = [];
foreach ($user_skills as $skill) {
    $user_skills_data[$skill['id']] = [
        'level' => $skill['level'],
        'years_experience' => $skill['years_experience']
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_skills') {
            $selected_skills = $_POST['skills'] ?? [];
            $skill_levels = $_POST['skill_levels'] ?? [];
            $skill_experience = $_POST['skill_experience'] ?? [];
            
            // Remove all current user skills first
            foreach ($user_skill_ids as $skill_id) {
                $skillModel->removeUserSkill($_SESSION['user_id'], $skill_id);
            }
            
            // Add selected skills with levels
            $added_count = 0;
            foreach ($selected_skills as $skill_id) {
                $skill_id = (int)$skill_id;
                $level = $skill_levels[$skill_id] ?? 'beginner';
                $experience = (int)($skill_experience[$skill_id] ?? 0);
                
                if (in_array($level, ['beginner', 'intermediate', 'advanced', 'expert'])) {
                    if ($skillModel->addUserSkill($_SESSION['user_id'], $skill_id, $level, $experience)) {
                        $added_count++;
                    }
                }
            }
            
            if ($added_count > 0) {
                $success = "Compétences mises à jour avec succès ! ($added_count compétence" . ($added_count > 1 ? 's' : '') . " configurée" . ($added_count > 1 ? 's' : '') . ")";
                // Refresh user skills
                $user_skills = $skillModel->getUserSkills($_SESSION['user_id']);
                $user_skill_ids = array_column($user_skills, 'id');
                $user_skills_data = [];
                foreach ($user_skills as $skill) {
                    $user_skills_data[$skill['id']] = [
                        'level' => $skill['level'],
                        'years_experience' => $skill['years_experience']
                    ];
                }
            } else {
                $success = "Compétences mises à jour ! Aucune compétence sélectionnée.";
            }
        }
    }
}

// Group skills by category
$skills_by_category = [];
foreach ($all_skills as $skill) {
    $skills_by_category[$skill['category']][] = $skill;
}

// Sort categories
ksort($skills_by_category);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Compétences - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="dashboard-main">
        <div class="container">
            <!-- Skills Header -->
            <div class="dashboard-header fade-in-up">
                <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--accent), var(--primary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; position: relative;">
                        🎯
                        <div style="position: absolute; bottom: -5px; right: -5px; width: 30px; height: 30px; background: var(--success); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; border: 3px solid white;">
                            ⚡
                        </div>
                    </div>
                    <div>
                        <h1 style="margin: 0;">Mes Compétences</h1>
                        <p style="margin: 0; color: var(--gray-600);">
                            <strong><?php echo count($user_skills); ?></strong> compétence<?php echo count($user_skills) > 1 ? 's' : ''; ?> configurée<?php echo count($user_skills) > 1 ? 's' : ''; ?>
                        </p>
                    </div>
                </div>
                <p style="color: var(--gray-600); max-width: 600px; margin: 0 auto;">Sélectionnez vos compétences techniques et définissez votre niveau d'expertise pour chacune.</p>
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
                            <span style="font-size: 1.2rem;">💡</span>
                            Gestion des compétences
                        </h3>
                    </div>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <button onclick="selectAllSkills()" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1rem;">✅</span>
                            Tout sélectionner
                        </button>
                        <button onclick="clearAllSkills()" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1rem;">🗑️</span>
                            Tout désélectionner
                        </button>
                        <a href="dashboard.php" class="btn btn-secondary btn-sm" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1rem;">📊</span>
                            Tableau de bord
                        </a>
                    </div>
                </div>
            </div>

            <!-- Skills Statistics -->
            <?php if (!empty($user_skills)): ?>
                <div class="dashboard-card fade-in-up" style="animation-delay: 0.2s; margin-bottom: 2rem;">
                    <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                        <span style="font-size: 1.5rem;">📊</span>
                        Aperçu de vos compétences
                    </h3>
                    
                    <?php 
                    $level_counts = ['beginner' => 0, 'intermediate' => 0, 'advanced' => 0, 'expert' => 0];
                    foreach ($user_skills as $skill) {
                        $level_counts[$skill['level']]++;
                    }
                    ?>
                    
                    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
                        <div class="stat-item interactive">
                            <span class="stat-number" style="color: var(--gray-500);"><?php echo $level_counts['beginner']; ?></span>
                            <span class="stat-label">Débutant</span>
                        </div>
                        <div class="stat-item interactive">
                            <span class="stat-number" style="color: var(--warning);"><?php echo $level_counts['intermediate']; ?></span>
                            <span class="stat-label">Intermédiaire</span>
                        </div>
                        <div class="stat-item interactive">
                            <span class="stat-number" style="color: var(--primary);"><?php echo $level_counts['advanced']; ?></span>
                            <span class="stat-label">Avancé</span>
                        </div>
                        <div class="stat-item interactive">
                            <span class="stat-number" style="color: var(--success);"><?php echo $level_counts['expert']; ?></span>
                            <span class="stat-label">Expert</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Skills Form -->
            <div class="dashboard-card fade-in-up" style="animation-delay: 0.3s;">
                <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 2rem;">
                    <span style="font-size: 1.5rem;">🛠️</span>
                    Sélectionner vos compétences
                </h3>

                <form method="POST" action="" id="skillsForm">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_skills">
                    
                    <?php if (empty($skills_by_category)): ?>
                        <div class="empty-state">
                            <div style="font-size: 4rem; margin-bottom: 1rem;">🔧</div>
                            <p>Aucune compétence disponible pour le moment.</p>
                            <p style="font-size: 1rem; color: var(--gray-400); margin-bottom: 2rem;">Contactez l'administrateur pour ajouter des compétences.</p>
                        </div>
                    <?php else: ?>
                        <div style="display: grid; gap: 2rem;">
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
                                'Testing' => '🧪'
                            ];
                            
                            foreach ($skills_by_category as $category => $skills): 
                            ?>
                                <div class="skill-category-section" style="background: var(--white); border-radius: 1.5rem; padding: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid var(--gray-200);">
                                    <h4 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem; color: var(--gray-800); font-size: 1.25rem;">
                                        <span style="font-size: 1.5rem;">
                                            <?php echo $category_icons[$category] ?? '💡'; ?>
                                        </span>
                                        <?php echo htmlspecialchars($category); ?>
                                        <span style="background: var(--gray-100); color: var(--gray-600); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
                                            <?php echo count($skills); ?> compétence<?php echo count($skills) > 1 ? 's' : ''; ?>
                                        </span>
                                    </h4>
                                    
                                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
                                        <?php foreach ($skills as $skill): ?>
                                            <?php 
                                            $is_selected = in_array($skill['id'], $user_skill_ids);
                                            $current_level = $user_skills_data[$skill['id']]['level'] ?? 'beginner';
                                            $current_experience = $user_skills_data[$skill['id']]['years_experience'] ?? 0;
                                            ?>
                                            <div class="skill-item" style="border: 2px solid <?php echo $is_selected ? 'var(--primary)' : 'var(--gray-200)'; ?>; border-radius: 1rem; padding: 1.5rem; transition: all 0.3s ease; background: <?php echo $is_selected ? 'rgba(99, 102, 241, 0.05)' : 'var(--gray-50)'; ?>;">
                                                <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                                    <label class="checkbox-label" style="cursor: pointer; flex: 1;">
                                                        <input type="checkbox" name="skills[]" value="<?php echo $skill['id']; ?>" 
                                                               <?php echo $is_selected ? 'checked' : ''; ?>
                                                               onchange="toggleSkillDetails(this, <?php echo $skill['id']; ?>)"
                                                               style="accent-color: var(--primary); transform: scale(1.2);">
                                                        <span style="font-weight: 600; color: var(--gray-800); font-size: 1rem;">
                                                            <?php echo htmlspecialchars($skill['name']); ?>
                                                        </span>
                                                    </label>
                                                </div>
                                                
                                                <?php if ($skill['description']): ?>
                                                    <p style="color: var(--gray-600); font-size: 0.875rem; margin-bottom: 1rem; line-height: 1.4;">
                                                        <?php echo htmlspecialchars($skill['description']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div id="skill-details-<?php echo $skill['id']; ?>" class="skill-details" style="display: <?php echo $is_selected ? 'block' : 'none'; ?>;">
                                                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                                                        <div>
                                                            <label style="display: block; font-weight: 600; color: var(--gray-700); margin-bottom: 0.5rem; font-size: 0.875rem;">
                                                                🎯 Niveau de maîtrise
                                                            </label>
                                                            <select name="skill_levels[<?php echo $skill['id']; ?>]" 
                                                                    style="width: 100%; padding: 0.5rem; border: 1px solid var(--gray-300); border-radius: 0.5rem; font-size: 0.875rem;">
                                                                <option value="beginner" <?php echo $current_level === 'beginner' ? 'selected' : ''; ?>>🌱 Débutant</option>
                                                                <option value="intermediate" <?php echo $current_level === 'intermediate' ? 'selected' : ''; ?>>⚡ Intermédiaire</option>
                                                                <option value="advanced" <?php echo $current_level === 'advanced' ? 'selected' : ''; ?>>🚀 Avancé</option>
                                                                <option value="expert" <?php echo $current_level === 'expert' ? 'selected' : ''; ?>>⭐ Expert</option>
                                                            </select>
                                                        </div>
                                                        
                                                        <div>
                                                            <label style="display: block; font-weight: 600; color: var(--gray-700); margin-bottom: 0.5rem; font-size: 0.875rem;">
                                                                📅 Années d'exp.
                                                            </label>
                                                            <input type="number" name="skill_experience[<?php echo $skill['id']; ?>]" 
                                                                   value="<?php echo $current_experience; ?>" min="0" max="50"
                                                                   style="width: 100%; padding: 0.5rem; border: 1px solid var(--gray-300); border-radius: 0.5rem; font-size: 0.875rem;">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Form Actions -->
                        <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--gray-200);">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span id="submitText">💾 Sauvegarder mes compétences</span>
                                <span id="submitLoader" style="display: none;">⏳ Sauvegarde...</span>
                            </button>
                            <a href="dashboard.php" class="btn btn-secondary">
                                ❌ Annuler
                            </a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Skills page interactions
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

            // Form submission with loading state
            document.getElementById('skillsForm').addEventListener('submit', function() {
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

            // Animate stats numbers if they exist
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

        function toggleSkillDetails(checkbox, skillId) {
            const details = document.getElementById('skill-details-' + skillId);
            const skillItem = checkbox.closest('.skill-item');
            
            if (checkbox.checked) {
                details.style.display = 'block';
                skillItem.style.borderColor = 'var(--primary)';
                skillItem.style.backgroundColor = 'rgba(99, 102, 241, 0.05)';
                
                // Animate in
                details.style.opacity = '0';
                details.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    details.style.transition = 'all 0.3s ease';
                    details.style.opacity = '1';
                    details.style.transform = 'translateY(0)';
                }, 10);
            } else {
                details.style.display = 'none';
                skillItem.style.borderColor = 'var(--gray-200)';
                skillItem.style.backgroundColor = 'var(--gray-50)';
            }
        }

        function selectAllSkills() {
            const checkboxes = document.querySelectorAll('input[name="skills[]"]');
            checkboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    checkbox.checked = true;
                    const skillId = checkbox.value;
                    toggleSkillDetails(checkbox, skillId);
                }
            });
        }

        function clearAllSkills() {
            const checkboxes = document.querySelectorAll('input[name="skills[]"]');
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    checkbox.checked = false;
                    const skillId = checkbox.value;
                    toggleSkillDetails(checkbox, skillId);
                }
            });
        }

        // Add smooth transitions to skill items
        document.querySelectorAll('.skill-item').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 25px -5px rgba(0, 0, 0, 0.1)';
            });
            
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    </script>
</body>
</html>