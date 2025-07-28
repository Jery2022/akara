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

        $stmt  = $pdo->query("SELECT * FROM quittances");
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
        if (! isset($data['employee_id'], $data['date_paiement'], $data['montant'], $data['periode_service'], $data['numero_quittance'], $data['date_emission'], $data['type'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $employee_id = $data['employee_id'] ?? null;
        $sql         = "INSERT INTO quittances (employee_id, date_paiement, montant, periode_service, numero_quittance, date_emission, type) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt        = $pdo->prepare($sql);
        $stmt->execute([
            $employee_id,
            $data['date_paiement'],
            $data['montant'],
            $data['periode_service'],
            $data['numero_quittance'],
            $data['date_emission'],
            $data['type'],
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
            'message' => 'Quittance créée avec succès',
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
        if (! isset($data['id'], $data['employee_id'], $data['date_paiement'], $data['periode_service'], $data['numero_quittance'], $data['date_emission'], $data['type'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        $employee_id = $data['employee_id'] ?? null;
        $sql         = "UPDATE quittances SET date_paiement=?, periode_service=?, numero_quittance=?, date_emission=?, type=? WHERE id=?";
        $stmt        = $pdo->prepare($sql);
        $stmt->execute([
            $employee_id,
            $data['date_paiement'],
            $data['periode_service'],
            $data['numero_quittance'],
            $data['date_emission'],
            $data['type'],
            $data['id'],
        ]);
        echo json_encode([
            'success' => true,
            'message' => 'Quittances mise à jour avec succès',
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
        $stmt = $pdo->prepare("DELETE FROM quittances WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Quittance supprimée avec succès',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    },
];

// Fermeture de la connexion à la base de données
$pdo = null;
