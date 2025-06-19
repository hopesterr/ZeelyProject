<<<<<<< HEAD
# Application Portfolio PHP/MySQL

## Installation et Configuration

### Prérequis
- Serveur web avec PHP 7.4+ (Apache/Nginx)
- MySQL 5.7+ ou MariaDB
- Extension PHP PDO activée

### Installation locale

#### Option 1: XAMPP (Recommandé pour Windows/Mac)
1. Téléchargez et installez [XAMPP](https://www.apachefriends.org/)
2. Démarrez Apache et MySQL depuis le panneau de contrôle XAMPP
3. Copiez tous les fichiers du projet dans `htdocs/portfolio/`
4. Accédez à `http://localhost/portfolio`

#### Option 2: WAMP (Windows)
1. Téléchargez et installez [WAMP](https://www.wampserver.com/)
2. Démarrez les services
3. Copiez les fichiers dans `www/portfolio/`
4. Accédez à `http://localhost/portfolio`

#### Option 3: MAMP (Mac)
1. Téléchargez et installez [MAMP](https://www.mamp.info/)
2. Démarrez les services
3. Copiez les fichiers dans `htdocs/portfolio/`
4. Accédez à `http://localhost:8888/portfolio`

### Configuration de la base de données

1. **Créer la base de données :**
   - Ouvrez phpMyAdmin (`http://localhost/phpmyadmin`)
   - Créez une nouvelle base de données nommée `portfolio_app`
   - Importez le fichier SQL : `supabase/migrations/20250610062533_divine_dune.sql`

2. **Configuration de connexion :**
   - Modifiez `config/database.php` si nécessaire
   - Par défaut : host=localhost, user=root, password='' (vide)

### Permissions des fichiers

Créez le dossier uploads et définissez les permissions :
```bash
mkdir uploads
chmod 755 uploads
```

### Comptes par défaut

**Administrateur :**
- Email: admin@portfolio.com
- Mot de passe: admin123

## Structure du projet

```
portfolio/
├── config/           # Configuration et base de données
├── models/           # Classes métier (MVC)
├── includes/         # Header/Footer réutilisables
├── utils/            # Utilitaires (upload, etc.)
├── assets/css/       # Styles CSS
├── uploads/          # Images uploadées
├── supabase/migrations/ # Script SQL
├── index.php         # Page d'accueil
├── login.php         # Connexion
├── register.php      # Inscription
├── dashboard.php     # Tableau de bord
└── README.md         # Ce fichier
```

## Fonctionnalités

### Authentification
- ✅ Inscription sécurisée avec validation
- ✅ Connexion avec "se souvenir de moi"
- ✅ Réinitialisation de mot de passe
- ✅ Gestion des rôles (user/admin)
- ✅ Protection CSRF et XSS

### Gestion des projets
- ✅ CRUD complet des projets
- ✅ Upload d'images sécurisé
- ✅ Statuts (brouillon/publié/archivé)
- ✅ Projets en vedette
- ✅ Pagination

### Gestion des compétences
- ✅ Compétences par catégories
- ✅ Niveaux de compétence
- ✅ Association utilisateur-compétences

### Sécurité
- ✅ Hachage sécurisé des mots de passe
- ✅ Protection contre injections SQL
- ✅ Validation et sanitisation des données
- ✅ Gestion sécurisée des sessions

## Accès à l'application

Une fois installée, accédez à :
- **Page d'accueil :** `http://localhost/portfolio/`
- **Connexion :** `http://localhost/portfolio/login.php`
- **Inscription :** `http://localhost/portfolio/register.php`
- **Admin :** Connectez-vous avec le compte admin

## Dépannage

### Erreur de connexion à la base de données
- Vérifiez que MySQL est démarré
- Vérifiez les paramètres dans `config/database.php`
- Assurez-vous que la base `portfolio_app` existe

### Erreur d'upload d'images
- Vérifiez que le dossier `uploads/` existe
- Vérifiez les permissions (755 ou 777)
- Vérifiez la configuration PHP (upload_max_filesize)

### Pages blanches
- Activez l'affichage des erreurs PHP
- Vérifiez les logs d'erreur du serveur
- Assurez-vous que toutes les classes sont bien incluses
=======
# PORTFOLIO
>>>>>>> 16d823039003296bbc4a74392f914d23ef4d6060
