<?php
require_once __DIR__ . '/../../../db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

return [
    'GET'    => function () {

        $pdo = getPDO();

        $headers = getallheaders();
        if (! isset($headers['Authorization']) || strpos($headers['Authorization'], 'Bearer ') !== 0) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Vous devez vous authentifier pour accéder à cette ressource.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        $jwt        = trim(str_replace('Bearer ', '', $headers['Authorization']));
        $secret_key = env('JWT_SECRET');
        try {
            JWT::decode($jwt, new Key($secret_key, 'HS256'));
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Token invalide.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $stmt  = $pdo->query("SELECT * FROM users");
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($items);
        exit;
    },

    'POST'   => function () {

        $pdo = getPDO();

        $headers = getallheaders();
        if (! isset($headers['Authorization']) || strpos($headers['Authorization'], 'Bearer ') !== 0) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Vous devez vous authentifier pour accéder à cette ressource.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        $jwt        = trim(str_replace('Bearer ', '', $headers['Authorization']));
        $secret_key = env('JWT_SECRET');
        try {
            JWT::decode($jwt, new Key($secret_key, 'HS256'));
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Token invalide.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['email'], $data['password'], $data['role'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Email invalide'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql           = "INSERT INTO users (email, password, role, statut) VALUES (?, ?, ?, ?)";
        $stmt          = $pdo->prepare($sql);
        $stmt->execute([
            $data['email'],
            $password_hash,
            $data['role'],
            'actif',
        ]);
        echo json_encode([
            'success' => true,
            'id'      => $pdo->lastInsertId(),
        ]);
        exit;
    },

    'PUT'    => function () {

        $pdo = getPDO();

        $headers = getallheaders();
        if (! isset($headers['Authorization']) || strpos($headers['Authorization'], 'Bearer ') !== 0) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Vous devez vous authentifier pour accéder à cette ressource.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        $jwt        = trim(str_replace('Bearer ', '', $headers['Authorization']));
        $secret_key = env('JWT_SECRET');
        try {
            JWT::decode($jwt, new Key($secret_key, 'HS256'));
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Token invalide.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['email'], $data['password'], $data['role'], $data['statut'], $data['pseudo'])) {
            echo json_encode(['error' => 'Champs manquants'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Email invalide'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $sql           = "UPDATE users SET email=?, password=?, role=?, statut=?, pseudo=? WHERE id=?";
        $stmt          = $pdo->prepare($sql);
        $stmt->execute([
            $data['email'],
            $password_hash,
            $data['role'],
            $data['statut'],
            $data['pseudo'],
            $data['id'],
        ]);
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    },

    'DELETE' => function () {

        $pdo = getPDO();

        $headers = getallheaders();
        if (! isset($headers['Authorization']) || strpos($headers['Authorization'], 'Bearer ') !== 0) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Vous devez vous authentifier pour accéder à cette ressource.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        $jwt        = trim(str_replace('Bearer ', '', $headers['Authorization']));
        $secret_key = env('JWT_SECRET');
        try {
            JWT::decode($jwt, new Key($secret_key, 'HS256'));
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                'error'   => 'Accès non autorisé',
                'message' => 'Token invalide.',
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    },
];
