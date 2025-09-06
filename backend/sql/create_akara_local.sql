

--
-- Base de données : `akara_local`
--

-- --------------------------------------------------------

--
-- Structure de la table `achats`
--

CREATE TABLE `achats` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `suppliers_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `contrat_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date_achat` date NOT NULL,
  `category` enum('fournitures','électricité','téléphone','carburant','eau','mobiliers','fiscalité','impôts','taxes') NOT NULL DEFAULT 'fournitures',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `type` enum('espèces','virement','chèque') NOT NULL DEFAULT 'chèque',
  `status` enum('réglé','en attente','annulé') NOT NULL DEFAULT 'en attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `achats`
--

INSERT INTO `achats` (`id`, `name`, `suppliers_id`, `user_id`, `contrat_id`, `description`, `amount`, `date_achat`, `category`, `is_active`, `type`, `status`) VALUES
(1, NULL, 1, 1, NULL, NULL, 1350.00, '2025-01-08', 'fournitures', 1, 'chèque', 'en attente'),
(2, NULL, 1, 7, 1, NULL, 2500.00, '2025-01-22', 'mobiliers', 1, 'chèque', 'en attente'),
(3, NULL, 2, 2, 2, NULL, 11000.00, '2025-02-26', 'électricité', 1, 'virement', 'en attente'),
(4, NULL, 2, 7, 2, NULL, 1500.00, '2025-03-20', 'électricité', 1, 'espèces', 'en attente'),
(5, NULL, 3, 2, 3, NULL, 5000.00, '2025-03-12', 'fournitures', 1, 'virement', 'en attente'),
(6, NULL, 4, 7, 4, NULL, 12500.00, '2025-04-07', 'fournitures', 1, 'chèque', 'en attente'),
(7, NULL, 5, 7, 5, NULL, 8650.00, '2025-05-09', 'mobiliers', 1, 'virement', 'en attente'),
(8, NULL, 6, 2, 6, NULL, 7500.00, '2025-06-10', 'fournitures', 1, 'virement', 'en attente');

-- --------------------------------------------------------

--
-- Structure de la table `contrats`
--

CREATE TABLE `contrats` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `objet` varchar(255) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `status` enum('en cours','terminé','annulé') NOT NULL DEFAULT 'en cours',
  `montant` decimal(12,2) NOT NULL,
  `signataire` varchar(100) DEFAULT NULL,
  `date_signature` date NOT NULL,
  `fichier_contrat` varchar(255) DEFAULT NULL,
  `type` enum('client','fournisseur','employe') NOT NULL DEFAULT 'fournisseur',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contrats`
--

INSERT INTO `contrats` (`id`, `name`, `objet`, `date_debut`, `date_fin`, `status`, `montant`, `signataire`, `date_signature`, `fichier_contrat`, `type`, `is_active`, `created_at`) VALUES
(1, 'CT-2023-001', 'Contrat client A', '2023-01-15', '2024-01-15', 'en cours', 50000.00, 'Jean Dupont', '2023-01-15', NULL, 'client', 1, '2023-01-16 09:00:00'),
(2, 'CT-2023-002', 'Contrat fournisseur B', '2023-03-01', '2024-03-01', 'terminé', 75000.00, 'Marie Curie', '2023-03-01', NULL, 'fournisseur', 1, '2023-03-01 09:00:00'),
(3, 'CT-2023-003', 'Contrat employé C', '2023-06-01', '0000-00-00', 'en cours', 45000.00, 'Pierre Martin', '2023-06-01', NULL, 'employe', 1, '2023-06-01 08:00:00'),
(4, 'CT-2023-004', 'Maintenance serveurs', '2023-08-10', '2024-08-10', 'en cours', 30000.00, 'Sophie Lambert', '2023-08-10', NULL, 'fournisseur', 1, '2023-08-10 08:00:00'),
(5, 'CT-2023-005', 'Contrat de stage', '2023-09-01', '2024-02-28', 'terminé', 150000.00, 'Lucas Moreau', '2023-09-01', '', 'employe', 1, '2023-09-01 08:00:00'),
(6, 'CT-2023-006', 'Service web cloud', '2023-11-15', '0000-00-00', 'en cours', 120000.00, 'AlphaTech SA', '2023-11-15', NULL, 'client', 1, '2023-11-15 09:00:00'),
(7, 'CT-2023-007', 'Fourniture de matériel informatique', '2023-02-01', '2023-12-31', 'terminé', 90000.00, 'Bureau Informatique', '2023-02-01', NULL, 'fournisseur', 1, '2023-02-01 09:00:00'),
(8, 'CT-2023-008', 'Contrat CDI', '2023-04-01', '0000-00-00', 'en cours', 42000.00, 'Émilie Dubois', '2023-04-01', NULL, 'employe', 1, '2023-04-01 08:00:00'),
(10, 'GT-355', 'Contrat achat poteaux métalliques', '2025-07-01', '2025-08-03', 'en cours', 1200000.00, NULL, '2025-07-01', NULL, 'fournisseur', 1, '2025-07-29 15:11:05'),
(11, 'GT-430', 'Contrat travaux toiture', '2025-07-14', '2025-08-10', 'en cours', 10000.00, NULL, '2025-07-14', NULL, 'employe', 1, '2025-07-29 15:22:45'),
(13, 'CF-210-2025', 'Achat de fournitures', '2025-08-04', '2025-08-30', 'en cours', 35000.00, 'GABON MECA', '2025-07-28', '/upload/68b351d8e60fd2.28440140.pdf', 'fournisseur', 1, '2025-08-30 19:32:41');

-- --------------------------------------------------------

--
-- Structure de la table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `refContact` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT 'à préciser',
  `ville` varchar(100) NOT NULL DEFAULT 'à préciser',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('à risque','à suivre','sérieux') NOT NULL DEFAULT 'à suivre',
  `black_list` enum('oui','non') NOT NULL DEFAULT 'non',
  `contrat_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `customers`
--

INSERT INTO `customers` (`id`, `name`, `refContact`, `phone`, `email`, `address`, `ville`, `is_active`, `status`, `black_list`, `contrat_id`, `created_at`) VALUES
(1, 'Entreprise Dupont', 'Jean Dupont', '+33 6 12 34 56 78', 'contact@dupont.fr', 'à préciser', 'à préciser', 1, 'à suivre', 'non', 1, '2025-06-13 19:51:01'),
(2, 'TechCorp France', 'Claire Martin', '+33 6 87 65 43 21', 'claire.martin@techcorp.fr', 'à préciser', 'à préciser', 1, 'à risque', 'non', 2, '2025-06-13 19:51:01'),
(3, 'Services & Co', 'Lucien Fabre', '+33 6 55 44 33 22', 'lucien.fabre@servicesco.fr', 'à préciser', 'à préciser', 1, 'à suivre', 'oui', 3, '2025-06-13 19:51:01'),
(4, 'BureauPlus', 'Aurélie Moreau', '+33 6 98 76 54 32', 'a.moreau@bureauplus.fr', 'à préciser', 'à préciser', 1, 'à suivre', 'non', 1, '2025-06-13 19:51:01'),
(5, 'LogiSolutions', 'Damien Rousseau', '+33 6 33 22 11 00', 'damien.rousseau@logisolutions.fr', 'à préciser', 'à préciser', 1, 'sérieux', 'non', 5, '2025-06-13 19:51:01'),
(6, 'EcoMat SA', 'Isabelle Leroy', '+33 6 44 55 66 77', 'i.leroy@ecomat.fr', 'à préciser', 'à préciser', 1, 'à risque', 'oui', NULL, '2025-06-13 19:51:01'),
(7, 'NovaVision', 'Olivier Charpentier', '+33 6 11 22 33 44', 'olivier.charpentier@novavision.fr', 'à préciser', 'à préciser', 1, 'sérieux', 'non', NULL, '2025-06-13 19:51:01'),
(8, 'Test-customer', 'jeffry2', '+24168456332', 'jeffrey@example.com', 'à préciser', 'à préciser', 1, 'à suivre', 'non', NULL, '2025-08-28 20:49:09');

-- --------------------------------------------------------

--
-- Structure de la table `depenses`
--

CREATE TABLE `depenses` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `produit_id` int(11) NOT NULL,
  `suppliers_id` int(11) NOT NULL,
  `contrat_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `date_depense` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `nature` enum('achat','location') NOT NULL DEFAULT 'achat',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `category` enum('fournitures','équipement','services','maintenance','logistique') NOT NULL DEFAULT 'fournitures'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `depenses`
--

INSERT INTO `depenses` (`id`, `name`, `user_id`, `produit_id`, `suppliers_id`, `contrat_id`, `quantity`, `price`, `total`, `date_depense`, `description`, `nature`, `is_active`, `category`) VALUES
(1, NULL, 23, 8, 5, NULL, 100, 15.00, 1500.00, '2025-01-21', NULL, 'achat', 1, 'fournitures'),
(2, NULL, NULL, 1, 2, NULL, 50, 25.00, 1250.00, '2025-02-12', NULL, 'achat', 1, 'fournitures'),
(3, NULL, 23, 3, 2, NULL, 650, 2.00, 1300.00, '2025-03-05', NULL, 'achat', 1, 'fournitures'),
(4, NULL, 7, 11, 1, NULL, 100, 5.00, 500.00, '2025-04-05', NULL, 'achat', 1, 'fournitures'),
(5, NULL, 23, 5, 5, NULL, 140, 5.00, 700.00, '2025-04-05', NULL, 'achat', 1, 'fournitures'),
(6, NULL, 25, 4, 1, NULL, 26, 100.00, 2600.00, '2025-05-15', NULL, 'location', 1, 'équipement'),
(7, 'D2010-2024', NULL, 2, 4, 11, 20, 20.00, 400.00, '2025-09-01', 'Dépenses test', 'achat', 1, 'fournitures'),
(10, 'CF-2100-2025', NULL, 11, 13, 11, 250, 50.00, 12500.00, '2025-09-01', 'Achat test', 'achat', 1, 'fournitures'),
(13, 'D2010-2025', NULL, 4, 4, 11, 10, 20.00, 200.00, '2025-09-01', 'Dépenses test', 'achat', 1, 'fournitures');

--
-- Déclencheurs `depenses`
--
DELIMITER $$
CREATE TRIGGER `depense_before_insert` BEFORE INSERT ON `depenses` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `depenses_before_update` BEFORE UPDATE ON `depenses` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `details_facture`
--

CREATE TABLE `details_facture` (
  `id` int(11) NOT NULL,
  `facture_id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_unit` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `amount_tva` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `details_facture`
--

INSERT INTO `details_facture` (`id`, `facture_id`, `produit_id`, `quantity`, `price_unit`, `amount`, `amount_tva`) VALUES
(1, 1, 1, 1, 1200.00, 1416.00, 216.00),
(2, 1, 2, 1, 5000.00, 5900.00, 900.00),
(3, 2, 2, 1, 5000.00, 5900.00, 900.00);

--
-- Déclencheurs `details_facture`
--
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

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `fonction` varchar(50) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `phone` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `quality` enum('ouvrier','technicien','ingénieur','ceo') NOT NULL,
  `category` enum('agent','agent de maitrise','cadre','cadre superieur') NOT NULL DEFAULT 'agent',
  `contrat_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `employees`
--

INSERT INTO `employees` (`id`, `name`, `fonction`, `salary`, `phone`, `email`, `quality`, `category`, `contrat_id`, `user_id`, `is_active`, `created_at`) VALUES
(1, 'Jean-Paul ', 'Chauffeur', 250000.00, '+24170154565', 'mika_services@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-01 08:00:00'),
(2, 'Marie-Louis Doukom', 'Secrétaire', 350000.00, '+24170154566', 'btp.solutions@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-02 08:00:00'),
(3, 'Idriss Galate', 'Technicient peintre', 350000.00, '+24170154567', 'tech-inovators@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-03 08:00:00'),
(4, 'Yves Roland', 'Gestionnaire', 450000.00, '+24170154568', 'green_energy@test.com', 'ouvrier', 'agent de maitrise', NULL, NULL, 1, '2024-10-04 08:00:00'),
(5, 'Ulrich POM', 'Dessinateur', 350000.00, '+24170154569', 'logistics.experts@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-05 08:00:00'),
(6, 'Mireille ARC', 'Secrétaire comptable', 450000.00, '+24170154570', 'health.solutions@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-06 08:00:00'),
(7, 'Roland Garcia', 'Responsable Adminitratif et Financier', 750000.00, '+24170154571', 'edutech.services@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-07 08:00:00'),
(8, 'Rachelle PEÄY', 'Architecte décoratrice', 750000.00, '+24170154572', 'retail.masters@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-08 08:00:00'),
(9, 'Luc PADRE', 'Ingénieur bâtiment', 850000.00, '+24170154573', 'construction.pros@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-09 08:00:00'),
(10, 'jeans FONTAINE', 'Directeur Général', 1850000.00, '+24170154574', 'food-beverage.co@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2024-10-10 08:00:00'),
(11, 'Ewomba-Jocktane', 'Collaborateur', 0.00, '+24165190408', 'jewomba@hotmail.com', 'ingénieur', 'cadre superieur', NULL, 7, 1, '2025-07-14 13:00:32'),
(13, 'charles', 'Collaborateur', 145222.00, '0617588270', 'abonga@test.com', 'ouvrier', 'agent', NULL, 13, 1, '2025-07-14 13:00:32'),
(27, 'Yvon', 'Topographe', 14785.00, '', 'moyvon@test.com', 'ouvrier', 'agent', NULL, 23, 0, '2025-07-14 17:41:21'),
(29, 'Pierrot', 'Topographe', 14785.00, '065190408', 'pierrot@test.com', 'technicien', 'cadre', NULL, 25, 1, '2025-07-14 18:37:57'),
(30, 'Yves EWOMBA ', 'Electricien', 14000.00, '+24165190408', 'jewomba@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2025-07-29 19:28:29'),
(32, 'JOCKTANE', 'Electricien', 145755.00, '+24165190408', 'jewomba@test.com', 'ouvrier', 'agent', NULL, NULL, 1, '2025-07-29 20:40:50'),
(33, 'Alain St Pierre', 'Collaborateur', 14552.00, '+24165190408', 'alainstpierre@test.com', 'ingénieur', 'agent', NULL, NULL, 1, '2025-07-30 21:23:15'),
(34, 'Zambo', 'cadre', 14500.00, '+2416565651', 'zambo@example.com', 'ingénieur', 'cadre', NULL, NULL, 1, '2025-08-27 20:00:29'),
(35, 'Rockewa', 'Responsable informatique', 8500.00, '+24167158897', 'rockewa@example.com', 'technicien', 'cadre', NULL, NULL, 0, '2025-08-27 20:14:58'),
(36, 'Rockewa', 'Responsable informatique', 8500.00, '+24167456332', 'rockewa@example.com', 'technicien', 'agent de maitrise', NULL, NULL, 1, '2025-08-27 20:20:37'),
(37, 'Rockewa', 'Responsable informatique', 8500.00, '+24167456332', 'rockewa@example.com', 'technicien', 'agent de maitrise', NULL, NULL, 1, '2025-08-27 20:21:43'),
(38, 'Rokewa', 'Responsable informatique', 8500.00, '+24167456332', 'rockewa@example.com', 'technicien', 'agent de maitrise', NULL, NULL, 0, '2025-08-27 20:24:41'),
(39, 'Rokewa', 'Responsable informatique', 8500.00, '+24167456332', 'rockewa@example.com', 'technicien', 'agent de maitrise', NULL, NULL, 0, '2025-08-27 20:24:50'),
(40, 'Rokewa', 'Responsable informatique', 8500.00, '+24167456332', 'rockewa@example.com', 'technicien', 'agent de maitrise', NULL, NULL, 0, '2025-08-27 20:31:10'),
(41, 'Rockewa', 'Responsable informatique', 8500.00, '+24167456332', 'rockewa@example.com', 'technicien', 'agent de maitrise', NULL, NULL, 1, '2025-08-27 20:31:53'),
(42, 'Rockewa', 'Responsable informatique', 85000.00, '+24167456332', 'rockewa@example.com', 'technicien', 'agent de maitrise', NULL, NULL, 0, '2025-08-27 20:53:58'),
(43, 'Tokewa', 'cadre', 8500.00, '+2416765651', 'tokewa@example.com', 'ingénieur', 'cadre', NULL, NULL, 1, '2025-08-27 21:50:39'),
(44, 'test-employé', 'test', 8000.00, '+24169456312', 'employe@example.com', 'technicien', 'cadre superieur', NULL, NULL, 0, '2025-08-28 08:49:44');

-- --------------------------------------------------------

--
-- Structure de la table `entreestock`
--

CREATE TABLE `entreestock` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `date_entree` date NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `suppliers_id` int(11) DEFAULT NULL,
  `entrepot_id` int(11) NOT NULL,
  `motif` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `entrepots` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `responsable` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telephone` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `quality_stockage` enum('bonne','moyenne','mauvaise') NOT NULL DEFAULT 'bonne',
  `black_list` enum('oui','non') NOT NULL DEFAULT 'non',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `entrepots`
--

INSERT INTO `entrepots` (`id`, `name`, `adresse`, `responsable`, `email`, `telephone`, `capacity`, `quality_stockage`, `black_list`, `created_at`) VALUES
(1, 'Entrepôt Centre-Ville', '12 Rue des Chantiers, 75000 Paris', 'Jean Moreau', 'entrepot-centre-ville@test.com', '+24177000000', 100, 'bonne', 'oui', '2025-06-13 20:02:30'),
(2, 'Entrepôt Sud-Est', '8 Avenue du Bâtiment, 69000 Lyon', 'Sophie Lambert', 'entrepôt-sud-est@test.com', '+24165000000', 200, 'bonne', 'non', '2025-06-13 20:02:30'),
(3, 'Entrepôt Nord-Ouest', '5 Boulevard des Travaux, 35000 Rennes', 'Marc Dubois', 'entrepot-nord-Ouest@test.org', '+3366000000', 1000, 'bonne', 'non', '2025-06-13 20:02:30'),
(4, 'Entrepôt Littoral', '19 Quai Maritime, 13000 Marseille', 'Amélie Fournier', 'entrepot_Littoral@test.com', '+3370000000', 10, 'mauvaise', 'oui', '2025-06-13 20:02:30'),
(5, 'Entrepôt Logistique Ouest', '2 Zone Industrielle, 44000 Nantes', 'Pierre Rousseau', 'entrepot.logistique-ouest@test.com', '+3377000000', 15, 'mauvaise', 'oui', '2025-06-13 20:02:30');

-- --------------------------------------------------------

--
-- Structure de la table `factures`
--

CREATE TABLE `factures` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `date_facture` date NOT NULL,
  `amount_total` decimal(10,2) NOT NULL,
  `amount_tva` decimal(10,2) DEFAULT 0.00,
  `amount_css` decimal(10,2) DEFAULT 0.00,
  `amount_ttc` decimal(10,2) NOT NULL,
  `avance_status` enum('oui','non') NOT NULL DEFAULT 'non',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('payée','en attente','annulée') DEFAULT 'en attente',
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `factures`
--

INSERT INTO `factures` (`id`, `customer_id`, `date_facture`, `amount_total`, `amount_tva`, `amount_css`, `amount_ttc`, `avance_status`, `is_active`, `status`, `name`) VALUES
(1, 1, '2023-06-15', 6200.00, 1116.00, 62.00, 7378.00, 'non', 1, 'payée', NULL),
(2, 2, '2023-06-20', 5000.00, 900.00, 50.00, 5950.00, 'oui', 1, 'en attente', NULL),
(3, 4, '2025-08-31', 15000.00, 2700.00, 150.00, 17850.00, 'non', 1, 'annulée', NULL);

--
-- Déclencheurs `factures`
--
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

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `type` enum('espèces','virement','chèque') NOT NULL,
  `customer_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `contrat_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `date_payment` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `category` enum('travaux','services') NOT NULL DEFAULT 'travaux'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `payments`
--

INSERT INTO `payments` (`id`, `type`, `customer_id`, `user_id`, `contrat_id`, `description`, `amount`, `date_payment`, `is_active`, `category`) VALUES
(7, 'virement', 1, 7, 1, 'Paiement acompte sur contrat CT-2023-001', 5000.00, '2024-03-10', 1, 'travaux'),
(8, 'chèque', 1, 7, 1, 'Paiement solde final - contrat terminé', 17000.00, '2024-04-05', 1, 'services'),
(9, 'virement', 2, NULL, 2, 'Paiement mensuel régulier', 2200.00, '2024-04-12', 1, 'travaux'),
(10, 'espèces', 3, 13, 1, 'Règlement comptant partiel', 10000.00, '2024-05-01', 1, 'travaux'),
(11, 'virement', 3, NULL, 2, 'Deuxième versement sur contrat CT-2023-003', 15000.00, '2024-05-15', 1, 'services'),
(12, 'chèque', 2, NULL, 2, 'Versement sans utilisateur spécifique', 5000.00, '2024-05-20', 1, 'travaux');

-- --------------------------------------------------------

--
-- Structure de la table `produits`
--

CREATE TABLE `produits` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `unit` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `provenance` enum('local','étranger') NOT NULL DEFAULT 'local',
  `disponibility` enum('oui','non') NOT NULL DEFAULT 'oui',
  `delai_livraison` int(11) NOT NULL DEFAULT 0,
  `category` enum('Matériaux de construction','Matériel de chantier','Outillages','Équipement de sécurité','Équipement de bureau','Engins et équipements','Produits de finition','Équipements électriques','Équipements de plomberie','Équipements de chauffage','Équipements de climatisation','Équipements de ventilation','Équipements sanitaires','Produits de second œuvre','Voirie et assainissement','préciser') NOT NULL DEFAULT 'préciser',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `supplier_id` int(11) DEFAULT NULL,
  `entrepot_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `name`, `description`, `unit`, `price`, `provenance`, `disponibility`, `delai_livraison`, `category`, `is_active`, `created_at`, `supplier_id`, `entrepot_id`) VALUES
(1, 'Ciment Portland standard', 'Sac de ciment 50 kg, utilisé pour béton, mortier et maçonnerie', '', 12.50, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', 4, 5),
(2, 'Béton prêt à l’emploi', 'Mélange de ciment, gravier et sable en sac de 40 kg', 'm3', 9.99, 'local', 'non', 0, 'préciser', 0, '2025-06-13 19:55:52', 1, 5),
(3, 'Parpaing creux standard', 'Bloc béton creux 20x20x50 cm, isolation thermique et acoustique', '', 3.75, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', NULL, NULL),
(4, 'Poutrelle métallique HEA 100', 'Profilé métallique en acier laminé à chaud, longueur 6m', '', 85.00, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', 2, 1),
(5, 'Tuile mécanique romane', 'Tuile en terre cuite rouge, format standard pour toiture inclinée', '', 2.20, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', NULL, NULL),
(6, 'Panneau isolant thermique', 'Isolation extérieure en polystyrène expansé (PSE), épaisseur 100 mm', '', 18.90, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', NULL, NULL),
(7, 'Gravier concassé 20/40 mm', 'Utilisé pour fondations, chaussées et drainage', '', 45.00, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', NULL, NULL),
(8, 'Bois de charpente sapin', 'Planche rabotée 4m x 10x15 cm, classe C24', '', 22.00, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', NULL, NULL),
(9, 'Tube PVC assainissement DN110', 'Tuyau rigide PVC Ø110 mm pour évacuation eaux usées', '', 4.80, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', NULL, NULL),
(10, 'Peinture anti-corrosion glyzéré', 'Peinture primaire pour protection acier, pot de 5L', '', 32.90, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', NULL, NULL),
(11, 'Clou acier zingué 50mm', 'Paquet de 100 clous galvanisés pour travaux bois et structure', '', 5.40, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', NULL, NULL),
(12, 'Sable silico-calcaire', 'Sable de carrière propre pour béton et remblai, par palette de 1 tonne', '', 60.00, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', NULL, NULL),
(13, 'Fer à béton HA8', 'Barre d’acier haute adhérence diamètre 8 mm, longueur 12 mètres', '', 14.20, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', NULL, NULL),
(14, 'Géotextile non tissé', 'Feutre stabilisateur pour voiries et chemins, largeur 2m, rouleau de 100m', '', 89.00, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', NULL, NULL),
(15, 'Plot de nivellement', 'Support réglable pour terrasse sur dalle béton ou chape', '', 2.10, 'local', 'non', 0, 'préciser', 1, '2025-06-13 19:55:52', 4, 2),
(16, 'fer à béton HA20', 'Aciers spécial', '', 2400.00, 'local', 'oui', 0, 'préciser', 1, '2025-08-29 09:29:01', NULL, NULL),
(18, 'sable 0/0.5', '', 'm3', 10500.00, 'local', 'oui', 0, 'préciser', 1, '2025-08-31 20:36:48', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `quittances`
--

CREATE TABLE `quittances` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_paiement` date NOT NULL,
  `periode_service` varchar(50) NOT NULL,
  `numero_quittance` int(11) NOT NULL,
  `date_emission` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `type` enum('fournisseur','client') DEFAULT 'client',
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `quittances`
--

INSERT INTO `quittances` (`id`, `employee_id`, `montant`, `date_paiement`, `periode_service`, `numero_quittance`, `date_emission`, `is_active`, `type`, `name`) VALUES
(1, 1, 150.00, '2025-06-01', 'juin 2025', 1001, '2025-06-01', 1, 'fournisseur', NULL),
(2, 2, 200.50, '2025-06-05', 'juin 2025', 1002, '2025-06-05', 1, 'client', NULL),
(3, 3, 75.75, '2025-06-10', 'juin 2025', 1004, '2025-06-10', 1, 'fournisseur', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `recettes`
--

CREATE TABLE `recettes` (
  `id` int(11) NOT NULL,
  `name` VARCHAR(255) UNIQUE,
  `user_id` int(11) DEFAULT NULL,
  `produit_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `contrat_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date_recette` date DEFAULT NULL,
  `description` text DEFAULT NULL,
  `nature` enum('vente','location') NOT NULL DEFAULT 'vente',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `category` enum('construction','sécurité','hygiène','entretien','logistique','mobilité') NOT NULL DEFAULT 'construction'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `recettes`
--

INSERT INTO `recettes` (`id`, `user_id`, `produit_id`, `customer_id`, `contrat_id`, `quantity`, `price`, `total`, `date_recette`, `description`, `nature`, `is_active`, `category`) VALUES
(1, 1, 1, 1, 1, 200, 5.00, 1000.00, '2024-03-10', NULL, 'vente', 1, 'construction'),
(2, 2, 2, 1, 1, 98, 10.00, 980.00, '2024-04-05', NULL, 'vente', 1, 'construction'),
(3, 7, 3, 2, 2, 100, 5.00, 500.00, '2024-04-12', NULL, 'vente', 1, 'construction'),
(4, 7, 4, 3, 3, 150, 5.00, 750.00, '2024-05-01', NULL, 'vente', 1, 'sécurité'),
(5, 2, 2, 3, 3, 100, 10.00, 1000.00, '2024-06-15', NULL, 'vente', 1, 'construction'),
(6, 1, 8, 2, 2, 50, 12.00, 600.00, '2024-07-20', NULL, 'vente', 1, 'entretien'),
(11, NULL, 2, 6, 10, 1, 2500.00, 2500.00, '2025-08-01', '', 'vente', 1, 'construction'),
(13, NULL, 2, 6, NULL, 1, 50000.00, 50000.00, '2025-08-01', '', 'vente', 1, 'construction');

--
-- Déclencheurs `recettes`
--
DELIMITER $$
CREATE TRIGGER `amount_before_insert` BEFORE INSERT ON `recettes` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `amount_before_update` BEFORE UPDATE ON `recettes` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `total_before_insert` BEFORE INSERT ON `recettes` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `total_before_update` BEFORE UPDATE ON `recettes` FOR EACH ROW SET NEW.total = NEW.price * NEW.quantity
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `sortiestock`
--

CREATE TABLE `sortiestock` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `date_sortie` date NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `entrepot_id` int(11) NOT NULL,
  `motif` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `produit_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `min` int(11) DEFAULT NULL,
  `rentability` enum('forte','bonne','faible') NOT NULL DEFAULT 'bonne',
  `classification` enum('A','B','C') NOT NULL DEFAULT 'C',
  `supplier_id` int(11) DEFAULT NULL,
  `entrepot_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `produit_id`, `quantity`, `unit`, `min`, `rentability`, `classification`, `supplier_id`, `entrepot_id`) VALUES
(2, 2, 150, 'sacs', 30, 'bonne', 'C', 1, 1),
(3, 3, 500, 'unités', 100, 'bonne', 'C', 2, 2),
(4, 4, 20, 'pièces', 21, 'bonne', 'C', 3, 2),
(5, 5, 1000, 'unité', 6, 'bonne', 'C', 2, 1),
(7, 11, 100, 'pcs', 10, 'bonne', 'C', 2, 4),
(16, 13, 10, 'pcs', 10, 'bonne', 'C', 2, 5),
(17, 10, 1500, 'pcs', 100, 'bonne', 'C', NULL, 5);

-- --------------------------------------------------------

--
-- Structure de la table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `refContact` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL DEFAULT 'à préciser',
  `ville` varchar(100) NOT NULL DEFAULT 'à préciser',
  `status` enum('à risque','à suivre','sérieux') NOT NULL DEFAULT 'à suivre',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `black_list` enum('oui','non') NOT NULL DEFAULT 'non',
  `contrat_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `refContact`, `phone`, `email`, `address`, `ville`, `status`, `is_active`, `black_list`, `contrat_id`, `created_at`) VALUES
(1, 'AlphaTech Supplies', 'Jean Martin', '+33 6 12 34 56 78', 'contact@alphatech.fr', 'à préciser', 'à préciser', 'à suivre', 1, 'non', 1, '2025-06-13 19:44:49'),
(2, 'BetaLogistics', 'Sophie Dubois', '+33 6 87 65 43 21', 's.dubois@betalogistics.fr', 'à préciser', 'à préciser', 'à risque', 1, 'oui', 2, '2025-06-13 19:44:49'),
(3, 'Gamma Solutions', 'Pierre Lefevre', '+33 6 55 44 33 22', 'p.lefevre@gammasol.com', 'à préciser', 'à préciser', 'à suivre', 1, 'non', 3, '2025-06-13 19:44:49'),
(4, 'Delta Services', 'Marie Curie', '+33 6 98 76 54 32', 'm.curie@deltaservices.fr', 'à préciser', 'à préciser', 'à risque', 1, 'oui', 4, '2025-06-13 19:44:49'),
(5, 'TERRAPOT2', 'Cyr Andry2', '+24169856954', 'cyr2@example.com', 'à préciser', 'à préciser', 'à suivre', 0, 'non', NULL, '2025-06-13 19:44:49'),
(6, 'Zeta Informatique 2000', 'Camille Rousseau', '+33 6 44 55 66 77', 'c.rousseau@zetainfo.fr', 'à préciser', 'à préciser', 'sérieux', 1, 'non', 8, '2025-06-13 19:44:49'),
(7, 'Omega Maintenance', 'Pauline Fabre', '+33 6 11 22 33 44', 'p.fabre@omegamaint.fr', 'à préciser', 'à préciser', 'sérieux', 1, 'non', 6, '2025-06-13 19:44:49'),
(13, 'CHEZ BONG', 'Bong Rousseau', '+33617588271', 'bong@gmail.com', 'à préciser', 'à préciser', 'à suivre', 1, 'non', NULL, '2025-07-31 10:23:01'),
(14, 'TERRAPOT', 'Cyr Andry', '+24169856954', 'cyr30@example.com', 'à préciser', 'à préciser', 'à suivre', 0, 'non', NULL, '2025-08-28 09:17:02'),
(15, 'Entreprise TERRA', 'Pierre Andry', '+24169856958', 'pierrelandry@example.com', 'à préciser', 'à préciser', 'à suivre', 0, 'non', NULL, '2025-08-28 12:56:48');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `pseudo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `role` enum('admin','employe','user','rh') DEFAULT 'user',
  `statut` enum('actif','désactivé') NOT NULL DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `pseudo`, `email`, `password`, `employee_id`, `role`, `statut`, `created_at`) VALUES
(1, 'ejyr241', 'jewomba@hotmail.com', '$2y$10$bW/E5UeWT.JVcLt/dQ9pZOAh1Me6wgus5ogsRIqJR4gaWp6iZdyPy', 11, 'admin', 'actif', '2025-06-11 23:08:55'),
(2, 'Blaisot', 'blaise@example.com', '$2y$10$1O26w2ZDsqo5FLGlp7Tb3uT8xZVPjDz.A4JHcP86BHhdpW5txzCgu', NULL, 'admin', 'désactivé', '2025-06-12 21:44:04'),
(3, 'admin', 'admin@example.com', '$2y$10$bW/E5UeWT.JVcLt/dQ9pZOAh1Me6wgus5ogsRIqJR4gaWp6iZdyPy', 11, 'admin', 'actif', '2025-06-11 23:08:55'),
(13, 'charles', 'refaze@uytr.com', '$2y$10$MXTtzHoV1BChX5qcyEqqjO/MaqtcLJ8G5JGDO9SNRjDtYPBpFwITe', 13, 'employe', 'actif', '2025-07-02 20:33:44'),
(23, 'Yvon', 'moyvon@test.com', '$2y$10$7ywK20ZbzuQTgqkfEWqRleaX/vTjZFSjTujb8S/BTe5H6cEx/2C/.', 27, 'employe', 'actif', '2025-07-14 17:41:21'),
(25, 'Pierrot', 'pierrot@test.com', '$2y$10$viWlvfimBeXnpbfvMsw3K.ZvTqFLhaNE7L7uSMGaaLKmOXyN/B3de', 29, 'employe', 'actif', '2025-07-14 18:37:57');

-- --------------------------------------------------------

--
-- Structure de la table `ventes`
--

CREATE TABLE `ventes` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `contrat_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date_vente` date NOT NULL,
  `category` enum('fournitures','électricité','téléphone','carburant','eau','mobiliers','fiscalité','impôts','taxes') NOT NULL DEFAULT 'fournitures',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `type` enum('espèces','virement','chèque') NOT NULL DEFAULT 'chèque',
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ventes`
--

INSERT INTO `ventes` (`id`, `customer_id`, `user_id`, `contrat_id`, `description`, `amount`, `date_vente`, `category`, `is_active`, `type`, `name`) VALUES
(1, 1, 1, 1, 'Vente de fournitures', 100.00, '2023-01-01', 'fournitures', 1, 'espèces', 'v2010-2025'),
(2, 2, 2, 2, 'Vente de services', 200.00, '2023-01-02', 'fournitures', 1, 'virement', 'V1005-2024'),
(3, 3, 3, 3, 'Vente de matériel', 300.00, '2023-01-03', 'fournitures', 1, 'chèque', 'V1010-2024'),
(9, 4, NULL, 13, 'Paiement de facture d\'électricité du mois de janvier 2025', 150000.00, '2025-08-27', 'électricité', 1, 'espèces', 'V1015-2025');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `achats`
--
ALTER TABLE `achats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `suppliers_id` (`suppliers_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `contrat_id` (`contrat_id`);

--
-- Index pour la table `contrats`
--
ALTER TABLE `contrats`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contrat_id` (`contrat_id`);

--
-- Index pour la table `depenses`
--
ALTER TABLE `depenses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `produit_id` (`produit_id`),
  ADD KEY `suppliers_id` (`suppliers_id`),
  ADD KEY `contrat_id` (`contrat_id`);

--
-- Index pour la table `details_facture`
--
ALTER TABLE `details_facture`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produit_id` (`produit_id`),
  ADD KEY `facture_id` (`facture_id`);

--
-- Index pour la table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contrat_id` (`contrat_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `entreestock`
--
ALTER TABLE `entreestock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produit_id` (`produit_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `suppliers_id` (`suppliers_id`),
  ADD KEY `entrepot_id` (`entrepot_id`);

--
-- Index pour la table `entrepots`
--
ALTER TABLE `entrepots`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `factures`
--
ALTER TABLE `factures`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Index pour la table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `contrat_id` (`contrat_id`);

--
-- Index pour la table `produits`
--
ALTER TABLE `produits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_supplier` (`supplier_id`),
  ADD KEY `fk_entrepot` (`entrepot_id`);

--
-- Index pour la table `quittances`
--
ALTER TABLE `quittances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_quittance` (`numero_quittance`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Index pour la table `recettes`
--
ALTER TABLE `recettes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produit_id` (`produit_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `contrat_id` (`contrat_id`),
  ADD KEY `fk_user` (`user_id`);

--
-- Index pour la table `sortiestock`
--
ALTER TABLE `sortiestock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produit_id` (`produit_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `entrepot_id` (`entrepot_id`);

--
-- Index pour la table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_produit_id` (`produit_id`),
  ADD KEY `produit_id` (`produit_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `entrepot_id` (`entrepot_id`);

--
-- Index pour la table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contrat_id` (`contrat_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `employee_id` (`employee_id`);

--
-- Index pour la table `ventes`
--
ALTER TABLE `ventes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `contrat_id` (`contrat_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `achats`
--
ALTER TABLE `achats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `contrats`
--
ALTER TABLE `contrats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `depenses`
--
ALTER TABLE `depenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `details_facture`
--
ALTER TABLE `details_facture`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT pour la table `entreestock`
--
ALTER TABLE `entreestock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `entrepots`
--
ALTER TABLE `entrepots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `factures`
--
ALTER TABLE `factures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `quittances`
--
ALTER TABLE `quittances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `recettes`
--
ALTER TABLE `recettes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `sortiestock`
--
ALTER TABLE `sortiestock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT pour la table `ventes`
--
ALTER TABLE `ventes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;
