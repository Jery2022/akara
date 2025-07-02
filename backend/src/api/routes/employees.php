<?php
require_once __DIR__ . '/../../../db.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Vérification du token JWT
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
    $secret_key = JWT_SECRET; // définie dans config.php

    try {
        $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
        return $decoded; // ou true si tu veux juste vérifier
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
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }

        $stmt  = $pdo->query("SELECT * FROM employees");
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
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['name'], $data['phone'], $data['email'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Email invalide']);
            exit;
        }
        $sql  = "INSERT INTO employees (name, role, salary, phone, email, contrat_id, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['role'] ?? null,
            $data['salary'] ?? null,
            $data['phone'],
            $data['email'],
            $data['contrat_id'] ?? null,
            $data['user_id'] ?? null,
        ]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId()]);
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
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        if (! isset($data['id'], $data['name'], $data['phone'], $data['email'])) {
            echo json_encode(['error' => 'Champs obligatoires manquants']);
            exit;
        }
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['error' => 'Email invalide']);
            exit;
        }
        $sql  = "UPDATE employees SET name=?, role=?, salary=?, phone=?, email=?, contrat_id=?, user_id=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $data['name'],
            $data['role'] ?? null,
            $data['salary'] ?? null,
            $data['phone'],
            $data['email'],
            $data['contrat_id'] ?? null,
            $data['user_id'] ?? null,
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
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if (! $id) {
            echo json_encode(['error' => 'ID manquant']);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM employees WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode([
            'success' => true,
            'message' => 'Employé supprimé avec succès',
        ]);
        exit;
    },
];
// Ferme la connexion à la base de données
$pdo = null;
