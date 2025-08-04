<?php
// backend/api/routes/payments.php

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
    // --- Méthode GET : Récupérer tous les paiements ---
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
            // Inclure les informations sur les clients, utilisateurs et contrats si pertinents
            $stmt = $pdo->query("SELECT p.*, c.name AS customer_name, u.pseudo AS user_name, co.type AS contrat_type
                                FROM payments p
                                LEFT JOIN customers c ON p.customer_id = c.id
                                LEFT JOIN users u ON p.user_id = u.id
                                LEFT JOIN contrats co ON p.contrat_id = co.id
                                ORDER BY p.date_payment DESC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::success('Paiements récupérés avec succès.', $items);
        } catch (PDOException $e) {
            error_log('Error fetching payments: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des paiements.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode GET_ID : Récupérer un paiement spécifique ---
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
            Response::badRequest('ID de paiement invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Inclure les informations sur les clients, utilisateurs et contrats si pertinents
            $stmt = $pdo->prepare("SELECT p.*, c.name AS customer_name, u.pseudo AS user_name, co.type AS contrat_type
                                   FROM payments p
                                   LEFT JOIN customers c ON p.customer_id = c.id
                                   LEFT JOIN users u ON p.user_id = u.id
                                   LEFT JOIN contrats co ON p.contrat_id = co.id
                                   WHERE p.id = :id");
            $stmt->execute([':id' => $id]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                Response::notFound('Paiement non trouvé.');
                return;
            }
            Response::success('Paiement récupéré avec succès.', $payment);
        } catch (PDOException $e) {
            error_log('Error fetching single payment: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération du paiement.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer un nouveau paiement ---
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
        $requiredFields = ['type', 'customer_id', 'amount', 'date_payment'];
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

        // Validation de la date (accepte 'YYYY-MM-DD' ou 'YYYY-MM-DD HH:MM:SS')
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data['date_payment'])) {
            Response::badRequest("Le format de la date est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
            return;
        }

        try {
            // Récupération de l'ID de l'utilisateur authentifié si disponible
            $user_id = $currentUser->id ?? null; 

            $type        = trim($data['type']);
            $customer_id = filter_var($data['customer_id'], FILTER_VALIDATE_INT);
            $amount      = (float) $data['amount'];
            $date_payment        = $data['date_payment'];
            $contrat_id  = filter_var($data['contrat_id'] ?? null, FILTER_VALIDATE_INT);
            $description = $data['description'] ?? null;

            if ($customer_id === false || $customer_id <= 0) {
                Response::badRequest('ID client invalide.');
                return;
            }
            if ($user_id !== null && ($user_id === false || $user_id <= 0)) {
                Response::badRequest('ID utilisateur invalide.');
                return;
            }
            if ($contrat_id !== null && ($contrat_id === false || $contrat_id <= 0)) {
                Response::badRequest('ID contrat invalide.');
                return;
            }


            $sql = "INSERT INTO payments (type, customer_id, user_id, contrat_id, description, amount, date_payment) 
                    VALUES (:type, :customer_id, :user_id, :contrat_id, :description, :amount, :date_payment)";
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([
                ':type'        => $type,
                ':customer_id' => $customer_id,
                ':user_id'     => $user_id,
                ':contrat_id'  => $contrat_id,
                ':description' => $description,
                ':amount'      => $amount,
                ':date_payment'        => $date_payment,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created('Paiement ajouté avec succès.', ['id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            error_log('Error creating payment: ' . $e->getMessage());
            Response::error('Erreur lors de la création du paiement.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT_ID : Modifier un paiement spécifique ---
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
            Response::badRequest('ID de paiement invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['type', 'customer_id', 'amount', 'date_payment'];
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
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data['date_payment'])) {
            Response::badRequest("Le format de la date est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
            return;
        }

        try {
            $type        = trim($data['type']);
            $customer_id = filter_var($data['customer_id'], FILTER_VALIDATE_INT);
            $amount      = (float) $data['amount'];
            $date_payment        = $data['date_payment'];
            $user_id     = filter_var($data['user_id'] ?? null, FILTER_VALIDATE_INT);
            $contrat_id  = filter_var($data['contrat_id'] ?? null, FILTER_VALIDATE_INT);
            $description = $data['description'] ?? null;

            if ($customer_id === false || $customer_id <= 0) {
                Response::badRequest('ID client invalide.');
                return;
            }
            if ($user_id !== null && ($user_id === false || $user_id <= 0)) {
                Response::badRequest('ID utilisateur invalide.');
                return;
            }
            if ($contrat_id !== null && ($contrat_id === false || $contrat_id <= 0)) {
                Response::badRequest('ID contrat invalide.');
                return;
            }

            $sql = "UPDATE payments SET 
                        type = :type, 
                        customer_id = :customer_id, 
                        user_id = :user_id, 
                        contrat_id = :contrat_id, 
                        description = :description, 
                        amount = :amount, 
                        date = :date 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([
                ':type'        => $type,
                ':customer_id' => $customer_id,
                ':user_id'     => $user_id,
                ':contrat_id'  => $contrat_id,
                ':description' => $description,
                ':amount'      => $amount,
                ':date_payment'        => $date_payment,
                ':id'          => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Paiement non trouvé avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Paiement modifié avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating payment: ' . $e->getMessage());
            Response::error('Erreur lors de la modification du paiement.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE_ID : Supprimer un paiement spécifique ---
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
            Response::badRequest('ID de paiement invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql = "DELETE FROM payments WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Paiement non trouvé avec l\'ID spécifié.');
                return;
            }

            Response::success('Paiement supprimé avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting payment: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression du paiement.', 500, ['details' => $e->getMessage()]);
        }
    },
];