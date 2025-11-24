-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 23 nov. 2025 à 11:53
-- Version du serveur : 8.0.31
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `thonaske`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateur`
--

DROP TABLE IF EXISTS `administrateur`;
CREATE TABLE IF NOT EXISTS `administrateur` (
  `id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `administrateur`
--

INSERT INTO `administrateur` (`id`) VALUES
(1);

-- --------------------------------------------------------

--
-- Structure de la table `avis`
--

DROP TABLE IF EXISTS `avis`;
CREATE TABLE IF NOT EXISTS `avis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `professionnel_id` int NOT NULL,
  `note` int NOT NULL,
  `commentaire` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_8F91ABF06B899279` (`patient_id`),
  KEY `IDX_8F91ABF08A49CC82` (`professionnel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `avis`
--

INSERT INTO `avis` (`id`, `patient_id`, `professionnel_id`, `note`, `commentaire`, `created_at`) VALUES
(9, 23, 22, 3, 'précis', '2025-05-11 16:54:45'),
(10, 23, 22, 5, 'mauvaise humeur', '2025-05-11 16:58:59'),
(11, 23, 22, 1, 'indécis', '2025-05-11 16:59:17'),
(12, 23, 22, 4, 'test', '2025-05-11 16:59:32');

-- --------------------------------------------------------

--
-- Structure de la table `consultation`
--

DROP TABLE IF EXISTS `consultation`;
CREATE TABLE IF NOT EXISTS `consultation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rendez_vous_id` int NOT NULL,
  `date` datetime NOT NULL,
  `notes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ordonnances` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '(DC2Type:json)',
  `partage_autorise` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `prix` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_964685A691EF7EAA` (`rendez_vous_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `consultation`
--

INSERT INTO `consultation` (`id`, `rendez_vous_id`, `date`, `notes`, `ordonnances`, `partage_autorise`, `created_at`, `updated_at`, `prix`) VALUES
(6, 21, '2025-05-12 05:51:05', 'Marary loha', '[{\"medicament\":\"Parac\\u00e9tamol\",\"dose\":\"2 pillule\",\"prise\":[\"matin\",\"midi\"]}]', 0, '2025-05-12 05:51:04', '2025-05-12 05:51:04', 209333);

-- --------------------------------------------------------

--
-- Structure de la table `consultation_partage_professionnels`
--

DROP TABLE IF EXISTS `consultation_partage_professionnels`;
CREATE TABLE IF NOT EXISTS `consultation_partage_professionnels` (
  `consultation_id` int NOT NULL,
  `professionnel_de_sante_id` int NOT NULL,
  PRIMARY KEY (`consultation_id`,`professionnel_de_sante_id`),
  KEY `IDX_F6E7941262FF6CDF` (`consultation_id`),
  KEY `IDX_F6E79412C0EC2381` (`professionnel_de_sante_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `consultation_partage_professionnels`
--

INSERT INTO `consultation_partage_professionnels` (`consultation_id`, `professionnel_de_sante_id`) VALUES
(6, 22);

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20250410115802', '2025-04-10 11:58:20', 25598),
('DoctrineMigrations\\Version20250501025945', '2025-05-01 03:00:01', 24206),
('DoctrineMigrations\\Version20250507115252', '2025-05-07 11:53:12', 4217),
('DoctrineMigrations\\Version20250507121434', '2025-05-07 12:14:43', 1957),
('DoctrineMigrations\\Version20250507140638', '2025-05-07 14:06:50', 17097),
('DoctrineMigrations\\Version20250508033501', '2025-05-08 03:35:06', 39578),
('DoctrineMigrations\\Version20250508050903', '2025-05-08 05:09:21', 1367),
('DoctrineMigrations\\Version20250508062918', '2025-05-08 06:29:35', 8646),
('DoctrineMigrations\\Version20250508073701', '2025-05-08 07:37:59', 4050),
('DoctrineMigrations\\Version20250508094535', '2025-05-08 09:45:53', 24559),
('DoctrineMigrations\\Version20250509085135', '2025-05-09 08:51:53', 41201),
('DoctrineMigrations\\Version20250509185732', '2025-05-09 18:58:03', 1925),
('DoctrineMigrations\\Version20250709091657', '2025-07-09 09:20:49', 1028);

-- --------------------------------------------------------

--
-- Structure de la table `gestion_agenda`
--

DROP TABLE IF EXISTS `gestion_agenda`;
CREATE TABLE IF NOT EXISTS `gestion_agenda` (
  `id` int NOT NULL AUTO_INCREMENT,
  `professionnel_id` int NOT NULL,
  `date_debut_indispo` datetime NOT NULL,
  `date_fin_indispo` datetime NOT NULL,
  `motif` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `IDX_15A364DA8A49CC82` (`professionnel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `gestion_agenda`
--

INSERT INTO `gestion_agenda` (`id`, `professionnel_id`, `date_debut_indispo`, `date_fin_indispo`, `motif`) VALUES
(23, 22, '2025-05-19 10:17:00', '2025-05-19 13:17:00', 'Déjeuner avec ma femme'),
(24, 22, '2025-05-13 08:51:00', '2025-05-13 12:51:00', 'soutenance');

-- --------------------------------------------------------

--
-- Structure de la table `horaire_travail`
--

DROP TABLE IF EXISTS `horaire_travail`;
CREATE TABLE IF NOT EXISTS `horaire_travail` (
  `id` int NOT NULL AUTO_INCREMENT,
  `professionnel_id` int NOT NULL,
  `jour` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_A74025A88A49CC82` (`professionnel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `horaire_travail`
--

INSERT INTO `horaire_travail` (`id`, `professionnel_id`, `jour`, `heure_debut`, `heure_fin`) VALUES
(74, 22, 'Lundi', '08:00:00', '15:00:00'),
(75, 22, 'Mardi', '08:00:00', '15:00:00'),
(76, 22, 'Mercredi', '08:00:00', '18:00:00'),
(77, 22, 'Jeudi', '14:00:00', '18:00:00'),
(78, 26, 'Lundi', '08:00:00', '17:00:00'),
(79, 26, 'Mardi', '08:00:00', '12:00:00'),
(80, 26, 'Mercredi', '09:00:00', '15:00:00'),
(81, 26, 'Jeudi', '08:00:00', '09:00:00'),
(82, 26, 'Vendredi', '08:00:00', '09:00:00'),
(83, 34, 'Mardi', '08:00:00', '09:00:00'),
(84, 34, 'Mercredi', '08:00:00', '09:00:00'),
(85, 34, 'Lundi', '08:00:00', '09:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `patient`
--

DROP TABLE IF EXISTS `patient`;
CREATE TABLE IF NOT EXISTS `patient` (
  `id` int NOT NULL,
  `antecedents_medicaux` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `maladies_chroniques` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:json)',
  `carnet_partage` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `patient`
--

INSERT INTO `patient` (`id`, `antecedents_medicaux`, `maladies_chroniques`, `carnet_partage`) VALUES
(23, 'NARARYYYYY', '[\"diab\\u00e8te\",\"hypertension\"]', 0),
(33, 'Lasa', '[\"asthme\"]', 0);

-- --------------------------------------------------------

--
-- Structure de la table `patient_partage_carnet`
--

DROP TABLE IF EXISTS `patient_partage_carnet`;
CREATE TABLE IF NOT EXISTS `patient_partage_carnet` (
  `patient_id` int NOT NULL,
  `professionnel_de_sante_id` int NOT NULL,
  PRIMARY KEY (`patient_id`,`professionnel_de_sante_id`),
  KEY `IDX_793475596B899279` (`patient_id`),
  KEY `IDX_79347559C0EC2381` (`professionnel_de_sante_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `professionnel_de_sante`
--

DROP TABLE IF EXISTS `professionnel_de_sante`;
CREATE TABLE IF NOT EXISTS `professionnel_de_sante` (
  `id` int NOT NULL,
  `specialite_id` int NOT NULL,
  `tarif` double NOT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D61F97A2195E0F0` (`specialite_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `professionnel_de_sante`
--

INSERT INTO `professionnel_de_sante` (`id`, `specialite_id`, `tarif`, `photo`) VALUES
(22, 28, 150000, 'KOCQ8585_20250511_195008.jpg'),
(26, 6, 20000, NULL),
(34, 13, 15000, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous`
--

DROP TABLE IF EXISTS `rendez_vous`;
CREATE TABLE IF NOT EXISTS `rendez_vous` (
  `id` int NOT NULL AUTO_INCREMENT,
  `patient_id` int NOT NULL,
  `professionnel_id` int NOT NULL,
  `date_heure` datetime NOT NULL,
  `statut` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `motif` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_65E8AA0A6B899279` (`patient_id`),
  KEY `IDX_65E8AA0A8A49CC82` (`professionnel_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `rendez_vous`
--

INSERT INTO `rendez_vous` (`id`, `patient_id`, `professionnel_id`, `date_heure`, `statut`, `motif`, `created_at`, `updated_at`) VALUES
(19, 23, 22, '2025-05-12 11:00:00', 'annule', 'aaaaaaaaaa', '2025-05-11 16:51:59', '2025-05-11 16:51:59'),
(20, 23, 22, '2025-05-19 11:30:00', 'en attente', 'contrôle optique', '2025-05-12 04:20:11', '2025-05-12 04:20:11'),
(21, 23, 22, '2025-05-19 09:00:00', 'annule', 'Test', '2025-05-12 05:48:16', '2025-05-12 05:48:16'),
(22, 33, 22, '2025-07-21 08:00:00', 'annule', 'marary diabeta', '2025-07-17 10:49:20', '2025-07-17 10:49:20'),
(23, 33, 22, '2025-07-22 08:30:00', 'en attente', 'marary be', '2025-07-17 10:54:54', '2025-07-17 10:54:54');

-- --------------------------------------------------------

--
-- Structure de la table `reset_password_request`
--

DROP TABLE IF EXISTS `reset_password_request`;
CREATE TABLE IF NOT EXISTS `reset_password_request` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `selector` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hashed_token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `expires_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_7CE748AA76ED395` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `specialite`
--

DROP TABLE IF EXISTS `specialite`;
CREATE TABLE IF NOT EXISTS `specialite` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `categorie` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sous_categorie` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `statut` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `specialite`
--

INSERT INTO `specialite` (`id`, `nom`, `description`, `categorie`, `sous_categorie`, `code`, `statut`) VALUES
(1, 'Allergologie', 'Spécialité qui étudie les allergies et le système immunitaire.', 'Médicale', 'Immunologie', NULL, ''),
(2, 'Anatomie et cytologie pathologiques', 'Examen des tissus et des cellules pour établir un diagnostic.', 'Médicale', 'Diagnostic', NULL, 'active'),
(3, 'Anesthésie-réanimation', 'Gestion de la douleur et prise en charge des urgences peropératoires et critiques.', 'Médicale', 'Urgences', NULL, ''),
(4, 'Biologie médicale', 'Analyse des échantillons biologiques afin d\'aider au diagnostic.', 'Médicale', 'Diagnostic', NULL, ''),
(5, 'Chirurgie maxillo-faciale', 'Interventions chirurgicales sur la face, la mâchoire et le cou.', 'Chirurgicale', 'Chirurgie de la face', NULL, ''),
(6, 'Chirurgie orale', 'Opérations chirurgicales concernant les dents et la cavité buccale.', 'Chirurgicale', 'Chirurgie buccale', NULL, ''),
(7, 'Chirurgie orthopédique et traumatologique', 'Traitement chirurgical des os et des lésions musculosquelettiques.', 'Chirurgicale', 'Chirurgie des os', NULL, ''),
(8, 'Chirurgie pédiatrique', 'Interventions chirurgicales adaptées aux besoins des enfants.', 'Chirurgicale', 'Chirurgie infantile', NULL, ''),
(9, 'Chirurgie plastique, reconstructrice et esthétique', 'Techniques chirurgicales visant à restaurer ou améliorer l\'apparence.', 'Chirurgicale', 'Chirurgie esthétique', NULL, ''),
(10, 'Chirurgie thoracique et cardiovasculaire', 'Traitement chirurgical des pathologies cardiaques et thoraciques.', 'Chirurgicale', 'Chirurgie cardiaque', NULL, ''),
(11, 'Chirurgie vasculaire', 'Interventions sur les vaisseaux sanguins afin de traiter les dysfonctionnements circulatoires.', 'Chirurgicale', 'Chirurgie des vaisseaux', NULL, ''),
(12, 'Chirurgie viscérale et digestive', 'Interventions chirurgicales sur les organes abdominaux et digestifs.', 'Chirurgicale', 'Chirurgie abdominale', NULL, ''),
(13, 'Dermatologie et vénérologie', 'Diagnostic et traitement des maladies de la peau et des affections vénériennes.', 'Médicale', 'Peau', NULL, ''),
(14, 'Endocrinologie-diabétologie-nutrition', 'Prise en charge des troubles hormonaux et métaboliques.', 'Médicale', 'Métabolisme', NULL, ''),
(15, 'Génétique médicale', 'Évaluation et traitement des maladies d’origine génétique.', 'Médicale', 'Diagnostic', NULL, ''),
(16, 'Gériatrie', 'Soins et suivi médical des personnes âgées.', 'Médicale', 'Soins aux personnes âgées', NULL, ''),
(17, 'Gynécologie médicale', 'Suivi et traitement des pathologies gynécologiques non chirurgicales.', 'Médicale', 'Santé féminine', NULL, ''),
(18, 'Gynécologie obstétrique', 'Prise en charge des grossesses et de l’accouchement.', 'Chirurgicale', 'Obstétrique', NULL, ''),
(19, 'Hématologie', 'Étude et traitement des maladies du sang.', 'Médicale', 'Diagnostic', NULL, ''),
(20, 'Hépato-gastro-entérologie', 'Prise en charge des maladies du foie et du système digestif.', 'Médicale', 'Digestif', NULL, ''),
(21, 'Maladies infectieuses et tropicales', 'Diagnostic et traitement des infections, notamment dans les zones tropicales.', 'Médicale', 'Infectiologie', NULL, ''),
(22, 'Médecine cardiovasculaire', 'Prise en charge médicale des pathologies du cœur et des vaisseaux.', 'Médicale', 'Cardiologie', NULL, ''),
(23, 'Médecine d\'urgence', 'Gestion des situations critiques et urgentes.', 'Médicale', 'Urgences', NULL, ''),
(24, 'Médecine et santé au travail', 'Prévention et traitement des risques professionnels.', 'Médicale', 'Santé publique', NULL, ''),
(25, 'Médecine générale', 'Soins primaires et suivi global des patients.', 'Médicale', 'Soins primaires', NULL, ''),
(26, 'Médecine intensive-réanimation', 'Prise en charge des patients en état critique nécessitant une réanimation.', 'Médicale', 'Urgences', NULL, ''),
(27, 'Médecine interne et immunologie clinique', 'Diagnostic et traitement des maladies internes.', 'Médicale', 'Soins internes', NULL, ''),
(28, 'Médecine légale et expertises médicales', 'Application des connaissances médicales dans le domaine légal.', 'Médicale', 'Légal', NULL, 'active'),
(29, 'Médecine nucléaire', 'Utilisation d\'isotopes pour le diagnostic et le traitement thérapeutique.', 'Médicale', 'Diagnostic', NULL, ''),
(30, 'Médecine physique et de réadaptation', 'Réadaptation des patients suite à des pathologies lourdes.', 'Médicale', 'Réadaptation', NULL, ''),
(31, 'Médecine vasculaire', 'Prise en charge non chirurgicale des maladies vasculaires.', 'Médicale', 'Vascularisation', NULL, ''),
(32, 'Néphrologie', 'Traitement des maladies rénales.', 'Médicale', 'Urinaire', NULL, ''),
(33, 'Neurochirurgie', 'Interventions chirurgicales sur le système nerveux central et périphérique.', 'Chirurgicale', 'Chirurgie du système nerveux', NULL, ''),
(34, 'Neurologie', 'Étude et traitement des troubles du système nerveux.', 'Médicale', 'Système nerveux', NULL, ''),
(35, 'Oncologie', 'Diagnostic et traitement des cancers.', 'Médicale', 'Cancer', NULL, ''),
(36, 'Ophtalmologie', 'Soins et interventions chirurgicales dédiés aux yeux.', 'Chirurgicale', 'Chirurgie de l\'œil', NULL, ''),
(37, 'Oto-rhino-laryngologie – chirurgie cervico-faciale', 'Prise en charge chirurgicale des affections ORL et des structures cervico-faciales.', 'Chirurgicale', 'ORL', NULL, ''),
(38, 'Pédiatrie', 'Soins médicaux destinés aux enfants et aux nouveau-nés.', 'Médicale', 'Enfants', NULL, ''),
(39, 'Pneumologie', 'Diagnostic et traitement des maladies respiratoires.', 'Médicale', 'Respiratoire', NULL, ''),
(40, 'Psychiatrie', 'Prise en charge des troubles mentaux et du comportement.', 'Médicale', 'Santé mentale', NULL, ''),
(41, 'Radiologie et imagerie médicale', 'Utilisation des techniques d\'imagerie pour le diagnostic médical.', 'Médicale', 'Diagnostic', NULL, ''),
(42, 'Rhumatologie', 'Traitement des affections musculosquelettiques et inflammatoires.', 'Médicale', 'Squelettique', NULL, ''),
(43, 'Santé publique', 'Organisation et promotion de la santé à l’échelle de la population.', 'Médicale', 'Santé communautaire', NULL, ''),
(44, 'Urologie', 'Prise en charge chirurgicale et médicale des affections du système urinaire.', 'Chirurgicale', 'Appareil urinaire', NULL, '');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adresse` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL,
  `discr` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `telephone`, `adresse`, `is_verified`, `discr`, `latitude`, `longitude`) VALUES
(1, 'Rakotomanga', 'Kantoniaina William', 'kantowilliam0@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$6QYTVPR0IW3cs7693ahRlQ$IzqHH0678LBJFNfuEynW/4daytjuiaqk+Rxn9pFi/C8', 'ROLE_ADMINISTRATEUR', NULL, NULL, 1, 'administrateur', NULL, NULL),
(22, 'Rakotomanga Razakamahazo', 'Andry William', 'rakotomangaandrywilliam@gmail.com', '$2y$13$3pRVO4gaS3NQP.X1vU7HX.9TsuzQSHDiMrd8HsA0QYkgTp3CibiM.', 'ROLE_PROFESSIONNEL_DE_SANTE', '0331808597', 'Lot II P 165 H Avaradoha', 1, 'professionnel_de_sante', -18.995040882269, 47.47939997876),
(23, 'Rakotomanga', 'Kantoniaina William', 'kantowilliam@hotmail.com', '$2y$13$ofUN3WLdOaJrHQMFmnSr/OVnC55X4hn2fYbLRNNe/rhMm6AfKLt.K', 'ROLE_PATIENT', '0389785400', 'lot II P 165 H Avaradoha', 1, 'patient', NULL, NULL),
(24, 'Rakoto', 'Jean', 'jean.rakoto@gmail.com', '$2y$13$ITNMabvSoWOdnwItO10BguSlvlUQgthtOJXFK7.tJTXZ0sacC/e0.', 'ROLE_PATIENT', '0341234567', 'Lot II F 23, Ambohimanarina, Antananarivo', 1, 'patient', NULL, NULL),
(25, 'Rasoanaivo', 'Lalao', 'lalao.rasoanaivo@hopital.mg', 'Dokotera2024!', 'ROLE_PATIENT', '0329876543', 'Hopitaly HJRA, Ampefiloha, Antananarivo', 1, 'patient', NULL, NULL),
(26, 'RAZAFINDRAKOTO', 'Hery', 'hery.razafindrakoto@hopital.mg', '$2y$13$ZL0eMqzZrSB/YJun0gD6pO85soAqwTE6gBf4pQyiigpQ.EF3BrzbK', 'ROLE_PROFESSIONNEL_DE_SANTE', '0341234567', 'Lot II F 23, Ambohimanarina, Antananarivo', 0, 'professionnel_de_sante', -18.8930206, 47.5379519),
(27, 'Bayane', 'Miguel', 'miguelsingcol@gmail.com', '$2y$13$D3Is4Ml/J9/0N61pMMqpTe5n0CqyHtdRT4oH9h/wEQ7uI4Ye/m9mO', 'ROLE_PATIENT', '0348349886', 'Alasora', 1, 'patient', NULL, NULL),
(28, 'Singcol', 'Singcol1', 'singcolmiguel9@gmail.com', '$2y$13$IUuLQaaapmOnN0AzGyDR0elfiWbSV/6OYjfdxihfK1YGeeTU/gS2u', 'ROLE_PATIENT', '0342190466', 'Alasora', 1, 'patient', NULL, NULL),
(33, 'Bayane', 'Miguel', 'bayane437@gmail.com', '$2y$13$.pONgA5ER8I4xN3cIF/jfemcUU601O.hb.qm.yaCKyFMecxxB2NMi', 'ROLE_PATIENT', '0348349886', 'Alasora', 1, 'patient', NULL, NULL),
(34, 'Soa', 'Rova', 'soa@gmail.com', '$2y$13$ZixXZq0VGACFKKgh00BRFOaKBWStpRqFwVDm5pkKuOd9rBKFcH7S6', 'ROLE_PROFESSIONNEL_DE_SANTE', '0342190466', 'Talata Maty', 0, 'professionnel_de_sante', -18.8969854, 47.5373728);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `administrateur`
--
ALTER TABLE `administrateur`
  ADD CONSTRAINT `FK_32EB52E8BF396750` FOREIGN KEY (`id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `avis`
--
ALTER TABLE `avis`
  ADD CONSTRAINT `FK_8F91ABF06B899279` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`),
  ADD CONSTRAINT `FK_8F91ABF08A49CC82` FOREIGN KEY (`professionnel_id`) REFERENCES `professionnel_de_sante` (`id`);

--
-- Contraintes pour la table `consultation`
--
ALTER TABLE `consultation`
  ADD CONSTRAINT `FK_964685A691EF7EAA` FOREIGN KEY (`rendez_vous_id`) REFERENCES `rendez_vous` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `consultation_partage_professionnels`
--
ALTER TABLE `consultation_partage_professionnels`
  ADD CONSTRAINT `FK_F6E7941262FF6CDF` FOREIGN KEY (`consultation_id`) REFERENCES `consultation` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_F6E79412C0EC2381` FOREIGN KEY (`professionnel_de_sante_id`) REFERENCES `professionnel_de_sante` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `gestion_agenda`
--
ALTER TABLE `gestion_agenda`
  ADD CONSTRAINT `FK_15A364DA8A49CC82` FOREIGN KEY (`professionnel_id`) REFERENCES `professionnel_de_sante` (`id`);

--
-- Contraintes pour la table `horaire_travail`
--
ALTER TABLE `horaire_travail`
  ADD CONSTRAINT `FK_A74025A88A49CC82` FOREIGN KEY (`professionnel_id`) REFERENCES `professionnel_de_sante` (`id`);

--
-- Contraintes pour la table `patient`
--
ALTER TABLE `patient`
  ADD CONSTRAINT `FK_1ADAD7EBBF396750` FOREIGN KEY (`id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `patient_partage_carnet`
--
ALTER TABLE `patient_partage_carnet`
  ADD CONSTRAINT `FK_793475596B899279` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_79347559C0EC2381` FOREIGN KEY (`professionnel_de_sante_id`) REFERENCES `professionnel_de_sante` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `professionnel_de_sante`
--
ALTER TABLE `professionnel_de_sante`
  ADD CONSTRAINT `FK_D61F97A2195E0F0` FOREIGN KEY (`specialite_id`) REFERENCES `specialite` (`id`),
  ADD CONSTRAINT `FK_D61F97ABF396750` FOREIGN KEY (`id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  ADD CONSTRAINT `FK_65E8AA0A6B899279` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_65E8AA0A8A49CC82` FOREIGN KEY (`professionnel_id`) REFERENCES `professionnel_de_sante` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reset_password_request`
--
ALTER TABLE `reset_password_request`
  ADD CONSTRAINT `FK_7CE748AA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
