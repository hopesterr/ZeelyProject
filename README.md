# Portfolio PHP – Plateforme de gestion de projets, compétences et utilisateurs

## Présentation du Projet

Ce projet est une application web moderne développée en **PHP** et **MySQL**. Elle permet à une communauté de développeurs de créer, gérer et partager leurs projets, de valoriser leurs compétences, et d'interagir entre eux via un système de likes et de projets en vedette. L'interface est pensée pour être intuitive, responsive et sécurisée, avec une séparation claire des rôles (utilisateur/admin) et une gestion fine des statuts des projets.

---

## Fonctionnalités détaillées

### Authentification & Gestion des Comptes

- **Inscription** : Création de compte avec validation des champs (nom, prénom, email unique, mot de passe sécurisé).
- **Connexion sécurisée** : Sessions PHP, vérification du mot de passe hashé, gestion des erreurs.
- **Déconnexion** : Fermeture propre de la session.
- **Gestion des rôles** : Deux rôles natifs (Utilisateur, Administrateur) avec des droits distincts.
- **Mise à jour du profil** : Modification du nom, prénom, email, biographie.
- **Changement de mot de passe** : Vérification de l'ancien mot de passe, validation du nouveau.
- **Sécurité** :
  - Protection CSRF sur tous les formulaires sensibles.
  - Hachage des mots de passe avec password_hash.
  - Validation et nettoyage des entrées utilisateur (XSS, SQLi).
  - Expiration automatique de la session après inactivité.

### Gestion des Compétences

- **Ajout, modification, suppression de compétences** (par l'admin uniquement).
- **Catégorisation** : Les compétences sont organisées par catégories (Frontend, Backend, Database, etc.).
- **Sélection par l'utilisateur** : Chaque utilisateur peut choisir ses compétences parmi celles proposées.
- **Niveau de compétence** : Pour chaque compétence, l'utilisateur indique son niveau (débutant, intermédiaire, avancé, expert) et ses années d'expérience.
- **Statistiques** : Visualisation graphique du niveau global de l'utilisateur.

### Gestion des Projets

- **Ajout, modification, publication, archivage** :
  - Un projet peut être créé, édité, publié, archivé ou supprimé (suppression définitive possible uniquement depuis la gestion des projets, sinon archivage privilégié pour éviter la perte de données).
  - **Archiver plutôt que supprimer** : Nous avons fait le choix UX de permettre l'archivage des projets (mise de côté, non visibles publiquement) plutôt que leur suppression immédiate, afin de préserver l'historique et d'éviter les suppressions accidentelles.
- **Statuts des projets** :
  - **Brouillon** : Projet en cours de rédaction, non visible publiquement.
  - **Publié** : Projet visible par tous sur la plateforme.
  - **Archivé** : Projet mis de côté, visible uniquement par son propriétaire.
- **Mise en vedette** :
  - Un utilisateur peut mettre en avant un ou plusieurs de ses projets (badge "En vedette").
  - Les projets en vedette sont mis en avant dans les listes et sur la page d'accueil.
- **Suppression** :
  - Suppression définitive possible depuis la gestion des projets (avec confirmation), ce qui supprime aussi l'image associée.
- **Upload d'image** :
  - Upload sécurisé (taille, format, nommage unique), suppression de l'ancienne image lors de la modification.
- **Champs du projet** :
  - Titre, description détaillée, image, technologies utilisées, lien externe (site), lien GitHub, statut, vedette.
- **Affichage structuré** :
  - Page d'accueil : Projets récents et top projets de la semaine.
  - Page "Tous les projets" : Liste paginée de tous les projets publiés.
  - Page de détail : Vue complète d'un projet, avec likes, description, technologies, liens, date, auteur.
  - Tableau de bord : Vue synthétique des projets de l'utilisateur, par statut.
- **Pagination** :
  - Les listes de projets sont paginées pour une meilleure expérience utilisateur.

### Interactions sociales

- **Likes** :
  - Chaque utilisateur connecté peut liker ou déliker un projet (un seul like par projet et par utilisateur).
  - Le nombre de likes est affiché partout (liste, détail, tableau de bord).
  - Les projets les plus likés de la semaine sont mis en avant sur la page d'accueil.
- **Classement hebdomadaire** :
  - Calcul automatique des projets les plus populaires sur les 7 derniers jours.

### Administration

- **Gestion des compétences** :
  - Ajout, modification, suppression de compétences et de catégories.
  - Statistiques sur le nombre de compétences disponibles.
- **Sécurité renforcée** :
  - Accès à l'admin strictement réservé aux administrateurs (vérification de rôle à chaque chargement de page).

### Expérience utilisateur & Design

- **Interface responsive** : Adaptée à tous les écrans (desktop, tablette, mobile).
- **Navigation claire** : Menu dynamique selon le rôle et la connexion.
- **Messages d'erreur et de succès** : Affichage clair, conservation des champs remplis en cas d'erreur.
- **Animations et transitions** : Effets de fade-in, badges, couleurs dynamiques.
- **Accessibilité** : Contrastes, labels, navigation clavier.

---

## Installation et Configuration

### Prérequis
- Serveur local (XAMPP, WAMP, etc.)
- PHP 8.x et MySQL
- Un navigateur moderne

### Étapes d'installation
1. **Cloner le projet sur votre serveur local** :
   ```bash
   git clone url_de_votre_repo
   cd projectb2
   ```
2. **Importer la base de données** :
   - Ouvrir `config/database.sql` dans phpMyAdmin ou via la ligne de commande MySQL
   - Exécuter le script pour créer la base et les tables
3. **Configurer la connexion à la base de données** :
   Modifier le fichier `config/database.php` si besoin :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'projetb2');
   define('DB_USER', 'projetb2');
   define('DB_PASS', 'password');
   define('DB_PORT', 3306);
   ```
4. **Démarrer le serveur PHP et tester l'application** :
   ```bash
   php -S localhost:8000
   ```
   Puis accéder à l'application via [http://localhost:8000](http://localhost:8000) ou via votre serveur Apache local.

---

## Comptes de Test

### Compte Administrateur
- Email : admin@portfolio.com
- Mot de passe : password

### Compte Utilisateur
- À créer via le formulaire d'inscription
- Email : user1@portfolio.com
- Mot de passe : password
- Email : user2@portfolio.com
- Mot de passe : password
---

## Structure du Projet

```
projectb2/
│
├── assets/
│   ├── css/style.css         # Feuille de style principale
│   └── js/main.js            # Script JS principal
│
├── config/
│   ├── config.php            # Configuration globale et autoload
│   ├── database.php          # Connexion à la base de données
│   └── database.sql          # Script SQL de création de la base
│
├── includes/
│   ├── header.php            # En-tête commun
│   └── footer.php            # Pied de page commun
│
├── models/
│   ├── User.php              # Modèle utilisateur
│   ├── Project.php           # Modèle projet (statuts, vedette, archivage, suppression, etc.)
│   ├── Skill.php             # Modèle compétence
│   └── Like.php              # Modèle like (gestion des likes, top semaine)
│
├── utils/
│   └── FileUpload.php        # Gestion sécurisée des uploads
│
├── uploads/                  # Images uploadées par les utilisateurs
│
├── index.php                 # Page d'accueil (projets récents, top projets)
├── dashboard.php             # Tableau de bord utilisateur
├── profile.php               # Gestion du profil utilisateur
├── skills.php                # Gestion des compétences utilisateur
├── projects.php              # Gestion des projets utilisateur (archivage, vedette, suppression)
├── project-form.php          # Formulaire d'ajout/édition de projet
├── viewprojects.php          # Liste de tous les projets (public, likes, pagination)
├── voirprojet.php            # Détail d'un projet (likes, vedette, liens, etc.)
├── admin.php                 # Interface d'administration des compétences
├── login.php                 # Connexion
├── register.php              # Inscription
├── logout.php                # Déconnexion
└── README.md                 # Documentation (ce fichier)
```

---

## Notes complémentaires

- **Philosophie du projet** :
  - Préserver la donnée utilisateur (archivage plutôt que suppression immédiate).
  - Mettre en avant la valorisation des compétences et des réalisations.
  - Favoriser l'interaction et la reconnaissance entre membres (likes, vedette, classement).
- **Sécurité et robustesse** :
  - Toutes les actions sensibles sont protégées (CSRF, XSS, SQLi).
  - Les fichiers uploadés sont contrôlés et nommés de façon unique.
- **Extensibilité** :
  - Le code est organisé pour faciliter l'ajout de nouvelles fonctionnalités (MVC simplifié, autoload, séparation des rôles).
- **Expérience utilisateur** :
  - Interface moderne, messages clairs, navigation fluide, responsive design.
- **Axe d'amélioration future**
  - Ajouter une option pour visiter le profil des utilisateurs et voir leurs compétences.  

---

Pour toute question, suggestion ou contribution, n'hésitez pas à ouvrir une issue ou une pull request ! 