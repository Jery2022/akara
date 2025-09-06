<?php
// backend/api/routes/sortieStock.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour Core\Response

use Core\Response;

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

$pdo = getPDO();

return [
    // --- Méthode GET : Récupérer une ou plusieurs sorties de stock ---
    'GET' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour accéder à cette ressource.');
            return;
        }
        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $id = $params['id'] ?? null;
        $baseQuery = "SELECT s.*, p.name AS produit_nom, u.name AS user_name, c.name AS customer_name, e.name AS entrepot_nom
                      FROM sortieStock s 
                      LEFT JOIN produits p ON s.produit_id = p.id
                      LEFT JOIN users u ON s.user_id = u.id
                      LEFT JOIN customers c ON s.customer_id = c.id
                      LEFT JOIN entrepots e ON s.entrepot_id = e.id";

        try {
            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID de sortie de stock invalide.');
                    return;
                }
                $stmt = $pdo->prepare("$baseQuery WHERE s.id = :id");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Sortie de stock non trouvée.');
                } else {
                    Response::success('Sortie de stock récupérée avec succès.', $item);
                }
            } else {
                $stmt = $pdo->query("$baseQuery ORDER BY s.date DESC");
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Sorties de stock récupérées avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('Error fetching sortieStock: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des sorties de stock.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Enregistrer une nouvelle sortie de stock ---
    'POST' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour créer une ressource.'
            );
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires
        $requiredFields = ['produit_id', 'quantity', 'date', 'entrepot_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        // Validation des ID (doivent être des entiers positifs)
        $idFields = ['produit_id', 'entrepot_id'];
        foreach ($idFields as $field) {
            $data[$field] = filter_var($data[$field], FILTER_VALIDATE_INT);
            if ($data[$field] === false || $data[$field] <= 0) {
                Response::badRequest("Le champ '{$field}' doit être un ID valide (entier positif).");
                return;
            }
        }

        // Validation de quantity
        $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity <= 0) {
            Response::badRequest("Le champ 'quantity' doit être un entier positif.");
            return;
        }

        // Validation de la date (AAAA-MM-JJ ou AAAA-MM-JJ HH:MM:SS)
        $date = $data['date'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $date)) {
            Response::badRequest("Le format de la date est invalide. Utilisez AAAA-MM-JJ ou AAAA-MM-JJ HH:MM:SS.");
            return;
        }

        // Validation optionnelle de user_id
        $user_id = null;
        if (isset($data['user_id']) && $data['user_id'] !== '') {
            $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
            if ($user_id === false || $user_id <= 0) {
                Response::badRequest("Le champ 'user_id' doit être un ID valide (entier positif) s'il est fourni.");
                return;
            }
        }

        // Validation optionnelle de customer_id
        $customer_id = null;
        if (isset($data['customer_id']) && $data['customer_id'] !== '') {
            $customer_id = filter_var($data['customer_id'], FILTER_VALIDATE_INT);
            if ($customer_id === false || $customer_id <= 0) {
                Response::badRequest("Le champ 'customer_id' doit être un ID valide (entier positif) s'il est fourni.");
                return;
            }
        }

        // Validation du motif (chaîne de caractères, longueur max)
        $motif = trim($data['motif'] ?? '');
        if (strlen($motif) > 255) { // Exemple de limite
            Response::badRequest("Le champ 'motif' ne peut pas dépasser 255 caractères.");
            return;
        }
        if (empty($motif)) {
            $motif = null; // Si vide, stocker comme NULL
        }

        try {
            $produit_id = $data['produit_id'];
            $entrepot_id = $data['entrepot_id'];

            $sql = "INSERT INTO sortieStock (produit_id, quantity, date, user_id, customer_id, entrepot_id, motif) 
                    VALUES (:produit_id, :quantity, :date, :user_id, :customer_id, :entrepot_id, :motif)";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':produit_id'  => $produit_id,
                ':quantity'    => $quantity,
                ':date'        => $date,
                ':user_id'     => $user_id,
                ':customer_id' => $customer_id,
                ':entrepot_id' => $entrepot_id,
                ':motif'       => $motif,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created(['id' => $pdo->lastInsertId()], 'Sortie de stock enregistrée avec succès.');
        } catch (PDOException $e) {
            error_log('Error creating sortieStock: ' . $e->getMessage());
            Response::error('Erreur lors de l\'enregistrement de la sortie de stock.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT : Modifier une sortie de stock spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour modifier une ressource.');
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de sortie de stock invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['produit_id', 'quantity', 'date', 'entrepot_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation des ID (doivent être des entiers positifs)
        $idFields = ['produit_id', 'entrepot_id'];
        foreach ($idFields as $field) {
            $data[$field] = filter_var($data[$field], FILTER_VALIDATE_INT);
            if ($data[$field] === false || $data[$field] <= 0) {
                Response::badRequest("Le champ '{$field}' doit être un ID valide (entier positif).");
                return;
            }
        }

        // Validation de quantity
        $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity <= 0) {
            Response::badRequest("Le champ 'quantity' doit être un entier positif.");
            return;
        }

        // Validation de la date (AAAA-MM-JJ ou AAAA-MM-JJ HH:MM:SS)
        $date = $data['date'];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $date)) {
            Response::badRequest("Le format de la date est invalide. Utilisez AAAA-MM-JJ ou AAAA-MM-JJ HH:MM:SS.");
            return;
        }

        // Validation optionnelle de user_id
        $user_id = null;
        if (isset($data['user_id']) && $data['user_id'] !== '') {
            $user_id = filter_var($data['user_id'], FILTER_VALIDATE_INT);
            if ($user_id === false || $user_id <= 0) {
                Response::badRequest("Le champ 'user_id' doit être un ID valide (entier positif) s'il est fourni.");
                return;
            }
        }

        // Validation optionnelle de customer_id
        $customer_id = null;
        if (isset($data['customer_id']) && $data['customer_id'] !== '') {
            $customer_id = filter_var($data['customer_id'], FILTER_VALIDATE_INT);
            if ($customer_id === false || $customer_id <= 0) {
                Response::badRequest("Le champ 'customer_id' doit être un ID valide (entier positif) s'il est fourni.");
                return;
            }
        }

        // Validation du motif (chaîne de caractères, longueur max)
        $motif = trim($data['motif'] ?? '');
        if (strlen($motif) > 255) { // Exemple de limite
            Response::badRequest("Le champ 'motif' ne peut pas dépasser 255 caractères.");
            return;
        }
        if (empty($motif)) {
            $motif = null; // Si vide, stocker comme NULL
        }

        try {
            $produit_id = $data['produit_id'];
            $entrepot_id = $data['entrepot_id'];

            $sql = "UPDATE sortieStock SET 
                        produit_id = :produit_id, 
                        quantity = :quantity, 
                        date = :date, 
                        user_id = :user_id, 
                        customer_id = :customer_id, 
                        entrepot_id = :entrepot_id, 
                        motif = :motif 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':produit_id'  => $produit_id,
                ':quantity'    => $quantity,
                ':date'        => $date,
                ':user_id'     => $user_id,
                ':customer_id' => $customer_id,
                ':entrepot_id' => $entrepot_id,
                ':motif'       => $motif,
                ':id'          => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Sortie de stock non trouvée avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Sortie de stock modifiée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating sortieStock: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de la sortie de stock.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE : Supprimer une sortie de stock spécifique ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour supprimer une ressource.');
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de sortie de stock invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql = "DELETE FROM sortieStock WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Sortie de stock non trouvée avec l\'ID spécifié.');
                return;
            }

            Response::success('Sortie de stock supprimée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting sortieStock: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression de la sortie de stock.', 500, ['details' => $e->getMessage()]);
        }
    },
];
