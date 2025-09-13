<?php
// backend/api/routes/quittances.php

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
    // --- Méthode GET : Récupérer une ou plusieurs quittances ---
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
        $baseQuery = "SELECT q.*, e.name AS employee_name 
                      FROM quittances q 
                      LEFT JOIN employees e ON q.employee_id = e.id
                      WHERE q.is_active = 1";

        try {
            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID de quittance invalide.');
                    return;
                }
                $stmt = $pdo->prepare("$baseQuery AND q.id = :id");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Quittance non trouvée.');
                } else {
                    Response::success('Quittance récupérée avec succès.', $item);
                }
            } else {
                $stmt = $pdo->query("$baseQuery ORDER BY q.date_emission DESC");
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Quittances récupérées avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('Error fetching quittances: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des quittances.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer une nouvelle quittance ---
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
        $requiredFields = ['employee_id', 'date_paiement', 'montant', 'periode_service', 'numero_quittance', 'date_emission', 'type'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        // Validation de l'ID employé
        $employee_id = filter_var($data['employee_id'], FILTER_VALIDATE_INT);
        if ($employee_id === false || $employee_id <= 0) {
            Response::badRequest('ID employé invalide.');
            return;
        }

        // Validation du montant
        if (!is_numeric($data['montant']) || $data['montant'] <= 0) {
            Response::badRequest("Le champ 'montant' doit être un nombre positif.");
            return;
        }

        // Validation du format des dates (YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS)
        $dateFields = ['date_paiement', 'date_emission'];
        foreach ($dateFields as $field) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data[$field])) {
                Response::badRequest("Le format de la date '{$field}' est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
                return;
            }
        }

        $periode_service = trim($data['periode_service']);
        if (empty($periode_service)) {
            Response::badRequest("Le champ 'periode_service' ne peut pas être vide.");
            return;
        }

        try {
            $montant          = (float) $data['montant'];
            $date_paiement    = $data['date_paiement'];
            $numero_quittance = trim($data['numero_quittance']);
            $date_emission    = $data['date_emission'];
            $type             = trim($data['type']);

            $sql = "INSERT INTO quittances (employee_id, date_paiement, montant, periode_service, numero_quittance, date_emission, type) 
                    VALUES (:employee_id, :date_paiement, :montant, :periode_service, :numero_quittance, :date_emission, :type)";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':employee_id'     => $employee_id,
                ':date_paiement'   => $date_paiement,
                ':montant'         => $montant,
                ':periode_service' => $periode_service,
                ':numero_quittance' => $numero_quittance,
                ':date_emission'   => $date_emission,
                ':type'            => $type,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created(['id' => $pdo->lastInsertId()], 'Quittance créée avec succès.');
        } catch (PDOException $e) {
            error_log('Error creating quittance: ' . $e->getMessage());
            Response::error('Erreur lors de la création de la quittance.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT : Modifier une quittance spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour modifier une ressource.');
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de quittance invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['employee_id', 'date_paiement', 'montant', 'periode_service', 'numero_quittance', 'date_emission', 'type'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation de l'ID employé
        $employee_id = filter_var($data['employee_id'], FILTER_VALIDATE_INT);
        if ($employee_id === false || $employee_id <= 0) {
            Response::badRequest('ID employé invalide.');
            return;
        }

        // Validation du montant
        if (!is_numeric($data['montant']) || $data['montant'] <= 0) {
            Response::badRequest("Le champ 'montant' doit être un nombre positif.");
            return;
        }

        // Validation du format des dates
        $dateFields = ['date_paiement', 'date_emission'];
        foreach ($dateFields as $field) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data[$field])) {
                Response::badRequest("Le format de la date '{$field}' est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
                return;
            }
        }

        // Validation de periode_service
        $periode_service = trim($data['periode_service']);
        if (empty($periode_service)) {
            Response::badRequest("Le champ 'periode_service' ne peut pas être vide.");
            return;
        }

        try {
            $montant          = (float) $data['montant'];
            $date_paiement    = $data['date_paiement'];
            $numero_quittance = trim($data['numero_quittance']);
            $date_emission    = $data['date_emission'];
            $type             = trim($data['type']);

            $sql = "UPDATE quittances SET 
                        employee_id = :employee_id, 
                        date_paiement = :date_paiement, 
                        montant = :montant, 
                        periode_service = :periode_service, 
                        numero_quittance = :numero_quittance, 
                        date_emission = :date_emission, 
                        type = :type 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':employee_id'     => $employee_id,
                ':date_paiement'   => $date_paiement,
                ':montant'         => $montant,
                ':periode_service' => $periode_service,
                ':numero_quittance' => $numero_quittance,
                ':date_emission'   => $date_emission,
                ':type'            => $type,
                ':id'              => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Quittance non trouvée avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Quittance modifiée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating quittance: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de la quittance.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE : Supprimer une quittance spécifique ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour supprimer une ressource.');
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID de quittance invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $sql = "UPDATE quittances SET is_active = 0 WHERE id = :id";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([':id' => $id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Quittance non trouvée avec l\'ID spécifié.');
                return;
            }

            Response::success('Quittance supprimée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting quittance: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression de la quittance.', 500, ['details' => $e->getMessage()]);
        }
    },
];
