<?php
// backend/api/routes/stock.php

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
    // --- Méthode GET : Récupérer un ou plusieurs éléments de stock ---
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

        try {
            if ($id) {
                // --- Logique pour récupérer un seul élément (anciennement GET_ID) ---
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID de stock invalide ou manquant dans l\'URL.');
                    return;
                }
                $stmt = $pdo->prepare("SELECT s.*, p.name AS produit_nom, sup.name AS supplier_name, e.name AS entrepot_nom FROM stock s LEFT JOIN produits p ON s.produit_id = p.id LEFT JOIN suppliers sup ON s.supplier_id = sup.id LEFT JOIN entrepots e ON s.entrepot_id = e.id WHERE s.id = :id");
                $stmt->execute([':id' => $id]);
                $stockItem = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$stockItem) {
                    Response::notFound('Élément de stock non trouvé.');
                } else {
                    Response::success('Élément de stock récupéré avec succès.', $stockItem);
                }
            } else {
                // --- Logique pour récupérer tous les éléments (anciennement GET) ---
                $stmt = $pdo->query("SELECT s.*, p.name AS produit_nom, sup.name AS supplier_name, e.name AS entrepot_nom FROM stock s LEFT JOIN produits p ON s.produit_id = p.id LEFT JOIN suppliers sup ON s.supplier_id = sup.id LEFT JOIN entrepots e ON s.entrepot_id = e.id ORDER BY s.id DESC");
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Éléments de stock récupérés avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('Error fetching stock: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des éléments de stock.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Ajouter un nouvel élément de stock ---
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
        $requiredFields = ['produit_id', 'quantity', 'unit', 'min', 'entrepot_id'];
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

        // Validation de quantity (entier non négatif)
        $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity < 0) {
            Response::badRequest("Le champ 'quantité' doit être un entier non négatif.");
            return;
        }

        // Validation de min (entier non négatif)
        $min = filter_var($data['min'], FILTER_VALIDATE_INT);
        if ($min === false || $min < 0) {
            Response::badRequest("Le champ 'minumum' doit être un entier non négatif.");
            return;
        }

        // Validation de l'unité (chaîne de caractères, longueur max)
        $unit = trim($data['unit']);
        if (empty($unit) || strlen($unit) > 15) {
            Response::badRequest("Le champ 'unité' est obligatoire et ne doit pas dépasser 15 caractères.");
            return;
        }

        // Validation optionnelle de supplier_id
        $supplier_id = null;
        if (isset($data['supplier_id']) && $data['supplier_id'] !== '') {
            $supplier_id = filter_var($data['supplier_id'], FILTER_VALIDATE_INT);
            if ($supplier_id === false || $supplier_id <= 0) {
                Response::badRequest("Le champ 'ID fourniseur' doit avoir un ID valide (entier positif) s'il est fourni.");
                return;
            }
        }

        try {
            $produit_id = $data['produit_id'];
            $entrepot_id = $data['entrepot_id'];

            // Vérifier si le produit existe déjà dans la table stock
            $checkSql = "SELECT COUNT(*) FROM stock WHERE produit_id = :produit_id";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([':produit_id' => $produit_id]);
            if ($checkStmt->fetchColumn() > 0) {
                Response::error('Ce produit est déjà enregistré en stock.', 409); // 409 Conflict
                return;
            }

            $sql = "INSERT INTO stock (produit_id, quantity, unit, min, supplier_id, entrepot_id) 
                    VALUES (:produit_id, :quantity, :unit, :min, :supplier_id, :entrepot_id)";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':produit_id'  => $produit_id,
                ':quantity'    => $quantity,
                ':unit'        => $unit,
                ':min'         => $min,
                ':supplier_id' => $supplier_id,
                ':entrepot_id' => $entrepot_id,
            ]);



            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }
            Response::success('Élément de stock ajouté avec succès.', ['id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            error_log('Error creating stock item: ' . $e->getMessage());
            Response::error('Erreur lors de l\'ajout de l\'élément de stock.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT : Modifier un élément de stock spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour modifier une ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de stock invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        // Vérifier si la stock existe avant de tenter la mise à jour
        try {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM stock WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            if ((int)$checkStmt->fetchColumn() === 0) {
                Response::notFound('Stock non trouvé avec l\'ID spécifié.');
                return;
            }
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification de la recette: ' . $e->getMessage());
            Response::error('Erreur lors de la vérification de la recette.', 500, ['details' => $e->getMessage()]);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['produit_id', 'quantity', 'unit', 'min', 'entrepot_id'];
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

        // Validation de quantity (entier non négatif)
        $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
        if ($quantity === false || $quantity < 0) {
            Response::badRequest("Le champ 'quantity' doit être un entier non négatif.");
            return;
        }

        // Validation de min (entier non négatif)
        $min = filter_var($data['min'], FILTER_VALIDATE_INT);
        if ($min === false || $min < 0) {
            Response::badRequest("Le champ 'min' doit être un entier non négatif.");
            return;
        }

        // Validation de l'unité (chaîne de caractères, longueur max)
        $unit = trim($data['unit']);
        if (empty($unit) || strlen($unit) > 15) {
            Response::badRequest("Le champ 'unité' est obligatoire et ne doit pas dépasser 15 caractères.");
            return;
        }

        // Validation optionnelle de supplier_id
        $supplier_id = null;
        if (isset($data['supplier_id']) && $data['supplier_id'] !== '') {
            $supplier_id = filter_var($data['supplier_id'], FILTER_VALIDATE_INT);
            if ($supplier_id === false || $supplier_id <= 0) {
                Response::badRequest("Le champ 'supplier_id' doit être un ID valide (entier positif) s'il est fourni.");
                return;
            }
        }

        try {
            $produit_id = $data['produit_id'];
            $entrepot_id = $data['entrepot_id'];

            $sql = "UPDATE stock SET 
                        produit_id = :produit_id, 
                        quantity = :quantity, 
                        unit = :unit,
                        min = :min, 
                        supplier_id = :supplier_id, 
                        entrepot_id = :entrepot_id 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':produit_id'  => $produit_id,
                ':quantity'    => $quantity,
                ':unit'        => $unit,
                ':min'         => $min,
                ':supplier_id' => $supplier_id,
                ':entrepot_id' => $entrepot_id,
                ':id'          => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }
            // if ($stmt->rowCount() === 0) {
            //     Response::notFound('Élément de stock non trouvé avec l\'ID spécifié ou aucune modification effectuée.');
            //     return;
            // }

            // Si rowCount est 0, les données étaient identiques, on renvoie quand même un succès
            Response::success('Élément de stock modifié avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating stock item: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de l\'élément de stock.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE : Supprimer un élément de stock spécifique ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour supprimer une ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de stock invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql = "DELETE FROM stock WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Élément de stock non trouvé avec l\'ID spécifié.');
                return;
            }

            Response::success('Élément de stock supprimé avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting stock item: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression de l\'élément de stock.', 500, ['details' => $e->getMessage()]);
        }
    },
];
