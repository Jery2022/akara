<?php
// backend/api/routes/ventes.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour Core\Response, et JWT si utilisé globalement

use Core\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Headers CORS. Idéalement, gérés par un middleware dans le routeur principal.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000"); // Adaptez à votre frontend
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Ajout de OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

$pdo = getPDO();

return [
    // --- Méthode GET : Récupérer toutes les ventes ---
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
            // Informations sur les utilisateurs, clients et contrats  
            $stmt = $pdo->query("SELECT v.*, u.pseudo AS user_name, c.name AS customer_name, co.type AS contrat_type 
                                FROM ventes v
                                LEFT JOIN users u ON v.user_id = u.id
                                LEFT JOIN customers c ON v.customer_id = c.id
                                LEFT JOIN contrats co ON v.contrat_id = co.id
                                ORDER BY v.date_vente DESC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::success('Ventes récupérées avec succès.', $items);
        } catch (PDOException $e) {
            error_log('Error fetching ventes: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des ventes.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode GET_ID : Récupérer une vente spécifique ---
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
            Response::badRequest('ID de vente invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Informations sur les utilisateurs, clients et contrats 
            $stmt = $pdo->prepare("SELECT v.*, u.pseudo AS user_name, c.name AS customer_name, co.type AS contrat_type
                                   FROM ventes v
                                   LEFT JOIN users u ON v.user_id = u.id
                                   LEFT JOIN customers c ON v.customer_id = c.id
                                   LEFT JOIN contrats co ON v.contrat_id = co.id
                                   WHERE v.id = :id");
            $stmt->execute([':id' => $id]);
            $vente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$vente) {
                Response::notFound('Vente non trouvée.');
                return;
            }
            Response::success('Vente récupérée avec succès.', $vente);
        } catch (PDOException $e) {
            error_log('Error fetching single vente: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération de la vente.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer une nouvelle vente ---
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
        $requiredFields = ['type', 'amount', 'date_vente', 'category'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        // Validation du montant
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            Response::badRequest("Le montant doit être un nombre positif.");
            return;
        }

        // Validation de la date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data['date_vente'])) {
            Response::badRequest("Le format de la date de vente est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
            return;
        }

        try {
            // Récupérer l'ID de l'utilisateur authentifié du token
            $user_id = $currentUser->id ?? null;

            $type        = trim($data['type']);
            $amount      = (float) $data['amount'];
            $date_vente  = $data['date_vente'];
            $category    = trim($data['category']);
            $customer_id = filter_var($data['customer_id'] ?? null, FILTER_VALIDATE_INT);
            $contrat_id  = filter_var($data['contrat_id'] ?? null, FILTER_VALIDATE_INT);
            $description = $data['description'] ?? null;

            $sql = "INSERT INTO ventes (type, amount, date_vente, category, user_id, customer_id, contrat_id, description) 
                    VALUES (:type, :amount, :date_vente, :category, :user_id, :customer_id, :contrat_id, :description)";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':type'        => $type,
                ':amount'      => $amount,
                ':date_vente'  => $date_vente,
                ':category'    => $category,
                ':user_id'     => $user_id,
                ':customer_id' => $customer_id,
                ':contrat_id'  => $contrat_id,
                ':description' => $description,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            $lastId = $pdo->lastInsertId();
            if ($lastId === false) {
                Response::error('Erreur lors de la récupération de l\'ID de la nouvelle vente.', 500);
                return;
            }

            Response::created(['id' => $lastId], 'Vente ajoutée avec succès.');
        } catch (PDOException $e) {
            error_log('Error creating vente: ' . $e->getMessage());
            Response::error('Erreur lors de la création de la vente.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT_ID : Modifier une vente spécifique ---
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
            Response::badRequest('ID de vente invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['type', 'amount', 'date_vente', 'category'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation du montant
        if (!is_numeric($data['amount']) || $data['amount'] <= 0) {
            Response::badRequest("Le montant doit être un nombre positif.");
            return;
        }

        // Validation de la date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data['date_vente'])) {
            Response::badRequest("Le format de la date de vente est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
            return;
        }

        try {
            $type        = trim($data['type']);
            $amount      = (float) $data['amount'];
            $date_vente  = $data['date_vente'];
            $category    = trim($data['category']);
            $user_id     = filter_var($data['user_id'] ?? null, FILTER_VALIDATE_INT);
            $customer_id = filter_var($data['customer_id'] ?? null, FILTER_VALIDATE_INT);
            $contrat_id  = filter_var($data['contrat_id'] ?? null, FILTER_VALIDATE_INT);
            $description = $data['description'] ?? null;

            $sql = "UPDATE ventes SET 
                        type = :type, 
                        amount = :amount, 
                        date_vente = :date_vente, 
                        category = :category, 
                        user_id = :user_id, 
                        customer_id = :customer_id, 
                        contrat_id = :contrat_id, 
                        description = :description 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':type'        => $type,
                ':amount'      => $amount,
                ':date_vente'  => $date_vente,
                ':category'    => $category,
                ':user_id'     => $user_id,
                ':customer_id' => $customer_id,
                ':contrat_id'  => $contrat_id,
                ':description' => $description,
                ':id'          => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Vente non trouvée avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Vente modifiée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating vente: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de la vente.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE_ID : Supprimer une vente spécifique ---
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
            Response::badRequest('ID de vente invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql = "DELETE FROM ventes WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Vente non trouvée avec l\'ID spécifié.');
                return;
            }

            Response::success('Vente supprimée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting vente: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression de la vente.', 500, ['details' => $e->getMessage()]);
        }
    },
];
