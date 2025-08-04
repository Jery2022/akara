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
    // --- Méthode GET : Récupérer tous les produits ---
    'GET' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour accéder à cette ressource.'
            );
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $stmt  = $pdo->query("SELECT * FROM produits ORDER BY name ASC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::success('Produits récupérés avec succès.', $items);
        } catch (PDOException $e) {
            error_log('Error fetching produits: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des produits.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode GET_ID : Récupérer un produit spécifique --- 
    'GET_ID' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour accéder à cette ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de produit invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $produit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$produit) {
                Response::notFound('Produit non trouvé.');
                return;
            }
            Response::success('Produit récupéré avec succès.', $produit);
        } catch (PDOException $e) {
            error_log('Error fetching single produit: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération du produit.', 500, ['details' => $e->getMessage()]);
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
        $requiredFields = ['name', 'price'];
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
            $name        = trim($data['name']);
            $price       = (float) $data['price'];
            $description = $data['description'] ?? null;

            $sql  = "INSERT INTO produits (name, description, price) VALUES (:name, :description, :price)";
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([
                ':name'        => $name,
                ':description' => $description,
                ':price'       => $price,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created('Produit ajouté avec succès.', ['id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            error_log('Error creating produit: ' . $e->getMessage());
            Response::error('Erreur lors de la création du produit.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT_ID : Modifier un produit spécifique ---
    'PUT_ID' => function (array $params, ?object $currentUser) use ($pdo) {
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
            Response::badRequest('ID de produit invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['name', 'price']; // ID n'est plus obligatoire dans $data
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
            $name        = trim($data['name']);
            $price       = (float) $data['price'];
            $description = $data['description'] ?? null;

            $sql  = "UPDATE produits SET name = :name, description = :description, price = :price WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([
                ':name'        => $name,
                ':description' => $description,
                ':price'       => $price,
                ':id'          => $id,
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

    // --- Méthode DELETE_ID : Supprimer un produit spécifique ---
    'DELETE_ID' => function (array $params, ?object $currentUser) use ($pdo) {
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
            Response::badRequest('ID de produit invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql  = "DELETE FROM produits WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Produit non trouvé avec l\'ID spécifié.');
                return;
            }

            Response::success('Produit supprimé avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting produit: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression du produit.', 500, ['details' => $e->getMessage()]);
        }
    },
];