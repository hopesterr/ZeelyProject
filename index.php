<?php
require_once 'config/config.php';

$database = new Database();
$db = $database->getConnection();
$projectModel = new Project($db);
$likeModel    = new Like($db);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6;
$offset = ($page - 1) * $limit;

$projects = $projectModel->getAllProjects($limit, $offset);
$total_projects = $projectModel->countProjects();
$total_pages = ceil($total_projects / $limit);
$top_projects  = $likeModel->getTopWeek(3);
$success_message = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - Portfolio</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
</head>
<body>
    <?php include 'includes/header.php'; ?>
<?php if ($success_message): ?>
  <div class="alert-success" style="
       background: rgba(16,185,129,0.1);
       border: 1px solid rgba(16,185,129,0.2);
       color: #065f46;
       padding: 1rem;
       text-align: center;
       font-weight: 500;
       margin: 1rem auto;
       max-width: 600px;
  ">
    ✅ <?= htmlspecialchars($success_message) ?>
  </div>
<?php endif; ?>
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <div class="hero-content fade-in-up">
                    <h1>Créez votre portfolio professionnel</h1>
                    <p>Rejoignez notre communauté de développeurs créatifs et partagez vos projets avec le monde entier. Une plateforme moderne pour mettre en valeur votre talent.</p>
                    <?php if (!isLoggedIn()): ?>
                        <div class="hero-actions">
                            <a href="register.php" class="btn btn-primary">Commencer gratuitement</a>
                            <a href="login.php" class="btn btn-secondary">Se connecter</a>
                        </div>
                    <?php else: ?>
                        <div class="hero-actions">
                            <a href="dashboard.php" class="btn btn-primary">Mon tableau de bord</a>
                            <a href="project-form.php" class="btn btn-secondary">Nouveau projet</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Projects Section -->
        <section class="projects-section">
            <div class="container">
                <h2 class="fade-in">Projets en vedette</h2>
                
                <?php if (empty($projects)): ?>
                    <div class="empty-state fade-in">
                        <div style="font-size: 4rem; margin-bottom: 1rem;">🚀</div>
                        <p>Aucun projet publié pour le moment.</p>
                        <p style="font-size: 1rem; color: var(--gray-400); margin-bottom: 2rem;">Soyez le premier à partager votre travail !</p>
                        <?php if (isLoggedIn()): ?>
                            <a href="dashboard.php" class="btn btn-primary">Créer votre premier projet</a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary">Rejoindre la communauté</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="projects-grid">
                        <?php foreach ($projects as $index => $project): ?>
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
                                    <p class="project-description"><?php echo htmlspecialchars(substr($project['description'], 0, 150)) . (strlen($project['description']) > 150 ? '...' : ''); ?></p>
                                    
                                    <?php if ($project['technologies']): ?>
                                        <div class="project-technologies">
                                            <?php 
                                            $techs = explode(',', $project['technologies']);
                                            foreach (array_slice($techs, 0, 4) as $tech): 
                                            ?>
                                                <span class="tech-tag"><?php echo htmlspecialchars(trim($tech)); ?></span>
                                            <?php endforeach; ?>
                                            <?php if (count($techs) > 4): ?>
                                                <span class="tech-tag" style="background: var(--gray-200);">+<?php echo count($techs) - 4; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="project-actions">
                                        <a href="project.php?id=<?php echo $project['id']; ?>" class="btn btn-primary btn-sm">Voir détails</a>
                                        <?php if ($project['external_link']): ?>
                                            <a href="<?php echo htmlspecialchars($project['external_link']); ?>" 
                                               target="_blank" class="btn btn-secondary btn-sm">🔗 Voir le site</a>
                                        <?php endif; ?>
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
        </section>

        <!-- Features Section -->
        <section style="padding: 4rem 0; background: var(--white);">
            <div class="container">
                <div style="text-align: center; margin-bottom: 3rem;">
                    <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--gray-900); margin-bottom: 1rem;">Pourquoi choisir notre plateforme ?</h2>
                    <p style="font-size: 1.125rem; color: var(--gray-600); max-width: 600px; margin: 0 auto;">Des outils modernes pour créer et partager vos projets professionnels</p>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                    <div class="dashboard-card interactive">
                        <div style="font-size: 2.5rem; margin-bottom: 1rem;">🎨</div>
                        <h3>Design moderne</h3>
                        <p style="color: var(--gray-600);">Interface élégante et intuitive inspirée des meilleures pratiques du web moderne.</p>
                    </div>
                    
                    <div class="dashboard-card interactive">
                        <div style="font-size: 2.5rem; margin-bottom: 1rem;">🔒</div>
                        <h3>Sécurisé</h3>
                        <p style="color: var(--gray-600);">Protection avancée contre les attaques XSS, CSRF et injections SQL.</p>
                    </div>
                    
                    <div class="dashboard-card interactive">
                        <div style="font-size: 2.5rem; margin-bottom: 1rem;">📱</div>
                        <h3>Responsive</h3>
                        <p style="color: var(--gray-600);">Parfaitement adapté à tous les appareils, du mobile au desktop.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Smooth animations on scroll
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

        // Observe all fade-in elements
        document.querySelectorAll('.fade-in, .fade-in-up').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(30px)';
            el.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            observer.observe(el);
        });

        // Header scroll effect
        let lastScrollY = window.scrollY;
        const header = document.querySelector('.main-header');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.98)';
                header.style.boxShadow = '0 4px 6px -1px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.boxShadow = 'none';
            }
            lastScrollY = window.scrollY;
        });
    </script>
</body>
</html>