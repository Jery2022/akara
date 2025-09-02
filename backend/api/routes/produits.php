<?php
// backend/api/routes/produits.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour Core\Response et JWT

use Core\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Headers CORS. Idéalement, gérés par un middleware dans le routeur principal.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000"); // Adaptez à votre frontend
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

$pdo = getPDO();


return [
    // --- Méthode GET : Récupérer un ou plusieurs produits ---
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
            $baseQuery = "
                SELECT 
                    p.*, 
                    s.name as supplier_name, 
                    e.name as entrepot_name 
                FROM 
                    produits p
                LEFT JOIN 
                    suppliers s ON p.supplier_id = s.id
                LEFT JOIN 
                    entrepots e ON p.entrepot_id = e.id
                WHERE p.is_active = 1
            ";

            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID de produit invalide.');
                    return;
                }
                $stmt = $pdo->prepare($baseQuery . " AND p.id = :id");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Produit non trouvé.');
                } else {
                    Response::success('Produit récupéré avec succès.', $item);
                }
            } else {
                $stmt = $pdo->query($baseQuery . " ORDER BY p.name ASC");
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Produits récupérés avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('Error fetching produits: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des produits.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer un nouveau produit ---
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
        $requiredFields = ['name', 'price', 'unit'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        // Validation du prix
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            Response::badRequest("Le prix doit être un nombre positif ou zéro.");
            return;
        }

        try {
            $name = trim($data['name']);
            $price = (float) $data['price'];
            $description = $data['description'] ?? null;
            $unit = $data['unit'] ?? '';
            $provenance = $data['provenance'] ?? 'local';
            $disponibility = $data['disponibility'] ?? 'oui';
            $delai_livraison = (int) ($data['delai_livraison'] ?? 0);
            $supplier_id = !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null;
            $entrepot_id = !empty($data['entrepot_id']) ? (int) $data['entrepot_id'] : null;

            $sql = "INSERT INTO produits (name, description, price, unit, provenance, disponibility, delai_livraison, supplier_id, entrepot_id) 
                    VALUES (:name, :description, :price, :unit, :provenance, :disponibility, :delai_livraison, :supplier_id, :entrepot_id)";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':unit' => $unit,
                ':provenance' => $provenance,
                ':disponibility' => $disponibility,
                ':delai_livraison' => $delai_livraison,
                ':supplier_id' => $supplier_id,
                ':entrepot_id' => $entrepot_id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created(['id' => $pdo->lastInsertId()], 'Produit ajouté avec succès.');
        } catch (PDOException $e) {
            error_log('Error creating produit: ' . $e->getMessage());
            Response::error('Erreur lors de la création du produit.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT : Modifier un produit spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour modifier une ressource.');
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de produit invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['name', 'price', 'unit'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation du prix
        if (!is_numeric($data['price']) || $data['price'] < 0) {
            Response::badRequest("Le prix doit être un nombre positif ou zéro.");
            return;
        }

        try {
            $name = trim($data['name']);
            $price = (float) $data['price'];
            $description = $data['description'] ?? null;
            $unit = $data['unit'] ?? '';
            $provenance = $data['provenance'] ?? 'local';
            $disponibility = $data['disponibility'] ?? 'oui';
            $delai_livraison = (int) ($data['delai_livraison'] ?? 0);
            $supplier_id = !empty($data['supplier_id']) ? (int) $data['supplier_id'] : null;
            $entrepot_id = !empty($data['entrepot_id']) ? (int) $data['entrepot_id'] : null;

            $sql = "UPDATE produits SET 
                        name = :name, 
                        description = :description, 
                        price = :price, 
                        unit = :unit, 
                        provenance = :provenance, 
                        disponibility = :disponibility, 
                        delai_livraison = :delai_livraison, 
                        supplier_id = :supplier_id, 
                        entrepot_id = :entrepot_id 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':unit' => $unit,
                ':provenance' => $provenance,
                ':disponibility' => $disponibility,
                ':delai_livraison' => $delai_livraison,
                ':supplier_id' => $supplier_id,
                ':entrepot_id' => $entrepot_id,
                ':id' => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Produit non trouvé avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Produit modifié avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating produit: ' . $e->getMessage());
            Response::error('Erreur lors de la modification du produit.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE : Supprimer un produit spécifique ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour supprimer une ressource.');
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de produit invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql  = "UPDATE produits SET is_active = 0 WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de désactivation.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Produit non trouvé avec l\'ID spécifié.');
                return;
            }

            Response::success('Produit désactivé avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting produit: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression du produit.', 500, ['details' => $e->getMessage()]);
        }
    },
];
