<?php
require_once 'config/config.php';
requireLogin();

$database = new Database();
$db = $database->getConnection();
$projectModel = new Project($db);

$user_projects = $projectModel->getUserProjects($_SESSION['user_id']);
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'delete_project') {
            $project_id = (int)($_POST['project_id'] ?? 0);
            
            // Verify project belongs to user
            $project = $projectModel->getProjectById($project_id);
            if ($project && $project['user_id'] == $_SESSION['user_id']) {
                if ($projectModel->deleteProject($project_id)) {
                    $success = 'Projet supprimé avec succès !';
                    $user_projects = $projectModel->getUserProjects($_SESSION['user_id']); // Refresh list
                } else {
                    $errors[] = 'Erreur lors de la suppression du projet';
                }
            } else {
                $errors[] = 'Projet non trouvé ou accès non autorisé';
            }
        } elseif ($action === 'toggle_featured') {
            $project_id = (int)($_POST['project_id'] ?? 0);
            
            // Verify project belongs to user
            $project = $projectModel->getProjectById($project_id);
            if ($project && $project['user_id'] == $_SESSION['user_id']) {
                if ($projectModel->toggleFeatured($project_id)) {
                    $success = 'Statut "en vedette" modifié avec succès !';
                    $user_projects = $projectModel->getUserProjects($_SESSION['user_id']); // Refresh list
                } else {
                    $errors[] = 'Erreur lors de la modification du statut';
                }
            } else {
                $errors[] = 'Projet non trouvé ou accès non autorisé';
            }
        }
    }
}

// Group projects by status
$projects_by_status = [
    'published' => [],
    'draft' => [],
    'archived' => []
];

foreach ($user_projects as $project) {
    $projects_by_status[$project['status']][] = $project;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Projets - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="dashboard-main">
        <div class="container">
            <!-- Projects Header -->
            <div class="dashboard-header fade-in-up">
                <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1rem;">
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; position: relative;">
                        💼
                        <div style="position: absolute; bottom: -5px; right: -5px; width: 30px; height: 30px; background: var(--accent); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; border: 3px solid white;">
                            ⚡
                        </div>
                    </div>
                    <div>
                        <h1 style="margin: 0;">Mes Projets</h1>
                        <p style="margin: 0; color: var(--gray-600);">
                            <strong><?php echo count($user_projects); ?></strong> projet<?php echo count($user_projects) > 1 ? 's' : ''; ?> au total
                        </p>
                    </div>
                </div>
                <p style="color: var(--gray-600); max-width: 600px; margin: 0 auto;">Gérez tous vos projets : créez, modifiez, publiez et organisez votre portfolio professionnel.</p>
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
                            Actions rapides
                        </h3>
                    </div>
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                        <a href="project-form.php" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.1rem;">➕</span>
                            Nouveau projet
                        </a>
                        <a href="dashboard.php" class="btn btn-secondary" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.1rem;">📊</span>
                            Tableau de bord
                        </a>
                    </div>
                </div>
            </div>

            <?php if (empty($user_projects)): ?>
                <!-- Empty State -->
                <div class="empty-state fade-in-up" style="animation-delay: 0.2s;">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem;">🚀</div>
                    <h3 style="color: var(--gray-700); margin-bottom: 1rem;">Aucun projet créé</h3>
                    <p>Commencez par créer votre premier projet et partagez votre travail avec le monde !</p>
                    <p style="font-size: 1rem; color: var(--gray-400); margin-bottom: 2rem;">Montrez vos compétences et construisez votre portfolio professionnel.</p>
                    <a href="project-form.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.2rem;">🎨</span>
                        Créer mon premier projet
                    </a>
                </div>
            <?php else: ?>
                <!-- Projects Statistics -->
                <div class="dashboard-card fade-in-up" style="animation-delay: 0.2s; margin-bottom: 2rem;">
                    <h3 style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.5rem;">
                        <span style="font-size: 1.5rem;">📊</span>
                        Statistiques de vos projets
                    </h3>
                    
                    <div class="stats-grid">
                        <div class="stat-item interactive">
                            <span class="stat-number" style="color: var(--success);"><?php echo count($projects_by_status['published']); ?></span>
                            <span class="stat-label">Publiés</span>
                        </div>
                        <div class="stat-item interactive">
                            <span class="stat-number" style="color: var(--warning);"><?php echo count($projects_by_status['draft']); ?></span>
                            <span class="stat-label">Brouillons</span>
                        </div>
                        <div class="stat-item interactive">
                            <span class="stat-number" style="color: var(--gray-500);"><?php echo count($projects_by_status['archived']); ?></span>
                            <span class="stat-label">Archivés</span>
                        </div>
                    </div>
                </div>

                <!-- Projects by Status -->
                <?php 
                $status_config = [
                    'published' => [
                        'title' => 'Projets publiés',
                        'icon' => '✅',
                        'color' => 'var(--success)',
                        'bg' => 'rgba(16, 185, 129, 0.1)',
                        'description' => 'Visibles par tous les visiteurs'
                    ],
                    'draft' => [
                        'title' => 'Brouillons',
                        'icon' => '📝',
                        'color' => 'var(--warning)',
                        'bg' => 'rgba(245, 158, 11, 0.1)',
                        'description' => 'En cours de rédaction, non visibles publiquement'
                    ],
                    'archived' => [
                        'title' => 'Projets archivés',
                        'icon' => '📦',
                        'color' => 'var(--gray-500)',
                        'bg' => 'var(--gray-100)',
                        'description' => 'Projets terminés ou mis de côté'
                    ]
                ];
                
                $delay = 0.3;
                foreach ($status_config as $status => $config):
                    if (!empty($projects_by_status[$status])):
                ?>
                    <div class="dashboard-section fade-in-up" style="animation-delay: <?php echo $delay; ?>s; margin-bottom: 3rem;">
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                            <div>
                                <h2 style="display: flex; align-items: center; gap: 0.75rem; margin: 0;">
                                    <span style="font-size: 1.5rem;"><?php echo $config['icon']; ?></span>
                                    <?php echo $config['title']; ?>
                                    <span style="background: <?php echo $config['bg']; ?>; color: <?php echo $config['color']; ?>; padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.875rem; font-weight: 600;">
                                        <?php echo count($projects_by_status[$status]); ?>
                                    </span>
                                </h2>
                                <p style="color: var(--gray-600); margin: 0.5rem 0 0 2.25rem; font-size: 0.875rem;">
                                    <?php echo $config['description']; ?>
                                </p>
                            </div>
                        </div>

                        <div class="projects-grid">
                            <?php foreach ($projects_by_status[$status] as $index => $project): ?>
                                <div class="project-card <?php echo $project['featured'] ? 'featured' : ''; ?> fade-in-up" style="animation-delay: <?php echo $delay + ($index * 0.1); ?>s;">
                                    <?php if ($project['image_path']): ?>
                                        <div class="project-image">
                                            <img src="<?php echo htmlspecialchars($project['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($project['title']); ?>"
                                                 loading="lazy">
                                            <?php if ($project['featured']): ?>
                                                <span class="featured-badge">⭐ En vedette</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="project-image" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); display: flex; align-items: center; justify-content: center;">
                                            <div style="font-size: 3rem; opacity: 0.7;">💼</div>
                                            <?php if ($project['featured']): ?>
                                                <span class="featured-badge">⭐ En vedette</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="project-content">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                            <h3 style="margin: 0; flex: 1;"><?php echo htmlspecialchars($project['title']); ?></h3>
                                            <div class="project-menu" style="position: relative;">
                                                <button onclick="toggleProjectMenu(<?php echo $project['id']; ?>)" 
                                                        style="background: none; border: none; cursor: pointer; padding: 0.5rem; border-radius: 0.5rem; color: var(--gray-500); transition: all 0.2s ease;">
                                                    ⋮
                                                </button>
                                                <div id="menu-<?php echo $project['id']; ?>" class="project-dropdown" 
                                                     style="display: none; position: absolute; right: 0; top: 100%; background: white; border: 1px solid var(--gray-200); border-radius: 0.75rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); z-index: 10; min-width: 200px;">
                                                    <a href="project.php?id=<?php echo $project['id']; ?>" 
                                                       style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; text-decoration: none; color: var(--gray-700); border-bottom: 1px solid var(--gray-100);">
                                                        👁️ Voir le projet
                                                    </a>
                                                    <a href="project-form.php?id=<?php echo $project['id']; ?>" 
                                                       style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; text-decoration: none; color: var(--gray-700); border-bottom: 1px solid var(--gray-100);">
                                                        ✏️ Modifier
                                                    </a>
                                                    <button onclick="toggleFeatured(<?php echo $project['id']; ?>)" 
                                                            style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: none; border: none; color: var(--gray-700); cursor: pointer; width: 100%; text-align: left; border-bottom: 1px solid var(--gray-100);">
                                                        <?php echo $project['featured'] ? '⭐ Retirer de la vedette' : '⭐ Mettre en vedette'; ?>
                                                    </button>
                                                    <button onclick="confirmDelete(<?php echo $project['id']; ?>, '<?php echo htmlspecialchars($project['title'], ENT_QUOTES); ?>')" 
                                                            style="display: flex; align-items: center; gap: 0.75rem; padding: 0.75rem 1rem; background: none; border: none; color: var(--error); cursor: pointer; width: 100%; text-align: left;">
                                                        🗑️ Supprimer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <p class="project-description" style="margin-bottom: 1rem;">
                                            <?php echo htmlspecialchars(substr($project['description'], 0, 120)) . (strlen($project['description']) > 120 ? '...' : ''); ?>
                                        </p>
                                        
                                        <?php if ($project['technologies']): ?>
                                            <div class="project-technologies" style="margin-bottom: 1rem;">
                                                <?php 
                                                $techs = explode(',', $project['technologies']);
                                                foreach (array_slice($techs, 0, 3) as $tech): 
                                                ?>
                                                    <span class="tech-tag"><?php echo htmlspecialchars(trim($tech)); ?></span>
                                                <?php endforeach; ?>
                                                <?php if (count($techs) > 3): ?>
                                                    <span class="tech-tag" style="background: var(--gray-200);">+<?php echo count($techs) - 3; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                                            <div style="display: flex; gap: 0.75rem;">
                                                <a href="project.php?id=<?php echo $project['id']; ?>" class="btn btn-primary btn-sm">
                                                    👁️ Voir
                                                </a>
                                                <a href="project-form.php?id=<?php echo $project['id']; ?>" class="btn btn-secondary btn-sm">
                                                    ✏️ Modifier
                                                </a>
                                            </div>
                                            <div style="font-size: 0.875rem; color: var(--gray-500);">
                                                <?php echo date('d/m/Y', strtotime($project['created_at'])); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php 
                    endif;
                    $delay += 0.1;
                endforeach; 
                ?>
            <?php endif; ?>
        </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); z-index: 1000; backdrop-filter: blur(5px);">
        <div style="display: flex; align-items: center; justify-content: center; height: 100%; padding: 2rem;">
            <div style="background: white; border-radius: 1.5rem; padding: 2rem; max-width: 500px; width: 100%; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2rem;">
                        🗑️
                    </div>
                    <h3 style="color: var(--gray-900); margin-bottom: 0.5rem;">Confirmer la suppression</h3>
                    <p style="color: var(--gray-600);">Êtes-vous sûr de vouloir supprimer le projet :</p>
                    <p style="font-weight: 600; color: var(--error);" id="projectToDelete"></p>
                    <p style="color: var(--gray-500); font-size: 0.875rem;">Cette action est irréversible.</p>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: center;">
                    <button onclick="closeDeleteModal()" class="btn btn-secondary">
                        ❌ Annuler
                    </button>
                    <form method="POST" style="display: inline;" id="deleteForm">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="delete_project">
                        <input type="hidden" name="project_id" id="deleteProjectId">
                        <button type="submit" class="btn" style="background: var(--error); color: white;">
                            🗑️ Supprimer définitivement
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden forms for actions -->
    <form method="POST" id="featuredForm" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="toggle_featured">
        <input type="hidden" name="project_id" id="featuredProjectId">
    </form>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Projects page interactions
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
                if (!e.target.closest('.project-menu')) {
                    document.querySelectorAll('.project-dropdown').forEach(dropdown => {
                        dropdown.style.display = 'none';
                    });
                }
            });
        });

        function toggleProjectMenu(projectId) {
            const menu = document.getElementById('menu-' + projectId);
            const isVisible = menu.style.display === 'block';
            
            // Close all other menus
            document.querySelectorAll('.project-dropdown').forEach(dropdown => {
                dropdown.style.display = 'none';
            });
            
            // Toggle current menu
            menu.style.display = isVisible ? 'none' : 'block';
        }

        function confirmDelete(projectId, projectTitle) {
            document.getElementById('deleteProjectId').value = projectId;
            document.getElementById('projectToDelete').textContent = projectTitle;
            document.getElementById('deleteModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function toggleFeatured(projectId) {
            document.getElementById('featuredProjectId').value = projectId;
            document.getElementById('featuredForm').submit();
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
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
    </script>
</body>
</html>