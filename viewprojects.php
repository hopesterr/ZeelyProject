<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();
$likeModel = new Like($db);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

$projects = $likeModel->getProjectsWithLikes($limit, $offset);

// Get total count for pagination
$projectModel = new Project($db);
$total_projects = $projectModel->countProjects();
$total_pages = ceil($total_projects / $limit);

// Handle like/unlike actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token de sécurité invalide';
    } else {
        $action = $_POST['action'] ?? '';
        $project_id = (int)($_POST['project_id'] ?? 0);
        
        if ($action === 'toggle_like' && $project_id > 0) {
            $likeModel->toggle($_SESSION['user_id'], $project_id);
            // Refresh page to update like counts
            header('Location: viewprojects.php?page=' . $page);
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les projets - <?php echo APP_NAME; ?></title>
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
                    <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--accent), var(--primary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; position: relative;">
                        🌟
                        <div style="position: absolute; bottom: -5px; right: -5px; width: 30px; height: 30px; background: var(--success); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; border: 3px solid white;">
                            ❤️
                        </div>
                    </div>
                    <div>
                        <h1 style="margin: 0;">Tous les projets</h1>
                        <p style="margin: 0; color: var(--gray-600);">
                            Découvrez les créations de notre <strong>communauté</strong>
                        </p>
                    </div>
                </div>
                <p style="color: var(--gray-600); max-width: 600px; margin: 0 auto;">Explorez les projets créatifs de nos développeurs et montrez votre appréciation !</p>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error fade-in">
                    <strong>❌ Erreur :</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($projects)): ?>
                <!-- Empty State -->
                <div class="empty-state fade-in-up">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem;">🎨</div>
                    <h3 style="color: var(--gray-700); margin-bottom: 1rem;">Aucun projet publié</h3>
                    <p>Soyez le premier à partager votre travail avec la communauté !</p>
                    <?php if (isLoggedIn()): ?>
                        <a href="project-form.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.75rem; margin-top: 1.5rem;">
                            <span style="font-size: 1.2rem;">🚀</span>
                            Créer mon premier projet
                        </a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 0.75rem; margin-top: 1.5rem;">
                            <span style="font-size: 1.2rem;">✨</span>
                            Rejoindre la communauté
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <!-- Projects Grid -->
                <div class="projects-grid fade-in-up" style="animation-delay: 0.1s;">
                    <?php foreach ($projects as $index => $project): ?>
                        <?php 
                        $like_count = $project['like_count'];
                        $user_has_liked = isLoggedIn() ? $likeModel->userHasLiked($_SESSION['user_id'], $project['id']) : false;
                        ?>
                        <div class="project-card <?php echo $project['featured'] ? 'featured' : ''; ?> fade-in-up" style="animation-delay: <?php echo $index * 0.1; ?>s;">
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
                                <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                                <p class="project-author">Par <?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?></p>
                                <p class="project-description"><?php echo htmlspecialchars(substr($project['description'], 0, 120)) . (strlen($project['description']) > 120 ? '...' : ''); ?></p>
                                
                                <?php if ($project['technologies']): ?>
                                    <div class="project-technologies">
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
                                
                                <div class="project-actions" style="display: flex; justify-content: space-between; align-items: center;">
                                    <div style="display: flex; gap: 0.75rem;">
                                        <a href="voirprojet.php?id=<?php echo $project['id']; ?>" class="btn btn-primary btn-sm">
                                            👁️ Voir
                                        </a>
                                        <?php if ($project['external_link']): ?>
                                            <a href="<?php echo htmlspecialchars($project['external_link']); ?>" 
                                               target="_blank" class="btn btn-secondary btn-sm">🔗 Site</a>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <?php if (isLoggedIn()): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="toggle_like">
                                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                                <button type="submit" class="btn btn-sm" 
                                                        style="background: <?php echo $user_has_liked ? 'var(--error)' : 'var(--gray-100)'; ?>; 
                                                               color: <?php echo $user_has_liked ? 'white' : 'var(--gray-600)'; ?>; 
                                                               border: 1px solid <?php echo $user_has_liked ? 'var(--error)' : 'var(--gray-300)'; ?>;">
                                                    <?php echo $user_has_liked ? '💔' : '❤️'; ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <span style="font-size: 0.875rem; color: var(--gray-600); font-weight: 600;">
                                            <?php echo $like_count; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pagination" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 3rem;">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="btn btn-secondary btn-sm">← Précédent</a>
                        <?php endif; ?>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);
                        
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="btn btn-sm <?php echo $i === $page ? 'btn-primary' : 'btn-secondary'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="btn btn-secondary btn-sm">Suivant →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Projects page animations
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

            // Like button animations
            document.querySelectorAll('form button[type="submit"]').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 150);
                });
            });
        });
    </script>
</body>
</html>