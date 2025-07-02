<?php
require_once __DIR__ . '/../../../db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

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
            echo json_encode(['error' => 'Échec de la connexion à la base de données'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $stmt  = $pdo->query("SELECT * FROM achats");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
        exit;
    },

    'POST'   => function () {

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
            echo json_encode(['error' => 'Échec de la connexion à la base de données'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['type'], $data['amount'], $data['date_achat'], $data['category'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $supplier_id = $data['supplier_id'] ?? null;
        $contrat_id  = $data['contrat_id'] ?? null;
        $description = $data['description'] ?? null;
        $stmt        = $pdo->prepare("INSERT INTO achats (type, amount, date_achat, category, user_id, supplier_id, contrat_id, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        $stmt->execute([
            $data['type'],
            $data['amount'],
            $data['date_achat'],
            $data['category'],
            $user_id,
            $supplier_id,
            $contrat_id,
            $description,
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
            'message' => 'Achat ajouté avec succès',
        ]);
        exit;
    },

    'PUT'    => function () {

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
            echo json_encode(['error' => 'Échec de la connexion à la base de données'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['type'], $data['amount'], $data['date_achat'], $data['category'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $supplier_id = $data['supplier_id'] ?? null;
        $contrat_id  = $data['contrat_id'] ?? null;
        $description = $data['description'] ?? null;
        $stmt        = $pdo->prepare("UPDATE achats SET type=?, amount=?, date_achat=?, category=?, user_id=?, supplier_id=?, contrat_id=?, description=? WHERE id=?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation']);
            exit;
        }
        $stmt->execute([
            $data['type'],
            $data['amount'],
            $data['date_achat'],
            $data['category'],
            $user_id,
            $supplier_id,
            $contrat_id,
            $description,
            $data['id'],
        ]);
        echo json_encode([
            'success' => true,
            'message' => 'Achat modifié avec succès',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    },

    'DELETE' => function () {

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
            echo json_encode(['error' => 'Échec de la connexion à la base de données'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM achats WHERE id = ?");
        if (! $stmt) {
            echo json_encode(['error' => 'Erreur lors de la préparation'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Achat supprimé avec succès',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    },
];
// Ferme la connexion à la base de données
$pdo = null;
