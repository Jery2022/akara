<?php
// backend/api/routes/suppliers.php

// Inclut la connexion à la base de données.
// __DIR__ ici est backend/api/routes/. Pour atteindre backend/config/, il faut remonter deux fois (../../).
require_once __DIR__ . '/../../config/db.php';

// Utilisez le namespace de la classe Response.
use Core\Response;

// Les fonctions isAuthenticated() et handleApiRequest() sont maintenant gérées par backend/api/index.php.
// Vous n'avez pas besoin de les redéfinir ici.

// Ce fichier retourne un tableau de fonctions anonymes (closures),
// chaque fonction étant le handler pour une méthode HTTP spécifique.
return [
    // --- GESTION DES REQUÊTES GET (Récupérer tous les fournisseurs) ---
    // Cette fonction sera appelée si la requête est GET et qu'aucun ID n'est fourni dans l'URL (ex: /suppliers).
    'GET'       => function (array $params, ?object $currentUser) {
        // L'authentification a déjà été gérée par index.php via handleApiRequest().
        // Si $currentUser est null, c'est que l'authentification a échoué et une 401 a été envoyée.
        // On n'a pas besoin de refaire isAuthenticated() ici.

        $pdo = getPDO();
        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $stmt  = $pdo->query("SELECT * FROM suppliers");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::json($items);
        } catch (\PDOException $e) {
            error_log("Erreur DB GET suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de la récupération des fournisseurs.', 500);
        }
    },

    // --- GESTION DES REQUÊTES GET_ID (Récupérer un fournisseur par ID) ---
    // Ce handler est appelé si la requête est GET et qu'un ID est fourni (ex: /suppliers/123).
    // Notez le suffixe '_ID' qui correspond à la logique de routage dans index.php.
    'GET_ID'    => function (array $params, ?object $currentUser) {
        $id = $params['id'] ?? null;

        if (! $id) {
            Response::badRequest('ID de fournisseur manquant.');
            return;
        }

        $pdo = getPDO();
        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $item) {
                Response::notFound('Fournisseur non trouvé.');
                return;
            }
            Response::json($item);
        } catch (\PDOException $e) {
            error_log("Erreur DB GET_ID suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de la récupération du fournisseur.', 500);
        }
    },

    // --- GESTION DES REQUÊTES POST (Ajouter un nouveau fournisseur) ---
    'POST'      => function (array $params, ?object $currentUser) {
        $pdo = getPDO();
        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (! isset($data['name'], $data['refContact'], $data['phone'], $data['email'])) {
            Response::badRequest('Champs obligatoires manquants: name, refContact, phone, email.');
            return;
        }

        $name       = trim($data['name']);
        $refContact = trim($data['refContact']);
        $phone      = trim($data['phone']);
        $email      = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        $contrat_id = filter_var($data['contrat_id'] ?? null, FILTER_VALIDATE_INT);

        if (! $email) {
            Response::badRequest('Format d\'e-mail invalide.');
            return;
        }

        try {
            $sql  = "INSERT INTO suppliers (name, refContact, phone, email, contrat_id) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name,
                $refContact,
                $phone,
                $email,
                $contrat_id,
            ]);
            Response::json([
                'message' => 'Fournisseur ajouté avec succès.',
                'id'      => $pdo->lastInsertId(),
            ], 201); // 201 Created
        } catch (\PDOException $e) {
            error_log("Erreur DB POST suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de l\'ajout du fournisseur.', 500);
        }
    },

    // --- GESTION DES REQUÊTES PUT_ID (Mettre à jour un fournisseur existant) ---
    // Notez le suffixe '_ID' car une mise à jour nécessite un ID dans l'URL.
    'PUT_ID'    => function (array $params, ?object $currentUser) {
        $id = $params['id'] ?? null;

        if (! $id) {
            Response::badRequest('ID de fournisseur manquant pour la mise à jour.');
            return;
        }

        $pdo = getPDO();
        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (! isset($data['name'], $data['refContact'], $data['phone'], $data['email'])) {
            Response::badRequest('Champs obligatoires manquants: name, refContact, phone, email.');
            return;
        }

        $name       = trim($data['name']);
        $refContact = trim($data['refContact']);
        $phone      = trim($data['phone']);
        $email      = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
        $contrat_id = filter_var($data['contrat_id'] ?? null, FILTER_VALIDATE_INT);

        if (! $email) {
            Response::badRequest('Format d\'e-mail invalide.');
            return;
        }

        try {
            $sql  = "UPDATE suppliers SET name=?, refContact=?, phone=?, email=?, contrat_id=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $name,
                $refContact,
                $phone,
                $email,
                $contrat_id,
                $id,
            ]);

            if ($stmt->rowCount() === 0) {
                Response::notFound('Aucun fournisseur trouvé avec cet ID ou aucune modification effectuée.');
                return;
            }

            Response::json(['message' => 'Fournisseur mis à jour avec succès.']);
        } catch (\PDOException $e) {
            error_log("Erreur DB PUT suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de la mise à jour du fournisseur.', 500);
        }
    },

    // --- GESTION DES REQUÊTES DELETE_ID (Supprimer un fournisseur) ---
    // Notez le suffixe '_ID' car une suppression nécessite un ID dans l'URL.
    'DELETE_ID' => function (array $params, ?object $currentUser) {
        $id = $params['id'] ?? null;

        if (! $id) {
            Response::badRequest('ID de fournisseur manquant ou invalide pour la suppression.');
            return;
        }

        $pdo = getPDO();
        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql  = "DELETE FROM suppliers WHERE id =?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                Response::notFound('Aucun fournisseur trouvé avec cet ID pour la suppression.');
                return;
            }

            Response::json(['message' => 'Fournisseur supprimé avec succès.'], 200);
        } catch (\PDOException $e) {
            error_log("Erreur DB DELETE suppliers: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de la suppression du fournisseur.', 500);
        }
    },
];
