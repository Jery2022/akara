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
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }

        $stmt  = $pdo->query("SELECT * FROM factures");
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
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['customer_id'], $data['date_facture'], $data['amount_total'], $data['amount_tva'], $data['amount_css'], $data['amount_ttc'], $data['status'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $customer_id = $data['customer_id'] ?? null;
        $sql         = "INSERT INTO factures (customer_id, date_facture, amount_total, amount_tva, amount_css, amount_ttc, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt        = $pdo->prepare($sql);
        $stmt->execute([
            $customer_id,
            $data['date_facture'],
            $data['amount_total'],
            $data['amount_tva'],
            $data['amount_css'],
            $data['amount_ttc'],
            $data['status'],
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
            'message' => 'Facture créée avec succès',
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
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['customer_id'], $data['date_facture'], $data['amount_total'], $data['amount_tva'], $data['amount_css'], $data['amount_ttc'], $data['status'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $customer_id = $data['customer_id'] ?? null;
        $sql         = "UPDATE factures SET customer_id=?, date_facture=?, amount_total=?, amount_tva=?, amount_css=?, amount_ttc=?, status=? WHERE id=?";
        $stmt        = $pdo->prepare($sql);
        $stmt->execute([
            $customer_id,
            $data['date_facture'],
            $data['amount_total'],
            $data['amount_tva'],
            $data['amount_css'],
            $data['amount_ttc'],
            $data['status'],
            $data['id'],
        ]);
        echo json_encode([
            'success' => true,
            'message' => 'Facture mise à jour avec succès',
        ]);
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
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM factures WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Facture supprimée avec succès',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    },
];

// Fermeture de la connexion à la base de données
$pdo = null;
