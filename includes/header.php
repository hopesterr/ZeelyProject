<header class="main-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="index.php"><?php echo APP_NAME; ?></a>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="viewprojects.php">Projets</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="dashboard.php">Tableau de bord</a></li>
                        <li><a href="projects.php">Mes projets</a></li>
                        <li><a href="profile.php">Profil</a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin.php">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Connexion</a></li>
                        <li><a href="register.php">Inscription</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div style="display: flex; align-items: center;">
                <button class="dark-mode-toggle" onclick="toggleDarkMode()" title="Basculer le mode sombre"></button>
                
                <div class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </div>
    </div>
</header>