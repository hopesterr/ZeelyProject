<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();
$projectModel = new Project($db);
$likeModel = new Like($db);

$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$project = null;
$error = '';

$project = $projectModel->getProjectById($project_id);
    // → on autorise le propriétaire à voir ses projets archivés
    if (
        !$project
        || (
            $project['status'] !== 'published'
            && (
                !isLoggedIn()
                || $_SESSION['user_id'] !== (int)$project['user_id']
            )
        )
    ) {
        $error = 'Projet non trouvé ou non publié';
    }

// Handle like/unlike actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn() && $project) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'toggle_like') {
            $likeModel->toggle($_SESSION['user_id'], $project_id);
            // Refresh page to update like status
            header('Location: voirprojet.php?id=' . $project_id);
            exit();
        }
    }
}

// Get like information
$like_count = 0;
$user_has_liked = false;
if ($project) {
    $like_count = $likeModel->countByProject($project_id);
    $user_has_liked = isLoggedIn() ? $likeModel->userHasLiked($_SESSION['user_id'], $project_id) : false;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $project ? htmlspecialchars($project['title']) : 'Projet'; ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="dashboard-main">
        <div class="container">
            <?php if ($error): ?>
                <div class="empty-state fade-in-up">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">❌</div>
                    <h3 style="color: var(--error); margin-bottom: 1rem;">Erreur</h3>
                    <p><?php echo htmlspecialchars($error); ?></p>
                    <a href="viewprojects.php" class="btn btn-primary" style="margin-top: 1.5rem;">
                        ← Retour aux projets
                    </a>
                </div>
            <?php else: ?>
                <!-- Project Header -->
                <div class="dashboard-header fade-in-up">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 1rem; margin-bottom: 1rem;">
                        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; position: relative;">
                            💼
                            <?php if ($project['featured']): ?>
                                <div style="position: absolute; bottom: -5px; right: -5px; width: 30px; height: 30px; background: var(--warning); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; border: 3px solid white;">
                                    ⭐
                                </div>
                            <?php endif; ?>
                        </div>
                        <div style="text-align: center;">
                            <h1 style="margin: 0;"><?php echo htmlspecialchars($project['title']); ?></h1>
                            <p style="margin: 0; color: var(--gray-600);">
                                Par <strong><?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?></strong>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Project Stats -->
                    <div style="display: flex; justify-content: center; gap: 2rem; margin-top: 1.5rem;">
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--error);"><?php echo $like_count; ?></div>
                            <div style="font-size: 0.875rem; color: var(--gray-600);">Like<?php echo $like_count > 1 ? 's' : ''; ?></div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                                <?php echo date('d/m/Y', strtotime($project['created_at'])); ?>
                            </div>
                            <div style="font-size: 0.875rem; color: var(--gray-600);">Publié le</div>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <div style="margin-bottom: 2rem; text-align: center;">
                    <a href="viewprojects.php" class="btn btn-secondary btn-sm">
                        ← Retour aux projets
                    </a>
                </div>

                <!-- Project Image -->
                <?php if ($project['image_path']): ?>
                    <div class="dashboard-card fade-in-up" style="animation-delay: 0.1s; padding: 0; overflow: hidden;">
                        <img src="<?php echo htmlspecialchars($project['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($project['title']); ?>"
                             style="width: 100%; height: auto; display: block; max-height: 500px; object-fit: cover;">
                    </div>
                <?php endif; ?>

                <!-- Project Content -->
                <div class="dashboard-card fade-in-up" style="animation-delay: 0.2s;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                        <div style="flex: 1;">
                            <h2 style="margin: 0 0 1rem 0; color: var(--gray-900);">Description du projet</h2>
                            <div style="color: var(--gray-700); line-height: 1.6; font-size: 1.1rem;">
                                <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                            </div>
                        </div>
                        
                        <!-- Like Button -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 1rem;">
                            <?php if (isLoggedIn()): ?>
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="toggle_like">
                                    <button type="submit" class="btn" 
                                            style="background: <?php echo $user_has_liked ? 'var(--error)' : 'var(--gray-100)'; ?>; 
                                                   color: <?php echo $user_has_liked ? 'white' : 'var(--gray-600)'; ?>; 
                                                   border: 2px solid <?php echo $user_has_liked ? 'var(--error)' : 'var(--gray-300)'; ?>;
                                                   font-size: 1.2rem; padding: 1rem 1.5rem;">
                                        <?php echo $user_has_liked ? '💔 Unlike' : '❤️ Like'; ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary" style="font-size: 1.2rem; padding: 1rem 1.5rem;">
                                    ❤️ Connectez-vous pour liker
                                </a>
                            <?php endif; ?>
                            
                            <div style="text-align: center;">
                                <div style="font-size: 1.5rem; font-weight: 700; color: var(--error);"><?php echo $like_count; ?></div>
                                <div style="font-size: 0.875rem; color: var(--gray-600);">Like<?php echo $like_count > 1 ? 's' : ''; ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Technologies -->
                    <?php if ($project['technologies']): ?>
                        <div style="margin-bottom: 2rem;">
                            <h3 style="margin-bottom: 1rem; color: var(--gray-800); display: flex; align-items: center; gap: 0.5rem;">
                                🛠️ Technologies utilisées
                            </h3>
                            <div class="project-technologies">
                                <?php 
                                $techs = explode(',', $project['technologies']);
                                foreach ($techs as $tech): 
                                ?>
                                    <span class="tech-tag" style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                        <?php echo htmlspecialchars(trim($tech)); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Project Links -->
                    <div style="display: flex; gap: 1rem; flex-wrap: wrap; padding-top: 2rem; border-top: 1px solid var(--gray-200);">
                        <?php if ($project['external_link']): ?>
                            <a href="<?php echo htmlspecialchars($project['external_link']); ?>" 
                               target="_blank" class="btn btn-primary">
                                🌐 Voir le site web
                            </a>
                        <?php endif; ?>
                        
                        <?php if ($project['github_link']): ?>
                            <a href="<?php echo htmlspecialchars($project['github_link']); ?>" 
                               target="_blank" class="btn btn-secondary">
                                💻 Code source
                            </a>
                        <?php endif; ?>
                        
                        <?php if (isLoggedIn() && $_SESSION['user_id'] == $project['user_id']): ?>
                            <a href="project-form.php?id=<?php echo $project['id']; ?>" class="btn btn-secondary">
                                ✏️ Modifier
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Project Info -->
                <div class="dashboard-card fade-in-up" style="animation-delay: 0.3s;">
                    <h3 style="margin-bottom: 1.5rem; color: var(--gray-800); display: flex; align-items: center; gap: 0.5rem;">
                        ℹ️ Informations du projet
                    </h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                        <div class="stat-item" style="text-align: left; padding: 1rem; background: var(--gray-50); border-radius: 0.75rem;">
                            <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.25rem;">👤 Créateur</div>
                            <div style="font-weight: 600; color: var(--gray-800);">
                                <?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?>
                            </div>
                        </div>
                        
                        <div class="stat-item" style="text-align: left; padding: 1rem; background: var(--gray-50); border-radius: 0.75rem;">
                            <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.25rem;">📅 Date de création</div>
                            <div style="font-weight: 600; color: var(--gray-800);">
                                <?php echo date('d/m/Y à H:i', strtotime($project['created_at'])); ?>
                            </div>
                        </div>
                        
                        <?php if ($project['updated_at'] !== $project['created_at']): ?>
                            <div class="stat-item" style="text-align: left; padding: 1rem; background: var(--gray-50); border-radius: 0.75rem;">
                                <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.25rem;">🔄 Dernière mise à jour</div>
                                <div style="font-weight: 600; color: var(--gray-800);">
                                    <?php echo date('d/m/Y à H:i', strtotime($project['updated_at'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="stat-item" style="text-align: left; padding: 1rem; background: var(--gray-50); border-radius: 0.75rem;">
                            <div style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.25rem;">❤️ Popularité</div>
                            <div style="font-weight: 600; color: var(--error);">
                                <?php echo $like_count; ?> like<?php echo $like_count > 1 ? 's' : ''; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Project detail page animations
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

            // Like button animation
            const likeBtn = document.querySelector('form button[type="submit"]');
            if (likeBtn) {
                likeBtn.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                });
            }
        });
    </script>
</body>
</html>