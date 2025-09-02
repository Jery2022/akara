<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php';

use Core\Response; // Utilisation de la classe Response pour les retours cohérents
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Headers CORS. Idéalement, gérés par un middleware dans le routeur principal.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, GET_ID, POST, PUT, PUT_ID, DELETE, DELETE_ID, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$pdo = getPDO();

return [
    // --- Méthode GET : Récupérer un ou plusieurs achats ---
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
        $sort = $_GET['sort'] ?? 'date_achat';
        $order = $_GET['order'] ?? 'desc';

        $allowedSortColumns = ['name', 'type', 'amount', 'date_achat', 'category', 'status'];
        if (!in_array($sort, $allowedSortColumns)) {
            $sort = 'date_achat';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        try {
            if ($id) {
                if (!is_numeric($id) || $id <= 0) {
                    Response::badRequest('ID d\'achat invalide.');
                    return;
                }
                $stmt = $pdo->prepare("SELECT * FROM achats WHERE id = :id");
                $stmt->execute([':id' => $id]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$item) {
                    Response::notFound('Achat non trouvé.');
                } else {
                    Response::success('Achat récupéré avec succès.', $item);
                }
            } else {
                $sql = "SELECT * FROM achats WHERE is_active = 1";
                $queryParams = [];
                if (!empty($search)) {
                    $sql .= " AND (name LIKE :search OR type LIKE :search OR category LIKE :search OR description LIKE :search)";
                    $queryParams[':search'] = '%' . $search . '%';
                }
                $sql .= " ORDER BY $sort $order";

                $stmt = $pdo->prepare($sql);
                $stmt->execute($queryParams);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
                Response::success('Achats récupérés avec succès.', $items);
            }
        } catch (PDOException $e) {
            error_log('Error fetching achats: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des achats.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer un nouvel achat ---
    'POST' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour créer une ressource.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['type', 'amount', 'date_achat', 'category'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] < 0) {
            Response::badRequest("Le montant doit être un nombre positif.");
            return;
        }

        try {
            $user_id = $currentUser->id ?? null;

            $sql = "INSERT INTO achats (name, type, amount, date_achat, category, user_id, supplier_id, contrat_id, description, status) 
                    VALUES (:name, :type, :amount, :date_achat, :category, :user_id, :supplier_id, :contrat_id, :description, :status)";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':name'        => $data['name'] ?? null,
                ':type'        => $data['type'],
                ':amount'      => $data['amount'],
                ':date_achat'  => $data['date_achat'],
                ':category'    => $data['category'],
                ':user_id'     => $user_id,
                ':supplier_id' => $data['supplier_id'] ?? null,
                ':contrat_id'  => $data['contrat_id'] ?? null,
                ':description' => $data['description'] ?? null,
                ':status'      => $data['status'] ?? 'en attente',
            ]);

            Response::created(['id' => $pdo->lastInsertId()], 'Achat ajouté avec succès.');
        } catch (PDOException $e) {
            error_log('Error creating achat: ' . $e->getMessage());
            Response::error('Erreur lors de la création de l\'achat.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT : Modifier un achat spécifique ---
    'PUT' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour modifier une ressource.');
            return;
        }

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'achat invalide.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $requiredFields = ['type', 'amount', 'date_achat', 'category'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] < 0) {
            Response::badRequest("Le montant doit être un nombre positif.");
            return;
        }

        try {
            $user_id = $currentUser->id ?? null;

            $sql = "UPDATE achats SET 
                        name = :name, type = :type, amount = :amount, date_achat = :date_achat, category = :category, 
                        user_id = :user_id, supplier_id = :supplier_id, contrat_id = :contrat_id, description = :description, status = :status 
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':name'        => $data['name'] ?? null,
                ':type'        => $data['type'],
                ':amount'      => $data['amount'],
                ':date_achat'  => $data['date_achat'],
                ':category'    => $data['category'],
                ':user_id'     => $user_id,
                ':supplier_id' => $data['supplier_id'] ?? null,
                ':contrat_id'  => $data['contrat_id'] ?? null,
                ':description' => $data['description'] ?? null,
                ':status'      => $data['status'] ?? 'en attente',
                ':id'          => $id,
            ]);

            if ($stmt->rowCount() === 0) {
                Response::notFound('Achat non trouvé ou aucune modification effectuée.');
                return;
            }

            Response::success('Achat modifié avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating achat: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de l\'achat.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE : Supprimer un achat spécifique ---
    'DELETE' => function (array $params, ?object $currentUser) use ($pdo) {
        if (!$currentUser) {
            Response::unauthorized('Accès non autorisé', 'Vous devez vous authentifier pour supprimer une ressource.');
            return;
        }

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'achat invalide.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            $stmt = $pdo->prepare("UPDATE achats SET is_active = 0 WHERE id = :id");
            $stmt->execute([':id' => $id]);

            if ($stmt->rowCount() === 0) {
                Response::notFound('Achat non trouvé.');
                return;
            }

            Response::success('Achat supprimé avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting achat: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression de l\'achat.', 500, ['details' => $e->getMessage()]);
        }
    },
];
