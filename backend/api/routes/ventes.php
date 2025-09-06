<?php
// backend/api/routes/ventes.php

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour Core\Response, et JWT si utilisé globalement

use Core\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No Content
    exit;
}

$pdo = getPDO();

return [
    // --- Méthode GET : Récupérer une ou plusieurs ventes ---
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
        $sort = $_GET['sort'] ?? 'date_vente';
        $order = $_GET['order'] ?? 'desc';

        $allowedSortColumns = ['name', 'type', 'amount', 'date_vente', 'category', 'customer_name'];
        if (!in_array($sort, $allowedSortColumns)) {
            $sort = 'date_vente';
        }
        // Pour trier par un champ d'une table jointe, il faut préfixer
        $sortColumn = $sort === 'customer_name' ? 'c.name' : 'v.' . $sort;

        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        $baseQuery = "SELECT v.*, u.pseudo AS user_name, c.name AS customer_name, co.type AS contrat_type 
                      FROM ventes v
                      LEFT JOIN users u ON v.user_id = u.id
                      LEFT JOIN customers c ON v.customer_id = c.id
                      LEFT JOIN contrats co ON v.contrat_id = co.id";

        try {
            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID de vente invalide.');
                    return;
                }
                $stmt = $pdo->prepare("$baseQuery WHERE v.id = :id");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Vente non trouvée.');
                } else {
                    Response::success('Vente récupérée avec succès.', $item);
                }
            } else {
                $sql = $baseQuery . " WHERE v.is_active = 1";
                $queryParams = [];
                if (!empty($search)) {
                    $sql .= " AND (v.name LIKE :search OR v.type LIKE :search OR v.category LIKE :search OR v.description LIKE :search OR c.name LIKE :search)";
                    $queryParams[':search'] = '%' . $search . '%';
                }
                $sql .= " ORDER BY $sortColumn $order";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($queryParams);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Ventes récupérées avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('Error fetching ventes: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des ventes.', 500, ['details' => $e->getMessage()]);
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
        $amount = filter_var($data['amount'], FILTER_VALIDATE_FLOAT);
        if ($amount === false || $amount <= 0) {
            Response::badRequest("Le montant doit être un nombre positif.");
            return;
        }

        // Validation de la date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data['date_vente'])) {
            Response::badRequest("Le format de la date de vente est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
            return;
        }

        // Validation des IDs optionnels
        $customer_id = null;
        if (isset($data['customer_id']) && $data['customer_id'] !== '') {
            $customer_id = filter_var($data['customer_id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
            if ($customer_id === false) {
                Response::badRequest("L'ID du client doit être un entier positif.");
                return;
            }
        }
        $contrat_id = null;
        if (isset($data['contrat_id']) && $data['contrat_id'] !== '') {
            $contrat_id = filter_var($data['contrat_id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
            if ($contrat_id === false) {
                Response::badRequest("L'ID du contrat doit être un entier positif.");
                return;
            }
        }

        try {
            // Récupérer l'ID de l'utilisateur authentifié du token
            $user_id = $currentUser->id ?? null;

            $name        = isset($data['name']) ? trim($data['name']) : null;
            $type        = trim($data['type']);
            $date_vente  = $data['date_vente'];
            $category    = trim($data['category']);
            $description = isset($data['description']) ? trim($data['description']) : null;

            $sql = "INSERT INTO ventes (name, type, amount, date_vente, category, user_id, customer_id, contrat_id, description) 
                    VALUES (:name, :type, :amount, :date_vente, :category, :user_id, :customer_id, :contrat_id, :description)";
            $stmt = $pdo->prepare($sql);

            $executed = $stmt->execute([
                ':name'        => $name,
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

    // --- Méthode PUT : Modifier une vente spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour modifier une ressource.');
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

        // Vérifier si la vente existe avant de tenter la mise à jour
        try {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM ventes WHERE id = :id");
            $checkStmt->execute([':id' => $id]);
            if ((int)$checkStmt->fetchColumn() === 0) {
                Response::notFound('Vente non trouvée avec l\'ID spécifié.');
                return;
            }
        } catch (PDOException $e) {
            error_log('Erreur lors de la vérification de la vente: ' . $e->getMessage());
            Response::error('Erreur lors de la vérification de la vente.', 500, ['details' => $e->getMessage()]);
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
        $amount = filter_var($data['amount'], FILTER_VALIDATE_FLOAT);
        if ($amount === false || $amount <= 0) {
            Response::badRequest("Le montant doit être un nombre positif.");
            return;
        }

        // Validation de la date
        if (!preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}:\d{2})?$/', $data['date_vente'])) {
            Response::badRequest("Le format de la date de vente est invalide. Utilisez YYYY-MM-DD ou YYYY-MM-DD HH:MM:SS.");
            return;
        }

        // Validation des IDs optionnels
        $customer_id = null;
        if (isset($data['customer_id']) && $data['customer_id'] !== '') {
            $customer_id = filter_var($data['customer_id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
            if ($customer_id === false) {
                Response::badRequest("L'ID du client doit être un entier positif.");
                return;
            }
        }
        $contrat_id = null;
        if (isset($data['contrat_id']) && $data['contrat_id'] !== '') {
            $contrat_id = filter_var($data['contrat_id'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
            if ($contrat_id === false) {
                Response::badRequest("L'ID du contrat doit être un entier positif.");
                return;
            }
        }

        try {
            $user_id = $currentUser->id ?? null; // Utiliser l'utilisateur courant pour la mise à jour
            $name        = isset($data['name']) ? trim($data['name']) : null;
            $type        = trim($data['type']);
            $date_vente  = $data['date_vente'];
            $category    = trim($data['category']);
            $description = isset($data['description']) ? trim($data['description']) : null;

            $sql = "UPDATE ventes SET 
                        name = :name,
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
                ':name'        => $name,
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

    // --- Méthode DELETE : Supprimer une vente spécifique ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        // Vérification de l'authentification
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour supprimer une ressource.');
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
            $sql = "UPDATE ventes SET is_active = 0 WHERE id = :id";
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
