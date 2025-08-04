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

/**
 * Vérifie l'authentification via JWT.
 * @return object|null L'objet décodé du JWT si authentifié, null sinon.
 */
function isAuthenticated(): ?object
{
    $headers = getallheaders();
    if (!isset($headers['Authorization'])) {
        return null;
    }

    $authHeader = $headers['Authorization'];
    if (strpos($authHeader, 'Bearer ') !== 0) {
        return null;
    }

    $jwt        = trim(str_replace('Bearer ', '', $authHeader));
    $secret_key = env('JWT_SECRET_KEY');  

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        error_log('JWT Decoding Error: ' . $e->getMessage()); // Log l'erreur pour le débogage
        return null;
    }
}

return [
    // --- Méthode GET : Récupérer tous les achats ---
    'GET' => function () use ($pdo) {
        $currentUser = isAuthenticated();
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
            // Optionnel: filtrer les achats par user_id si c'est pertinent pour votre logique métier
            // $stmt = $pdo->prepare("SELECT * FROM achats WHERE user_id = ? ORDER BY date_achat DESC");
            // $stmt->execute([$currentUser->id]);
            $stmt  = $pdo->query("SELECT * FROM achats ORDER BY date_achat DESC"); // Récupérer tous les achats
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::success('Achats récupérés avec succès.', $items);
        } catch (PDOException $e) {
            error_log('Error fetching achats: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des achats.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode GET_ID : Récupérer un achat spécifique ---
    'GET_ID' => function (array $params) use ($pdo) {
        $currentUser = isAuthenticated();
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour accéder à cette ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null;
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'achat invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Optionnel: vérifier si l'achat appartient à l'utilisateur authentifié
            // $stmt = $pdo->prepare("SELECT * FROM achats WHERE id = ? AND user_id = ?");
            // $stmt->execute([$id, $currentUser->id]);
            $stmt = $pdo->prepare("SELECT * FROM achats WHERE id = ?");
            $stmt->execute([$id]);
            $achat = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$achat) {
                Response::notFound('Achat non trouvé.');
                return;
            }
            Response::success('Achat récupéré avec succès.', $achat);
        } catch (PDOException $e) {
            error_log('Error fetching single achat: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération de l\'achat.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer un nouvel achat ---
    'POST' => function () use ($pdo) {
        $currentUser = isAuthenticated();
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
        $requiredFields = ['type', 'amount', 'date_achat', 'category'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }
        
        // Validation spécifique du montant si nécessaire (ex: is_numeric($data['amount']))
        if (!is_numeric($data['amount']) || $data['amount'] < 0) {
            Response::badRequest("Le montant doit être un nombre positif.");
            return;
        }

        try {
            // Récupérer l'ID de l'utilisateur authentifié du token
            $user_id = $currentUser->id ?? null;

            $sql = "INSERT INTO achats (type, amount, date_achat, category, user_id, supplier_id, contrat_id, description) 
                    VALUES (:type, :amount, :date_achat, :category, :user_id, :supplier_id, :contrat_id, :description)";
            
            $stmt = $pdo->prepare($sql);
            if (!$stmt) {
                Response::error('Erreur lors de la préparation de la requête.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $executed = $stmt->execute([
                ':type'        => $data['type'],
                ':amount'      => $data['amount'],
                ':date_achat'  => $data['date_achat'],
                ':category'    => $data['category'],
                ':user_id'     => $user_id, // L'ID de l'utilisateur authentifié
                ':supplier_id' => $data['supplier_id'] ?? null,
                ':contrat_id'  => $data['contrat_id'] ?? null,
                ':description' => $data['description'] ?? null,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created('Achat ajouté avec succès.', ['id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            error_log('Error creating achat: ' . $e->getMessage());
            Response::error('Erreur lors de la création de l\'achat.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT_ID : Modifier un achat spécifique ---
    'PUT_ID' => function (array $params) use ($pdo) {
        $currentUser = isAuthenticated();
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour modifier une ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'achat invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['type', 'amount', 'date_achat', 'category'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        if (!is_numeric($data['amount']) || $data['amount'] < 0) {
            Response::badRequest("Le montant doit être un nombre positif.");
            return;
        }

        try {
            // Optionnel: vérifiez que l'achat appartient à l'utilisateur avant de le modifier
            // $checkStmt = $pdo->prepare("SELECT id FROM achats WHERE id = ? AND user_id = ?");
            // $checkStmt->execute([$id, $currentUser->id]);
            // if ($checkStmt->rowCount() === 0) {
            //     Response::forbidden('Accès refusé. Vous n\'êtes pas autorisé à modifier cet achat.');
            //     return;
            // }

            $sql = "UPDATE achats SET 
                        type = :type, 
                        amount = :amount, 
                        date_achat = :date_achat, 
                        category = :category, 
                        user_id = :user_id, 
                        supplier_id = :supplier_id, 
                        contrat_id = :contrat_id, 
                        description = :description 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            if (!$stmt) {
                Response::error('Erreur lors de la préparation de la requête.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $user_id = $currentUser->id ?? null; // Assurez-vous d'utiliser l'ID de l'utilisateur authentifié

            $executed = $stmt->execute([
                ':type'        => $data['type'],
                ':amount'      => $data['amount'],
                ':date_achat'  => $data['date_achat'],
                ':category'    => $data['category'],
                ':user_id'     => $user_id,
                ':supplier_id' => $data['supplier_id'] ?? null,
                ':contrat_id'  => $data['contrat_id'] ?? null,
                ':description' => $data['description'] ?? null,
                ':id'          => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Achat non trouvé avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Achat modifié avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating achat: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de l\'achat.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE_ID : Supprimer un achat spécifique ---
    //  l'ID est passé dans l'URL (ex: /achats/123)
    'DELETE_ID' => function (array $params) use ($pdo) {
        $currentUser = isAuthenticated();
        if (!$currentUser) {
            Response::unauthorized(
                'Accès non autorisé',
                'Vous devez vous authentifier pour supprimer une ressource.'
            );
            return;
        }

        $id = $params['id'] ?? null; // L'ID vient des paramètres de l'URL
        if (!is_numeric($id) || $id <= 0) {
            Response::badRequest('ID d\'achat invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Optionnel: vérifiez que l'achat appartient à l'utilisateur avant de le supprimer
            // $checkStmt = $pdo->prepare("SELECT id FROM achats WHERE id = ? AND user_id = ?");
            // $checkStmt->execute([$id, $currentUser->id]);
            // if ($checkStmt->rowCount() === 0) {
            //     Response::forbidden('Accès refusé. Vous n\'êtes pas autorisé à supprimer cet achat.');
            //     return;
            // }

            $stmt = $pdo->prepare("DELETE FROM achats WHERE id = ?");
            if (!$stmt) {
                Response::error('Erreur lors de la préparation de la requête de suppression.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $executed = $stmt->execute([$id]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de suppression.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Achat non trouvé avec l\'ID spécifié.'); // L'ID n'existait pas
                return;
            }

            Response::success('Achat supprimé avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting achat: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression de l\'achat.', 500, ['details' => $e->getMessage()]);
        }
    },
];