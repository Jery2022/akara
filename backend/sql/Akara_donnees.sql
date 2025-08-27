
--
-- Base de données : `akara_local`
--

USE akara_local;

-- --------------------------------------------------------

--
-- Structure de la table `achats`
--

DROP TABLE IF EXISTS `achats`;
CREATE TABLE IF NOT EXISTS `achats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `suppliers_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `contrat_id` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `amount` decimal(10,2) NOT NULL,
  `date_achat` date NOT NULL,
  `category` enum('fournitures','électricité','téléphone','carburant','eau','mobiliers','fiscalité','impôts','taxes') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'fournitures',
  `type` enum('espèces','virement','chèque') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'chèque',
  PRIMARY KEY (`id`),
  KEY `suppliers_id` (`suppliers_id`),
  KEY `user_id` (`user_id`),
  KEY `contrat_id` (`contrat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `achats`
--

INSERT INTO `achats` (`id`, `suppliers_id`, `user_id`, `contrat_id`, `description`, `amount`, `date_achat`, `category`, `type`) VALUES
(1, 1, 1, NULL, NULL, '1350.00', '2025-01-08', 'fournitures', 'chèque'),
(2, 1, 7, 1, NULL, '2500.00', '2025-01-22', 'mobiliers', 'chèque'),
(3, 2, 2, 2, NULL, '11000.00', '2025-02-26', 'électricité', 'virement'),
(4, 2, 7, 2, NULL, '1500.00', '2025-03-20', 'électricité', 'espèces'),
(5, 3, 2, 3, NULL, '5000.00', '2025-03-12', 'fournitures', 'virement'),
(6, 4, 7, 4, NULL, '12500.00', '2025-04-07', 'fournitures', 'chèque'),
(7, 5, 7, 5, NULL, '8650.00', '2025-05-09', 'mobiliers', 'virement'),
(8, 6, 2, 6, NULL, '7500.00', '2025-06-10', 'fournitures', 'virement');

-- --------------------------------------------------------

--
-- Structure de la table `contrats`
--

DROP TABLE IF EXISTS `contrats`;
CREATE TABLE IF NOT EXISTS `contrats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ref` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `objet` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `status` enum('en cours','terminé','annulé') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en cours',
  `montant` decimal(12,2) NOT NULL,
  `signataire` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `date_signature` date NOT NULL,
  `fichier_contrat` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` enum('client','fournisseur','employe') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'fournisseur',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contrats`
--

INSERT INTO `contrats` (`id`, `ref`, `objet`, `date_debut`, `date_fin`, `status`, `montant`, `signataire`, `date_signature`, `fichier_contrat`, `type`, `created_at`) VALUES
(1, 'CT-2023-001', 'Contrat client A', '2023-01-15', '2024-01-15', 'en cours', '50000.00', 'Jean Dupont', '2023-01-15', NULL, 'client', '2023-01-16 10:00:00'),
(2, 'CT-2023-002', 'Contrat fournisseur B', '2023-03-01', '2024-03-01', 'terminé', '75000.00', 'Marie Curie', '2023-03-01', NULL, 'fournisseur', '2023-03-01 10:00:00'),
(3, 'CT-2023-003', 'Contrat employé C', '2023-06-01', '0000-00-00', 'en cours', '45000.00', 'Pierre Martin', '2023-06-01', NULL, 'employe', '2023-06-01 09:00:00'),
(4, 'CT-2023-004', 'Maintenance serveurs', '2023-08-10', '2024-08-10', 'en cours', '30000.00', 'Sophie Lambert', '2023-08-10', NULL, 'fournisseur', '2023-08-10 09:00:00'),
(5, 'CT-2023-005', 'Contrat de stage', '2023-09-01', '2024-02-28', 'annulé', '0.00', 'Lucas Moreau', '2023-09-01', NULL, 'employe', '2023-09-01 09:00:00'),
(6, 'CT-2023-006', 'Service web cloud', '2023-11-15', '0000-00-00', 'en cours', '120000.00', 'AlphaTech SA', '2023-11-15', NULL, 'client', '2023-11-15 10:00:00'),
(7, 'CT-2023-007', 'Fourniture de matériel informatique', '2023-02-01', '2023-12-31', 'terminé', '90000.00', 'Bureau Informatique', '2023-02-01', NULL, 'fournisseur', '2023-02-01 10:00:00'),
(8, 'CT-2023-008', 'Contrat CDI', '2023-04-01', '0000-00-00', 'en cours', '42000.00', 'Émilie Dubois', '2023-04-01', NULL, 'employe', '2023-04-01 09:00:00'),
(10, 'GT-355', 'Contrat achat poteaux métalliques', '2025-07-01', '2025-08-03', 'en cours', '1200000.00', NULL, '2025-07-01', NULL, 'fournisseur', '2025-07-29 16:11:05'),
(11, 'GT-430', 'Contrat travaux toiture', '2025-07-14', '2025-08-10', 'en cours', '10000.00', NULL, '2025-07-14', NULL, 'employe', '2025-07-29 16:22:45');

-- --------------------------------------------------------

--
-- Structure de la table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `refContact` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'à préciser',
  `ville` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'à préciser',
  `status` enum('à risque','à suivre','sérieux') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'à suivre',
  `black_list` enum('oui','non') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'non',
  `contrat_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `contrat_id` (`contrat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `customers`
--

INSERT INTO `customers` (`id`, `name`, `refContact`, `phone`, `email`, `address`, `ville`, `status`, `black_list`, `contrat_id`, `created_at`) VALUES
(1, 'Entreprise Dupont', 'Jean Dupont', '+33 6 12 34 56 78', 'contact@dupont.fr', 'à préciser', 'à préciser', 'à suivre', 'non', 1, '2025-06-13 20:51:01'),
(2, 'TechCorp France', 'Claire Martin', '+33 6 87 65 43 21', 'claire.martin@techcorp.fr', 'à préciser', 'à préciser', 'à risque', 'non', 2, '2025-06-13 20:51:01'),
(3, 'Services & Co', 'Lucien Fabre', '+33 6 55 44 33 22', 'lucien.fabre@servicesco.fr', 'à préciser', 'à préciser', 'à suivre', 'oui', 3, '2025-06-13 20:51:01'),
(4, 'BureauPlus', 'Aurélie Moreau', '+33 6 98 76 54 32', 'a.moreau@bureauplus.fr', 'à préciser', 'à préciser', 'à suivre', 'non', 4, '2025-06-13 20:51:01'),
(5, 'LogiSolutions', 'Damien Rousseau', '+33 6 33 22 11 00', 'damien.rousseau@logisolutions.fr', 'à préciser', 'à préciser', 'sérieux', 'non', 5, '2025-06-13 20:51:01'),
(6, 'EcoMat SA', 'Isabelle Leroy', '+33 6 44 55 66 77', 'i.leroy@ecomat.fr', 'à préciser', 'à préciser', 'à risque', 'oui', NULL, '2025-06-13 20:51:01'),
(7, 'NovaVision', 'Olivier Charpentier', '+33 6 11 22 33 44', 'olivier.charpentier@novavision.fr', 'à préciser', 'à préciser', 'sérieux', 'non', NULL, '2025-06-13 20:51:01');

-- --------------------------------------------------------

--
-- Structure de la table `depenses`
--

DROP TABLE IF EXISTS `depenses`;
CREATE TABLE IF NOT EXISTS `depenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `produit_id` int NOT NULL,
  `suppliers_id` int NOT NULL,
  `contrat_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `date_depense` date DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `nature` enum('achat','location') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'achat',
  `category` enum('fournitures','équipement','services','maintenance','logistique') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'fournitures',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `produit_id` (`produit_id`),
  KEY `suppliers_id` (`suppliers_id`),
  KEY `contrat_id` (`contrat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `depenses`
--

INSERT INTO `depenses` (`id`, `user_id`, `produit_id`, `suppliers_id`, `contrat_id`, `quantity`, `price`, `total`, `date_depense`, `description`, `nature`, `category`) VALUES
(1, 23, 8, 5, NULL, 100, '15.00', '1500.00', '2025-01-21', NULL, 'achat', 'fournitures'),
(2, NULL, 1, 2, NULL, 50, '25.00', '1250.00', '2025-02-12', NULL, 'achat', 'fournitures'),
(3, 23, 3, 2, NULL, 650, '2.00', '1300.00', '2025-03-05', NULL, 'achat', 'fournitures'),
(4, 7, 11, 1, NULL, 100, '5.00', '500.00', '2025-04-05', NULL, 'achat', 'fournitures'),
(5, 23, 5, 5, NULL, 140, '5.00', '700.00', '2025-04-05', NULL, 'achat', 'fournitures'),
(6, 25, 4, 1, NULL, 26, '100.00', '2600.00', '2025-05-15', NULL, 'location', 'équipement');

--
-- Déclencheurs `depenses`
--
DROP TRIGGER IF EXISTS `depense_before_insert`;
DELIMITER $$
CREATE TRIGGER `depense_before_insert` BEFORE INSERT ON `depenses` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `depenses_before_update`;
DELIMITER $$
CREATE TRIGGER `depenses_before_update` BEFORE UPDATE ON `depenses` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `details_facture`
--

DROP TABLE IF EXISTS `details_facture`;
CREATE TABLE IF NOT EXISTS `details_facture` (
  `id` int NOT NULL AUTO_INCREMENT,
  `facture_id` int NOT NULL,
  `produit_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price_unit` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `amount_tva` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `produit_id` (`produit_id`),
  KEY `facture_id` (`facture_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `details_facture`
--

INSERT INTO `details_facture` (`id`, `facture_id`, `produit_id`, `quantity`, `price_unit`, `amount`, `amount_tva`) VALUES
(1, 1, 1, 1, '1200.00', '1416.00', '216.00'),
(2, 1, 2, 1, '5000.00', '5900.00', '900.00'),
(3, 2, 2, 1, '5000.00', '5900.00', '900.00');

--
-- Déclencheurs `details_facture`
--
DROP TRIGGER IF EXISTS `before_insert_details_facture`;
DELIMITER $$
CREATE TRIGGER `before_insert_details_facture` BEFORE INSERT ON `details_facture` FOR EACH ROW BEGIN
    DECLARE amount_tva DECIMAL(10, 2);
    
    -- Calculer le montant de la TVA (18%)
    SET amount_tva = NEW.price_unit * NEW.quantity * 0.18;

    -- Calculer le montant total
    SET NEW.amount = (NEW.price_unit * NEW.quantity) + amount_tva;

    -- Mettre à jour le montant de la TVA
    SET NEW.amount_tva = amount_tva;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `before_update_details_facture`;
DELIMITER $$
CREATE TRIGGER `before_update_details_facture` BEFORE UPDATE ON `details_facture` FOR EACH ROW BEGIN
    DECLARE amount_tva DECIMAL(10, 2);
    
    -- Calculer le montant de la TVA (18%)
    SET amount_tva = NEW.price_unit * NEW.quantity * 0.18;

    -- Calculer le montant total
    SET NEW.amount = (NEW.price_unit * NEW.quantity) + amount_tva;

    -- Mettre à jour le montant de la TVA
    SET NEW.amount_tva = amount_tva;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `fonction` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `phone` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `quality` enum('ouvrier','technicien','ingénieur','ceo') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category` enum('agent','agent de maitrise','cadre','cadre superieur') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'agent',
  `contrat_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `contrat_id` (`contrat_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `employees`
--

INSERT INTO `employees` (`id`, `name`, `fonction`, `salary`, `phone`, `email`, `quality`, `category`, `contrat_id`, `user_id`, `is_active`, `created_at`) VALUES
(1, 'Jean-Paul ', 'Chauffeur', '250000.00', '+24170154565', 'mika_services@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-01 09:00:00'),
(2, 'Marie-Louis Doukom', 'Secrétaire', '350000.00', '+24170154566', 'btp.solutions@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-02 09:00:00'),
(3, 'Idriss Galate', 'Technicient peintre', '350000.00', '+24170154567', 'tech-inovators@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-03 09:00:00'),
(4, 'Yves Roland', 'Gestionnaire', '450000.00', '+24170154568', 'green_energy@test.com', 'ouvrier', 'agent de maitrise', NULL, NULL, 1, '2024-10-04 09:00:00'),
(5, 'Ulrich POM', 'Dessinateur', '350000.00', '+24170154569', 'logistics.experts@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-05 09:00:00'),
(6, 'Mireille ARC', 'Secrétaire comptable', '450000.00', '+24170154570', 'health.solutions@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-06 09:00:00'),
(7, 'Roland Garcia', 'Responsable Adminitratif et Financier', '750000.00', '+24170154571', 'edutech.services@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-07 09:00:00'),
(8, 'Rachelle PEÄY', 'Architecte décoratrice', '750000.00', '+24170154572', 'retail.masters@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-08 09:00:00'),
(9, 'Luc PADRE', 'Ingénieur bâtiment', '850000.00', '+24170154573', 'construction.pros@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-09 09:00:00'),
(10, 'jeans FONTAINE', 'Directeur Général', '1850000.00', '+24170154574', 'food-beverage.co@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-10 09:00:00'),
(11, 'jean', 'Collaborateur', '0.00', '065190408', 'jewomba@hotmail.com', 'ouvrier', 'agent', NULL, 7, 1, '2025-07-14 14:00:32'),
(13, 'charles', 'Collaborateur', '145222.00', '0617588270', 'abonga@test.com', 'ouvrier', 'agent', NULL, 13, 1, '2025-07-14 14:00:32'),
(27, 'Yvon', 'Topographe', '14785.00', '', 'moyvon@test.com', 'ouvrier', 'agent', NULL, 23, 0, '2025-07-14 18:41:21'),
(29, 'Pierrot', 'Topographe', '14785.00', '065190408', 'pierrot@test.com', 'technicien', 'cadre', NULL, 25, 1, '2025-07-14 19:37:57'),
(30, 'Yves EWOMBA ', 'Electricien', '14000.00', '+24165190408', 'jewomba@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2025-07-29 20:28:29'),
(32, 'JOCKTANE', 'Electricien', '145755.00', '+24165190408', 'jewomba@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2025-07-29 21:40:50'),
(33, 'Alain St Pierre', 'Collaborateur', '14552.00', '+24165190408', 'alainstpierre@test.com', 'ingénieur', 'agent', NULL, NULL, 1, '2025-07-30 22:23:15');

-- --------------------------------------------------------

--
-- Structure de la table `entreestock`
--

DROP TABLE IF EXISTS `entreestock`;
CREATE TABLE IF NOT EXISTS `entreestock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produit_id` int NOT NULL,
  `quantity` int NOT NULL,
  `date_entree` date NOT NULL,
  `user_id` int DEFAULT NULL,
  `suppliers_id` int DEFAULT NULL,
  `entrepot_id` int NOT NULL,
  `motif` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produit_id` (`produit_id`),
  KEY `user_id` (`user_id`),
  KEY `suppliers_id` (`suppliers_id`),
  KEY `entrepot_id` (`entrepot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `entreestock`
--

INSERT INTO `entreestock` (`id`, `produit_id`, `quantity`, `date_entree`, `user_id`, `suppliers_id`, `entrepot_id`, `motif`) VALUES
(11, 1, 200, '2024-04-01', 1, 1, 1, 'Réapprovisionnement régulier'),
(12, 2, 150, '2024-04-02', 1, 1, 1, 'Livraison hebdomadaire'),
(13, 3, 500, '2024-04-03', 2, 2, 2, 'Nouvelle commande pour chantier A'),
(14, 4, 20, '2024-04-04', 2, 3, 2, 'Arrivage poutrelles métalliques'),
(15, 5, 1000, '2024-04-05', 2, 2, 1, 'Commande urgente pour projet urgent');

-- --------------------------------------------------------

--
-- Structure de la table `entrepots`
--

DROP TABLE IF EXISTS `entrepots`;
CREATE TABLE IF NOT EXISTS `entrepots` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `adresse` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `responsable` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `telephone` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `capacity` int DEFAULT NULL,
  `quality_stockage` enum('bonne','moyenne','mauvaise') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'bonne',
  `black_list` enum('oui','non') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'non',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `entrepots`
--

INSERT INTO `entrepots` (`id`, `name`, `adresse`, `responsable`, `email`, `telephone`, `capacity`, `quality_stockage`, `black_list`, `created_at`) VALUES
(1, 'Entrepôt Centre-Ville', '12 Rue des Chantiers, 75000 Paris', 'Jean Moreau', 'entrepot-centre-ville@test.com', '+24177000000', 100, 'bonne', 'oui', '2025-06-13 21:02:30'),
(2, 'Entrepôt Sud-Est', '8 Avenue du Bâtiment, 69000 Lyon', 'Sophie Lambert', 'entrepôt-sud-est@test.com', '+24165000000', 200, 'bonne', 'non', '2025-06-13 21:02:30'),
(3, 'Entrepôt Nord-Ouest', '5 Boulevard des Travaux, 35000 Rennes', 'Marc Dubois', 'entrepot-nord-Ouest@test.org', '+3366000000', 1000, 'bonne', 'non', '2025-06-13 21:02:30'),
(4, 'Entrepôt Littoral', '19 Quai Maritime, 13000 Marseille', 'Amélie Fournier', 'entrepot_Littoral@test.com', '+3370000000', 10, 'mauvaise', 'oui', '2025-06-13 21:02:30'),
(5, 'Entrepôt Logistique Ouest', '2 Zone Industrielle, 44000 Nantes', 'Pierre Rousseau', 'entrepot.logistique-ouest@test.com', '+3377000000', 15, 'mauvaise', 'oui', '2025-06-13 21:02:30');

-- --------------------------------------------------------

--
-- Structure de la table `factures`
--

DROP TABLE IF EXISTS `factures`;
CREATE TABLE IF NOT EXISTS `factures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `date_facture` date NOT NULL,
  `amount_total` decimal(10,2) NOT NULL,
  `amount_tva` decimal(10,2) DEFAULT '0.00',
  `amount_css` decimal(10,2) DEFAULT '0.00',
  `amount_ttc` decimal(10,2) NOT NULL,
  `avance_status` enum('oui','non') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'non',
  `status` enum('payée','en attente','annulée') COLLATE utf8mb4_general_ci DEFAULT 'en attente',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `factures`
--

INSERT INTO `factures` (`id`, `customer_id`, `date_facture`, `amount_total`, `amount_tva`, `amount_css`, `amount_ttc`, `avance_status`, `status`) VALUES
(1, 1, '2023-06-15', '6200.00', '1116.00', '62.00', '7378.00', 'non', 'payée'),
(2, 2, '2023-06-20', '5000.00', '900.00', '50.00', '5950.00', 'oui', 'en attente');

--
-- Déclencheurs `factures`
--
DROP TRIGGER IF EXISTS `before_insert_factures`;
DELIMITER $$
CREATE TRIGGER `before_insert_factures` BEFORE INSERT ON `factures` FOR EACH ROW BEGIN
    DECLARE amount_tva DECIMAL(10, 2);
    DECLARE amount_css DECIMAL(10, 2);
    
    -- Calculer le montant de la TVA (18%)
    SET amount_tva = NEW.amount_total * 0.18;

    -- Calculer le montant de la CSS (1%)
    SET amount_css = NEW.amount_total * 0.01;

    -- Calculer le montant total TTC
    SET NEW.amount_ttc = NEW.amount_total + amount_tva + amount_css;

    -- Mettre à jour les montants de la TVA et de la CSS dans la facture
    SET NEW.amount_tva = amount_tva;
    SET NEW.amount_css = amount_css;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `before_update_factures`;
DELIMITER $$
CREATE TRIGGER `before_update_factures` BEFORE UPDATE ON `factures` FOR EACH ROW BEGIN
    DECLARE amount_tva DECIMAL(10, 2);
    DECLARE amount_css DECIMAL(10, 2);
    
    -- Calculer le montant de la TVA (18%)
    SET amount_tva = NEW.amount_total * 0.18;

    -- Calculer le montant de la CSS (1%)
    SET amount_css = NEW.amount_total * 0.01;

    -- Calculer le montant total TTC
    SET NEW.amount_ttc = NEW.amount_total + amount_tva + amount_css;

    -- Mettre à jour les montants de la TVA et de la CSS dans la facture
    SET NEW.amount_tva = amount_tva;
    SET NEW.amount_css = amount_css;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('espèces','virement','chèque') COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `contrat_id` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `amount` decimal(10,2) DEFAULT NULL,
  `date_payment` date DEFAULT NULL,
  `category` enum('travaux','services') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'travaux',
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  KEY `contrat_id` (`contrat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `payments`
--

INSERT INTO `payments` (`id`, `type`, `customer_id`, `user_id`, `contrat_id`, `description`, `amount`, `date_payment`, `category`) VALUES
(7, 'virement', 1, 7, 1, 'Paiement acompte sur contrat CT-2023-001', '5000.00', '2024-03-10', 'travaux'),
(8, 'chèque', 1, 7, 1, 'Paiement solde final - contrat terminé', '17000.00', '2024-04-05', 'services'),
(9, 'virement', 2, NULL, 2, 'Paiement mensuel régulier', '2200.00', '2024-04-12', 'travaux'),
(10, 'espèces', 3, 13, 1, 'Règlement comptant partiel', '10000.00', '2024-05-01', 'travaux'),
(11, 'virement', 3, NULL, 2, 'Deuxième versement sur contrat CT-2023-003', '15000.00', '2024-05-15', 'services'),
(12, 'chèque', 2, NULL, 2, 'Versement sans utilisateur spécifique', '5000.00', '2024-05-20', 'travaux');

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

DROP TABLE IF EXISTS `produits`;
CREATE TABLE IF NOT EXISTS `produits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `unit` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `provenance` enum('local','étranger') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'local',
  `disponibility` enum('oui','non') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'oui',
  `delai_livraison` int NOT NULL DEFAULT '0',
  `category` enum('Matériaux de construction','Matériel de chantier','Outillages','Équipement de sécurité','Équipement de bureau','Engins et équipements','Produits de finition','Équipements électriques','Équipements de plomberie','Équipements de chauffage','Équipements de climatisation','Équipements de ventilation','Équipements sanitaires','Produits de second œuvre','Voirie et assainissement','préciser') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'préciser',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `supplier_id` int DEFAULT NULL,
  `entrepot_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_supplier` (`supplier_id`),
  KEY `fk_entrepot` (`entrepot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `name`, `description`, `unit`, `price`, `provenance`, `disponibility`, `delai_livraison`, `category`, `created_at`, `supplier_id`, `entrepot_id`) VALUES
(1, 'Ciment Portland standard', 'Sac de ciment 50 kg, utilisé pour béton, mortier et maçonnerie', '', '12.50', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', 4, 5),
(2, 'Béton prêt à l’emploi', 'Mélange de ciment, gravier et sable en sac de 40 kg', '', '9.99', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', 1, NULL),
(3, 'Parpaing creux standard', 'Bloc béton creux 20x20x50 cm, isolation thermique et acoustique', '', '3.75', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', NULL, NULL),
(4, 'Poutrelle métallique HEA 100', 'Profilé métallique en acier laminé à chaud, longueur 6m', '', '85.00', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', 2, 1),
(5, 'Tuile mécanique romane', 'Tuile en terre cuite rouge, format standard pour toiture inclinée', '', '2.20', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', NULL, NULL),
(6, 'Panneau isolant thermique', 'Isolation extérieure en polystyrène expansé (PSE), épaisseur 100 mm', '', '18.90', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', NULL, NULL),
(7, 'Gravier concassé 20/40 mm', 'Utilisé pour fondations, chaussées et drainage', '', '45.00', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', NULL, NULL),
(8, 'Bois de charpente sapin', 'Planche rabotée 4m x 10x15 cm, classe C24', '', '22.00', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', NULL, NULL),
(9, 'Tube PVC assainissement DN110', 'Tuyau rigide PVC Ø110 mm pour évacuation eaux usées', '', '4.80', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', NULL, NULL),
(10, 'Peinture anti-corrosion glyzéré', 'Peinture primaire pour protection acier, pot de 5L', '', '32.90', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', NULL, NULL),
(11, 'Clou acier zingué 50mm', 'Paquet de 100 clous galvanisés pour travaux bois et structure', '', '5.40', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', NULL, NULL),
(12, 'Sable silico-calcaire', 'Sable de carrière propre pour béton et remblai, par palette de 1 tonne', '', '60.00', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', NULL, NULL),
(13, 'Fer à béton HA8', 'Barre d’acier haute adhérence diamètre 8 mm, longueur 12 mètres', '', '14.20', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', NULL, NULL),
(14, 'Géotextile non tissé', 'Feutre stabilisateur pour voiries et chemins, largeur 2m, rouleau de 100m', '', '89.00', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', NULL, NULL),
(15, 'Plot de nivellement', 'Support réglable pour terrasse sur dalle béton ou chape', '', '2.10', 'local', 'non', 0, 'préciser', '2025-06-13 20:55:52', 4, 2);

-- --------------------------------------------------------

--
-- Structure de la table `quittances`
--

DROP TABLE IF EXISTS `quittances`;
CREATE TABLE IF NOT EXISTS `quittances` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_paiement` date NOT NULL,
  `periode_service` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `numero_quittance` int NOT NULL,
  `date_emission` date NOT NULL,
  `type` enum('fournisseur','client') COLLATE utf8mb4_general_ci DEFAULT 'client',
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_quittance` (`numero_quittance`),
  KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `quittances`
--

INSERT INTO `quittances` (`id`, `employee_id`, `montant`, `date_paiement`, `periode_service`, `numero_quittance`, `date_emission`, `type`) VALUES
(1, 1, '150.00', '2025-06-01', 'juin 2025', 1001, '2025-06-01', 'fournisseur'),
(2, 2, '200.50', '2025-06-05', 'juin 2025', 1002, '2025-06-05', 'client'),
(3, 3, '75.75', '2025-06-10', 'juin 2025', 1003, '2025-06-10', 'fournisseur');

-- --------------------------------------------------------

--
-- Structure de la table `recettes`
--

DROP TABLE IF EXISTS `recettes`;
CREATE TABLE IF NOT EXISTS `recettes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `produit_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `contrat_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `date_recette` date DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `nature` enum('vente','location') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'vente',
  `category` enum('construction','sécurité','hygiène','entretien','logistique','mobilité') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'construction',
  PRIMARY KEY (`id`),
  KEY `produit_id` (`produit_id`),
  KEY `customer_id` (`customer_id`),
  KEY `contrat_id` (`contrat_id`),
  KEY `fk_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `recettes`
--

INSERT INTO `recettes` (`id`, `user_id`, `produit_id`, `customer_id`, `contrat_id`, `quantity`, `price`, `total`, `date_recette`, `description`, `nature`, `category`) VALUES
(1, 1, 1, 1, 1, 200, '5.00', '1000.00', '2024-03-10', NULL, 'vente', 'construction'),
(2, 2, 2, 1, 1, 98, '10.00', '980.00', '2024-04-05', NULL, 'vente', 'construction'),
(3, 7, 3, 2, 2, 100, '5.00', '500.00', '2024-04-12', NULL, 'vente', 'construction'),
(4, 7, 4, 3, 3, 150, '5.00', '750.00', '2024-05-01', NULL, 'vente', 'sécurité'),
(5, 2, 2, 3, 3, 100, '10.00', '1000.00', '2024-06-15', NULL, 'vente', 'construction'),
(6, 1, 8, 2, 2, 50, '12.00', '600.00', '2024-07-20', NULL, 'vente', 'entretien'),
(11, NULL, 2, 6, 10, 1, '2500.00', '2500.00', '2025-08-01', '', 'vente', 'construction'),
(13, NULL, 2, 6, NULL, 1, '50000.00', '50000.00', '2025-08-01', '', 'vente', 'construction');

--
-- Déclencheurs `recettes`
--
DROP TRIGGER IF EXISTS `amount_before_insert`;
DELIMITER $$
CREATE TRIGGER `amount_before_insert` BEFORE INSERT ON `recettes` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `amount_before_update`;
DELIMITER $$
CREATE TRIGGER `amount_before_update` BEFORE UPDATE ON `recettes` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `total_before_insert`;
DELIMITER $$
CREATE TRIGGER `total_before_insert` BEFORE INSERT ON `recettes` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `total_before_update`;
DELIMITER $$
CREATE TRIGGER `total_before_update` BEFORE UPDATE ON `recettes` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `sortiestock`
--

DROP TABLE IF EXISTS `sortiestock`;
CREATE TABLE IF NOT EXISTS `sortiestock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produit_id` int NOT NULL,
  `quantity` int NOT NULL,
  `date_sortie` date NOT NULL,
  `user_id` int DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `entrepot_id` int NOT NULL,
  `motif` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `produit_id` (`produit_id`),
  KEY `user_id` (`user_id`),
  KEY `customer_id` (`customer_id`),
  KEY `entrepot_id` (`entrepot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sortiestock`
--

INSERT INTO `sortiestock` (`id`, `produit_id`, `quantity`, `date_sortie`, `user_id`, `customer_id`, `entrepot_id`, `motif`) VALUES
(1, 1, 100, '2024-05-01', 1, 1, 1, 'Livraison chantier rue des Lilas'),
(2, 2, 80, '2024-05-02', 1, 1, 1, 'Commande régulière'),
(3, 3, 300, '2024-05-03', 2, 2, 2, 'Fourniture pour projet urbain'),
(4, 4, 15, '2024-05-04', 2, 3, 2, 'Livraison poutrelles pour construction'),
(5, 5, 700, '2024-05-05', 2, 2, 1, 'Urgence client - livraison express');

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

DROP TABLE IF EXISTS `stock`;
CREATE TABLE IF NOT EXISTS `stock` (
  `id` int NOT NULL AUTO_INCREMENT,
  `produit_id` int NOT NULL,
  `quantity` int DEFAULT NULL,
  `unit` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `min` int DEFAULT NULL,
  `rentability` enum('forte','bonne','faible') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'bonne',
  `classification` enum('A','B','C') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'C',
  `supplier_id` int DEFAULT NULL,
  `entrepot_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `produit_id` (`produit_id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `entrepot_id` (`entrepot_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `produit_id`, `quantity`, `unit`, `min`, `rentability`, `classification`, `supplier_id`, `entrepot_id`) VALUES
(1, 1, 200, 'sacs', 50, 'bonne', 'C', 1, 1),
(2, 2, 150, 'sacs', 30, 'bonne', 'C', 1, 1),
(3, 3, 500, 'unités', 100, 'bonne', 'C', 2, 2),
(4, 4, 20, 'pièces', 21, 'bonne', 'C', 3, 2),
(5, 5, 1000, 'unité', 6, 'bonne', 'C', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `refContact` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'à préciser',
  `ville` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'à préciser',
  `status` enum('à risque','à suivre','sérieux') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'à suivre',
  `black_list` enum('oui','non') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'non',
  `contrat_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `contrat_id` (`contrat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `refContact`, `phone`, `email`, `address`, `ville`, `status`, `black_list`, `contrat_id`, `created_at`) VALUES
(1, 'AlphaTech Supplies', 'Jean Martin', '+33 6 12 34 56 78', 'contact@alphatech.fr', 'à préciser', 'à préciser', 'à suivre', 'non', 1, '2025-06-13 20:44:49'),
(2, 'BetaLogistics', 'Sophie Dubois', '+33 6 87 65 43 21', 's.dubois@betalogistics.fr', 'à préciser', 'à préciser', 'à risque', 'oui', 2, '2025-06-13 20:44:49'),
(3, 'Gamma Solutions', 'Pierre Lefevre', '+33 6 55 44 33 22', 'p.lefevre@gammasol.com', 'à préciser', 'à préciser', 'à suivre', 'non', 3, '2025-06-13 20:44:49'),
(4, 'Delta Services', 'Marie Curie', '+33 6 98 76 54 32', 'm.curie@deltaservices.fr', 'à préciser', 'à préciser', 'à risque', 'oui', 4, '2025-06-13 20:44:49'),
(5, 'Epsilon Equipements', 'Luc Mercier', '+33 6 33 22 11 00', 'l.mercier@epsilonequip.fr', 'à préciser', 'à préciser', 'à suivre', 'non', 5, '2025-06-13 20:44:49'),
(6, 'Zeta Informatique 2000', 'Camille Rousseau', '+33 6 44 55 66 77', 'c.rousseau@zetainfo.fr', 'à préciser', 'à préciser', 'sérieux', 'non', 8, '2025-06-13 20:44:49'),
(7, 'Omega Maintenance', 'Pauline Fabre', '+33 6 11 22 33 44', 'p.fabre@omegamaint.fr', 'à préciser', 'à préciser', 'sérieux', 'non', 6, '2025-06-13 20:44:49'),
(13, 'CHEZ BONG', 'Bong Rousseau', '+33617588270', 'bong@gmail.com', 'à préciser', 'à préciser', 'à suivre', 'non', NULL, '2025-07-31 11:23:01');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--


CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `employee_id` int DEFAULT NULL,
  `role` enum('admin','employe') COLLATE utf8mb4_general_ci DEFAULT 'employe',
  `statut` enum('actif','désactivé') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `employee_id` (`employee_id`),
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `pseudo`, `email`, `password`, `employee_id`, `role`, `statut`, `created_at`) VALUES
(1, 'ejyr241', 'jewomba@hotmail.com', '$2y$10$bW/E5UeWT.JVcLt/dQ9pZOAh1Me6wgus5ogsRIqJR4gaWp6iZdyPy', NULL, 'admin', 'actif', '2025-06-12 00:08:55'),
(2, 'Blaisot', 'blaise@example.com', '$2y$10$1O26w2ZDsqo5FLGlp7Tb3uT8xZVPjDz.A4JHcP86BHhdpW5txzCgu', NULL, 'admin', 'désactivé', '2025-06-12 22:44:04'),
(7, 'jean', 'Jean.paulin.garba@test.com', '$2y$10$KaERRf9VAt1NpvoLEVM/oOEStiLpB0CauYpz592RH0ETxJNp3FfF2', 11, 'employe', 'actif', '2025-06-18 08:09:48'),
(13, 'charles', 'refaze@uytr.com', '$2y$10$MXTtzHoV1BChX5qcyEqqjO/MaqtcLJ8G5JGDO9SNRjDtYPBpFwITe', 13, 'employe', 'actif', '2025-07-02 21:33:44'),
(23, 'Yvon', 'moyvon@test.com', '$2y$10$7ywK20ZbzuQTgqkfEWqRleaX/vTjZFSjTujb8S/BTe5H6cEx/2C/.', 27, 'employe', 'actif', '2025-07-14 18:41:21'),
(25, 'Pierrot', 'pierrot@test.com', '$2y$10$viWlvfimBeXnpbfvMsw3K.ZvTqFLhaNE7L7uSMGaaLKmOXyN/B3de', 29, 'employe', 'actif', '2025-07-14 19:37:57');

-- --------------------------------------------------------

--
-- Structure de la table `ventes`
--

DROP TABLE IF EXISTS `ventes`;
CREATE TABLE IF NOT EXISTS `ventes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `contrat_id` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_general_ci,
  `amount` decimal(10,2) NOT NULL,
  `date_vente` date NOT NULL,
  `category` enum('produits','services') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'produits',
  `type` enum('espèces','virement','chèque') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'chèque',
  PRIMARY KEY (`id`),
  KEY `customers_id` (`customer_id`),
  KEY `user_id` (`user_id`),
  KEY `contrat_id` (`contrat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ventes`
--

INSERT INTO `ventes` (`id`, `customer_id`, `user_id`, `contrat_id`, `description`, `amount`, `date_vente`, `category`, `type`) VALUES
(1, 1, 1, 1, 'Vente de fournitures', '100.00', '2023-01-01', '', 'espèces'),
(2, 2, 2, 2, 'Vente de services', '200.00', '2023-01-02', 'services', 'virement'),
(3, 3, 7, 3, 'Vente de matériel', '300.00', '2023-01-04', 'produits', 'chèque');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `achats`
--
ALTER TABLE `achats`
  ADD CONSTRAINT `achats_ibfk_1` FOREIGN KEY (`suppliers_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `achats_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `achats_ibfk_3` FOREIGN KEY (`contrat_id`) REFERENCES `contrats` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`contrat_id`) REFERENCES `contrats` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `depenses`
--
ALTER TABLE `depenses`
  ADD CONSTRAINT `depenses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `depenses_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `depenses_ibfk_3` FOREIGN KEY (`suppliers_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `depenses_ibfk_4` FOREIGN KEY (`contrat_id`) REFERENCES `contrats` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `details_facture`
--
ALTER TABLE `details_facture`
  ADD CONSTRAINT `details_facture_ibfk_1` FOREIGN KEY (`facture_id`) REFERENCES `factures` (`id`),
  ADD CONSTRAINT `details_facture_ibfk_2` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`);

--
-- Contraintes pour la table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`contrat_id`) REFERENCES `contrats` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `entreestock`
--
ALTER TABLE `entreestock`
  ADD CONSTRAINT `entreeStock_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  ADD CONSTRAINT `entreeStock_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `entreeStock_ibfk_3` FOREIGN KEY (`suppliers_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `entreeStock_ibfk_4` FOREIGN KEY (`entrepot_id`) REFERENCES `entrepots` (`id`);

--
-- Contraintes pour la table `factures`
--
ALTER TABLE `factures`
  ADD CONSTRAINT `factures_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Contraintes pour la table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payments_ibfk_3` FOREIGN KEY (`contrat_id`) REFERENCES `contrats` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `produits`
--
ALTER TABLE `produits`
  ADD CONSTRAINT `fk_entrepot` FOREIGN KEY (`entrepot_id`) REFERENCES `entrepots` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `quittances`
--
ALTER TABLE `quittances`
  ADD CONSTRAINT `quittances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Contraintes pour la table `recettes`
--
ALTER TABLE `recettes`
  ADD CONSTRAINT `fk_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recettes_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recettes_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recettes_ibfk_3` FOREIGN KEY (`contrat_id`) REFERENCES `contrats` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `sortiestock`
--
ALTER TABLE `sortiestock`
  ADD CONSTRAINT `sortieStock_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`),
  ADD CONSTRAINT `sortieStock_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `sortieStock_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sortieStock_ibfk_4` FOREIGN KEY (`entrepot_id`) REFERENCES `entrepots` (`id`);

--
-- Contraintes pour la table `stock`
--
ALTER TABLE `stock`
  ADD CONSTRAINT `stock_ibfk_1` FOREIGN KEY (`produit_id`) REFERENCES `produits` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_ibfk_3` FOREIGN KEY (`entrepot_id`) REFERENCES `entrepots` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`contrat_id`) REFERENCES `contrats` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `ventes`
--
ALTER TABLE `ventes`
  ADD CONSTRAINT `ventes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

