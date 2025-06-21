<?php
include 'db.php';
include 'config.php';

require_once __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start(); // Démarrer la session

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

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Récupére un seul utilisateur

    if ($user) {
        // Comparaison avec le mot de passe haché en base
        if (password_verify($password, $user['password'])) {
            // Authentification réussie, enregistrer les données en session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['pseudo']  = $user['pseudo'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            // Génération d'un vrai JWT
            $secret_key = JWT_SECRET;
            $issuedAt   = time();
            $expire     = $issuedAt + 3600; // 1 heure

            $payload = [
                'iat'    => $issuedAt,
                'exp'    => $expire,
                'sub'    => $user['id'],
                'pseudo' => $user['pseudo'],
                'email'  => $user['email'],
                'role'   => $user['role'],
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
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
}
