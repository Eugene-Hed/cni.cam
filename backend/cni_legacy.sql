-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : lun. 24 mars 2025 à 23:34
-- Version du serveur : 8.0.41-0ubuntu0.24.04.1
-- Version de PHP : 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `cni`
--

-- --------------------------------------------------------

--
-- Structure de la table `cartesidentite`
--

CREATE TABLE `cartesidentite` (
  `CarteID` int NOT NULL,
  `UtilisateurID` int NOT NULL,
  `DemandeID` int DEFAULT NULL,
  `NumeroCarteIdentite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `DateEmission` date DEFAULT NULL,
  `DateExpiration` date DEFAULT NULL,
  `CodeQR` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `CheminFichier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Statut` enum('Active','Expiree','Perdue','Annulee') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cartesidentite`
--

INSERT INTO `cartesidentite` (`CarteID`, `UtilisateurID`, `DemandeID`, `NumeroCarteIdentite`, `DateEmission`, `DateExpiration`, `CodeQR`, `CheminFichier`, `Statut`) VALUES
(30, 16, 27, 'CNI00000001', '2025-03-19', '2035-03-19', '../uploads/qrcodes/CNI00000001_qr.png', '../uploads/cni/CNI00000001.pdf', 'Active'),
(31, 19, 30, 'CNI00000002', '2025-03-20', '2035-03-20', '../uploads/qrcodes/CNI00000002_qr.png', '../uploads/cni/CNI00000002.pdf', 'Active');

-- --------------------------------------------------------

--
-- Structure de la table `certificatsnationalite`
--

CREATE TABLE `certificatsnationalite` (
  `CertificatID` int NOT NULL,
  `DemandeID` int DEFAULT NULL,
  `NumeroCertificat` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `DateEmission` date DEFAULT NULL,
  `CheminPDF` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `SignaturePresidentielle` tinyint(1) DEFAULT '0',
  `CheminSignaturePresident` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demandes`
--

CREATE TABLE `demandes` (
  `DemandeID` int NOT NULL,
  `NumeroReference` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `UtilisateurID` int DEFAULT NULL,
  `TypeDemande` enum('CNI','CertificatNationalite','NATIONALITE') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `SousTypeDemande` enum('premiere','renouvellement','perte','naturalisation') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Statut` enum('Soumise','EnCours','Approuvee','Rejetee','Terminee','Annulee') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `DateSoumission` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DateAchevement` timestamp NULL DEFAULT NULL,
  `MontantPaiement` decimal(10,2) DEFAULT NULL,
  `StatutPaiement` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `SignatureRequise` tinyint(1) DEFAULT '0',
  `SignatureEnregistree` tinyint(1) DEFAULT '0',
  `CheminSignature` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `DateSignature` timestamp NULL DEFAULT NULL,
  `SignatureOfficierRequise` tinyint(1) DEFAULT '0',
  `SignatureOfficierEnregistree` tinyint(1) DEFAULT '0',
  `CheminSignatureOfficier` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `DateSignatureOfficier` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes`
--

INSERT INTO `demandes` (`DemandeID`, `NumeroReference`, `UtilisateurID`, `TypeDemande`, `SousTypeDemande`, `Statut`, `DateSoumission`, `DateAchevement`, `MontantPaiement`, `StatutPaiement`, `SignatureRequise`, `SignatureEnregistree`, `CheminSignature`, `DateSignature`, `SignatureOfficierRequise`, `SignatureOfficierEnregistree`, `CheminSignatureOfficier`, `DateSignatureOfficier`) VALUES
(27, 'CNI-20250316-A4518E', 16, 'CNI', 'premiere', 'Terminee', '2025-03-16 20:49:14', NULL, 10000.00, 'Complete', 1, 1, '../uploads/signatures/signature_27_1742372777.png', '2025-03-19 08:26:18', 0, 1, '../uploads/signatures_officier/signature_officier_27_1742374147.png', '2025-03-19 08:49:07'),
(30, 'CNI-20250320-7DA7AF', 19, 'CNI', 'premiere', 'Terminee', '2025-03-20 15:41:27', NULL, 10000.00, 'Complete', 1, 1, '../uploads/signatures/signature_30_1742485493.png', '2025-03-20 15:44:53', 0, 1, '../uploads/signatures_officier/signature_officier_30_1742485526.png', '2025-03-20 15:45:26'),
(33, 'NAT-20250324-4D25E4', 17, 'NATIONALITE', NULL, 'Soumise', '2025-03-24 23:31:32', NULL, 5000.00, 'En attente', 0, 0, NULL, NULL, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demande_cni_details`
--

CREATE TABLE `demande_cni_details` (
  `DetailID` int NOT NULL,
  `DemandeID` int NOT NULL,
  `TypeDemande` enum('premiere','renouvellement','perte','naturalisation') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Prenom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `DateNaissance` date NOT NULL,
  `LieuNaissance` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Adresse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Sexe` enum('M','F') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Taille` int NOT NULL,
  `Profession` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `StatutCivil` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `NumeroCNIPrecedente` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `DatePerteVol` date DEFAULT NULL,
  `NumeroDecretNaturalisation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `NationalitePere` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `NationaliteMere` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demande_cni_details`
--

INSERT INTO `demande_cni_details` (`DetailID`, `DemandeID`, `TypeDemande`, `Nom`, `Prenom`, `DateNaissance`, `LieuNaissance`, `Adresse`, `Sexe`, `Taille`, `Profession`, `StatutCivil`, `NumeroCNIPrecedente`, `DatePerteVol`, `NumeroDecretNaturalisation`, `NationalitePere`, `NationaliteMere`) VALUES
(16, 27, 'premiere', 'MEKA KENGNE', 'Pascale Ariel', '2006-04-16', 'Bafoussam, Noun, Ouest', 'mfou', 'F', 160, 'Etudiante', 'Celibataire', '', NULL, NULL, NULL, NULL),
(19, 30, 'premiere', 'sanfo', 'longin', '2000-03-14', 'BAMEKA, Hauts-Plateaux, Ouest', 'Mimboman Chapelle II', 'M', 180, 'Etudiant', 'Celibataire', '', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `demande_nationalite_details`
--

CREATE TABLE `demande_nationalite_details` (
  `DetailID` int NOT NULL,
  `DemandeID` int NOT NULL,
  `Nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Prenom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `DateNaissance` date NOT NULL,
  `LieuNaissance` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Sexe` enum('M','F') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `NomPere` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `NomMere` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Adresse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Ville` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `CodePostal` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `EtatCivil` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Profession` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `NationaliteActuelle` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Motif` enum('naissance','mariage','naturalisation','filiation') COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demande_nationalite_details`
--

INSERT INTO `demande_nationalite_details` (`DetailID`, `DemandeID`, `Nom`, `Prenom`, `DateNaissance`, `LieuNaissance`, `Sexe`, `NomPere`, `NomMere`, `Adresse`, `Ville`, `CodePostal`, `Telephone`, `EtatCivil`, `Profession`, `NationaliteActuelle`, `Motif`) VALUES
(8, 33, 'Mbog', 'Alain', '2004-07-10', 'Mokolo', 'M', 'SIMO JEAN', 'FOTSING JULIENNE', 'Mimboman', 'Yaoundé', '237', '+237690163494', 'Celibataire', 'Étudiante', 'Camerounaise', 'naturalisation');

-- --------------------------------------------------------

--
-- Structure de la table `departements`
--

CREATE TABLE `departements` (
  `DepartementID` int NOT NULL,
  `RegionID` int NOT NULL,
  `NomDepartement` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `departements`
--

INSERT INTO `departements` (`DepartementID`, `RegionID`, `NomDepartement`) VALUES
(1, 1, 'Djérem'),
(2, 1, 'Faro-et-Déo'),
(3, 1, 'Mayo-Banyo'),
(4, 1, 'Mbéré'),
(5, 1, 'Vina'),
(6, 2, 'Haute-Sanaga'),
(7, 2, 'Lekié'),
(8, 2, 'Mbam-et-Inoubou'),
(9, 2, 'Mbam-et-Kim'),
(10, 2, 'Méfou-et-Afamba'),
(11, 2, 'Méfou-et-Akono'),
(12, 2, 'Mfoundi'),
(13, 3, 'Boumba-et-Ngoko'),
(14, 3, 'Haut-Nyong'),
(15, 3, 'Kadey'),
(16, 3, 'Lom-et-Djérem'),
(17, 4, 'Diamaré'),
(18, 4, 'Logone-et-Chari'),
(19, 4, 'Mayo-Danay'),
(20, 4, 'Mayo-Kani'),
(21, 4, 'Mayo-Sava'),
(22, 4, 'Mayo-Tsanaga'),
(23, 5, 'Moungo'),
(24, 5, 'Nkam'),
(25, 5, 'Sanaga-Maritime'),
(26, 5, 'Wouri'),
(27, 6, 'Bénoué'),
(28, 6, 'Faro'),
(29, 6, 'Mayo-Louti'),
(30, 6, 'Mayo-Rey'),
(31, 7, 'Boyo'),
(32, 7, 'Bui'),
(33, 7, 'Donga-Mantung'),
(34, 7, 'Menchum'),
(35, 7, 'Mezam'),
(36, 7, 'Momo'),
(37, 7, 'Ngoketunjia'),
(38, 8, 'Bamboutos'),
(39, 8, 'Haut-Nkam'),
(40, 8, 'Hauts-Plateaux'),
(41, 8, 'Koung-Khi'),
(42, 8, 'Menoua'),
(43, 8, 'Mifi'),
(44, 8, 'Ndé'),
(45, 8, 'Noun'),
(46, 9, 'Dja-et-Lobo'),
(47, 9, 'Mvila'),
(48, 9, 'Océan'),
(49, 9, 'Vallée-du-Ntem'),
(50, 10, 'Fako'),
(51, 10, 'Koupé-Manengouba'),
(52, 10, 'Lebialem'),
(53, 10, 'Manyu'),
(54, 10, 'Meme'),
(55, 10, 'Ndian'),
(56, 2, 'Nyong-et-Kéllé'),
(57, 2, 'Nyong-et-Mfoumou'),
(58, 2, 'Nyong-et-So\'o');

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

CREATE TABLE `documents` (
  `DocumentID` int NOT NULL,
  `DemandeID` int DEFAULT NULL,
  `TypeDocument` enum('Photo','PhotoIdentite','ActeNaissance','CertificatNationalite','AncienneCNI','ActeMariage','JustificatifProfession','DecretNaturalisation','CasierJudiciaire','DeclarationPerte','Signature') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `CheminFichier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `DateTelechargement` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `StatutValidation` enum('EnAttente','Approuve','Rejete') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'EnAttente',
  `Utilisateurid` int NOT NULL,
  `DateValidation` timestamp NULL DEFAULT NULL,
  `ValidePar` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`DocumentID`, `DemandeID`, `TypeDocument`, `CheminFichier`, `DateTelechargement`, `StatutValidation`, `Utilisateurid`, `DateValidation`, `ValidePar`) VALUES
(45, 27, 'PhotoIdentite', '../uploads/documents/67d7394a4ead0_photo_16.jpg', '2025-03-16 22:31:47', 'Approuve', 2, NULL, NULL),
(46, 27, 'ActeNaissance', '../uploads/documents/67d7394a4ee67_acte.pdf', '2025-03-16 20:49:14', 'Approuve', 16, '2025-03-17 06:05:48', 2),
(47, 27, 'CertificatNationalite', '../uploads/documents/67d7394a4fbfe_bacc.pdf', '2025-03-16 20:49:14', 'Approuve', 16, '2025-03-17 06:05:43', 2),
(48, 27, 'JustificatifProfession', '../uploads/documents/67d7394a5087c_releverDeNote.pdf', '2025-03-16 20:49:14', 'Approuve', 16, '2025-03-17 06:05:53', 2),
(52, 27, 'Signature', '../uploads/signatures/signature_27_1742372777.png', '2025-03-19 08:26:17', 'Approuve', 16, NULL, NULL),
(57, 30, 'PhotoIdentite', '../uploads/documents/67dc3727ef863_photo_19.jpg', '2025-03-20 15:41:28', 'Approuve', 19, '2025-03-20 15:43:56', 2),
(58, 30, 'ActeNaissance', '../uploads/documents/67dc372836514_CNI00000002.pdf', '2025-03-20 15:41:28', 'Approuve', 19, '2025-03-20 15:44:01', 2),
(59, 30, 'CertificatNationalite', '../uploads/documents/67dc372837140_CahierDeChargeAgenceDeVoyage(TAMBO SIMO HEDRIC).pdf', '2025-03-20 15:41:28', 'Approuve', 19, '2025-03-20 15:44:04', 2),
(60, 30, 'JustificatifProfession', '../uploads/documents/67dc37283f07a_Rapport1.1.pdf', '2025-03-20 15:41:28', 'Approuve', 19, '2025-03-20 15:44:08', 2),
(61, 30, 'Signature', '../uploads/signatures/signature_30_1742485493.png', '2025-03-20 15:44:53', 'Approuve', 19, NULL, NULL),
(68, 33, 'ActeNaissance', '../uploads/documents_nationalite/67e1eb54da9d6_CahierDeChargeAgenceDeVoyage(TAMBO SIMO HEDRIC).pdf', '2025-03-24 23:31:32', 'EnAttente', 17, NULL, NULL),
(69, 33, 'CertificatNationalite', '../uploads/documents_nationalite/67e1eb54db147_CNI00000002.pdf', '2025-03-24 23:31:32', 'EnAttente', 17, NULL, NULL),
(70, 33, 'CasierJudiciaire', '../uploads/documents_nationalite/67e1eb54dbd2b_sortie.pdf', '2025-03-24 23:31:32', 'EnAttente', 17, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `ethnies`
--

CREATE TABLE `ethnies` (
  `EthnieID` int NOT NULL,
  `NomEthnie` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `Description` text COLLATE utf8mb4_general_ci,
  `RegionPrincipale` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ethnies`
--

INSERT INTO `ethnies` (`EthnieID`, `NomEthnie`, `Description`, `RegionPrincipale`) VALUES
(1, 'Bassa', 'Le peuple Bassa est originaire de la région du Littoral. Ils sont principalement concentrés autour de Douala et la région côtière.', 5),
(2, 'Bamiléké', 'Les Bamilékés sont une ethnie du Cameroun située dans la région de l’Ouest, dans les montagnes. Ils sont célèbres pour leur organisation sociale et leurs structures claniques.', 8),
(3, 'Beti', 'Le peuple Beti est originaire du Centre du Cameroun, en particulier autour de Yaoundé. Ils sont connus pour leur culture riche et leur histoire liée à la forêt équatoriale.', 2),
(4, 'Douala', 'Le peuple Douala est un groupe ethnique du Littoral, particulièrement autour de la ville de Douala. Ils sont connus pour leur rôle important dans l’histoire commerciale du pays.', 5),
(5, 'Fang', 'Le peuple Fang vit principalement dans la région du Sud et le Sud-Est. Ils sont un des groupes ethniques les plus connus dans la région forestière camerounaise.', 9),
(6, 'Moungo', 'Les Moungos sont originaires de la région du Littoral, une ethnie qui vit principalement dans les zones rurales de cette région.', 5),
(7, 'Maka', 'Les Maka sont une ethnie du Centre, qui se trouve principalement dans la région montagneuse et les forêts tropicales.', 2),
(8, 'Mouko', 'Les Moukos vivent dans la région de l’Ouest, ils sont traditionnellement agriculteurs et connus pour leur culture vivrière.', 8),
(9, 'Pygmées', 'Les Pygmées sont un groupe ethnique vivant principalement dans les forêts équatoriales du Cameroun. Ils sont célèbres pour leur mode de vie nomade et leurs compétences en chasse et cueillette.', 3),
(10, 'Tikar', 'Les Tikars sont un peuple de la région du Centre et de l’Ouest, réputés pour leur langue et leur art traditionnel très influent dans cette zone.', 8),
(11, 'Beti-Ewondo', 'Le peuple Ewondo, souvent associé au groupe Beti, se trouve principalement dans le Centre et la région de Yaoundé, avec une grande influence culturelle sur la région.', 2),
(12, 'Mashi', 'Le peuple Mashi est situé dans le département de la Menchum, région du Nord-Ouest, connu pour son organisation sociale complexe et ses coutumes uniques.', 7),
(13, 'Ngumba', 'Les Ngumba sont un groupe ethnique du Sud-Ouest du Cameroun, principalement situé autour de la région de Limbé et les montagnes environnantes.', 10),
(14, 'Sawa', 'Les Sawa sont un groupe ethnique principalement situé dans les régions côtières du Littoral et du Sud-Ouest, connus pour leur culture maritime et commerciale.', 5),
(15, 'Fulbé', 'Le peuple Fulbé ou Peulh est présent dans plusieurs régions du Cameroun, notamment dans le Nord, où ils sont réputés pour leur élevage de bétail et leur culture nomade.', 6),
(16, 'Bakossi', 'Les Bakossi sont originaires du département de la Manyu dans la région du Sud-Ouest, avec une grande réputation de cultivateurs et de bâtisseurs traditionnels.', 10);

-- --------------------------------------------------------

--
-- Structure de la table `historique_demandes`
--

CREATE TABLE `historique_demandes` (
  `HistoriqueID` int NOT NULL,
  `DemandeID` int NOT NULL,
  `AncienStatut` enum('Soumise','EnCours','Approuvee','Rejetee','Terminee') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `NouveauStatut` enum('Soumise','EnCours','Approuvee','Rejetee','Terminee','Annulee') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `DateModification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Commentaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `ModifiePar` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `historique_demandes`
--

INSERT INTO `historique_demandes` (`HistoriqueID`, `DemandeID`, `AncienStatut`, `NouveauStatut`, `DateModification`, `Commentaire`, `ModifiePar`) VALUES
(62, 27, 'Soumise', 'EnCours', '2025-03-17 05:59:39', 'Début du traitement de la demande', 2),
(65, 27, 'EnCours', 'Approuvee', '2025-03-19 07:34:45', '', 2),
(67, 27, 'EnCours', 'Approuvee', '2025-03-19 08:02:22', '', 2),
(69, 27, 'EnCours', 'Approuvee', '2025-03-19 08:26:00', '', 2),
(70, 27, 'Approuvee', 'Approuvee', '2025-03-19 08:26:18', 'Signature enregistrée par le citoyen', 16),
(73, 27, 'Approuvee', 'Approuvee', '2025-03-19 08:49:07', 'Signature de l\'officier enregistrée', 2),
(80, 27, 'Approuvee', 'Terminee', '2025-03-19 10:45:56', 'CNI générée avec succès', 2),
(81, 30, 'Soumise', 'EnCours', '2025-03-20 15:42:00', 'Paiement effectué', 19),
(82, 30, 'EnCours', 'Approuvee', '2025-03-20 15:44:20', 'valide', 2),
(83, 30, 'Approuvee', 'Approuvee', '2025-03-20 15:44:53', 'Signature enregistrée par le citoyen', 19),
(84, 30, 'Approuvee', 'Approuvee', '2025-03-20 15:45:26', 'Signature de l\'officier enregistrée', 2),
(85, 30, 'Approuvee', 'Terminee', '2025-03-20 15:45:43', 'CNI générée avec succès', 2),
(88, 33, NULL, 'Soumise', '2025-03-24 23:31:32', 'Demande de certificat de nationalité soumise', 17);

-- --------------------------------------------------------

--
-- Structure de la table `journalactivites`
--

CREATE TABLE `journalactivites` (
  `JournalID` int NOT NULL,
  `UtilisateurID` int DEFAULT NULL,
  `TypeActivite` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `AdresseIP` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `DateHeure` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `journalactivites`
--

INSERT INTO `journalactivites` (`JournalID`, `UtilisateurID`, `TypeActivite`, `Description`, `AdresseIP`, `DateHeure`) VALUES
(45, 2, 'Validation_Document', 'Document #47 approuvé', '127.0.0.1', '2025-03-17 06:05:43'),
(46, 2, 'Validation_Document', 'Document #46 approuvé', '127.0.0.1', '2025-03-17 06:05:48'),
(47, 2, 'Validation_Document', 'Document #48 approuvé', '127.0.0.1', '2025-03-17 06:05:53'),
(51, 2, 'Traitement_Demande', 'Demande #27 approuvée', '127.0.0.1', '2025-03-19 08:26:00'),
(58, 2, 'Generation_CNI', 'Génération de la CNI numéro CNI00000001', '127.0.0.1', '2025-03-19 10:45:56'),
(59, 2, 'Validation_Document', 'Document #57 approuvé', '127.0.0.1', '2025-03-20 15:43:56'),
(60, 2, 'Validation_Document', 'Document #58 approuvé', '127.0.0.1', '2025-03-20 15:44:01'),
(61, 2, 'Validation_Document', 'Document #59 approuvé', '127.0.0.1', '2025-03-20 15:44:04'),
(62, 2, 'Validation_Document', 'Document #60 approuvé', '127.0.0.1', '2025-03-20 15:44:08'),
(63, 2, 'Traitement_Demande', 'Demande #30 approuvée', '127.0.0.1', '2025-03-20 15:44:20'),
(64, 2, 'Generation_CNI', 'Génération de la CNI numéro CNI00000002', '127.0.0.1', '2025-03-20 15:45:43'),
(65, 4, 'Modification_Utilisateur', 'Modification de l\'utilisateur ID: 6', '127.0.0.1', '2025-03-24 19:51:51');

-- --------------------------------------------------------

--
-- Structure de la table `lieuxretrait`
--

CREATE TABLE `lieuxretrait` (
  `LieuID` int NOT NULL,
  `NomLieu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Adresse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `NumeroContact` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `NotificationID` int NOT NULL,
  `UtilisateurID` int DEFAULT NULL,
  `DemandeID` int DEFAULT NULL,
  `Contenu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `TypeNotification` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `EstLue` tinyint(1) DEFAULT '0',
  `DateCreation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notifications`
--

INSERT INTO `notifications` (`NotificationID`, `UtilisateurID`, `DemandeID`, `Contenu`, `TypeNotification`, `EstLue`, `DateCreation`) VALUES
(29, 2, NULL, 'Votre demande #10 a été approuvée', 'demande_approuvee', 0, '2025-02-15 16:33:43'),
(57, 16, 27, 'Votre paiement de 10000 FCFA pour la demande #27 a été reçu. Votre demande est en cours de traitement.', 'paiement_recu', 0, '2025-03-16 20:50:57'),
(58, 16, 27, 'Votre demande de CNI a été approuvée. Vous serez notifié lorsqu\'elle sera prête pour le retrait.', 'demande_approuvee', 0, '2025-03-16 22:52:15'),
(61, 16, 27, 'Votre demande de CNI a été approuvée. Veuillez enregistrer votre signature pour finaliser le processus.', 'demande_approuvee', 0, '2025-03-19 07:34:45'),
(62, 2, 27, 'La signature pour la demande #27 a été enregistrée. Vous pouvez maintenant générer la CNI.', 'signature_enregistree', 0, '2025-03-19 07:34:59'),
(63, 16, 27, 'Votre demande de CNI a été approuvée. Veuillez enregistrer votre signature pour finaliser le processus.', 'demande_approuvee', 0, '2025-03-19 08:02:22'),
(64, 2, 27, 'La signature pour la demande #27 a été enregistrée. Vous pouvez maintenant générer la CNI.', 'signature_enregistree', 0, '2025-03-19 08:02:36'),
(65, 16, 27, 'Votre demande de CNI a été approuvée. Veuillez enregistrer votre signature pour finaliser le processus.', 'demande_approuvee', 0, '2025-03-19 08:26:00'),
(66, 2, 27, 'La signature pour la demande #27 a été enregistrée. Vous pouvez maintenant générer la CNI.', 'signature_enregistree', 0, '2025-03-19 08:26:18'),
(68, 16, 27, 'Votre CNI a été générée avec succès.', 'Generation_CNI', 0, '2025-03-19 09:20:47'),
(69, 16, 27, 'Votre CNI a été générée avec succès.', 'Generation_CNI', 0, '2025-03-19 09:35:04'),
(70, 16, 27, 'Votre CNI a été générée avec succès.', 'Generation_CNI', 0, '2025-03-19 09:50:20'),
(71, 16, 27, 'Votre CNI a été générée avec succès.', 'Generation_CNI', 0, '2025-03-19 10:34:15'),
(72, 16, 27, 'Votre CNI a été générée avec succès.', 'Generation_CNI', 0, '2025-03-19 10:36:09'),
(73, 16, 27, 'Votre CNI a été générée avec succès.', 'Generation_CNI', 0, '2025-03-19 10:45:56'),
(74, 19, 30, 'Votre paiement de 10000 FCFA pour la demande #30 a été reçu. Votre demande est en cours de traitement.', 'paiement_recu', 0, '2025-03-20 15:42:00'),
(75, 19, 30, 'Votre demande de CNI a été approuvée. Veuillez enregistrer votre signature pour finaliser le processus.', 'demande_approuvee', 0, '2025-03-20 15:44:20'),
(76, 2, 30, 'La signature pour la demande #30 a été enregistrée. Vous pouvez maintenant générer la CNI.', 'signature_enregistree', 0, '2025-03-20 15:44:53'),
(77, 19, 30, 'Votre CNI a été générée avec succès.', 'Generation_CNI', 0, '2025-03-20 15:45:43'),
(81, 17, 33, 'Votre demande de certificat de nationalité a été soumise avec succès.', 'demande', 0, '2025-03-24 23:31:32'),
(82, 2, 33, 'Nouvelle demande de certificat de nationalité à traiter.', 'nouvelle_demande', 0, '2025-03-24 23:31:32');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `PaiementID` int NOT NULL,
  `DemandeID` int DEFAULT NULL,
  `Montant` decimal(10,2) NOT NULL,
  `DatePaiement` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `StatutPaiement` enum('EnAttente','Complete','Echoue') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `ReferenceTransaction` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`PaiementID`, `DemandeID`, `Montant`, `DatePaiement`, `StatutPaiement`, `ReferenceTransaction`) VALUES
(1, 27, 10000.00, '2025-03-16 20:50:56', 'Complete', 'TRX-20250316205056-0D6EFC'),
(2, 30, 10000.00, '2025-03-20 15:42:00', 'Complete', 'TRX-20250320154159-7E2C8C');

-- --------------------------------------------------------

--
-- Structure de la table `reclamations`
--

CREATE TABLE `reclamations` (
  `ReclamationID` int NOT NULL,
  `UtilisateurID` int DEFAULT NULL,
  `DemandeID` int DEFAULT NULL,
  `TypeReclamation` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `Statut` enum('Ouverte','EnCours','Fermee') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Ouverte',
  `DateCreation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DateMiseAJour` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `regions`
--

CREATE TABLE `regions` (
  `RegionID` int NOT NULL,
  `NomRegion` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `regions`
--

INSERT INTO `regions` (`RegionID`, `NomRegion`) VALUES
(1, 'Adamaoua'),
(2, 'Centre'),
(3, 'Est'),
(4, 'Extrême-Nord'),
(5, 'Littoral'),
(6, 'Nord'),
(7, 'Nord-Ouest'),
(8, 'Ouest'),
(9, 'Sud'),
(10, 'Sud-Ouest');

-- --------------------------------------------------------

--
-- Structure de la table `rendezvous`
--

CREATE TABLE `rendezvous` (
  `RendezVousID` int NOT NULL,
  `DemandeID` int DEFAULT NULL,
  `DateRendezVous` datetime DEFAULT NULL,
  `Lieu` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Statut` enum('Planifie','Termine','Annule') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Planifie'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `role`
--

CREATE TABLE `role` (
  `id` int NOT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `role`
--

INSERT INTO `role` (`id`, `role`) VALUES
(1, 'Administrateur'),
(2, 'Citoyen'),
(3, 'Officier'),
(4, 'President');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `UtilisateurID` int NOT NULL,
  `Codeutilisateur` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `NumeroTelephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `Prenom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Nom` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `DateNaissance` date DEFAULT NULL,
  `Adresse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `RoleId` tinyint NOT NULL,
  `DateCreation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `DateMiseAJour` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `PhotoUtilisateur` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `Genre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `IsActive` tinyint DEFAULT NULL,
  `RegionNaissanceID` int DEFAULT NULL,
  `DepartementNaissanceID` int DEFAULT NULL,
  `VilleNaissanceID` int DEFAULT NULL,
  `RegionResidenceID` int DEFAULT NULL,
  `DepartementResidenceID` int DEFAULT NULL,
  `VilleResidenceID` int DEFAULT NULL,
  `EthnieID` int DEFAULT NULL,
  `Profession` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `CodeOTP` varchar(6) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ExpirationOTP` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`UtilisateurID`, `Codeutilisateur`, `Email`, `NumeroTelephone`, `Prenom`, `Nom`, `DateNaissance`, `Adresse`, `RoleId`, `DateCreation`, `DateMiseAJour`, `PhotoUtilisateur`, `Genre`, `IsActive`, `RegionNaissanceID`, `DepartementNaissanceID`, `VilleNaissanceID`, `RegionResidenceID`, `DepartementResidenceID`, `VilleResidenceID`, `EthnieID`, `Profession`, `CodeOTP`, `ExpirationOTP`) VALUES
(2, 'Admin', 'simohedric2024@gmail.com', '+237656774289', 'Hedric', 'Simo', '2024-09-12', 'mimboman-chapelle', 3, '2024-09-12 06:12:08', '2025-03-20 15:42:31', '../uploads/profile_pictures/profile_67c19c445b2cf.jpg', 'Homme', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'CEadmin', 'admin@gmail.com', '+237656774278', 'Hedric', 'Simo', '2002-06-04', 'Yaoundé', 1, '2024-09-13 04:07:24', '2025-03-24 19:50:21', NULL, 'Homme', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 'AR237', 'Ariel2006@gmail.com', '+237656774266', 'Pascal', 'Meka', '2006-04-16', 'Nfou', 4, '2024-09-13 13:46:49', '2025-03-24 23:32:39', '../uploads/profile_pictures/profile_67c1a099d2f3d.jpg', 'Femme', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'CNI20256562', 'simehedric2000@gmail.com', '+237656774288', 'Pascale Ariel', 'MEKA KENGNE', '2006-04-16', 'mfou', 2, '2025-03-16 16:45:20', '2025-03-20 15:35:53', NULL, 'F', 1, 8, 45, 118, 2, 10, 21, 2, 'Etudiante', NULL, NULL),
(17, 'CNI20256788', 'alainmboc09@gmail.com', '+237690163494', 'Alain', 'Mbog', '2004-07-10', 'Mimboman', 2, '2025-03-18 11:01:15', '2025-03-24 23:16:50', NULL, 'M', 1, 5, 25, 63, 2, 12, 26, 1, 'Étudiante', NULL, NULL),
(18, 'CNI20255783', 'patrickokemgne@gmail.com', '+237690376580', 'Patrick ', 'Kemgne', '2005-03-04', 'Mimboman', 2, '2025-03-18 11:03:14', '2025-03-18 11:05:23', NULL, 'M', 1, 2, 12, 26, 2, 12, 26, 2, 'Etudiant', NULL, NULL),
(19, 'CNI20252720', 'sanfo2025@gmail.com', '+237656774280', 'longin', 'sanfo', '2000-03-14', 'Mimboman Chapelle II', 2, '2025-03-20 15:38:35', '2025-03-20 15:38:58', NULL, 'M', 1, 8, 40, 157, 2, 12, 26, 2, 'Etudiant', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `villes`
--

CREATE TABLE `villes` (
  `VilleID` int NOT NULL,
  `DepartementID` int NOT NULL,
  `NomVille` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `villes`
--

INSERT INTO `villes` (`VilleID`, `DepartementID`, `NomVille`) VALUES
(1, 1, 'Tibati'),
(2, 1, 'Ngaoundal'),
(3, 2, 'Bankim'),
(4, 2, 'Banyo'),
(5, 3, 'Meiganga'),
(6, 3, 'Dir'),
(7, 4, 'Ngaoundéré'),
(8, 4, 'Nganha'),
(9, 5, 'Tignère'),
(10, 5, 'Galim-Tignère'),
(11, 6, 'Nanga Eboko'),
(12, 6, 'Minta'),
(13, 7, 'Monatélé'),
(14, 7, 'Obala'),
(15, 7, 'Ebebda'),
(16, 8, 'Bafia'),
(17, 8, 'Ombessa'),
(18, 8, 'Ndikiniméki'),
(19, 9, 'Ntui'),
(20, 9, 'Yoko'),
(21, 10, 'Mfou'),
(22, 10, 'Afanloum'),
(23, 10, 'Esse'),
(24, 11, 'Akono'),
(25, 11, 'Ngoumou'),
(26, 12, 'Yaoundé'),
(27, 12, 'Nkolafamba'),
(28, 13, 'Eséka'),
(29, 13, 'Makak'),
(30, 13, 'Matomb'),
(31, 14, 'Akonolinga'),
(32, 14, 'Endom'),
(33, 15, 'Mbalmayo'),
(34, 15, 'Ngomedzap'),
(35, 15, 'Meyomessala'),
(36, 16, 'Yokadouma'),
(37, 16, 'Moloundou'),
(38, 16, 'Salapoumbé'),
(39, 17, 'Abong-Mbang'),
(40, 17, 'Doumé'),
(41, 17, 'Messamena'),
(42, 18, 'Batouri'),
(43, 18, 'Kentzou'),
(44, 18, 'Ndelele'),
(45, 19, 'Bertoua'),
(46, 19, 'Garoua-Boulaï'),
(47, 19, 'Bélabo'),
(48, 20, 'Maroua'),
(49, 20, 'Bogo'),
(50, 20, 'Dargala'),
(51, 21, 'Kousséri'),
(52, 21, 'Blangoua'),
(53, 21, 'Makary'),
(54, 22, 'Yagoua'),
(55, 22, 'Gobo'),
(56, 22, 'Gueme'),
(57, 23, 'Kaélé'),
(58, 23, 'Guidiguis'),
(59, 23, 'Moutourwa'),
(60, 24, 'Mora'),
(61, 24, 'Tokombéré'),
(62, 24, 'Kolofata'),
(63, 25, 'Mokolo'),
(64, 25, 'Bourrha'),
(65, 25, 'Mayo-Moskota'),
(66, 26, 'Nkongsamba'),
(67, 26, 'Loum'),
(68, 26, 'Melong'),
(69, 27, 'Yabassi'),
(70, 27, 'Nkondjock'),
(71, 27, 'Dizangué'),
(72, 28, 'Édéa'),
(73, 28, 'Pouma'),
(74, 29, 'Douala'),
(75, 29, 'Manjo'),
(76, 29, 'Dibombari'),
(77, 30, 'Garoua'),
(78, 30, 'Pitoa'),
(79, 30, 'Demsa'),
(80, 31, 'Poli'),
(81, 31, 'Tcholliré'),
(82, 32, 'Guider'),
(83, 32, 'Figuil'),
(84, 33, 'Tcholliré'),
(85, 33, 'Touboro'),
(86, 34, 'Fundong'),
(87, 34, 'Belo'),
(88, 34, 'Njinikom'),
(89, 35, 'Kumbo'),
(90, 35, 'Nkor'),
(91, 35, 'Tatum'),
(92, 36, 'Nkambe'),
(93, 36, 'Misaje'),
(94, 36, 'Ako'),
(95, 37, 'Wum'),
(96, 37, 'Furu-Awa'),
(97, 38, 'Bamenda'),
(98, 38, 'Bali'),
(99, 38, 'Chomba'),
(100, 39, 'Mbengwi'),
(101, 39, 'Batibo'),
(102, 39, 'Njikwa'),
(103, 40, 'Ndop'),
(104, 40, 'Babessi'),
(105, 40, 'Bamessing'),
(106, 41, 'Mbouda'),
(107, 41, 'Galim'),
(108, 41, 'Bamesso'),
(109, 42, 'Bafang'),
(110, 42, 'Kékem'),
(111, 42, 'Bana'),
(112, 43, 'Baham'),
(113, 43, 'Batié'),
(114, 43, 'Bandjoun'),
(115, 44, 'Dschang'),
(116, 44, 'Fongo-Tongo'),
(117, 44, 'Fotetsa'),
(118, 45, 'Bafoussam'),
(119, 45, 'Bamendjou'),
(120, 45, 'Bansoa'),
(121, 46, 'Bangangté'),
(122, 46, 'Tonga'),
(123, 46, 'Bazou'),
(124, 47, 'Foumban'),
(125, 47, 'Koutaba'),
(126, 47, 'Foumbot'),
(127, 48, 'Sangmélima'),
(128, 48, 'Meyomessala'),
(129, 48, 'Zoétélé'),
(130, 49, 'Ebolowa'),
(131, 49, 'Mvangane'),
(132, 49, 'Akom II'),
(133, 50, 'Kribi'),
(134, 50, 'Campo'),
(135, 50, 'Bipindi'),
(136, 51, 'Ambam'),
(137, 51, 'Olamze'),
(138, 51, 'Ma\'an'),
(139, 52, 'Limbe'),
(140, 52, 'Buea'),
(141, 52, 'Tiko'),
(142, 53, 'Bangem'),
(143, 53, 'Nguti'),
(144, 53, 'Tombel'),
(145, 54, 'Menji'),
(146, 54, 'Alou'),
(147, 54, 'Wabane'),
(148, 55, 'Mamfe'),
(149, 55, 'Eyumojock'),
(150, 55, 'Akwaya'),
(151, 56, 'Kumba'),
(152, 56, 'Mbonge'),
(153, 56, 'Konye'),
(154, 57, 'Mundemba'),
(155, 57, 'Isanguele'),
(156, 57, 'Bamuso'),
(157, 40, 'BAMEKA'),
(158, 40, 'BAFOUSSAM');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `cartesidentite`
--
ALTER TABLE `cartesidentite`
  ADD PRIMARY KEY (`CarteID`),
  ADD UNIQUE KEY `NumeroCarteIdentite` (`NumeroCarteIdentite`),
  ADD KEY `DemandeID` (`DemandeID`),
  ADD KEY `fk_cartesidentite_utilisateur` (`UtilisateurID`);

--
-- Index pour la table `certificatsnationalite`
--
ALTER TABLE `certificatsnationalite`
  ADD PRIMARY KEY (`CertificatID`),
  ADD UNIQUE KEY `NumeroCertificat` (`NumeroCertificat`),
  ADD KEY `DemandeID` (`DemandeID`);

--
-- Index pour la table `demandes`
--
ALTER TABLE `demandes`
  ADD PRIMARY KEY (`DemandeID`),
  ADD KEY `UtilisateurID` (`UtilisateurID`),
  ADD KEY `idx_demandes_user_type` (`UtilisateurID`,`TypeDemande`,`Statut`);

--
-- Index pour la table `demande_cni_details`
--
ALTER TABLE `demande_cni_details`
  ADD PRIMARY KEY (`DetailID`),
  ADD KEY `DemandeID` (`DemandeID`);

--
-- Index pour la table `demande_nationalite_details`
--
ALTER TABLE `demande_nationalite_details`
  ADD PRIMARY KEY (`DetailID`),
  ADD KEY `DemandeID` (`DemandeID`);

--
-- Index pour la table `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`DepartementID`),
  ADD KEY `RegionID` (`RegionID`);

--
-- Index pour la table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`DocumentID`),
  ADD KEY `DemandeID` (`DemandeID`),
  ADD KEY `idx_documents_user_type` (`Utilisateurid`,`TypeDocument`);

--
-- Index pour la table `ethnies`
--
ALTER TABLE `ethnies`
  ADD PRIMARY KEY (`EthnieID`),
  ADD KEY `RegionPrincipale` (`RegionPrincipale`);

--
-- Index pour la table `historique_demandes`
--
ALTER TABLE `historique_demandes`
  ADD PRIMARY KEY (`HistoriqueID`),
  ADD KEY `DemandeID` (`DemandeID`),
  ADD KEY `ModifiePar` (`ModifiePar`);

--
-- Index pour la table `journalactivites`
--
ALTER TABLE `journalactivites`
  ADD PRIMARY KEY (`JournalID`),
  ADD KEY `UtilisateurID` (`UtilisateurID`);

--
-- Index pour la table `lieuxretrait`
--
ALTER TABLE `lieuxretrait`
  ADD PRIMARY KEY (`LieuID`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`NotificationID`),
  ADD KEY `UtilisateurID` (`UtilisateurID`),
  ADD KEY `idx_notifications_demande` (`DemandeID`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`PaiementID`),
  ADD KEY `DemandeID` (`DemandeID`);

--
-- Index pour la table `reclamations`
--
ALTER TABLE `reclamations`
  ADD PRIMARY KEY (`ReclamationID`),
  ADD KEY `UtilisateurID` (`UtilisateurID`),
  ADD KEY `DemandeID` (`DemandeID`);

--
-- Index pour la table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`RegionID`);

--
-- Index pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  ADD PRIMARY KEY (`RendezVousID`),
  ADD KEY `DemandeID` (`DemandeID`);

--
-- Index pour la table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`UtilisateurID`),
  ADD UNIQUE KEY `NomUtilisateur` (`Codeutilisateur`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `RegionNaissanceID` (`RegionNaissanceID`),
  ADD KEY `DepartementNaissanceID` (`DepartementNaissanceID`),
  ADD KEY `VilleNaissanceID` (`VilleNaissanceID`),
  ADD KEY `RegionResidenceID` (`RegionResidenceID`),
  ADD KEY `DepartementResidenceID` (`DepartementResidenceID`),
  ADD KEY `VilleResidenceID` (`VilleResidenceID`),
  ADD KEY `EthnieID` (`EthnieID`),
  ADD KEY `NumeroTelephone` (`NumeroTelephone`);

--
-- Index pour la table `villes`
--
ALTER TABLE `villes`
  ADD PRIMARY KEY (`VilleID`),
  ADD KEY `DepartementID` (`DepartementID`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `cartesidentite`
--
ALTER TABLE `cartesidentite`
  MODIFY `CarteID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT pour la table `certificatsnationalite`
--
ALTER TABLE `certificatsnationalite`
  MODIFY `CertificatID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `demandes`
--
ALTER TABLE `demandes`
  MODIFY `DemandeID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `demande_cni_details`
--
ALTER TABLE `demande_cni_details`
  MODIFY `DetailID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `demande_nationalite_details`
--
ALTER TABLE `demande_nationalite_details`
  MODIFY `DetailID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `departements`
--
ALTER TABLE `departements`
  MODIFY `DepartementID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT pour la table `documents`
--
ALTER TABLE `documents`
  MODIFY `DocumentID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT pour la table `ethnies`
--
ALTER TABLE `ethnies`
  MODIFY `EthnieID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `historique_demandes`
--
ALTER TABLE `historique_demandes`
  MODIFY `HistoriqueID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT pour la table `journalactivites`
--
ALTER TABLE `journalactivites`
  MODIFY `JournalID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT pour la table `lieuxretrait`
--
ALTER TABLE `lieuxretrait`
  MODIFY `LieuID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `NotificationID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `PaiementID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `reclamations`
--
ALTER TABLE `reclamations`
  MODIFY `ReclamationID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `regions`
--
ALTER TABLE `regions`
  MODIFY `RegionID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  MODIFY `RendezVousID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `role`
--
ALTER TABLE `role`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `UtilisateurID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `villes`
--
ALTER TABLE `villes`
  MODIFY `VilleID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=159;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cartesidentite`
--
ALTER TABLE `cartesidentite`
  ADD CONSTRAINT `cartesidentite_ibfk_1` FOREIGN KEY (`DemandeID`) REFERENCES `demandes` (`DemandeID`),
  ADD CONSTRAINT `fk_cartesidentite_utilisateur` FOREIGN KEY (`UtilisateurID`) REFERENCES `utilisateurs` (`UtilisateurID`);

--
-- Contraintes pour la table `certificatsnationalite`
--
ALTER TABLE `certificatsnationalite`
  ADD CONSTRAINT `certificatsnationalite_ibfk_1` FOREIGN KEY (`DemandeID`) REFERENCES `demandes` (`DemandeID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes`
--
ALTER TABLE `demandes`
  ADD CONSTRAINT `demandes_ibfk_1` FOREIGN KEY (`UtilisateurID`) REFERENCES `utilisateurs` (`UtilisateurID`);

--
-- Contraintes pour la table `demande_cni_details`
--
ALTER TABLE `demande_cni_details`
  ADD CONSTRAINT `demande_cni_details_ibfk_1` FOREIGN KEY (`DemandeID`) REFERENCES `demandes` (`DemandeID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demande_nationalite_details`
--
ALTER TABLE `demande_nationalite_details`
  ADD CONSTRAINT `demande_nationalite_details_ibfk_1` FOREIGN KEY (`DemandeID`) REFERENCES `demandes` (`DemandeID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `departements`
--
ALTER TABLE `departements`
  ADD CONSTRAINT `departements_ibfk_1` FOREIGN KEY (`RegionID`) REFERENCES `regions` (`RegionID`);

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`DemandeID`) REFERENCES `demandes` (`DemandeID`);

--
-- Contraintes pour la table `ethnies`
--
ALTER TABLE `ethnies`
  ADD CONSTRAINT `ethnies_ibfk_1` FOREIGN KEY (`RegionPrincipale`) REFERENCES `regions` (`RegionID`);

--
-- Contraintes pour la table `historique_demandes`
--
ALTER TABLE `historique_demandes`
  ADD CONSTRAINT `historique_demandes_ibfk_1` FOREIGN KEY (`DemandeID`) REFERENCES `demandes` (`DemandeID`) ON DELETE CASCADE,
  ADD CONSTRAINT `historique_demandes_ibfk_2` FOREIGN KEY (`ModifiePar`) REFERENCES `utilisateurs` (`UtilisateurID`);

--
-- Contraintes pour la table `journalactivites`
--
ALTER TABLE `journalactivites`
  ADD CONSTRAINT `journalactivites_ibfk_1` FOREIGN KEY (`UtilisateurID`) REFERENCES `utilisateurs` (`UtilisateurID`);

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`UtilisateurID`) REFERENCES `utilisateurs` (`UtilisateurID`),
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`DemandeID`) REFERENCES `demandes` (`DemandeID`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`DemandeID`) REFERENCES `demandes` (`DemandeID`);

--
-- Contraintes pour la table `reclamations`
--
ALTER TABLE `reclamations`
  ADD CONSTRAINT `reclamations_ibfk_1` FOREIGN KEY (`UtilisateurID`) REFERENCES `utilisateurs` (`UtilisateurID`),
  ADD CONSTRAINT `reclamations_ibfk_2` FOREIGN KEY (`DemandeID`) REFERENCES `demandes` (`DemandeID`);

--
-- Contraintes pour la table `rendezvous`
--
ALTER TABLE `rendezvous`
  ADD CONSTRAINT `rendezvous_ibfk_1` FOREIGN KEY (`DemandeID`) REFERENCES `demandes` (`DemandeID`);

--
-- Contraintes pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD CONSTRAINT `utilisateurs_ibfk_1` FOREIGN KEY (`RegionNaissanceID`) REFERENCES `regions` (`RegionID`),
  ADD CONSTRAINT `utilisateurs_ibfk_2` FOREIGN KEY (`DepartementNaissanceID`) REFERENCES `departements` (`DepartementID`),
  ADD CONSTRAINT `utilisateurs_ibfk_3` FOREIGN KEY (`VilleNaissanceID`) REFERENCES `villes` (`VilleID`),
  ADD CONSTRAINT `utilisateurs_ibfk_4` FOREIGN KEY (`RegionResidenceID`) REFERENCES `regions` (`RegionID`),
  ADD CONSTRAINT `utilisateurs_ibfk_5` FOREIGN KEY (`DepartementResidenceID`) REFERENCES `departements` (`DepartementID`),
  ADD CONSTRAINT `utilisateurs_ibfk_6` FOREIGN KEY (`VilleResidenceID`) REFERENCES `villes` (`VilleID`),
  ADD CONSTRAINT `utilisateurs_ibfk_7` FOREIGN KEY (`EthnieID`) REFERENCES `ethnies` (`EthnieID`);

--
-- Contraintes pour la table `villes`
--
ALTER TABLE `villes`
  ADD CONSTRAINT `villes_ibfk_1` FOREIGN KEY (`DepartementID`) REFERENCES `departements` (`DepartementID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
