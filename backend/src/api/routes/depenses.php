<?php
require_once __DIR__ . '/../../../db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function isAuthenticated()
{
    $headers = getallheaders();
    if (! isset($headers['Authorization'])) {
        return false;
    }

    $authHeader = $headers['Authorization'];
    if (strpos($authHeader, 'Bearer ') !== 0) {
        return false;
    }

    $jwt        = trim(str_replace('Bearer ', '', $authHeader));
    $secret_key = JWT_SECRET;

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        return false;
    }
}

return [
    'GET'    => function () {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: http://localhost:3000");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        if (! isAuthenticated()) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Vous devez vous authentifier pour accéder à cette ressource.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $pdo = getPDO();
        if (! $pdo) {
            echo json_encode(['error' => 'Échec de la connexion à la base de données']);
            exit;
        }

        $stmt  = $pdo->query("SELECT * FROM depenses");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
        exit;
    },

    'POST'   => function () {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: http://localhost:3000");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        if (! isAuthenticated()) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Vous devez vous authentifier pour accéder à cette ressource.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $pdo = getPDO();
        if (! $pdo) {
            echo json_encode(['error' => 'Échec de la connexion à la base de données']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['produit_id'], $data['supplier_id'], $data['quantity'], $data['price'], $data['nature'], $data['category'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $contrat_id  = $data['contrat_id'] ?? null;
        $date        = $data['date_depense'] ?? date('Y-m-d');
        $description = $data['description'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO depenses (user_id, produit_id, supplier_id, contrat_id, quantity, price, date_depense, description, nature, category) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $user_id,
            $data['produit_id'],
            $data['supplier_id'],
            $contrat_id,
            $data['quantity'],
            $data['price'],
            $date,
            $description,
            $data['nature'],
            $data['category'],
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
            'message' => 'Dépense ajoutée avec succès',
        ]);
        exit;
    },

    'PUT'    => function () {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: http://localhost:3000");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        if (! isAuthenticated()) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Vous devez vous authentifier pour accéder à cette ressource.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $pdo = getPDO();
        if (! $pdo) {
            echo json_encode(['error' => 'Échec de la connexion à la base de données']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['produit_id'], $data['supplier_id'], $data['quantity'], $data['price'], $data['nature'], $data['category'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $contrat_id  = $data['contrat_id'] ?? null;
        $date        = $data['date_depense'] ?? date('Y-m-d');
        $description = $data['description'] ?? null;

        $stmt = $pdo->prepare("UPDATE depenses SET user_id=?, produit_id=?, supplier_id=?, contrat_id=?, quantity=?, price=?, date_depense=?, description=?, nature=?, category=? WHERE id=?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $user_id,
            $data['produit_id'],
            $data['supplier_id'],
            $contrat_id,
            $data['quantity'],
            $data['price'],
            $date,
            $description,
            $data['nature'],
            $data['category'],
            $data['id'],
        ]);
        echo json_encode([
            'success' => true,
            'message' => 'Dépense modifiée avec succès',
        ]);
        exit;
    },

    'DELETE' => function () {
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: http://localhost:3000");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        if (! isAuthenticated()) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Vous devez vous authentifier pour accéder à cette ressource.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $pdo = getPDO();
        if (! $pdo) {
            echo json_encode(['error' => 'Échec de la connexion à la base de données']);
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM depenses WHERE id = ?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Dépense supprimée avec succès',
        ]);
        exit;
    },
];
// Ferme la connexion à la base de données
$pdo = null;
