-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 24 nov. 2025 à 13:43
-- Version du serveur : 11.8.3-MariaDB-log
-- Version de PHP : 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `u482683110_26LALIE_BDD`
--

-- --------------------------------------------------------

--
-- Structure de la table `analytics_cr`
--

CREATE TABLE `analytics_cr` (
  `id` int(11) NOT NULL,
  `groupe_id` int(11) DEFAULT NULL,
  `mois` varchar(7) NOT NULL,
  `total_cr` int(11) DEFAULT 0,
  `cr_soumis` int(11) DEFAULT 0,
  `cr_evalues` int(11) DEFAULT 0,
  `cr_approuves` int(11) DEFAULT 0,
  `taux_soumission` decimal(5,2) DEFAULT 0.00,
  `taux_evaluation` decimal(5,2) DEFAULT 0.00,
  `delai_moyen_evaluation` decimal(10,2) DEFAULT 0.00,
  `date_calcul` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `archives_cr`
--

CREATE TABLE `archives_cr` (
  `id` int(11) NOT NULL,
  `cr_id` bigint(20) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `raison_archivage` text DEFAULT NULL,
  `date_archivage` datetime DEFAULT current_timestamp(),
  `archivable` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `audit_export`
--

CREATE TABLE `audit_export` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `nom_fichier` varchar(100) NOT NULL,
  `type_export` varchar(20) DEFAULT 'pdf',
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `filtres` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`filtres`)),
  `nombre_enregistrements` int(11) DEFAULT 0,
  `chemin_fichier` varchar(150) DEFAULT NULL,
  `date_export` datetime DEFAULT current_timestamp(),
  `date_suppression` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `audit_trail`
--

CREATE TABLE `audit_trail` (
  `id` bigint(20) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `entite` varchar(50) NOT NULL,
  `entite_id` bigint(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `anciennes_donnees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`anciennes_donnees`)),
  `nouvelles_donnees` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`nouvelles_donnees`)),
  `adresse_ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `date_action` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `checklists_modeles`
--

CREATE TABLE `checklists_modeles` (
  `id` int(11) NOT NULL,
  `modele_id` int(11) NOT NULL,
  `item_texte` varchar(150) NOT NULL,
  `ordre` int(11) NOT NULL,
  `obligatoire` tinyint(1) DEFAULT 1,
  `description_aide` text DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commentaires`
--

CREATE TABLE `commentaires` (
  `id` int(11) NOT NULL,
  `cr_id` bigint(20) NOT NULL,
  `professeur_id` int(11) NOT NULL,
  `commentaire` text DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cr`
--

CREATE TABLE `cr` (
  `num` bigint(20) NOT NULL,
  `date` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `titre` varchar(100) DEFAULT NULL,
  `contenu_html` longtext DEFAULT NULL,
  `vu` tinyint(1) DEFAULT 0,
  `archivé` tinyint(1) DEFAULT 0,
  `datetime` datetime DEFAULT NULL,
  `num_version` int(11) DEFAULT 1,
  `num_utilisateur` int(11) DEFAULT NULL,
  `groupe_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cr`
--

INSERT INTO `cr` (`num`, `date`, `description`, `titre`, `contenu_html`, `vu`, `archivé`, `datetime`, `num_version`, `num_utilisateur`, `groupe_id`) VALUES
(10, '2025-10-21', 'ffff', NULL, '<p>ffff</p>', 0, 0, '2025-10-21 22:21:07', 1, 3, NULL),
(11, '2025-10-21', 'ddddd', NULL, '<p>ddddddddd</p>', 0, 0, '2025-10-21 22:33:35', 1, 3, NULL),
(12, '2025-10-21', 'fdddd', NULL, '<p>ddddd</p>', 0, 0, '2025-10-21 23:03:35', 1, 3, NULL),
(13, '2025-10-21', 'fff', NULL, '<p>ffffff</p>', 0, 0, '2025-10-21 23:16:54', 1, 3, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `forum_commentaires`
--

CREATE TABLE `forum_commentaires` (
  `comment_id` int(11) NOT NULL,
  `r_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `forum_favoris`
--

CREATE TABLE `forum_favoris` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `question_id` int(11) NOT NULL,
  `added_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `forum_likes`
--

CREATE TABLE `forum_likes` (
  `like_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `r_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `forum_likes`
--

INSERT INTO `forum_likes` (`like_id`, `user_id`, `r_id`, `created_at`) VALUES
(1, 1, 2, '2025-11-24 11:45:00'),
(2, 3, 2, '2025-11-24 11:46:00');

-- --------------------------------------------------------

--
-- Structure de la table `forum_logs`
--

CREATE TABLE `forum_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `forum_logs`
--

INSERT INTO `forum_logs` (`log_id`, `user_id`, `action`, `details`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 11, 'connexion', 'Utilisateur ethan.lalienne connecté', '2a01:cb00:b72:ba00:816e:ca49:4a86:5c22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:41:43'),
(2, 11, 'connexion', 'Utilisateur ethan.lalienne connecté', '2a01:cb00:b72:ba00:816e:ca49:4a86:5c22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:41:48'),
(3, 11, 'connexion', 'Utilisateur ethan.lalienne connecté', '2a01:cb00:b72:ba00:816e:ca49:4a86:5c22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:42:54'),
(4, 11, 'connexion', 'Utilisateur ethan.lalienne connecté', '2a01:cb00:b72:ba00:816e:ca49:4a86:5c22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:42:54'),
(5, 11, 'connexion', 'Utilisateur ethan.lalienne connecté', '2a01:cb00:b72:ba00:816e:ca49:4a86:5c22', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-24 13:42:55');

-- --------------------------------------------------------

--
-- Structure de la table `forum_notifications`
--

CREATE TABLE `forum_notifications` (
  `notif_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `titre` varchar(200) NOT NULL,
  `contenu` text NOT NULL,
  `lien` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `forum_question`
--

CREATE TABLE `forum_question` (
  `q_id` int(11) NOT NULL,
  `q_date_ajout` datetime DEFAULT current_timestamp(),
  `q_titre` varchar(100) NOT NULL,
  `q_contenu` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `views_count` int(11) DEFAULT 0,
  `last_activity` datetime DEFAULT current_timestamp(),
  `is_solution` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `forum_question`
--

INSERT INTO `forum_question` (`q_id`, `q_date_ajout`, `q_titre`, `q_contenu`, `user_id`, `status`, `views_count`, `last_activity`, `is_solution`) VALUES
(1, '2013-03-24 12:54:00', 'Comment réparer un ordinateur?', 'Bonjour, j\'ai mon ordinateur de cassé, comment puis-je procéder pour le réparer?', 1, 'open', 1, '2025-11-24 13:24:50', 0),
(2, '2013-03-26 19:27:00', 'Comment changer un pneu?', 'Quel est la meilleur méthode pour changer un pneu facilement ?', 1, 'open', 0, '2025-11-24 13:24:50', 0),
(3, '2013-04-18 20:09:00', 'Que faire si un appareil est cassé?', 'Est-il préférable de réparer les appareils électriques ou d\'en acheter de nouveaux?', 3, 'open', 0, '2025-11-24 13:24:50', 0),
(4, '2013-04-22 17:14:00', 'Comment faire nettoyer un clavier d\'ordinateur?', 'Bonjour, sous mon clavier d\'ordinateur il y a beaucoup de poussière, comment faut-il procéder pour le nettoyer? Merci', 3, 'closed', 0, '2025-11-24 13:24:50', 0);

-- --------------------------------------------------------

--
-- Structure de la table `forum_reponse`
--

CREATE TABLE `forum_reponse` (
  `r_id` int(11) NOT NULL,
  `r_date_ajout` datetime DEFAULT current_timestamp(),
  `r_contenu` text NOT NULL,
  `r_fk_question_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `likes_count` int(11) DEFAULT 0,
  `is_solution` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `forum_reponse`
--

INSERT INTO `forum_reponse` (`r_id`, `r_date_ajout`, `r_contenu`, `r_fk_question_id`, `user_id`, `likes_count`, `is_solution`) VALUES
(1, '2013-03-27 07:44:00', 'Bonjour. Pouvez-vous expliquer ce qui ne fonctionne pas avec votre ordinateur? Merci.', 1, 2, 0, 0),
(2, '2013-03-28 19:27:00', 'Bonsoir, le plus simple consiste à faire appel à un professionnel pour réparer un ordinateur. Cordialement,', 1, 3, 2, 0),
(3, '2013-05-09 22:10:00', 'Des conseils son disponible sur internet sur ce sujet.', 2, 2, 0, 0),
(4, '2013-05-24 09:47:00', 'Bonjour. Ça dépend de vous, de votre budget et de vos préférence vis-à-vis de l\'écologie. Cordialement,', 3, 2, 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `forum_utilisateur`
--

CREATE TABLE `forum_utilisateur` (
  `user_id` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `derniere_connexion` datetime DEFAULT NULL,
  `is_admin` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `date_inscription` datetime DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL,
  `signature` text DEFAULT NULL,
  `reputation` int(11) DEFAULT 0,
  `email` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `forum_utilisateur`
--

INSERT INTO `forum_utilisateur` (`user_id`, `login`, `password`, `date_naissance`, `derniere_connexion`, `is_admin`, `created_at`, `date_inscription`, `avatar`, `signature`, `reputation`, `email`) VALUES
(1, 'utilisateur1', '$2y$12$uRV81mf.DZ/hb.ybyRqCFOdSDOSioV4L3SBW1VfMSTIwDX1GenZxO', '1980-01-01', NULL, 0, '2025-11-24 12:02:46', '2025-11-24 13:24:50', NULL, NULL, 0, NULL),
(2, 'utilisateur2', '$2y$12$hgKpU4hPDcckOwjZvGt0vedahduuCgdocItl8NKJVhLzr9PIiyJgG', '1970-01-01', NULL, 0, '2025-11-24 12:02:46', '2025-11-24 13:24:50', NULL, NULL, 0, NULL),
(3, 'utilisateur3', '$2y$12$TlX24dT3wKencVn3KqQ8ZuTce4Elb7rThR.iH7U9pGObLLYriKaUu', '1990-01-01', NULL, 0, '2025-11-24 12:02:46', '2025-11-24 13:24:50', NULL, NULL, 0, NULL),
(4, 'utilisateur4', '$2y$12$gsJfJuQNVNLqvgG4Zo1uxOEe1et92ybfve1T3ZiIFKxnqeydFaVqy', '1985-06-15', NULL, 0, '2025-11-24 12:02:46', '2025-11-24 13:24:50', NULL, NULL, 0, NULL),
(5, 'utilisateur5', '$2y$12$NFtC1UWrSiV7c8YETfX7lesCzWLthw2OZgJqfBuK2d27Lrr/Ks1fa', '1988-03-22', NULL, 0, '2025-11-24 12:02:46', '2025-11-24 13:24:50', NULL, NULL, 0, NULL),
(6, 'utilisateur6', '$2y$12$gGMKQYq8w8OBSq7kqKNfU.HzFUcDvf0GWjhrnUWt4o9yEBJQQvL2S', '1992-11-10', NULL, 0, '2025-11-24 12:02:46', '2025-11-24 13:24:50', NULL, NULL, 0, NULL),
(7, 'utilisateur7', '$2y$12$clMe8..zOFqvN.Wn3wktquwlbyB5nkHuBOiWTetc7XJ1il4uKXCBq', '1975-07-05', NULL, 0, '2025-11-24 12:02:46', '2025-11-24 13:24:50', NULL, NULL, 0, NULL),
(8, 'utilisateur8', '$2y$12$br1CFZYGP6v.WxNDHTsgn.l9XIsTzTGvgEgHLNLQ2XeJpPZ4KQemW', '1982-09-18', NULL, 0, '2025-11-24 12:02:46', '2025-11-24 13:24:50', NULL, NULL, 0, NULL),
(9, 'utilisateur9', '$2y$12$ORulLtxk1HgnpAAhswuvz.GRdUzjLuEETYGTa7e/bik9AOM88OP0q', '1995-12-25', NULL, 0, '2025-11-24 12:02:46', '2025-11-24 13:24:50', NULL, NULL, 0, NULL),
(10, 'utilisateur10', '$2y$12$BSkwNga3gxHTfUl6j.euZekYem5s87EyJ5sGMs9.QXVaFfy0sTF9S', '1987-04-14', NULL, 0, '2025-11-24 12:02:46', '2025-11-24 13:24:50', NULL, NULL, 0, NULL),
(11, 'ethan.lalienne', '$2y$12$tqKgDcXvmcWpTI.KBiwhQOOwKTDK1YTOdYz8K26VVfAz4uunZVvgu', '1995-01-01', '2025-11-24 13:42:55', 1, '2025-11-24 12:02:46', '2025-11-24 13:24:50', NULL, NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `groupes`
--

CREATE TABLE `groupes` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `professeur_responsable_id` int(11) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `logs_erreurs`
--

CREATE TABLE `logs_erreurs` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `page` varchar(100) NOT NULL,
  `erreur` text NOT NULL,
  `trace` text DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `membres_groupe`
--

CREATE TABLE `membres_groupe` (
  `id` int(11) NOT NULL,
  `groupe_id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `date_ajout` datetime DEFAULT current_timestamp(),
  `statut` varchar(20) DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `modeles_cr`
--

CREATE TABLE `modeles_cr` (
  `id` int(11) NOT NULL,
  `titre` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `contenu_html` longtext DEFAULT NULL,
  `professeur_id` int(11) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `titre` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `lien` varchar(150) DEFAULT NULL,
  `lue` tinyint(1) DEFAULT 0,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `pieces_jointes`
--

CREATE TABLE `pieces_jointes` (
  `id` int(11) NOT NULL,
  `cr_id` bigint(20) NOT NULL,
  `nom_fichier` varchar(100) NOT NULL,
  `type_mime` varchar(50) DEFAULT NULL,
  `taille` int(11) DEFAULT NULL,
  `donnees` longblob DEFAULT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rappels_soumission`
--

CREATE TABLE `rappels_soumission` (
  `id` int(11) NOT NULL,
  `groupe_id` int(11) NOT NULL,
  `date_limite` datetime NOT NULL,
  `titre` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `professeur_id` int(11) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sauvegardes_auto`
--

CREATE TABLE `sauvegardes_auto` (
  `id` int(11) NOT NULL,
  `cr_id` bigint(20) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `contenu_html` longtext DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_sauvegarde` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `stage`
--

CREATE TABLE `stage` (
  `num` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `adresse` varchar(80) DEFAULT NULL,
  `CP` int(11) DEFAULT NULL,
  `ville` varchar(30) DEFAULT NULL,
  `tel` varchar(15) DEFAULT NULL,
  `libelleStage` varchar(200) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `num_tuteur` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `stage`
--

INSERT INTO `stage` (`num`, `nom`, `adresse`, `CP`, `ville`, `tel`, `libelleStage`, `email`, `num_tuteur`) VALUES
(1, 'First Car', '15 Rue Carnot', 92400, 'Courbevoie', '0123456789', 'BTS SIO SLAM : faire un site internet', 'firstcar92@gmail.com', 1),
(2, 'First Car', '15 Rue Carnot', 92400, 'Courbevoie', '0123456789', 'Faire un site internet', 'firstcar92@gmail.com', 2);

-- --------------------------------------------------------

--
-- Structure de la table `statuts_cr`
--

CREATE TABLE `statuts_cr` (
  `id` int(11) NOT NULL,
  `cr_id` bigint(20) NOT NULL,
  `statut` varchar(20) NOT NULL DEFAULT 'brouillon',
  `date_soumission` datetime DEFAULT NULL,
  `date_evaluation` datetime DEFAULT NULL,
  `date_approbation` datetime DEFAULT NULL,
  `date_limite_soumission` datetime DEFAULT NULL,
  `professeur_evaluateur_id` int(11) DEFAULT NULL,
  `notes_evaluation` text DEFAULT NULL,
  `feedback_general` text DEFAULT NULL,
  `date_modification_statut` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tuteur`
--

CREATE TABLE `tuteur` (
  `num` int(11) NOT NULL,
  `nom` varchar(30) NOT NULL,
  `prenom` varchar(30) NOT NULL,
  `tel` varchar(15) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `tuteur`
--

INSERT INTO `tuteur` (`num`, `nom`, `prenom`, `tel`, `email`) VALUES
(1, 'Morin', 'Nicolas', '0123456789', 'nicolasmorin@gmail.com'),
(2, 'Morin', 'Nicolas', '0123456789', 'nicolas@gmail.com');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `num` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `tel` varchar(15) DEFAULT NULL,
  `login` varchar(50) NOT NULL,
  `motdepasse` varchar(32) NOT NULL,
  `type` int(11) DEFAULT NULL,
  `email` varchar(80) DEFAULT NULL,
  `option` int(11) DEFAULT NULL,
  `num_stage` int(11) DEFAULT NULL,
  `token` varchar(100) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `token_created_at` datetime DEFAULT NULL,
  `email_valide` tinyint(1) DEFAULT 0,
  `code_verification` varchar(6) DEFAULT NULL,
  `email_verification_token` varchar(50) DEFAULT NULL,
  `email_verification_token_created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`num`, `nom`, `prenom`, `tel`, `login`, `motdepasse`, `type`, `email`, `option`, `num_stage`, `token`, `date`, `token_created_at`, `email_valide`, `code_verification`, `email_verification_token`, `email_verification_token_created_at`) VALUES
(1, 'Lalienne', 'Ethan', '0123456789', 'ethan.lalienne', '482c811da5d5b4bc6d497ffa98491e38', 0, 'ethanlalienne92@gmail.com', 1, 1, NULL, '2025-01-15', NULL, 1, NULL, NULL, NULL),
(2, 'Dubois', 'Marie', '0134567890', 'marie.dubois', '482c811da5d5b4bc6d497ffa98491e38', 1, 'marie.dubois@edu.esiee.fr', NULL, NULL, NULL, '2025-01-15', NULL, 1, NULL, NULL, NULL),
(3, 'Martin', 'Pierre', '0145678901', 'pierre.martin', '482c811da5d5b4bc6d497ffa98491e38', 0, 'pierre.martin@edu.esiee.fr', 1, 1, NULL, '2025-01-15', NULL, 1, NULL, NULL, NULL),
(4, 'Bernard', 'Sophie', '0156789012', 'sophie.bernard', '482c811da5d5b4bc6d497ffa98491e38', 0, 'sophie.bernard@edu.esiee.fr', 2, 2, NULL, '2025-01-15', NULL, 1, NULL, NULL, NULL),
(5, 'Richard', 'Thomas', '0167890123', 'thomas.richard', '482c811da5d5b4bc6d497ffa98491e38', 1, 'thomas.richard@edu.esiee.fr', NULL, NULL, NULL, '2025-01-15', NULL, 1, NULL, NULL, NULL),
(6, 'Petit', 'Laura', '0178901234', 'laura.petit', '482c811da5d5b4bc6d497ffa98491e38', 0, 'laura.petit@edu.esiee.fr', 1, 1, NULL, '2025-01-15', NULL, 1, NULL, NULL, NULL),
(7, 'Robert', 'Kevin', '0189012345', 'kevin.robert', '482c811da5d5b4bc6d497ffa98491e38', 0, 'kevin.robert@edu.esiee.fr', 2, 2, NULL, '2025-01-15', NULL, 1, NULL, NULL, NULL),
(8, 'Durand', 'Alice', '0190123456', 'alice.durand', '482c811da5d5b4bc6d497ffa98491e38', 1, 'alice.durand@edu.esiee.fr', NULL, NULL, NULL, '2025-01-15', NULL, 1, NULL, NULL, NULL),
(9, 'Moreau', 'Julien', '0112345678', 'julien.moreau', '482c811da5d5b4bc6d497ffa98491e38', 0, 'julien.moreau@edu.esiee.fr', 1, 1, NULL, '2025-01-15', NULL, 1, NULL, NULL, NULL),
(10, 'Simon', 'Chloé', '0123456789', 'chloe.simon', '482c811da5d5b4bc6d497ffa98491e38', 0, 'chloe.simon@edu.esiee.fr', 2, 2, NULL, '2025-01-15', NULL, 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `validations_cr`
--

CREATE TABLE `validations_cr` (
  `id` int(11) NOT NULL,
  `cr_id` bigint(20) NOT NULL,
  `checklist_item_id` int(11) NOT NULL,
  `complete` tinyint(1) DEFAULT 0,
  `commentaire` text DEFAULT NULL,
  `date_verification` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `versions_cr`
--

CREATE TABLE `versions_cr` (
  `id` bigint(20) NOT NULL,
  `cr_id` bigint(20) NOT NULL,
  `numero_version` int(11) NOT NULL,
  `titre` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contenu_html` longtext DEFAULT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `note_version` text DEFAULT NULL,
  `restaurable` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `versions_cr_audit`
--

CREATE TABLE `versions_cr_audit` (
  `id` bigint(20) NOT NULL,
  `cr_id` bigint(20) NOT NULL,
  `numero_version` int(11) NOT NULL,
  `titre` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contenu_html` longtext DEFAULT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `note_version` text DEFAULT NULL,
  `type_modification` varchar(20) DEFAULT 'modification',
  `nb_caracteres_ajoutes` int(11) DEFAULT 0,
  `nb_caracteres_supprimes` int(11) DEFAULT 0,
  `taille_fichier` int(11) DEFAULT NULL,
  `restaurable` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `analytics_cr`
--
ALTER TABLE `analytics_cr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groupe_mois` (`groupe_id`,`mois`),
  ADD KEY `groupe_id` (`groupe_id`),
  ADD KEY `mois` (`mois`);

--
-- Index pour la table `archives_cr`
--
ALTER TABLE `archives_cr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cr_id` (`cr_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `audit_export`
--
ALTER TABLE `audit_export`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `date_export` (`date_export`),
  ADD KEY `type_export` (`type_export`);

--
-- Index pour la table `audit_trail`
--
ALTER TABLE `audit_trail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `action` (`action`),
  ADD KEY `entite` (`entite`),
  ADD KEY `entite_id` (`entite_id`),
  ADD KEY `date_action` (`date_action`);

--
-- Index pour la table `checklists_modeles`
--
ALTER TABLE `checklists_modeles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modele_id` (`modele_id`);

--
-- Index pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cr_id` (`cr_id`),
  ADD KEY `professeur_id` (`professeur_id`);

--
-- Index pour la table `cr`
--
ALTER TABLE `cr`
  ADD PRIMARY KEY (`num`),
  ADD KEY `num_utilisateur` (`num_utilisateur`),
  ADD KEY `groupe_id` (`groupe_id`);

--
-- Index pour la table `forum_commentaires`
--
ALTER TABLE `forum_commentaires`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `commentaires_ibfk_1` (`r_id`),
  ADD KEY `commentaires_ibfk_2` (`user_id`);

--
-- Index pour la table `forum_favoris`
--
ALTER TABLE `forum_favoris`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favoris` (`user_id`,`question_id`),
  ADD KEY `favoris_ibfk_1` (`user_id`),
  ADD KEY `favoris_ibfk_2` (`question_id`);

--
-- Index pour la table `forum_likes`
--
ALTER TABLE `forum_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`r_id`),
  ADD KEY `likes_ibfk_1` (`user_id`),
  ADD KEY `likes_ibfk_2` (`r_id`);

--
-- Index pour la table `forum_logs`
--
ALTER TABLE `forum_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `logs_ibfk_1` (`user_id`);

--
-- Index pour la table `forum_notifications`
--
ALTER TABLE `forum_notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD KEY `notifications_ibfk_1` (`user_id`),
  ADD KEY `notifications_ibfk_2` (`from_user_id`);

--
-- Index pour la table `forum_question`
--
ALTER TABLE `forum_question`
  ADD PRIMARY KEY (`q_id`),
  ADD KEY `question_ibfk_1` (`user_id`);

--
-- Index pour la table `forum_reponse`
--
ALTER TABLE `forum_reponse`
  ADD PRIMARY KEY (`r_id`),
  ADD KEY `reponse_ibfk_1` (`r_fk_question_id`),
  ADD KEY `reponse_ibfk_2` (`user_id`);

--
-- Index pour la table `forum_utilisateur`
--
ALTER TABLE `forum_utilisateur`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- Index pour la table `groupes`
--
ALTER TABLE `groupes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professeur_responsable_id` (`professeur_responsable_id`);

--
-- Index pour la table `logs_erreurs`
--
ALTER TABLE `logs_erreurs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `membres_groupe`
--
ALTER TABLE `membres_groupe`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `groupe_utilisateur` (`groupe_id`,`utilisateur_id`),
  ADD KEY `groupe_id` (`groupe_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `modeles_cr`
--
ALTER TABLE `modeles_cr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `professeur_id` (`professeur_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `pieces_jointes`
--
ALTER TABLE `pieces_jointes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cr_id` (`cr_id`);

--
-- Index pour la table `rappels_soumission`
--
ALTER TABLE `rappels_soumission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `groupe_id` (`groupe_id`),
  ADD KEY `professeur_id` (`professeur_id`),
  ADD KEY `date_limite` (`date_limite`);

--
-- Index pour la table `sauvegardes_auto`
--
ALTER TABLE `sauvegardes_auto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cr_id` (`cr_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `date_sauvegarde` (`date_sauvegarde`);

--
-- Index pour la table `stage`
--
ALTER TABLE `stage`
  ADD PRIMARY KEY (`num`),
  ADD KEY `num_tuteur` (`num_tuteur`);

--
-- Index pour la table `statuts_cr`
--
ALTER TABLE `statuts_cr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cr_id` (`cr_id`),
  ADD KEY `professeur_evaluateur_id` (`professeur_evaluateur_id`);

--
-- Index pour la table `tuteur`
--
ALTER TABLE `tuteur`
  ADD PRIMARY KEY (`num`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`num`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `num_stage` (`num_stage`);

--
-- Index pour la table `validations_cr`
--
ALTER TABLE `validations_cr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cr_item` (`cr_id`,`checklist_item_id`),
  ADD KEY `cr_id` (`cr_id`),
  ADD KEY `checklist_item_id` (`checklist_item_id`);

--
-- Index pour la table `versions_cr`
--
ALTER TABLE `versions_cr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cr_id` (`cr_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `date_creation` (`date_creation`);

--
-- Index pour la table `versions_cr_audit`
--
ALTER TABLE `versions_cr_audit`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cr_id` (`cr_id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `date_creation` (`date_creation`),
  ADD KEY `numero_version` (`numero_version`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `analytics_cr`
--
ALTER TABLE `analytics_cr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `archives_cr`
--
ALTER TABLE `archives_cr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `audit_export`
--
ALTER TABLE `audit_export`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `audit_trail`
--
ALTER TABLE `audit_trail`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `checklists_modeles`
--
ALTER TABLE `checklists_modeles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commentaires`
--
ALTER TABLE `commentaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cr`
--
ALTER TABLE `cr`
  MODIFY `num` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `forum_commentaires`
--
ALTER TABLE `forum_commentaires`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `forum_favoris`
--
ALTER TABLE `forum_favoris`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `forum_likes`
--
ALTER TABLE `forum_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `forum_logs`
--
ALTER TABLE `forum_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `forum_notifications`
--
ALTER TABLE `forum_notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `forum_question`
--
ALTER TABLE `forum_question`
  MODIFY `q_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `forum_reponse`
--
ALTER TABLE `forum_reponse`
  MODIFY `r_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `forum_utilisateur`
--
ALTER TABLE `forum_utilisateur`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `groupes`
--
ALTER TABLE `groupes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `logs_erreurs`
--
ALTER TABLE `logs_erreurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `membres_groupe`
--
ALTER TABLE `membres_groupe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `modeles_cr`
--
ALTER TABLE `modeles_cr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `pieces_jointes`
--
ALTER TABLE `pieces_jointes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rappels_soumission`
--
ALTER TABLE `rappels_soumission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sauvegardes_auto`
--
ALTER TABLE `sauvegardes_auto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `stage`
--
ALTER TABLE `stage`
  MODIFY `num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `statuts_cr`
--
ALTER TABLE `statuts_cr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `tuteur`
--
ALTER TABLE `tuteur`
  MODIFY `num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `num` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `validations_cr`
--
ALTER TABLE `validations_cr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `versions_cr`
--
ALTER TABLE `versions_cr`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `versions_cr_audit`
--
ALTER TABLE `versions_cr_audit`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `commentaires`
--
ALTER TABLE `commentaires`
  ADD CONSTRAINT `fk_commentaires_utilisateur` FOREIGN KEY (`professeur_id`) REFERENCES `utilisateur` (`num`);

--
-- Contraintes pour la table `cr`
--
ALTER TABLE `cr`
  ADD CONSTRAINT `fk_cr_groupe` FOREIGN KEY (`groupe_id`) REFERENCES `groupes` (`id`),
  ADD CONSTRAINT `fk_cr_utilisateur` FOREIGN KEY (`num_utilisateur`) REFERENCES `utilisateur` (`num`);

--
-- Contraintes pour la table `forum_commentaires`
--
ALTER TABLE `forum_commentaires`
  ADD CONSTRAINT `forum_commentaires_ibfk_1` FOREIGN KEY (`r_id`) REFERENCES `forum_reponse` (`r_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_commentaires_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `forum_utilisateur` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `forum_favoris`
--
ALTER TABLE `forum_favoris`
  ADD CONSTRAINT `forum_favoris_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `forum_utilisateur` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_favoris_ibfk_2` FOREIGN KEY (`question_id`) REFERENCES `forum_question` (`q_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `forum_likes`
--
ALTER TABLE `forum_likes`
  ADD CONSTRAINT `forum_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `forum_utilisateur` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_likes_ibfk_2` FOREIGN KEY (`r_id`) REFERENCES `forum_reponse` (`r_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `forum_logs`
--
ALTER TABLE `forum_logs`
  ADD CONSTRAINT `forum_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `forum_utilisateur` (`user_id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `forum_notifications`
--
ALTER TABLE `forum_notifications`
  ADD CONSTRAINT `forum_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `forum_utilisateur` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_notifications_ibfk_2` FOREIGN KEY (`from_user_id`) REFERENCES `forum_utilisateur` (`user_id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `forum_question`
--
ALTER TABLE `forum_question`
  ADD CONSTRAINT `forum_question_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `forum_utilisateur` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `forum_reponse`
--
ALTER TABLE `forum_reponse`
  ADD CONSTRAINT `forum_reponse_ibfk_1` FOREIGN KEY (`r_fk_question_id`) REFERENCES `forum_question` (`q_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_reponse_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `forum_utilisateur` (`user_id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `membres_groupe`
--
ALTER TABLE `membres_groupe`
  ADD CONSTRAINT `fk_membres_groupe` FOREIGN KEY (`groupe_id`) REFERENCES `groupes` (`id`),
  ADD CONSTRAINT `fk_membres_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`num`);

--
-- Contraintes pour la table `statuts_cr`
--
ALTER TABLE `statuts_cr`
  ADD CONSTRAINT `fk_statuts_evaluateur` FOREIGN KEY (`professeur_evaluateur_id`) REFERENCES `utilisateur` (`num`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
