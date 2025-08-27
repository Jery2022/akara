--
-- Structure de la table `achats`
--

CREATE TABLE `achats` (
  `id` int(11) NOT NULL,
  `suppliers_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `contrat_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date_achat` date NOT NULL,
  `category` enum('fournitures','électricité','téléphone','carburant','eau','mobiliers','fiscalité','impôts','taxes') NOT NULL DEFAULT 'fournitures',
  `type` enum('espèces','virement','chèque') NOT NULL DEFAULT 'chèque'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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
  `type` enum('espèces','virement','chèque') NOT NULL DEFAULT 'chèque'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Structure de la table `contrats`
--

CREATE TABLE `contrats` (
  `id` int(11) NOT NULL,
  `ref` varchar(100) NOT NULL,
  `objet` varchar(255) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `status` enum('en cours','terminé','annulé') NOT NULL DEFAULT 'en cours',
  `montant` decimal(12,2) NOT NULL,
  `signataire` varchar(100) DEFAULT NULL,
  `date_signature` date NOT NULL,
  `fichier_contrat` varchar(255) DEFAULT NULL,
  `type` enum('client','fournisseur','employe') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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
  `status` enum('à risque','à suivre','sérieux') NOT NULL DEFAULT 'à suivre',
  `black_list` enum('oui','non') NOT NULL DEFAULT 'non',
  `contrat_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



--
-- Structure de la table `depenses`
--

CREATE TABLE `depenses` (
  `id` int(11) NOT NULL,
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
  `category` enum('fournitures','équipement','services','maintenance','logistique') NOT NULL DEFAULT 'fournitures'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `quality` enum('ouvrier','technicien','ingénieur','ceo') NOT NULL DEFAULT 'ouvrier',
  `category` enum('agent','agent de maitrise','cadre','cadre superieur') NOT NULL DEFAULT 'agent',
  `status` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `contrat_id` int(11) DEFAULT NULL,
  `user_id` int(11) UNIQUE DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Structure de la table `entreeStock`
--

CREATE TABLE `entreeStock` (
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
  `status` enum('payée','en attente','annulée') DEFAULT 'en attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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
  `category` enum('travaux','services') NOT NULL DEFAULT 'travaux'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



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
  `category` enum('matériaux de construction','matériel de chantier','outillage','équipement de sécurité','équipement de bureau','engins et équipements','Produits de finition','équipements électriques','équipements de plomberie','équipements de chauffage','équipements de climatisation','équipements de ventilation','équipements sanitaires','Produits de second œuvre','Voirie et assainissement','préciser') NOT NULL DEFAULT 'préciser',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `supplier_id` int(11) DEFAULT NULL,
  `entrepot_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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
  `type` enum('fournisseur','client') DEFAULT 'client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci; 



--
-- Structure de la table `recettes`
--

CREATE TABLE `recettes` (
  `id` int(11) NOT NULL,
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
  `category` enum('construction','sécurité','hygiène','entretien','logistique','mobilité') NOT NULL DEFAULT 'construction'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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
-- Structure de la table `sortieStock`
--

CREATE TABLE `sortieStock` (
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
  `black_list` enum('oui','non') NOT NULL DEFAULT 'non',
  `contrat_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `pseudo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employe') DEFAULT 'employe',
  `statut` enum('actif','désactivé') NOT NULL DEFAULT 'actif',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `employee_id` int(11) UNIQUE,
  FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


--
-- Index pour les tables déchargées
--

--
-- Index pour la table `achats`
--
ALTER TABLE `achats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `suppliers_id` (`suppliers_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `contrat_id` (`contrat_id`);

  --
-- Index pour la table `ventes`
--
  ALTER TABLE `ventes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
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
-- Index pour la table `entreeStock`
--
ALTER TABLE `entreeStock`
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
-- Index pour la table `sortieStock`
--
ALTER TABLE `sortieStock`
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
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `achats`
--
ALTER TABLE `achats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;


  --
-- AUTO_INCREMENT pour la table `ventes`
--
ALTER TABLE `ventes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `contrats`
--
ALTER TABLE `contrats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `depenses`
--
ALTER TABLE `depenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `details_facture`
--
ALTER TABLE `details_facture`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `entreeStock`
--
ALTER TABLE `entreeStock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `entrepots`
--
ALTER TABLE `entrepots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `factures`
--
ALTER TABLE `factures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `produits`
--
ALTER TABLE `produits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `quittances`
--
ALTER TABLE `quittances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `recettes`
--
ALTER TABLE `recettes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `sortieStock`
--
ALTER TABLE `sortieStock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
-- Contraintes pour la table `ventes`
--
ALTER TABLE `ventes`
  ADD CONSTRAINT `ventes_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ventes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ventes_ibfk_3` FOREIGN KEY (`contrat_id`) REFERENCES `contrats` (`id`) ON DELETE SET NULL;

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
-- Contraintes pour la table `entreeStock`
--
ALTER TABLE `entreeStock`
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
-- Contraintes pour la table `sortieStock`
--
ALTER TABLE `sortieStock`
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
COMMIT;