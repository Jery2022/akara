<?php
require_once __DIR__ . '/../../config/db.php';

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
    $secret_key = env('JWT_SECRET_KEY');

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
            echo json_encode(['error' => 'Échec de la connexion à la base de données']);
            exit;
        }

        $stmt  = $pdo->query("SELECT * FROM sortieStock");
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
            echo json_encode(['error' => 'Échec de la connexion à la base de données']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['produit_id'], $data['quantity'], $data['date'], $data['entrepot_id'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $customer_id = $data['customer_id'] ?? null;
        $motif       = $data['motif'] ?? null;
        $sql         = "INSERT INTO sortieStock (produit_id, quantity, date, user_id, customer_id, entrepot_id, motif) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt        = $pdo->prepare($sql);
        $stmt->execute([
            $data['produit_id'],
            $data['quantity'],
            $data['date'],
            $user_id,
            $customer_id,
            $data['entrepot_id'],
            $motif,
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
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
            echo json_encode(['error' => 'Échec de la connexion à la base de données']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['produit_id'], $data['quantity'], $data['date'], $data['entrepot_id'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $user_id     = $data['user_id'] ?? null;
        $customer_id = $data['customer_id'] ?? null;
        $motif       = $data['motif'] ?? null;
        $sql         = "UPDATE sortieStock SET produit_id=?, quantity=?, date=?, user_id=?, customer_id=?, entrepot_id=?, motif=? WHERE id=?";
        $stmt        = $pdo->prepare($sql);
        $stmt->execute([
            $data['produit_id'],
            $data['quantity'],
            $data['date'],
            $user_id,
            $customer_id,
            $data['entrepot_id'],
            $motif,
            $data['id'],
        ]);
        echo json_encode(['success' => true]);
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
            echo json_encode(['error' => 'Échec de la connexion à la base de données']);
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM sortieStock WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Sortie stock supprimée avec succès',
        ]);
        exit;
    },
];
// Ferme la connexion à la base de données
$pdo = null;
