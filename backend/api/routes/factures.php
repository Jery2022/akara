<?php
// backend/api/routes/factures.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour Core\Response

use Core\Response;

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
    // --- Méthode GET : Récupérer toutes les factures ---
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
            // Joindre avec la table 'customers' pour obtenir le nom du client
            $stmt = $pdo->query("SELECT f.*, c.name AS customer_name 
                                FROM factures f 
                                LEFT JOIN customers c ON f.customer_id = c.id
                                ORDER BY f.date_facture DESC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::success('Factures récupérées avec succès.', $items);
        } catch (PDOException $e) {
            error_log('Error fetching invoices: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des factures.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode GET_ID : Récupérer une facture spécifique ---
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
            Response::badRequest('ID de facture invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Joindre avec la table 'customers' pour obtenir le nom du client
            $stmt = $pdo->prepare("SELECT f.*, c.name AS customer_name 
                                   FROM factures f 
                                   LEFT JOIN customers c ON f.customer_id = c.id
                                   WHERE f.id = :id");
            $stmt->execute([':id' => $id]);
            $invoice = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$invoice) {
                Response::notFound('Facture non trouvée.');
                return;
            }
            Response::success('Facture récupérée avec succès.', $invoice);
        } catch (PDOException $e) {
            error_log('Error fetching single invoice: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération de la facture.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer une nouvelle facture ---
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
        $requiredFields = ['customer_id', 'date_facture', 'amount_total', 'amount_tva', 'amount_css', 'amount_ttc', 'status'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        // Validation des montants
        $amountFields = ['amount_total', 'amount_tva', 'amount_css', 'amount_ttc'];
        foreach ($amountFields as $field) {
            if (!is_numeric($data[$field]) || $data[$field] < 0) { // Les montants peuvent être 0, mais pas négatifs
                Response::badRequest("Le champ '{$field}' doit être un nombre positif ou nul.");
                return;
            }
        }
        
        // Validation de l'ID client
        $customer_id = filter_var($data['customer_id'], FILTER_VALIDATE_INT);
        if ($customer_id === false || $customer_id <= 0) {
            Response::badRequest('ID client invalide.');
            return;
        }

        // Validation du format de la date (YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data['date_facture'])) {
            Response::badRequest("Le format de la date de facture est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
            return;
        }

        try {
            $date_facture = $data['date_facture'];
            $amount_total = (float) $data['amount_total'];
            $amount_tva   = (float) $data['amount_tva'];
            $amount_css   = (float) $data['amount_css'];
            $amount_ttc   = (float) $data['amount_ttc'];
            $status       = trim($data['status']);

            $sql = "INSERT INTO factures (customer_id, date_facture, amount_total, amount_tva, amount_css, amount_ttc, status) 
                    VALUES (:customer_id, :date_facture, :amount_total, :amount_tva, :amount_css, :amount_ttc, :status)";
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([
                ':customer_id'  => $customer_id,
                ':date_facture' => $date_facture,
                ':amount_total' => $amount_total,
                ':amount_tva'   => $amount_tva,
                ':amount_css'   => $amount_css,
                ':amount_ttc'   => $amount_ttc,
                ':status'       => $status,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created('Facture créée avec succès.', ['id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            error_log('Error creating invoice: ' . $e->getMessage());
            Response::error('Erreur lors de la création de la facture.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT_ID : Modifier une facture spécifique ---
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
            Response::badRequest('ID de facture invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['customer_id', 'date_facture', 'amount_total', 'amount_tva', 'amount_css', 'amount_ttc', 'status'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation des montants
        $amountFields = ['amount_total', 'amount_tva', 'amount_css', 'amount_ttc'];
        foreach ($amountFields as $field) {
            if (!is_numeric($data[$field]) || $data[$field] < 0) {
                Response::badRequest("Le champ '{$field}' doit être un nombre positif ou nul.");
                return;
            }
        }

        // Validation de l'ID client
        $customer_id = filter_var($data['customer_id'], FILTER_VALIDATE_INT);
        if ($customer_id === false || $customer_id <= 0) {
            Response::badRequest('ID client invalide.');
            return;
        }

        // Validation du format de la date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data['date_facture'])) {
            Response::badRequest("Le format de la date de facture est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
            return;
        }

        try {
            $date_facture = $data['date_facture'];
            $amount_total = (float) $data['amount_total'];
            $amount_tva   = (float) $data['amount_tva'];
            $amount_css   = (float) $data['amount_css'];
            $amount_ttc   = (float) $data['amount_ttc'];
            $status       = trim($data['status']);

            $sql = "UPDATE factures SET 
                        customer_id = :customer_id, 
                        date_facture = :date_facture, 
                        amount_total = :amount_total, 
                        amount_tva = :amount_tva, 
                        amount_css = :amount_css, 
                        amount_ttc = :amount_ttc, 
                        status = :status 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([
                ':customer_id'  => $customer_id,
                ':date_facture' => $date_facture,
                ':amount_total' => $amount_total,
                ':amount_tva'   => $amount_tva,
                ':amount_css'   => $amount_css,
                ':amount_ttc'   => $amount_ttc,
                ':status'       => $status,
                ':id'           => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Facture non trouvée avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Facture modifiée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating invoice: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de la facture.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE_ID : Supprimer une facture spécifique ---
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
            Response::badRequest('ID de facture invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql = "DELETE FROM factures WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Facture non trouvée avec l\'ID spécifié.');
                return;
            }

            Response::success('Facture supprimée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting invoice: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression de la facture.', 500, ['details' => $e->getMessage()]);
        }
    },
];