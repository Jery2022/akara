<?php
// backend/api/routes/customers.php

// Désactiver l'affichage des erreurs pour éviter l'output HTML non JSON
ini_set('display_errors', 'Off');
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour JWT et Core\Response

use Core\Response; // Importation de la classe Response
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Headers CORS. Idéalement, gérés par un middleware dans le routeur principal.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

$pdo = getPDO();

if (! $pdo) {
    Response::error('Échec de la connexion à la base de données.', 500);
    return;
}

/**
 * Vérifie l'authentification via JWT.
 * @return object|null L'objet décodé du JWT si authentifié, null sinon.
 */
function isAuthenticated(): ?object
{
    $headers = getallheaders();
    if (! isset($headers['Authorization'])) {
        return null; // Retourne null si le header n'est pas présent
    }

    $authHeader = $headers['Authorization'];
    if (strpos($authHeader, 'Bearer ') !== 0) {
        return null; // Retourne null si le format Bearer est incorrect
    }

    $jwt        = trim(str_replace('Bearer ', '', $authHeader));
    $secret_key = env('JWT_SECRET_KEY');

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        error_log('JWT Decoding Error: ' . $e->getMessage()); // Log l'erreur pour le débogage
        return null;                                          // Retourne null en cas d'erreur de décodage
    }
}

return [
    'GET'       => function () use ($pdo) {
        $currentUser = isAuthenticated();

        if (! $currentUser) {
            Response::unauthorized(
                'Accès non autorisé', 'Vous devez vous authentifier pour accéder à cette ressource.');
            return;
        }

        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $stmt  = $pdo->query("SELECT * FROM customers ORDER BY name ASC");
            $items  = $stmt ->fetchAll(PDO::FETCH_ASSOC);
            Response::success('Clients récupérés avec succès.', $items);
        } catch (PDOException $e) {
            error_log('Error fetching customers: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des clients.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode GET_ID : Récupérer un client spécifique ---
    'GET_ID'    => function (array $params) use ($pdo) {
        $currentUser = isAuthenticated();
        if (! $currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour accéder à cette ressource.',
            );
            return;
        }

        $id = $params['id'] ?? null;
        if (! is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de client invalide ou manquant dans l\'URL.');
            return;
        }

        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Utilisation d'un paramètre nommé pour la cohérence
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $customer) {
                Response::notFound('Client non trouvé.');
                return;
            }
            Response::success('Client récupéré avec succès.', $customer);
        } catch (PDOException $e) {
            error_log('Error fetching single customer: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération du client.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer un nouveau client ---
    'POST'      => function () use ($pdo) {
        $currentUser = isAuthenticated();
        if (! $currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour créer une ressource.'
            );
            return;
        }

        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires
        $requiredFields = ['name', 'refContact', 'phone', 'email'];
        foreach ($requiredFields as $field) {
            if (! isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        // Validation spécifique de l'email
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::badRequest('Email invalide.');
            return;
        }

        // Validation du téléphone (simple regex pour des chiffres, espaces, tirets, parenthèses, plus)
        if (! preg_match('/^[0-9\s\-\(\)\+]+$/', $data['phone'])) {
            Response::badRequest('Numéro de téléphone invalide.');
            return;
        }
        try {
                                // Gestion améliorée de contrat_id
            $contrat_id = null; // Initialiser à null par défaut
            if (isset($data['contrat_id']) && $data['contrat_id'] !== '') {
                $filtered_contrat_id = filter_var($data['contrat_id'], FILTER_VALIDATE_INT);
                if ($filtered_contrat_id === false || $filtered_contrat_id <= 0) {
                    Response::badRequest("Le champ 'contrat_id' doit être un ID valide (entier positif) s'il est fourni.");
                    return;
                }
                $contrat_id = $filtered_contrat_id;
            }

            $sql = "INSERT INTO customers (name, refContact, phone, email, contrat_id)
                    VALUES (:name, :refContact, :phone, :email, :contrat_id)";

            $stmt = $pdo->prepare($sql);
            if (! $stmt) {
                Response::error('Erreur lors de la préparation de la requête.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $executed = $stmt->execute([
                ':name'       => $data['name'],
                ':refContact' => $data['refContact'],
                ':phone'      => $data['phone'],
                ':email'      => $data['email'],
                ':contrat_id' => $contrat_id,
            ]);

            if (! $executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created('Client ajouté avec succès.');

        } catch (PDOException $e) {
            error_log('Error creating customer: ' . $e->getMessage());
                                             // Gestion plus spécifique pour les erreurs de clé étrangère
            if ($e->getCode() === '23000') { // SQLSTATE for Integrity Constraint Violation
                Response::error('Impossible d\'ajouter le client : l\'ID du contrat spécifié n\'existe pas.', 409, ['details' => $e->getMessage()]);
            } else {
                Response::error('Erreur lors de la création du client.', 500, ['details' => $e->getMessage()]);
            }
        }
    },

    // --- Méthode PUT_ID : Modifier un client spécifique ---
    'PUT_ID'    => function (array $params) use ($pdo) {
        $currentUser = isAuthenticated();
        if (! $currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour modifier une ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (! is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de client invalide ou manquant dans l\'URL.');
            return;
        }

        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['name', 'refContact', 'phone', 'email'];
        foreach ($requiredFields as $field) {
            if (! isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation spécifique de l'email
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::badRequest('Email invalide.');
            return;
        }

        // Validation du téléphone
        if (! preg_match('/^[0-9\s\-\(\)\+]+$/', $data['phone'])) {
            Response::badRequest('Numéro de téléphone invalide.');
            return;
        }

        try {
                                // Gestion améliorée de contrat_id pour la mise à jour
            $contrat_id = null; // Initialiser à null par défaut
            if (isset($data['contrat_id']) && $data['contrat_id'] !== '') {
                $filtered_contrat_id = filter_var($data['contrat_id'], FILTER_VALIDATE_INT);
                if ($filtered_contrat_id === false || $filtered_contrat_id <= 0) {
                    Response::badRequest("Le champ 'contrat_id' doit être un ID valide (entier positif) s'il est fourni.");
                    return;
                }
                $contrat_id = $filtered_contrat_id;
            }

            $sql = "UPDATE customers SET
                        name = :name,
                        refContact = :refContact,
                        phone = :phone,
                        email = :email,
                        contrat_id = :contrat_id
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);
            if (! $stmt) {
                Response::error('Erreur lors de la préparation de la requête.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $executed = $stmt->execute([
                ':name'       => $data['name'],
                ':refContact' => $data['refContact'],
                ':phone'      => $data['phone'],
                ':email'      => $data['email'],
                ':contrat_id' => $contrat_id,
                ':id'         => $id,
            ]);

            if (! $executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Client non trouvé avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Client modifié avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating customer: ' . $e->getMessage());
                                             // Ajout d'une gestion plus spécifique pour les erreurs de clé étrangère
            if ($e->getCode() === '23000') { // SQLSTATE for Integrity Constraint Violation
                Response::error('Impossible de modifier le client : l\'ID du contrat spécifié n\'existe pas.', 409, ['details' => $e->getMessage()]);
            } else {
                Response::error('Erreur lors de la modification du client.', 500, ['details' => $e->getMessage()]);
            }
        }
    },

    // --- Méthode DELETE_ID : Supprimer un client spécifique ---
    'DELETE_ID' => function (array $params) use ($pdo) {
        $currentUser = isAuthenticated();
        if (! $currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour supprimer une ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (! is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de client invalide ou manquant dans l\'URL.');
            return;
        }

        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Utilisation d'un paramètre nommé pour la cohérence
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id = :id");
            if (! $stmt) {
                Response::error('Erreur lors de la préparation de la requête de suppression.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $executed = $stmt->execute([':id' => $id]);

            if (! $executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Client non trouvé avec l\'ID spécifié.');
                return;
            }

            Response::success('Client supprimé avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting customer: ' . $e->getMessage());
                                             // Ajout d'une gestion plus spécifique pour les erreurs de clé étrangère
            if ($e->getCode() === '23000') { // SQLSTATE for Integrity Constraint Violation
                Response::error('Impossible de supprimer ce client car il est lié à d\'autres enregistrements (ex: ventes, contrats).', 409, ['details' => $e->getMessage()]);
            } else {
                Response::error('Erreur lors de la suppression du client.', 500, ['details' => $e->getMessage()]);
            }
        }
    },
];
