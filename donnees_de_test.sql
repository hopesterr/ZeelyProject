-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 25 juin 2025 à 15:33
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projetb2`
--
CREATE DATABASE IF NOT EXISTS `projetb2` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `projetb2`;

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `external_link` varchar(500) DEFAULT NULL,
  `github_link` varchar(500) DEFAULT NULL,
  `technologies` text DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `projects`
--

INSERT INTO `projects` (`id`, `user_id`, `title`, `description`, `image_path`, `external_link`, `github_link`, `technologies`, `status`, `featured`, `created_at`, `updated_at`) VALUES
(1, 2, 'exemple 1', 'exemple111', 'uploads/project_685beb4959fff.png', '', '', 'exemple1', 'published', 0, '2025-06-25 12:27:53', '2025-06-25 12:27:53'),
(2, 2, 'exemple 2', 'exemple222', 'uploads/project_685beb94a49c4.png', '', '', 'exemple2', 'published', 0, '2025-06-25 12:29:08', '2025-06-25 12:29:08'),
(3, 2, 'exemple 3', 'exemple333', 'uploads/project_685bebbd3c481.png', '', '', 'exemple3', 'published', 0, '2025-06-25 12:29:49', '2025-06-25 12:29:49'),
(4, 3, 'exemple1', 'exemple111', 'uploads/project_685bec3d89f96.png', '', '', 'exemple1', 'published', 0, '2025-06-25 12:31:57', '2025-06-25 12:31:57'),
(5, 3, 'exemple 2', 'exemple222', 'uploads/project_685bec50c3152.png', '', '', 'exemple 2', 'published', 0, '2025-06-25 12:32:16', '2025-06-25 12:32:16'),
(6, 3, 'exemple 3', 'exemple333', 'uploads/project_685bec6193463.png', '', '', 'exemple3', 'published', 0, '2025-06-25 12:32:33', '2025-06-25 12:32:33'),
(7, 4, 'yamaro', 'yamaro est un projet pour lister et noter les films vus au cinéma', 'uploads/project_685bee853cdf1.png', 'https://yamaro.fr', '', 'react + vite express.js', 'published', 0, '2025-06-25 12:41:41', '2025-06-25 12:41:41'),
(8, 4, 'Zeely', 'Plateforme de portfolio pour les développeurs', 'uploads/project_685bef84e2a97.png', '', '', 'PHP, JavaScript, SQL', 'published', 0, '2025-06-25 12:45:56', '2025-06-25 12:45:56');

-- --------------------------------------------------------

--
-- Structure de la table `project_likes`
--

DROP TABLE IF EXISTS `project_likes`;
CREATE TABLE `project_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `project_likes`
--

INSERT INTO `project_likes` (`id`, `user_id`, `project_id`, `created_at`) VALUES
(1, 4, 7, '2025-06-25 12:46:03'),
(2, 4, 8, '2025-06-25 12:46:04');

-- --------------------------------------------------------

--
-- Structure de la table `skills`
--

DROP TABLE IF EXISTS `skills`;
CREATE TABLE `skills` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `skills`
--

INSERT INTO `skills` (`id`, `name`, `category`, `description`, `created_at`, `updated_at`) VALUES
(1, 'PHP', 'Backend', NULL, '2025-06-25 12:08:24', '2025-06-25 12:08:24'),
(2, 'JavaScript', 'Frontend', NULL, '2025-06-25 12:08:24', '2025-06-25 12:08:24'),
(3, 'MySQL', 'Database', NULL, '2025-06-25 12:08:24', '2025-06-25 12:08:24'),
(4, 'HTML', 'Frontend', NULL, '2025-06-25 12:08:24', '2025-06-25 12:08:24'),
(5, 'CSS', 'Frontend', NULL, '2025-06-25 12:08:24', '2025-06-25 12:08:24'),
(6, 'React', 'Frontend', NULL, '2025-06-25 12:08:24', '2025-06-25 12:08:24'),
(7, 'Node.js', 'Backend', NULL, '2025-06-25 12:08:24', '2025-06-25 12:08:24'),
(8, 'Python', 'Backend', NULL, '2025-06-25 12:08:24', '2025-06-25 12:08:24'),
(9, 'Git', 'Tools', NULL, '2025-06-25 12:08:24', '2025-06-25 12:08:24'),
(10, 'Docker', 'DevOps', NULL, '2025-06-25 12:08:24', '2025-06-25 12:08:24');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `profile_image` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `profile_image`, `bio`, `created_at`, `updated_at`, `is_active`, `reset_token`, `reset_token_expires`) VALUES
(1, 'admin', 'admin@portfolio.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'User', 'admin', NULL, NULL, '2025-06-25 12:08:24', '2025-06-25 12:08:24', 1, NULL, NULL),
(2, 'user1', 'user1@portfolio.com', '$2y$10$tgy2J6KXtmU9Va2IPn6JwOCSJiQm8Qwqn1Glcmq4OPLyWD6YA4Fwi', 'User', 'Exemple', 'user', NULL, 'user1', '2025-06-25 12:23:20', '2025-06-25 12:25:22', 1, NULL, NULL),
(3, 'user2', 'user2@portfolio.com', '$2y$10$t1pHOfyCdmR9XNaaI8kWEe67pothnI8PmhVR5oz2WaRFPhJzR94kW', 'User', 'Tes', 'user', NULL, NULL, '2025-06-25 12:31:18', '2025-06-25 12:31:18', 1, NULL, NULL),
(4, 'HopesteR', 'c.lahmadi.braconnier@gmail.com', '$2y$10$J3BZ9ft7rOS42my5kDqzU.cefc1UOh0059zBwI0ts8G8LGnuFagmq', 'Yanis', 'LAHMADI-BRACONNIER', 'user', NULL, 'Etudiant en informatique à ESGI en alternance chez OneTeam', '2025-06-25 12:38:00', '2025-06-25 12:44:10', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user_skills`
--

DROP TABLE IF EXISTS `user_skills`;
CREATE TABLE `user_skills` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `level` enum('beginner','intermediate','advanced','expert') NOT NULL,
  `years_experience` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_skills`
--

INSERT INTO `user_skills` (`id`, `user_id`, `skill_id`, `level`, `years_experience`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'advanced', 1, '2025-06-25 12:43:02', '2025-06-25 12:43:02'),
(2, 4, 8, 'advanced', 1, '2025-06-25 12:43:02', '2025-06-25 12:43:02'),
(3, 4, 3, 'intermediate', 1, '2025-06-25 12:43:02', '2025-06-25 12:43:02'),
(4, 4, 10, 'beginner', 1, '2025-06-25 12:43:02', '2025-06-25 12:43:02'),
(5, 4, 5, 'advanced', 1, '2025-06-25 12:43:02', '2025-06-25 12:43:02'),
(6, 4, 4, 'advanced', 1, '2025-06-25 12:43:02', '2025-06-25 12:43:02'),
(7, 4, 2, 'intermediate', 1, '2025-06-25 12:43:02', '2025-06-25 12:43:02'),
(8, 4, 6, 'beginner', 1, '2025-06-25 12:43:02', '2025-06-25 12:43:02'),
(9, 4, 9, 'intermediate', 1, '2025-06-25 12:43:02', '2025-06-25 12:43:02');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `project_likes`
--
ALTER TABLE `project_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`project_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Index pour la table `skills`
--
ALTER TABLE `skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_skill` (`user_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `project_likes`
--
ALTER TABLE `project_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `skills`
--
ALTER TABLE `skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user_skills`
--
ALTER TABLE `user_skills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `project_likes`
--
ALTER TABLE `project_likes`
  ADD CONSTRAINT `project_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_likes_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_skills`
--
ALTER TABLE `user_skills`
  ADD CONSTRAINT `user_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
