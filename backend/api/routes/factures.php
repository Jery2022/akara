<?php
// backend/api/routes/factures.php

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
    // --- Méthode GET : Récupérer une ou plusieurs factures ---
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
        $search = $_GET['search'] ?? '';
        $sort = $_GET['sort'] ?? 'date_facture';
        $order = $_GET['order'] ?? 'desc';

        $allowedSortColumns = ['customer_id', 'date_facture', 'amount_ttc', 'status'];
        if (!in_array($sort, $allowedSortColumns)) {
            $sort = 'date_facture';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        $baseQuery = "SELECT f.*, c.name AS customer_id
                      FROM factures f 
                      LEFT JOIN customers c ON f.customer_id = c.id
                      WHERE f.is_active = 1";

        try {
            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID de facture invalide.');
                    return;
                }
                $stmt = $pdo->prepare("$baseQuery AND f.id = :id");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Facture non trouvée.');
                } else {
                    Response::success('Facture récupérée avec succès.', $item);
                }
            } else {
                $sql = $baseQuery;
                $queryParams = [];
                if (!empty($search)) {
                    $sql .= " AND (c.name LIKE :search OR f.status LIKE :search OR f.amount_ttc LIKE :search)";
                    $queryParams[':search'] = '%' . $search . '%';
                }
                $sql .= " ORDER BY $sort $order";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($queryParams);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Factures récupérées avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('Error fetching invoices: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des factures.', 500, ['details' => $e->getMessage()]);
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
        $requiredFields = ['customer_id', 'date_facture', 'amount_total', 'amount_ttc', 'status', 'avance_status'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || ($data[$field] === '' && !in_array($field, ['amount_tva', 'amount_css']))) {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        // Validation des montants
        $amountFields = ['amount_total', 'amount_tva', 'amount_css', 'amount_ttc'];
        foreach ($amountFields as $field) {
            if (isset($data[$field]) && (!is_numeric($data[$field]) || $data[$field] < 0)) {
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


        // Validation du format de la date (YYYY-MM-DD)
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_facture'])) {
            Response::badRequest("Le format de la date de facture est invalide. Utilisez YYYY-MM-DD.");
            return;
        }

        // Validation des statuts
        $valid_statuses = ['payée', 'en attente', 'annulée'];
        if (!in_array($data['status'], $valid_statuses)) {
            Response::badRequest("Le statut '{$data['status']}' est invalide.");
            return;
        }
        $valid_avance_statuses = ['oui', 'non'];
        if (!in_array($data['avance_status'], $valid_avance_statuses)) {
            Response::badRequest("Le statut d'avance '{$data['avance_status']}' est invalide.");
            return;
        }


        try {
            $date_facture = $data['date_facture'];
            $amount_total = (float) $data['amount_total'];
            $amount_tva   = (float) ($data['amount_tva'] ?? 0.00);
            $amount_css   = (float) ($data['amount_css'] ?? 0.00);
            $amount_ttc   = (float) $data['amount_ttc'];
            $status       = trim($data['status']);
            $avance_status = trim($data['avance_status']);

            $sql = "INSERT INTO factures (customer_id, date_facture, amount_total, amount_tva, amount_css, amount_ttc, status, avance_status) 
                    VALUES (:customer_id, :date_facture, :amount_total, :amount_tva, :amount_css, :amount_ttc, :status, :avance_status)";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':customer_id'  => $customer_id,
                ':date_facture' => $date_facture,
                ':amount_total' => $amount_total,
                ':amount_tva'   => $amount_tva,
                ':amount_css'   => $amount_css,
                ':amount_ttc'   => $amount_ttc,
                ':status'       => $status,
                ':avance_status' => $avance_status,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created(['id' => $pdo->lastInsertId()], 'Facture créée avec succès.');
        } catch (PDOException $e) {
            error_log('Error creating invoice: ' . $e->getMessage());
            Response::error('Erreur lors de la création de la facture.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT : Modifier une facture spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour modifier une ressource.');
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
        $requiredFields = ['customer_id', 'date_facture', 'amount_total', 'amount_ttc', 'status', 'avance_status'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || ($data[$field] === '' && !in_array($field, ['amount_tva', 'amount_css']))) {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation des montants
        $amountFields = ['amount_total', 'amount_tva', 'amount_css', 'amount_ttc'];
        foreach ($amountFields as $field) {
            if (isset($data[$field]) && (!is_numeric($data[$field]) || $data[$field] < 0)) {
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
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_facture'])) {
            Response::badRequest("Le format de la date de facture est invalide. Utilisez YYYY-MM-DD.");
            return;
        }

        // Validation des statuts
        $valid_statuses = ['payée', 'en attente', 'annulée'];
        if (!in_array($data['status'], $valid_statuses)) {
            Response::badRequest("Le statut '{$data['status']}' est invalide.");
            return;
        }
        $valid_avance_statuses = ['oui', 'non'];
        if (!in_array($data['avance_status'], $valid_avance_statuses)) {
            Response::badRequest("Le statut d'avance '{$data['avance_status']}' est invalide.");
            return;
        }

        try {
            $date_facture = $data['date_facture'];
            $amount_total = (float) $data['amount_total'];
            $amount_tva   = (float) ($data['amount_tva'] ?? 0.00);
            $amount_css   = (float) ($data['amount_css'] ?? 0.00);
            $amount_ttc   = (float) $data['amount_ttc'];
            $status       = trim($data['status']);
            $avance_status = trim($data['avance_status']);

            $sql = "UPDATE factures SET 
                        customer_id = :customer_id, 
                        date_facture = :date_facture, 
                        amount_total = :amount_total, 
                        amount_tva = :amount_tva, 
                        amount_css = :amount_css, 
                        amount_ttc = :amount_ttc, 
                        status = :status,
                        avance_status = :avance_status
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
                ':avance_status' => $avance_status,
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

    // --- Méthode DELETE : Supprimer une facture spécifique ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour supprimer une ressource.');
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
            $sql = "UPDATE factures SET is_active = 0 WHERE id = :id";
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
