<?php
// backend/api/routes/users.php

// Inclut la connexion à la base de données.
// Le chemin est correct pour aller de backend/api/routes/ à backend/config/
require_once __DIR__ . '/../../config/db.php';

// Utilise la classe Response pour des réponses JSON uniformes.
use Core\Response;

// Récupère l'instance PDO de la base de données.
$pdo = getPDO();

// Ce fichier retourne un tableau de fonctions anonymes (handlers),
// chaque fonction gérant une méthode HTTP spécifique pour la ressource 'users'.
return [
    // --- GESTION DES REQUÊTES GET (Récupérer un ou plusieurs utilisateurs) ---
    'GET' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::forbidden('Accès non autorisé.');
            return;
        }

        $id = $params['id'] ?? null;

        if ($id) {
            // Un admin peut voir n'importe qui, un utilisateur normal que son propre profil
            if ($currentUser->role !== 'admin' && $currentUser->user_id != $id) {
                Response::forbidden('Accès refusé. Vous ne pouvez voir que votre propre profil.');
                return;
            }
            try {
                $stmt = $pdo->prepare("SELECT id, email, role, statut, pseudo FROM users WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    Response::success('Utilisateur récupéré avec succès.', $user);
                } else {
                    Response::notFound('Utilisateur non trouvé.');
                }
            } catch (\PDOException $e) {
                error_log("Erreur PDO GET_ID user: " . $e->getMessage());
                Response::error("Erreur interne du serveur.", 500);
            }
        } else {
            // Seuls les admins peuvent lister tous les utilisateurs
            if ($currentUser->role !== 'admin') {
                Response::forbidden('Accès refusé. Seuls les administrateurs peuvent lister les utilisateurs.');
                return;
            }
            try {
                $stmt = $pdo->query("SELECT id, email, role, statut, pseudo FROM users");
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Utilisateurs récupérés avec succès.', $users);
            } catch (\PDOException $e) {
                error_log("Erreur PDO GET users: " . $e->getMessage());
                Response::error("Erreur interne du serveur.", 500);
            }
        }
    },

    // --- GESTION DES REQUÊTES POST (Créer un nouvel utilisateur) ---
    // Cette fonction est appelée pour une requête POST sur /api/users.
    'POST'      => function (array $params, ?object $currentUser) use ($pdo) {
        // AUTORISATION : Seuls les administrateurs peuvent créer des utilisateurs.
        if (! $currentUser || $currentUser->role !== 'admin') {
            Response::forbidden('Accès refusé. Seuls les administrateurs peuvent créer des utilisateurs.');
            return;
        }

        // Décode les données JSON envoyées dans le corps de la requête.
        $data = json_decode(file_get_contents('php://input'), true);

        // VALIDATION des champs d'entrée.
        // Vérifie la présence des champs obligatoires.
        if (! isset($data['email'], $data['password'], $data['role'], $data['pseudo'])) {
            Response::badRequest('Champs obligatoires (email, password, role, pseudo) manquants.');
            return;
        }
        // Valide le format de l'email.
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::badRequest('Email invalide.');
            return;
        }
        // Valide le rôle (doit être 'admin' ou 'employe').
        if (! in_array($data['role'], ['admin', 'employe'])) {
            Response::badRequest('Rôle invalide. Utilisez "admin" ou "employe".');
            return;
        }
        // Valide la longueur du pseudo.
        if (empty($data['pseudo']) || strlen(trim($data['pseudo'])) < 3) {
            Response::badRequest("Le pseudo doit contenir au moins 3 caractères.");
            return;
        }
        // Valide la longueur du mot de passe.
        if (strlen($data['password']) < 8) {
            Response::badRequest("Mot de passe invalide. Il doit contenir au moins 8 caractères.");
            return;
        }

        try {
            // Vérifie si l'email existe déjà pour éviter les doublons.
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute([':email' => $data['email']]);
            if ($stmt->fetch()) {
                Response::error('Un utilisateur avec cet email existe déjà.', 409); // 409 Conflict
                return;
            }
            // Vérifie si le pseudo existe déjà pour éviter les doublons.
            $stmt = $pdo->prepare("SELECT id FROM users WHERE pseudo = :pseudo");
            $stmt->execute([':pseudo' => $data['pseudo']]);
            if ($stmt->fetch()) {
                Response::error('Un utilisateur avec ce pseudo existe déjà.', 409); // 409 Conflict
                return;
            }

            // Hache le mot de passe avant de l'insérer dans la base de données.
            $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
            // Requête préparée pour insérer le nouvel utilisateur.
            $sql  = "INSERT INTO users (email, password_hash, role, statut, pseudo) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $data['email'],
                $password_hash,
                $data['role'],
                $data['statut'] ?? 'actif', // Définit 'actif' par défaut si non fourni.
                trim($data['pseudo']),
            ]);

            // Renvoie une réponse de succès avec l'ID du nouvel utilisateur (statut 201 Created).
            Response::created(['id' => $pdo->lastInsertId()], 'Utilisateur créé avec succès.');
        } catch (\PDOException $e) {
            error_log("Erreur PDO lors de la création de l'utilisateur: " . $e->getMessage());
            Response::error("Erreur interne du serveur lors de la création de l'utilisateur.", 500);
        }
    },

    // --- GESTION DES REQUÊTES PUT (Mettre à jour un utilisateur par ID) ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        $userId = $params['id']; // L'ID de l'utilisateur à modifier.

        // AUTORISATION : Seul l'administrateur peut modifier n'importe quel utilisateur.
        // Un utilisateur normal ne peut modifier que son propre profil.
        if (! $currentUser || ($currentUser->role !== 'admin' && $currentUser->user_id !== $userId)) {
            Response::forbidden('Accès refusé. Vous ne pouvez modifier que votre propre profil.');
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // VALIDATION des champs d'entrée.
        // Les champs obligatoires pour la mise à jour (au moins un doit être présent pour avoir quelque chose à modifier).
        // Ici, on vérifie la présence des champs que l'on s'attend à modifier.
        if (! isset($data['email']) && ! isset($data['password']) && ! isset($data['role']) && ! isset($data['statut']) && ! isset($data['pseudo'])) {
            Response::badRequest('Aucune donnée à mettre à jour fournie.');
            return;
        }

        // Assainissement et validation des données.
        $email    = isset($data['email']) ? filter_var($data['email'], FILTER_VALIDATE_EMAIL) : null;
        $password = $data['password'] ?? null;
        $role     = $data['role'] ?? null;
        $statut   = $data['statut'] ?? null;
        $pseudo   = isset($data['pseudo']) ? trim($data['pseudo']) : null;

        if ($email === false) { // filter_var retourne false si l'email est invalide
            Response::badRequest('Email invalide.');
            return;
        }
        if ($password !== null && strlen($password) < 8) {
            Response::badRequest("Mot de passe invalide. Il doit contenir au moins 8 caractères.");
            return;
        }
        if ($role !== null && ! in_array($role, ['admin', 'employe'])) {
            Response::badRequest('Rôle invalide. Utilisez "admin" ou "employe".');
            return;
        }
        if ($pseudo !== null && strlen($pseudo) < 3) {
            Response::badRequest("Le pseudo doit contenir au moins 3 caractères.");
            return;
        }

        try {
            $updateFields = [];
            $updateValues = [];

            // Construction dynamique de la requête UPDATE en fonction des champs fournis.
            if ($email !== null) {
                $updateFields[] = 'email = ?';
                $updateValues[] = $email;
            }
            if ($password !== null) {
                $password_hash  = password_hash($password, PASSWORD_DEFAULT);
                $updateFields[] = 'password_hash = ?'; // Utilise 'password_hash' pour la colonne
                $updateValues[] = $password_hash;
            }
            if ($role !== null) {
                // Autorisation spécifique pour la modification du rôle : seul un admin peut changer le rôle d'un autre.
                if ($currentUser->role !== 'admin' && $currentUser->user_id !== $userId) {
                    Response::forbidden('Vous n\'êtes pas autorisé à modifier le rôle.');
                    return;
                }
                $updateFields[] = 'role = ?';
                $updateValues[] = $role;
            }
            if ($statut !== null) {
                $updateFields[] = 'statut = ?';
                $updateValues[] = $statut;
            }
            if ($pseudo !== null) {
                $updateFields[] = 'pseudo = ?';
                $updateValues[] = $pseudo;
            }

            if (empty($updateFields)) {
                Response::badRequest('Aucune donnée valide à mettre à jour fournie.');
                return;
            }

            $sql            = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $updateValues[] = $userId; // Ajoute l'ID de l'utilisateur à la fin pour la clause WHERE.

            $stmt = $pdo->prepare($sql);
            $stmt->execute($updateValues);

            // Vérifie si la mise à jour a affecté des lignes.
            if ($stmt->rowCount() > 0) {
                Response::json(['message' => 'Utilisateur mis à jour avec succès.'], 200);
            } else {
                // Si rowCount est 0, soit l'utilisateur n'existe pas, soit aucune donnée n'a changé.
                Response::notFound('Utilisateur non trouvé ou aucune modification effectuée.');
            }
        } catch (\PDOException $e) {
            error_log("Erreur PDO lors de la mise à jour de l'utilisateur: " . $e->getMessage());
            Response::error("Erreur interne du serveur lors de la mise à jour de l'utilisateur.", 500);
        }
    },

    // --- GESTION DES REQUÊTES DELETE (Supprimer un utilisateur par ID) ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        $userId = $params['id']; // L'ID de l'utilisateur à supprimer.

        // AUTORISATION : Seuls les administrateurs peuvent supprimer des utilisateurs.
        if (! $currentUser || $currentUser->role !== 'admin') {
            Response::forbidden('Accès refusé. Seuls les administrateurs peuvent supprimer des utilisateurs.');
            return;
        }

        // Empêcher un administrateur de se supprimer lui-même via cette route API.
        if ($currentUser->user_id === $userId) {
            Response::forbidden('Vous ne pouvez pas supprimer votre propre compte via cette route.');
            return;
        }

        try {
            // Requête préparée pour supprimer l'utilisateur.
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);

            // Vérifie si une ligne a été réellement supprimée.
            if ($stmt->rowCount() > 0) {
                Response::json(['message' => 'Utilisateur supprimé avec succès.'], 200);
            } else {
                // Si rowCount est 0, l'utilisateur n'a pas été trouvé.
                Response::notFound('Utilisateur non trouvé.');
            }
        } catch (\PDOException $e) {
            error_log("Erreur PDO lors de la suppression de l'utilisateur: " . $e->getMessage());
            Response::error("Erreur interne du serveur lors de la suppression de l'utilisateur.", 500);
        }
    },
];
