<?php
// backend/api/routes/recettes.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../src/vendor/autoload.php'; // Pour Core\Response

use Core\Response;

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

$pdo = getPDO();

return [
    // --- Méthode GET : Récupérer une ou plusieurs recettes ---
    'GET' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier.');
            return;
        }
        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $id = $params['id'] ?? null;

        try {
            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID de recette invalide.');
                    return;
                }
                $stmt = $pdo->prepare("SELECT * FROM recettes WHERE id = :id AND is_active = 1");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Recette non trouvée.');
                } else {
                    Response::success('Recette récupérée avec succès.', $item);
                }
            } else {
                $stmt = $pdo->query("SELECT * FROM recettes WHERE is_active = 1 ORDER BY date_recette DESC");
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Recettes récupérées avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('Error fetching recettes: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des recettes.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer une nouvelle recette ---
    'POST' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires (note: 'contrat_id' n'est pas obligatoire)
        $requiredFields = ['name', 'produit_id', 'customer_id', 'quantity', 'price', 'total', 'date_recette', 'nature', 'category'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        // Validation des données
        if (!is_numeric($data['quantity']) || $data['quantity'] <= 0) {
            Response::badRequest("Le champ 'quantity' doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            Response::badRequest("Le champ 'price' doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['total']) || $data['total'] <= 0) {
            Response::badRequest("Le champ 'total' doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['produit_id']) || $data['produit_id'] <= 0) {
            Response::badRequest("Le champ 'produit_id' doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['customer_id']) || $data['customer_id'] <= 0) {
            Response::badRequest("Le champ 'customer_id' doit être un nombre positif.");
            return;
        }

        // Validation ajustée pour permettre à 'contrat_id' d'être vide
        if (isset($data['contrat_id']) && !empty($data['contrat_id']) && (!is_numeric($data['contrat_id']) || $data['contrat_id'] <= 0)) {
            Response::badRequest("Le champ 'contrat_id' doit être un nombre positif s'il est fourni.");
            return;
        }

        try {
            // Définir contrat_id à NULL si la valeur est vide ou non fournie
            $contratId = !empty($data['contrat_id']) ? (int) $data['contrat_id'] : null;

            $sql = "INSERT INTO recettes (name, produit_id, customer_id, contrat_id, quantity, price, total, date_recette, description, nature, category) 
                    VALUES (:name, :produit_id, :customer_id, :contrat_id, :quantity, :price, :total, :date_recette, :description, :nature, :category)";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':name'             => $data['name'],
                ':produit_id'       => $data['produit_id'],
                ':customer_id'      => $data['customer_id'],
                ':contrat_id'       => $contratId, // Utilisation de la variable ajustée
                ':quantity'         => (float) $data['quantity'],
                ':price'            => (float) $data['price'],
                ':total'            => (float) $data['total'],
                ':date_recette'     => $data['date_recette'],
                ':description'      => $data['description'] ?? null,
                ':nature'           => $data['nature'],
                ':category'         => $data['category'],
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created(['id' => $pdo->lastInsertId()], 'Recette ajoutée avec succès.');
        } catch (PDOException $e) {
            error_log('Error creating recette: ' . $e->getMessage());
            Response::error('Erreur lors de la création de la recette.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT : Modifier une recette spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier.');
            return;
        }

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de recette invalide ou manquant.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        // Vérifier si la recette existe avant de tenter la mise à jour
        try {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM recettes WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            if ((int)$checkStmt->fetchColumn() === 0) {
                Response::notFound('Recette non trouvée avec l\'ID spécifié.');
                return;
            }
        } catch (PDOException $e) {
            error_log('Error checking for recette existence: ' . $e->getMessage());
            Response::error('Erreur lors de la vérification de la recette.', 500, ['details' => $e->getMessage()]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['name', 'produit_id', 'customer_id', 'quantity', 'price', 'total', 'date_recette', 'nature', 'category'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation des données
        if (!is_numeric($data['quantity']) || $data['quantity'] <= 0) {
            Response::badRequest("Le champ 'quantity' doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            Response::badRequest("Le champ 'price' doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['total']) || $data['total'] <= 0) {
            Response::badRequest("Le champ 'total' doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['produit_id']) || $data['produit_id'] <= 0) {
            Response::badRequest("Le champ 'produit_id' doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['customer_id']) || $data['customer_id'] <= 0) {
            Response::badRequest("Le champ 'customer_id' doit être un nombre positif.");
            return;
        }

        // Validation ajustée pour permettre à 'contrat_id' d'être vide
        if (isset($data['contrat_id']) && !empty($data['contrat_id']) && (!is_numeric($data['contrat_id']) || $data['contrat_id'] <= 0)) {
            Response::badRequest("Le champ 'contrat_id' doit être un nombre positif s'il est fourni.");
            return;
        }

        try {
            // Définir contrat_id à NULL si la valeur est vide ou non fournie
            $contratId = !empty($data['contrat_id']) ? (int) $data['contrat_id'] : null;

            $sql = "UPDATE recettes SET 
                        name = :name,
                        produit_id = :produit_id, 
                        customer_id = :customer_id, 
                        contrat_id = :contrat_id, 
                        quantity = :quantity, 
                        price = :price,
                        total = :total,
                        date_recette = :date_recette,
                        description = :description,
                        nature = :nature,
                        category = :category
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':name'             => $data['name'],
                ':produit_id'       => $data['produit_id'],
                ':customer_id'      => $data['customer_id'],
                ':contrat_id'       => $contratId, // Utilisation de la variable ajustée
                ':quantity'         => (float) $data['quantity'],
                ':price'            => (float) $data['price'],
                ':total'            => (float) $data['total'],
                ':date_recette'     => $data['date_recette'],
                ':description'      => $data['description'] ?? null,
                ':nature'           => $data['nature'],
                ':category'         => $data['category'],
                ':id'               => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            // Si rowCount est 0, les données étaient identiques, on renvoie quand même un succès
            Response::success('Recette modifiée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating recette: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de la recette.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE : Supprimer une recette spécifique (suppression logique) ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier.');
            return;
        }

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de recette invalide ou manquant.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql = "UPDATE recettes SET is_active = 0 WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Recette non trouvée avec l\'ID spécifié.');
                return;
            }

            Response::success('Recette supprimée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting recette: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression de la recette.', 500, ['details' => $e->getMessage()]);
        }
    },
];
