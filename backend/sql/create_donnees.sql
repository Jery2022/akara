
--
-- Déchargement des données de la table `achats`
--

INSERT INTO `achats` (`id`, `suppliers_id`, `user_id`, `contrat_id`, `description`, `amount`, `date_achat`, `category`, `type`) VALUES
(1, 1, 1, NULL, NULL, 1350.00, '2025-01-08', 'fournitures', 'chèque'),
(2, 1, 7, 1, NULL, 2500.00, '2025-01-22', 'mobiliers', 'chèque'),
(3, 2, 2, 2, NULL, 11000.00, '2025-02-26', 'électricité', 'virement'),
(4, 2, 7, 2, NULL, 1500.00, '2025-03-20', 'électricité', 'espèces'),
(5, 3, 2, 3, NULL, 5000.00, '2025-03-12', 'fournitures', 'virement'),
(6, 4, 7, 4, NULL, 12500.00, '2025-04-07', 'fournitures', 'chèque'),
(7, 5, 7, 5, NULL, 8650.00, '2025-05-09', 'mobiliers', 'virement'),
(8, 6, 2, 6, NULL, 7500.00, '2025-06-10', 'fournitures', 'virement');

-- jeux de données pour la table `ventes`

INSERT INTO `ventes` (`id`, `suppliers_id`, `user_id`, `contrat_id`, `description`, `amount`, `date_vente`, `category`, `type`) VALUES
(1, 1, 1, 1, 'Vente de fournitures', 100.00, '2023-01-01', 'fournitures', 'espèces'),
(2, 2, 2, 2, 'Vente de services', 200.00, '2023-01-02', 'services', 'virement'),
(3, 3, 3, 3, 'Vente de matériel', 300.00, '2023-01-03', 'équipement', 'chèque');


-- --------------------------------------------------------
--
-- Déchargement des données de la table `contrats`
--

INSERT INTO `contrats` (`id`, `ref`, `objet`, `date_debut`, `date_fin`, `status`, `montant`, `signataire`, `date_signature`, `fichier_contrat`, `type`, `created_at`) VALUES
(1, 'CT-2023-001', 'Contrat client A', '2023-01-15', '2024-01-15', 'en cours', 50000.00, 'Jean Dupont', '2023-01-15', NULL, 'client', '2023-01-16 11:00:00'),
(2, 'CT-2023-002', 'Contrat fournisseur B', '2023-03-01', '2024-03-01', 'terminé', 75000.00, 'Marie Curie', '2023-03-01', NULL, 'fournisseur', '2023-03-01 11:00:00'),
(3, 'CT-2023-003', 'Contrat employé C', '2023-06-01', '0000-00-00', 'en cours', 45000.00, 'Pierre Martin', '2023-06-01', NULL, 'employe', '2023-06-01 10:00:00'),
(4, 'CT-2023-004', 'Maintenance serveurs', '2023-08-10', '2024-08-10', 'en cours', 30000.00, 'Sophie Lambert', '2023-08-10', NULL, 'fournisseur', '2023-08-10 10:00:00'),
(5, 'CT-2023-005', 'Contrat de stage', '2023-09-01', '2024-02-28', 'annulé', 0.00, 'Lucas Moreau', '2023-09-01', NULL, 'employe', '2023-09-01 10:00:00'),
(6, 'CT-2023-006', 'Service web cloud', '2023-11-15', '0000-00-00', 'en cours', 120000.00, 'AlphaTech SA', '2023-11-15', NULL, 'client', '2023-11-15 11:00:00'),
(7, 'CT-2023-007', 'Fourniture de matériel informatique', '2023-02-01', '2023-12-31', 'terminé', 90000.00, 'Bureau Informatique', '2023-02-01', NULL, 'fournisseur', '2023-02-01 11:00:00'),
(8, 'CT-2023-008', 'Contrat CDI', '2023-04-01', '0000-00-00', 'en cours', 42000.00, 'Émilie Dubois', '2023-04-01', NULL, 'employe', '2023-04-01 10:00:00');

-- --------------------------------------------------------
--
-- Déchargement des données de la table `customers`
--

INSERT INTO `customers` (`id`, `name`, `refContact`, `phone`, `email`, `address`, `ville`, `status`, `black_list`, `contrat_id`, `created_at`) VALUES
(1, 'Entreprise Dupont', 'Jean Dupont', '+33 6 12 34 56 78', 'contact@dupont.fr', 'à préciser', 'à préciser', 'à suivre', 'non', 1, '2025-06-13 21:51:01'),
(2, 'TechCorp France', 'Claire Martin', '+33 6 87 65 43 21', 'claire.martin@techcorp.fr', 'à préciser', 'à préciser', 'à risque', 'non', 2, '2025-06-13 21:51:01'),
(3, 'Services & Co', 'Lucien Fabre', '+33 6 55 44 33 22', 'lucien.fabre@servicesco.fr', 'à préciser', 'à préciser', 'à suivre', 'oui', 3, '2025-06-13 21:51:01'),
(4, 'BureauPlus', 'Aurélie Moreau', '+33 6 98 76 54 32', 'a.moreau@bureauplus.fr', 'à préciser', 'à préciser', 'à suivre', 'non', 4, '2025-06-13 21:51:01'),
(5, 'LogiSolutions', 'Damien Rousseau', '+33 6 33 22 11 00', 'damien.rousseau@logisolutions.fr', 'à préciser', 'à préciser', 'sérieux', 'non', 5, '2025-06-13 21:51:01'),
(6, 'EcoMat SA', 'Isabelle Leroy', '+33 6 44 55 66 77', 'i.leroy@ecomat.fr', 'à préciser', 'à préciser', 'à risque', 'oui', NULL, '2025-06-13 21:51:01'),
(7, 'NovaVision', 'Olivier Charpentier', '+33 6 11 22 33 44', 'olivier.charpentier@novavision.fr', 'à préciser', 'à préciser', 'sérieux', 'non', NULL, '2025-06-13 21:51:01');

-- --------------------------------------------------------

--
-- Déchargement des données de la table `depenses`
--

INSERT INTO `depenses` (`id`, `user_id`, `produit_id`, `suppliers_id`, `contrat_id`, `quantity`, `price`, `total`, `date_depense`, `description`, `nature`, `category`) VALUES
(1, 2, 8, 5, NULL, 100, 15.00, 1500.00, '2025-01-21', NULL, 'achat', 'fournitures'),
(2, 7, 1, 2, NULL, 50, 25.00, 1250.00, '2025-02-12', NULL, 'achat', 'fournitures'),
(3, 7, 3, 2, NULL, 650, 2.00, 1300.00, '2025-03-05', NULL, 'achat', 'fournitures'),
(4, 7, 11, 1, NULL, 100, 5.00, 500.00, '2025-04-05', NULL, 'achat', 'fournitures'),
(5, 2, 5, 5, NULL, 140, 5.00, 700.00, '2025-04-05', NULL, 'achat', 'fournitures'),
(6, 2, 4, 1, NULL, 26, 100.00, 2600.00, '2025-05-15', NULL, 'location', 'équipement');


--
-- Déchargement des données de la table `details_facture`
--

INSERT INTO `details_facture` (`id`, `facture_id`, `produit_id`, `quantity`, `price_unit`, `amount`, `amount_tva`) VALUES
(1, 1, 1, 1, 1200.00, 1200.00, 216.00),
(2, 1, 2, 1, 5000.00, 5000.00, 900.00),
(3, 2, 2, 1, 5000.00, 5000.00, 900.00);

--
-- Déchargement des données de la table `employees`
--

INSERT INTO `employees` (`id`, `name`, `fonction`, `salary`, `phone`, `email`, `quality`, `category`, `status`, `contrat_id`, `user_id`, `created_at`) VALUES
(1, 'Jean-Paul Razel', 'Chauffeur', 250000.00, '+24170154565', 'mika_services@test.com', 'ouvrier', 'agent', 'actif', NULL, NULL, '2024-10-01 10:00:00'),
(2, 'Marie-Louis Doukom', 'Secrétaire', 350000.00, '+24170154566', 'btp.solutions@test.com', 'ouvrier', 'agent', 'actif', NULL, NULL, '2024-10-02 10:00:00'),
(3, 'Idriss Galate', 'Technicient peintre', 350000.00, '+24170154567', 'tech-inovators@test.com', 'ouvrier', 'agent', 'actif', NULL, NULL, '2024-10-03 10:00:00'),
(4, 'Yves Roland', 'Gestionnaire', 450000.00, '+24170154568', 'green_energy@test.com', 'ouvrier', 'agent', 'actif', NULL, NULL, '2024-10-04 10:00:00'),
(5, 'Ulrich POM', 'Dessinateur', 350000.00, '+24170154569', 'logistics.experts@test.com', 'ouvrier', 'agent', 'actif', NULL, NULL, '2024-10-05 10:00:00'),
(6, 'Mireille ARC', 'Secrétaire comptable', 450000.00, '+24170154570', 'health.solutions@test.com', 'ouvrier', 'agent', 'actif', NULL, NULL, '2024-10-06 10:00:00'),
(7, 'Roland Garcia', 'Responsable Adminitratif et Financier', 750000.00, '+24170154571', 'edutech.services@test.com', 'ouvrier', 'agent', 'actif', NULL, NULL, '2024-10-07 10:00:00'),
(8, 'Rachelle PEÄY', 'Architecte décoratrice', 750000.00, '+24170154572', 'retail.masters@test.com', 'ouvrier', 'agent', 'actif', NULL, NULL, '2024-10-08 10:00:00'),
(9, 'Luc PADRE', 'Ingénieur bâtiment', 850000.00, '+24170154573', 'construction.pros@test.com', 'ouvrier', 'agent', 'actif', NULL, NULL, '2024-10-09 10:00:00'),
(10, 'jeans FONTAINE', 'Directeur Général', 1850000.00, '+24170154574', 'food-beverage.co@test.com', 'ouvrier', 'agent', 'actif', NULL, NULL, '2024-10-10 10:00:00');

-- --------------------------------------------------------
--
-- Déchargement des données de la table `entreeStock`
--

INSERT INTO `entreeStock` (`id`, `produit_id`, `quantity`, `date_entree`, `user_id`, `suppliers_id`, `entrepot_id`, `motif`) VALUES
(11, 1, 200, '2024-04-01', 1, 1, 1, 'Réapprovisionnement régulier'),
(12, 2, 150, '2024-04-02', 1, 1, 1, 'Livraison hebdomadaire'),
(13, 3, 500, '2024-04-03', 2, 2, 2, 'Nouvelle commande pour chantier A'),
(14, 4, 20, '2024-04-04', 2, 3, 2, 'Arrivage poutrelles métalliques'),
(15, 5, 1000, '2024-04-05', 2, 2, 1, 'Commande urgente pour projet urgent');

-- --------------------------------------------------------

--
-- Déchargement des données de la table `entrepots`
--

INSERT INTO `entrepots` (`id`, `name`, `adresse`, `responsable`, `email`, `telephone`, `capacity`, `quality_stockage`, `black_list`, `created_at`) VALUES
(1, 'Entrepôt Centre-Ville', '12 Rue des Chantiers, 75000 Paris', 'Jean Moreau', 'entrepôt-centre-ville@test.com', '+24177000000', 100, 'moyenne', 'non', '2025-06-13 22:02:30'),
(2, 'Entrepôt Sud-Est', '8 Avenue du Bâtiment, 69000 Lyon', 'Sophie Lambert', 'entrepôt-sud-est@test.com', '+24165000000', 200, 'bonne', 'non', '2025-06-13 22:02:30'),
(3, 'Entrepôt Nord-Ouest', '5 Boulevard des Travaux, 35000 Rennes', 'Marc Dubois', 'entrepot-nord-Ouest@test.org', '+3366000000', 1000, 'bonne', 'non', '2025-06-13 22:02:30'),
(4, 'Entrepôt Littoral', '19 Quai Maritime, 13000 Marseille', 'Amélie Fournier', 'entrepot_Littoral@test.com', '+3370000000', 10, 'mauvaise', 'oui', '2025-06-13 22:02:30'),
(5, 'Entrepôt Logistique Ouest', '2 Zone Industrielle, 44000 Nantes', 'Pierre Rousseau', 'entrepot.logistique-ouest@test.com', '+3377000000', 15, 'mauvaise', 'oui', '2025-06-13 22:02:30');

-- --------------------------------------------------------

--
-- Déchargement des données de la table `factures`
--

INSERT INTO `factures` (`id`, `customer_id`, `date_facture`, `amount_total`, `amount_tva`, `amount_css`, `amount_ttc`, `avance_status`, `status`) VALUES
(1, 1, '2023-06-15', 6200.00, 1116.00, 62.00, 7378.00, 'non', 'payée'),
(2, 2, '2023-06-20', 5000.00, 900.00, 50.00, 5950.00, 'oui', 'en attente');


--
-- Déchargement des données de la table `payments`
--

INSERT INTO `payments` (`id`, `type`, `customer_id`, `user_id`, `contrat_id`, `description`, `amount`, `date_payment`, `category`) VALUES
(7, 'virement', 1, 1, 1, 'Paiement acompte sur contrat CT-2023-001', 5000.00, '2024-03-10', 'travaux'),
(8, 'chèque', 1, 2, 1, 'Paiement solde final - contrat terminé', 17000.00, '2024-04-05', 'travaux'),
(9, 'virement', 2, 2, 2, 'Paiement mensuel régulier', 2200.00, '2024-04-12', 'travaux'),
(10, 'espèces', 3, 2, 1, 'Règlement comptant partiel', 10000.00, '2024-05-01', 'travaux'),
(11, 'virement', 3, 1, 2, 'Deuxième versement sur contrat CT-2023-003', 15000.00, '2024-05-15', 'travaux'),
(12, 'chèque', 2, 2, 2, 'Versement sans utilisateur spécifique', 5000.00, '2024-05-20', 'travaux');

-- --------------------------------------------------------

--
-- Déchargement des données de la table `produits`
--

INSERT INTO `produits` (`id`, `name`, `description`, `unit`, `price`, `provenance`, `disponibility`, `delai_livraison`, `category`, `created_at`, `supplier_id`, `entrepot_id`) VALUES
(1, 'Ciment Portland standard', 'Sac de ciment 50 kg, utilisé pour béton, mortier et maçonnerie', '', 12.50, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', 4, 5),
(2, 'Béton prêt à l’emploi', 'Mélange de ciment, gravier et sable en sac de 40 kg', '', 9.99, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', 1, NULL),
(3, 'Parpaing creux standard', 'Bloc béton creux 20x20x50 cm, isolation thermique et acoustique', '', 3.75, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', NULL, NULL),
(4, 'Poutrelle métallique HEA 100', 'Profilé métallique en acier laminé à chaud, longueur 6m', '', 85.00, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', 2, 1),
(5, 'Tuile mécanique romane', 'Tuile en terre cuite rouge, format standard pour toiture inclinée', '', 2.20, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', NULL, NULL),
(6, 'Panneau isolant thermique', 'Isolation extérieure en polystyrène expansé (PSE), épaisseur 100 mm', '', 18.90, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', NULL, NULL),
(7, 'Gravier concassé 20/40 mm', 'Utilisé pour fondations, chaussées et drainage', '', 45.00, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', NULL, NULL),
(8, 'Bois de charpente sapin', 'Planche rabotée 4m x 10x15 cm, classe C24', '', 22.00, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', NULL, NULL),
(9, 'Tube PVC assainissement DN110', 'Tuyau rigide PVC Ø110 mm pour évacuation eaux usées', '', 4.80, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', NULL, NULL),
(10, 'Peinture anti-corrosion glyzéré', 'Peinture primaire pour protection acier, pot de 5L', '', 32.90, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', NULL, NULL),
(11, 'Clou acier zingué 50mm', 'Paquet de 100 clous galvanisés pour travaux bois et structure', '', 5.40, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', NULL, NULL),
(12, 'Sable silico-calcaire', 'Sable de carrière propre pour béton et remblai, par palette de 1 tonne', '', 60.00, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', NULL, NULL),
(13, 'Fer à béton HA8', 'Barre d’acier haute adhérence diamètre 8 mm, longueur 12 mètres', '', 14.20, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', NULL, NULL),
(14, 'Géotextile non tissé', 'Feutre stabilisateur pour voiries et chemins, largeur 2m, rouleau de 100m', '', 89.00, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', NULL, NULL),
(15, 'Plot de nivellement', 'Support réglable pour terrasse sur dalle béton ou chape', '', 2.10, 'local', 'non', 0, 'préciser', '2025-06-13 21:55:52', 4, 2);

-- --------------------------------------------------------

--
-- Déchargement des données de la table `quittances`
--

INSERT INTO `quittances` (`id`, `employee_id`, `montant`, `date_paiement`, `periode_service`, `numero_quittance`, `date_emission`, `type`) VALUES
(1, 1, 150.00, '2025-06-01', 'juin 2025', 1001, '2025-06-01', 'fournisseur'),
(2, 2, 200.50, '2025-06-05', 'juin 2025', 1002, '2025-06-05', 'client'),
(3, 3, 75.75, '2025-06-10', 'juin 2025', 1003, '2025-06-10', 'client');

-- --------------------------------------------------------

--
-- Déchargement des données de la table `recettes`
--

INSERT INTO `recettes` (`id`, `user_id`, `produit_id`, `customer_id`, `contrat_id`, `quantity`, `price`, `total`, `date_recette`, `description`, `nature`, `category`) VALUES
(1, 1, 1, 1, 1, 200, 5.00, 1000.00, '2024-03-10', NULL, 'vente', 'construction'),
(2, 2, 2, 1, 1, 98, 10.00, 980.00, '2024-04-05', NULL, 'vente', 'construction'),
(3, 7, 3, 2, 2, 100, 5.00, 500.00, '2024-04-12', NULL, 'vente', 'construction'),
(4, 7, 4, 3, 3, 150, 5.00, 750.00, '2024-05-01', NULL, 'vente', 'sécurité'),
(5, 2, 5, 3, 3, 100, 10.00, 1000.00, '2024-06-15', NULL, 'vente', 'construction'),
(6, 1, 1, 2, 2, 100, 12.00, 1200.00, '2024-07-20', NULL, 'vente', 'entretien');


--
-- Déchargement des données de la table `sortieStock`
--

INSERT INTO `sortieStock` (`id`, `produit_id`, `quantity`, `date_sortie`, `user_id`, `customer_id`, `entrepot_id`, `motif`) VALUES
(1, 1, 100, '2024-05-01', 1, 1, 1, 'Livraison chantier rue des Lilas'),
(2, 2, 80, '2024-05-02', 1, 1, 1, 'Commande régulière'),
(3, 3, 300, '2024-05-03', 2, 2, 2, 'Fourniture pour projet urbain'),
(4, 4, 15, '2024-05-04', 2, 3, 2, 'Livraison poutrelles pour construction'),
(5, 5, 700, '2024-05-05', 2, 2, 1, 'Urgence client - livraison express');

-- --------------------------------------------------------

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`id`, `produit_id`, `quantity`, `unit`, `min`, `rentability`, `classification`, `supplier_id`, `entrepot_id`) VALUES
(1, 1, 200, 'sacs', 50, 'bonne', 'C', 1, 1),
(2, 2, 150, 'sacs', 30, 'bonne', 'C', 1, 1),
(3, 3, 500, 'unités', 100, 'bonne', 'C', 2, 2),
(4, 4, 20, 'pièces', 5, 'bonne', 'C', 3, 2),
(5, 5, 1000, 'unités', 200, 'bonne', 'C', 2, 1);

-- --------------------------------------------------------

--
-- Déchargement des données de la table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `refContact`, `phone`, `email`, `address`, `ville`, `status`, `black_list`, `contrat_id`, `created_at`) VALUES
(1, 'AlphaTech Supplies', 'Jean Martin', '+33 6 12 34 56 78', 'contact@alphatech.fr', 'à préciser', 'à préciser', 'à suivre', 'non', 1, '2025-06-13 21:44:49'),
(2, 'BetaLogistics', 'Sophie Dubois', '+33 6 87 65 43 21', 's.dubois@betalogistics.fr', 'à préciser', 'à préciser', 'à risque', 'oui', 2, '2025-06-13 21:44:49'),
(3, 'Gamma Solutions', 'Pierre Lefevre', '+33 6 55 44 33 22', 'p.lefevre@gammasol.com', 'à préciser', 'à préciser', 'à suivre', 'non', 3, '2025-06-13 21:44:49'),
(4, 'Delta Services', 'Marie Curie', '+33 6 98 76 54 32', 'm.curie@deltaservices.fr', 'à préciser', 'à préciser', 'à risque', 'oui', 4, '2025-06-13 21:44:49'),
(5, 'Epsilon Equipements', 'Luc Mercier', '+33 6 33 22 11 00', 'l.mercier@epsilonequip.fr', 'à préciser', 'à préciser', 'à suivre', 'non', 5, '2025-06-13 21:44:49'),
(6, 'Zeta Informatique', 'Camille Rousseau', '+33 6 44 55 66 77', 'c.rousseau@zetainfo.fr', 'à préciser', 'à préciser', 'sérieux', 'non', 8, '2025-06-13 21:44:49'),
(7, 'Omega Maintenance', 'Pauline Fabre', '+33 6 11 22 33 44', 'p.fabre@omegamaint.fr', 'à préciser', 'à préciser', 'sérieux', 'non', 6, '2025-06-13 21:44:49');

-- --------------------------------------------------------


--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `pseudo`, `email`, `password`, `role`, `statut`, `created_at`) VALUES
(1, 'ejyr241', 'jewomba@hotmail.com', '$2y$10$unXILLprtyTLoQvG7IjUhuPeRVn.brIWHyAmiLJB3c99ainDUg7sC', 'admin', 'actif', '2025-06-12 01:08:55'),
(2, 'admin', 'admin@test.com', '$2y$10$o8dmayTqdt9krcGNcSPYRe7BD1xEUlwAlIWxl/v/baZOxXudptxdK', 'employe', 'actif', '2025-06-12 23:44:04'),
(7, 'jean', 'Jean.paulin.garba@test.com', '$2y$10$KaERRf9VAt1NpvoLEVM/oOEStiLpB0CauYpz592RH0ETxJNp3FfF2', 'admin', 'désactivé', '2025-06-18 09:09:48');
