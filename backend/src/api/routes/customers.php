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

        $stmt  = $pdo->query("SELECT * FROM customers");
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
        if (! isset($data['name'], $data['refContact'], $data['phone'], $data['email'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $contrat_id = $data['contrat_id'] ?? null;
        $stmt       = $pdo->prepare("INSERT INTO customers (name, refContact, phone, email, contrat_id) VALUES (?, ?, ?, ?, ?)");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $data['name'],
            $data['refContact'],
            $data['phone'],
            $data['email'],
            $contrat_id,
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
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
        if (! isset($data['id'], $data['name'], $data['refContact'], $data['phone'], $data['email'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $contrat_id = $data['contrat_id'] ?? null;
        $stmt       = $pdo->prepare("UPDATE customers SET name=?, refContact=?, phone=?, email=?, contrat_id=? WHERE id=?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $data['name'],
            $data['refContact'],
            $data['phone'],
            $data['email'],
            $contrat_id,
            $data['id'],
        ]);
        echo json_encode(['success' => true]);
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
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Client supprimé avec succès',
        ]);
        exit;
    },
];
// Ferme la connexion à la base de données
$pdo = null;
