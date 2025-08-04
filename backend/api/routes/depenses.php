<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../../vendor/autoload.php'; // Pour JWT et Core\Response

use Core\Response;  
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Headers CORS. Idéalement, gérés par un middleware dans le routeur principal.
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Ajout de OPTIONS
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Gestion des requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);  
    exit;
}

// Obtenez l'instance PDO une seule fois au début
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
    // --- Méthode GET : Récupérer toutes les dépenses ---
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
            // Optionnel: filtrer les dépenses par user_id si c'est pertinent
            // $stmt = $pdo->prepare("SELECT * FROM depenses WHERE user_id = ? ORDER BY date_depense DESC");
            // $stmt->execute([$currentUser->id]);
            $stmt  = $pdo->query("SELECT * FROM depenses ORDER BY date_depense DESC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Response::success('Dépenses récupérées avec succès.', $items);
        } catch (PDOException $e) {
            error_log('Error fetching depenses: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération des dépenses.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode GET_ID : Récupérer une dépense spécifique ---
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
            Response::badRequest('ID de dépense invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Optionnel: vérifier si la dépense appartient à l'utilisateur authentifié
            // $stmt = $pdo->prepare("SELECT * FROM depenses WHERE id = ? AND user_id = ?");
            // $stmt->execute([$id, $currentUser->id]);
            $stmt = $pdo->prepare("SELECT * FROM depenses WHERE id = ?");
            $stmt->execute([$id]);
            $depense = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$depense) {
                Response::notFound('Dépense non trouvée.');
                return;
            }
            Response::success('Dépense récupérée avec succès.', $depense);
        } catch (PDOException $e) {
            error_log('Error fetching single depense: ' . $e->getMessage());
            Response::error('Erreur lors de la récupération de la dépense.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode POST : Créer une nouvelle dépense ---
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
        $requiredFields = ['produit_id', 'supplier_id', 'quantity', 'price', 'nature', 'category'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire.");
                return;
            }
        }

        // Validation des types numériques
        if (!is_numeric($data['quantity']) || $data['quantity'] <= 0) {
            Response::badRequest("La quantité doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            Response::badRequest("Le prix doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['produit_id']) || $data['produit_id'] <= 0) {
            Response::badRequest("L'ID du produit doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['supplier_id']) || $data['supplier_id'] <= 0) {
            Response::badRequest("L'ID du fournisseur doit être un nombre positif.");
            return;
        }
        
        try {
            // Récupérer l'ID de l'utilisateur authentifié du token
            $user_id = $currentUser->id ?? null;
            $contrat_id = $data['contrat_id'] ?? null;
            $date_depense = $data['date_depense'] ?? date('Y-m-d'); // Utilise la date fournie ou la date actuelle
            $description = $data['description'] ?? null;

            $sql = "INSERT INTO depenses (user_id, produit_id, supplier_id, contrat_id, quantity, price, date_depense, description, nature, category) 
                    VALUES (:user_id, :produit_id, :supplier_id, :contrat_id, :quantity, :price, :date_depense, :description, :nature, :category)";
            
            $stmt = $pdo->prepare($sql);
            if (!$stmt) {
                Response::error('Erreur lors de la préparation de la requête.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $executed = $stmt->execute([
                ':user_id'     => $user_id,
                ':produit_id'  => $data['produit_id'],
                ':supplier_id' => $data['supplier_id'],
                ':contrat_id'  => $contrat_id,
                ':quantity'    => $data['quantity'],
                ':price'       => $data['price'],
                ':date_depense' => $date_depense,
                ':description' => $description,
                ':nature'      => $data['nature'],
                ':category'    => $data['category'],
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de création.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            Response::created('Dépense ajoutée avec succès.', ['id' => $pdo->lastInsertId()]);
        } catch (PDOException $e) {
            error_log('Error creating depense: ' . $e->getMessage());
            Response::error('Erreur lors de la création de la dépense.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode PUT_ID : Modifier une dépense spécifique ---
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
            Response::badRequest('ID de dépense invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        // Champs obligatoires pour la mise à jour
        $requiredFields = ['produit_id', 'supplier_id', 'quantity', 'price', 'nature', 'category'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                Response::badRequest("Le champ '{$field}' est obligatoire pour la mise à jour.");
                return;
            }
        }

        // Validation des types numériques
        if (!is_numeric($data['quantity']) || $data['quantity'] <= 0) {
            Response::badRequest("La quantité doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['price']) || $data['price'] <= 0) {
            Response::badRequest("Le prix doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['produit_id']) || $data['produit_id'] <= 0) {
            Response::badRequest("L'ID du produit doit être un nombre positif.");
            return;
        }
        if (!is_numeric($data['supplier_id']) || $data['supplier_id'] <= 0) {
            Response::badRequest("L'ID du fournisseur doit être un nombre positif.");
            return;
        }

        try {
            // Optionnel: vérifiez que la dépense appartient à l'utilisateur avant de la modifier
            // $checkStmt = $pdo->prepare("SELECT id FROM depenses WHERE id = ? AND user_id = ?");
            // $checkStmt->execute([$id, $currentUser->id]);
            // if ($checkStmt->rowCount() === 0) {
            //     Response::forbidden('Accès refusé. Vous n\'êtes pas autorisé à modifier cette dépense.');
            //     return;
            // }

            $user_id = $currentUser->id ?? null; // L'ID de l'utilisateur authentifié
            $contrat_id = $data['contrat_id'] ?? null;
            $date_depense = $data['date_depense'] ?? date('Y-m-d');
            $description = $data['description'] ?? null;

            $sql = "UPDATE depenses SET 
                        user_id = :user_id, 
                        produit_id = :produit_id, 
                        supplier_id = :supplier_id, 
                        contrat_id = :contrat_id, 
                        quantity = :quantity, 
                        price = :price, 
                        date_depense = :date_depense, 
                        description = :description, 
                        nature = :nature, 
                        category = :category 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            if (!$stmt) {
                Response::error('Erreur lors de la préparation de la requête.', 500, ['details' => $pdo->errorInfo()]);
                return;
            }

            $executed = $stmt->execute([
                ':user_id'     => $user_id,
                ':produit_id'  => $data['produit_id'],
                ':supplier_id' => $data['supplier_id'],
                ':contrat_id'  => $contrat_id,
                ':quantity'    => $data['quantity'],
                ':price'       => $data['price'],
                ':date_depense' => $date_depense,
                ':description' => $description,
                ':nature'      => $data['nature'],
                ':category'    => $data['category'],
                ':id'          => $id,
            ]);

            if (!$executed) {
                Response::error('Erreur lors de l\'exécution de la requête de mise à jour.', 500, ['details' => $stmt->errorInfo()]);
                return;
            }

            if ($stmt->rowCount() === 0) {
                Response::notFound('Dépense non trouvée avec l\'ID spécifié ou aucune modification effectuée.');
                return;
            }

            Response::success('Dépense modifiée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error updating depense: ' . $e->getMessage());
            Response::error('Erreur lors de la modification de la dépense.', 500, ['details' => $e->getMessage()]);
        }
    },

    // --- Méthode DELETE_ID : Supprimer une dépense spécifique ---
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
            Response::badRequest('ID de dépense invalide ou manquant dans l\'URL.');
            return;
        }

        if (!$pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        try {
            // Optionnel: vérifier que la dépense appartient à l'utilisateur avant de la supprimer
            // $checkStmt = $pdo->prepare("SELECT id FROM depenses WHERE id = ? AND user_id = ?");
            // $checkStmt->execute([$id, $currentUser->id]);
            // if ($checkStmt->rowCount() === 0) {
            //     Response::forbidden('Accès refusé. Vous n\'êtes pas autorisé à supprimer cette dépense.');
            //     return;
            // }

            $stmt = $pdo->prepare("DELETE FROM depenses WHERE id = ?");
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
                Response::notFound('Dépense non trouvée avec l\'ID spécifié.'); // L'ID n'existait pas
                return;
            }

            Response::success('Dépense supprimée avec succès.', ['id' => (int) $id]);
        } catch (PDOException $e) {
            error_log('Error deleting depense: ' . $e->getMessage());
            Response::error('Erreur lors de la suppression de la dépense.', 500, ['details' => $e->getMessage()]);
        }
    },
];