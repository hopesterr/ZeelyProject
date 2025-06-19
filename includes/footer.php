<footer class="main-footer">
  <!-- Bascule mode sombre -->
  <button id="dark-mode-toggle"
          style="position:fixed;top:1rem;right:1rem;background:none;border:none;font-size:1.5rem;cursor:pointer;z-index:1000;">
    🌙
  </button>

  <script src="assets/js/main.js"></script>

<script src="assets/js/main.js"></script>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h4><?php echo APP_NAME; ?></h4>
                <p>Plateforme de portfolio pour développeurs créatifs</p>
            </div>
            
            <div class="footer-section">
                <h4>Liens utiles</h4>
                <ul>
                    <li><a href="index.php">Accueil</a></li>
                    <?php if (!isLoggedIn()): ?>
                        <li><a href="register.php">S'inscrire</a></li>
                        <li><a href="login.php">Se connecter</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Support</h4>
                <ul>
                    <li><a href="mailto:support@portfolio.com">Contact</a></li>
                    <li><a href="#">Aide</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Tous droits réservés.</p>
        </div>
    </div>
</footer>

<script src="assets/js/main.js"></script>