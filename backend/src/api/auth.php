<?php
require_once __DIR__ . '/../db.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

return [
    'POST' => function () {

        header("Content-Type: application/json");

        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            http_response_code(415);
            echo json_encode(['error' => 'Content-Type attendu : application/json']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (! isset($data['email'], $data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Champs manquants']);
            exit;
        }

        $email    = trim($data['email']);
        $password = $data['password'];

        $pdo  = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $issuedAt = time();
            $expire   = $issuedAt + 1800;

            $payload = [
                'iat'   => $issuedAt,
                'exp'   => $expire,
                'sub'   => $user['id'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ];

            $jwt = JWT::encode($payload, env('JWT_SECRET'), 'HS256');

            echo json_encode([
                'token'      => $jwt,
                'token_type' => 'Bearer',
                'expires_in' => 1800,
            ]);
            exit;
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Identifiants invalides'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            exit;
        }
    },
];
