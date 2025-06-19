<?php
require_once 'config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$projectModel = new Project($db);
$skillModel = new Skill($db);

$user_projects = $projectModel->getUserProjects($_SESSION['user_id']);
$user_skills = $skillModel->getUserSkills($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="dashboard-main">
        <div class="container">
            <!-- Welcome Header -->
            <div class="dashboard-header fade-in-up">
                <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
                        👋
                    </div>
                    <div>
                        <h1 style="margin: 0;">Tableau de bord</h1>
                        <p style="margin: 0; color: var(--gray-600);">Bienvenue, <strong><?php echo htmlspecialchars($_SESSION['first_name']); ?></strong> !</p>
                    </div>
                </div>
                <p style="color: var(--gray-600); max-width: 600px; margin: 0 auto;">Gérez vos projets, compétences et suivez vos statistiques depuis votre espace personnel.</p>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success fade-in">
                    <strong>✅ Succès :</strong> <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error fade-in">
                    <strong>❌ Erreur :</strong> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Stats and Quick Actions Grid -->
            <div class="dashboard-grid">
                <!-- Statistics Card -->
                <div class="dashboard-card fade-in-up" style="animation-delay: 0.1s;">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                        📊 Statistiques
                    </h3>
                    <div class="stats-grid">
                        <div class="stat-item interactive">
                            <span class="stat-number"><?php echo count($user_projects); ?></span>
                            <span class="stat-label">Projets</span>
                        </div>
                        <div class="stat-item interactive">
                            <span class="stat-number"><?php echo count($user_skills); ?></span>
                            <span class="stat-label">Compétences</span>
                        </div>
                        <div class="stat-item interactive">
                            <span class="stat-number"><?php echo count(array_filter($user_projects, function($p) { return $p['status'] === 'published'; })); ?></span>
                            <span class="stat-label">Publiés</span>
                        </div>
                    </div>
                    
                    <!-- Progress indicators -->
                    <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid var(--gray-200);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.875rem; color: var(--gray-600);">Profil complété</span>
                            <span style="font-size: 0.875rem; font-weight: 600; color: var(--primary);">
                                <?php 
                                $completion = 20; // Base
                                if (count($user_projects) > 0) $completion += 40;
                                if (count($user_skills) > 0) $completion += 40;
                                echo $completion; 
                                ?>%
                            </span>
                        </div>
                        <div style="width: 100%; height: 8px; background: var(--gray-200); border-radius: 4px; overflow: hidden;">
                            <div style="width: <?php echo $completion; ?>%; height: 100%; background: linear-gradient(90deg, var(--primary), var(--secondary)); transition: width 0.5s ease;"></div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Card -->
                <div class="dashboard-card fade-in-up" style="animation-delay: 0.2s;">
                    <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                        ⚡ Actions rapides
                    </h3>
                    <div class="quick-actions">
                        <a href="project-form.php" class="btn btn-primary" style="justify-content: flex-start; gap: 0.75rem;">
                            <span style="font-size: 1.2rem;">📝</span>
                            Nouveau projet
                        </a>
                        <a href="profile.php" class="btn btn-secondary" style="justify-content: flex-start; gap: 0.75rem;">
                            <span style="font-size: 1.2rem;">👤</span>
                            Modifier profil
                        </a>
                        <a href="skills.php" class="btn btn-secondary" style="justify-content: flex-start; gap: 0.75rem;">
                            <span style="font-size: 1.2rem;">🎯</span>
                            Gérer compétences
                        </a>
                        <?php if (isAdmin()): ?>
                            <a href="admin.php" class="btn" style="background: linear-gradient(135deg, var(--warning), #f97316); color: white; justify-content: flex-start; gap: 0.75rem;">
                                <span style="font-size: 1.2rem;">⚙️</span>
                                Administration
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Projects Section -->
            <div class="dashboard-section fade-in-up" style="animation-delay: 0.3s;">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h2 style="display: flex; align-items: center; gap: 0.75rem; margin: 0;">
                        <span style="font-size: 1.5rem;">💼</span>
                        Mes projets récents
                    </h2>
                    <a href="project-form.php" class="btn btn-primary btn-sm">
                        <span style="margin-right: 0.5rem;">➕</span>
                        Ajouter un projet
                    </a>
                </div>

                <?php if (empty($user_projects)): ?>
                    <div class="empty-state">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">📁</div>
                        <p>Vous n'avez pas encore créé de projet.</p>
                        <p style="font-size: 1rem; color: var(--gray-400); margin-bottom: 2rem;">Commencez par partager votre premier projet avec la communauté !</p>
                        <a href="project-form.php" class="btn btn-primary">
                            <span style="margin-right: 0.5rem;">🚀</span>
                            Créer votre premier projet
                        </a>
                    </div>
                <?php else: ?>
                    <div class="projects-table" style="background: var(--white); border-radius: 1.5rem; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid var(--gray-200);">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead style="background: var(--gray-50);">
                                <tr>
                                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700); border-bottom: 1px solid var(--gray-200);">Projet</th>
                                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700); border-bottom: 1px solid var(--gray-200);">Statut</th>
                                    <th style="padding: 1rem; text-align: left; font-weight: 600; color: var(--gray-700); border-bottom: 1px solid var(--gray-200);">Créé le</th>
                                    <th style="padding: 1rem; text-align: center; font-weight: 600; color: var(--gray-700); border-bottom: 1px solid var(--gray-200);">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($user_projects, 0, 5) as $project): ?>
                                    <tr style="border-bottom: 1px solid var(--gray-100); transition: background-color 0.2s ease;" onmouseover="this.style.backgroundColor='var(--gray-50)'" onmouseout="this.style.backgroundColor='transparent'">
                                        <td style="padding: 1rem;">
                                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                <div style="width: 40px; height: 40px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                                    💼
                                                </div>
                                                <div>
                                                    <strong style="color: var(--gray-900);"><?php echo htmlspecialchars($project['title']); ?></strong>
                                                    <?php if ($project['featured']): ?>
                                                        <span style="display: inline-block; margin-left: 0.5rem; background: var(--primary); color: white; padding: 0.125rem 0.5rem; border-radius: 0.75rem; font-size: 0.75rem; font-weight: 600;">⭐ En vedette</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <?php 
                                            $status_config = [
                                                'draft' => ['label' => 'Brouillon', 'color' => 'var(--gray-500)', 'bg' => 'var(--gray-100)', 'icon' => '📝'],
                                                'published' => ['label' => 'Publié', 'color' => 'var(--success)', 'bg' => 'rgba(16, 185, 129, 0.1)', 'icon' => '✅'],
                                                'archived' => ['label' => 'Archivé', 'color' => 'var(--warning)', 'bg' => 'rgba(245, 158, 11, 0.1)', 'icon' => '📦']
                                            ];
                                            $config = $status_config[$project['status']];
                                            ?>
                                            <span style="display: inline-flex; align-items: center; gap: 0.25rem; background: <?php echo $config['bg']; ?>; color: <?php echo $config['color']; ?>; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 500;">
                                                <?php echo $config['icon']; ?>
                                                <?php echo $config['label']; ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem; color: var(--gray-600);">
                                            <?php echo date('d/m/Y', strtotime($project['created_at'])); ?>
                                        </td>
                                        <td style="padding: 1rem;">
                                            <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                                <a href="voirprojet.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-secondary" title="Voir le projet">
                                                    👁️
                                                </a>
                                                <a href="project-form.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary" title="Modifier">
                                                    ✏️
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <?php if (count($user_projects) > 5): ?>
                            <div style="padding: 1rem; text-align: center; border-top: 1px solid var(--gray-200); background: var(--gray-50);">
                                <a href="my-projects.php" class="btn btn-secondary btn-sm">
                                    <span style="margin-right: 0.5rem;">📋</span>
                                    Voir tous mes projets (<?php echo count($user_projects); ?>)
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Skills Overview Section -->
            <div class="dashboard-section fade-in-up" style="animation-delay: 0.4s;">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h2 style="display: flex; align-items: center; gap: 0.75rem; margin: 0;">
                        <span style="font-size: 1.5rem;">🎯</span>
                        Mes compétences
                    </h2>
                    <a href="skills.php" class="btn btn-secondary btn-sm">
                        <span style="margin-right: 0.5rem;">⚙️</span>
                        Gérer
                    </a>
                </div>

                <?php if (empty($user_skills)): ?>
                    <div class="empty-state">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">🎯</div>
                        <p>Vous n'avez pas encore ajouté de compétences.</p>
                        <p style="font-size: 1rem; color: var(--gray-400); margin-bottom: 2rem;">Montrez vos talents en ajoutant vos compétences techniques !</p>
                        <a href="skills.php" class="btn btn-primary">
                            <span style="margin-right: 0.5rem;">➕</span>
                            Ajouter des compétences
                        </a>
                    </div>
                <?php else: ?>
                    <div class="skills-overview" style="background: var(--white); padding: 2rem; border-radius: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid var(--gray-200);">
                        <?php 
                        $skills_by_category = [];
                        foreach ($user_skills as $skill) {
                            $skills_by_category[$skill['category']][] = $skill;
                        }
                        ?>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                            <?php foreach ($skills_by_category as $category => $skills): ?>
                                <div class="skill-category">
                                    <h4 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem; color: var(--gray-800);">
                                        <span style="font-size: 1.2rem;">
                                            <?php 
                                            $category_icons = [
                                                'Frontend' => '🎨',
                                                'Backend' => '⚙️',
                                                'Database' => '🗄️',
                                                'Tools' => '🔧',
                                                'DevOps' => '🚀'
                                            ];
                                            echo $category_icons[$category] ?? '💡';
                                            ?>
                                        </span>
                                        <?php echo htmlspecialchars($category); ?>
                                    </h4>
                                    <div class="skills-list" style="display: flex; flex-direction: column; gap: 0.75rem;">
                                        <?php foreach ($skills as $skill): ?>
                                            <div class="skill-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; background: var(--gray-50); border-radius: 0.75rem; border: 1px solid var(--gray-200);">
                                                <span class="skill-name" style="font-weight: 500; color: var(--gray-800);">
                                                    <?php echo htmlspecialchars($skill['name']); ?>
                                                </span>
                                                <?php 
                                                $level_config = [
                                                    'beginner' => ['label' => 'Débutant', 'color' => 'var(--gray-500)', 'bg' => 'var(--gray-100)'],
                                                    'intermediate' => ['label' => 'Intermédiaire', 'color' => 'var(--warning)', 'bg' => 'rgba(245, 158, 11, 0.1)'],
                                                    'advanced' => ['label' => 'Avancé', 'color' => 'var(--primary)', 'bg' => 'rgba(99, 102, 241, 0.1)'],
                                                    'expert' => ['label' => 'Expert', 'color' => 'var(--success)', 'bg' => 'rgba(16, 185, 129, 0.1)']
                                                ];
                                                $config = $level_config[$skill['level']];
                                                ?>
                                                <span style="background: <?php echo $config['bg']; ?>; color: <?php echo $config['color']; ?>; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem; font-weight: 600;">
                                                    <?php echo $config['label']; ?>
                                                </span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Dashboard animations and interactions
        document.addEventListener('DOMContentLoaded', function() {
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
        });

        // Welcome animation
        setTimeout(() => {
            const welcomeIcon = document.querySelector('.dashboard-header div div');
            if (welcomeIcon) {
                welcomeIcon.style.animation = 'bounce 1s ease-in-out';
            }
        }, 1000);

        // Add bounce animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes bounce {
                0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                40% { transform: translateY(-10px); }
                60% { transform: translateY(-5px); }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>