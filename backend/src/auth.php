<?php
include 'db.php';
include 'config.php';

require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Vérification des champs reçus
    if (! isset($data['email'], $data['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Champs manquants']);
        exit;
    }

    $email    = $data['email'];
    $password = $data['password'];

    // Utilisation de PDO pour éviter les injections SQL
    $stmt = $PDO->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Utilisation de fetch pour obtenir directement l'utilisateur

    if ($user) {
        // Comparaison avec le mot de passe haché en base
        if (password_verify($password, $user['password'])) {
                                      // Génération d'un vrai JWT
            $secret_key = JWT_SECRET; // Assurez-vous que JWT_SECRET est défini dans config.php
            $issuedAt   = time();
            $expire     = $issuedAt + 1800; // 30 minutes

            $payload = [
                'iat'   => $issuedAt,
                'exp'   => $expire,
                'sub'   => $user['id'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ];

            $jwt = JWT::encode($payload, $secret_key, 'HS256');

            echo json_encode(['token' => $jwt]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Mot de passe incorrect']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Utilisateur introuvable']);
    }
}
