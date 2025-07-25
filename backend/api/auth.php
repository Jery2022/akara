<?php
// backend/api/auth.php

require_once __DIR__ . '/../config/db.php';

use Core\Response;
use Firebase\JWT\JWT;

return [
    'POST' => function () {
        // Définition des en-têtes CORS (peut aussi être centralisé dans index.php si toutes les routes d'auth le nécessitent)
        header("Content-Type: application/json");
        header("Access-Control-Allow-Origin: http://localhost:3000");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization");

        // Gérer les requêtes OPTIONS (pre-flight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $pdo = getPDO();
        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $email    = $input['email'] ?? null;
        $password = $input['password'] ?? null;

        if (! $email || ! $password) {
            Response::badRequest('Email et mot de passe requis.');
            return;
        }

        // Assainissement de l'email
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::badRequest('Format d\'email invalide.');
            return;
        }

        try {
            $stmt = $pdo->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $user || ! password_verify($password, $user['password'])) {
                Response::unauthorized('Identifiants invalides.');
                return;
            }

            // Récupération de la clé secrète JWT. DOIT être la même que celle utilisée pour la validation.
            $secretKey = $_ENV['JWT_SECRET_KEY'] ?: getenv('JWT_SECRET_KEY');

            // Log de débogage pour la clé secrète lors de la génération
            error_log("[AUTH] Clé secrète pour génération JWT: " . ($secretKey ? 'DÉFINIE (taille: ' . strlen($secretKey) . ')' : 'NON DÉFINIE'));

            if (empty($secretKey)) {
                error_log("[AUTH] Erreur: JWT_SECRET_KEY n'est pas définie pour la génération de token.");
                Response::error('Erreur de configuration du serveur lors de la génération du token.', 500);
                return;
            }

            // Définition du payload du JWT
            $issuedAt       = time();
            $expirationTime = $issuedAt + (60 * 60 * 24);   // Token valide 24 heures
            $issuer         = "http://localhost:8000/api/"; // Qui émet le token
            $audience       = "http://localhost:3000";      // Pour qui le token est destiné

            $payload = [
                'iat'  => $issuedAt,
                'exp'  => $expirationTime,
                'iss'  => $issuer,
                'aud'  => $audience,
                'data' => [
                    'user_id' => $user['id'],
                    'email'   => $user['email'],
                    'role'    => $user['role'],
                ],
            ];

            // Encodage du JWT
            $jwt = JWT::encode($payload, $secretKey, 'HS256');

            // Réponse de succès avec le token
            Response::json([
                'message' => 'Connexion réussie',
                'jwt'     => $jwt,
                'user'    => [
                    'id'    => $user['id'],
                    'email' => $user['email'],
                    'role'  => $user['role'],
                ],
            ]);

        } catch (\PDOException $e) {
            error_log("Erreur DB POST auth: " . $e->getMessage());
            Response::error('Erreur interne du serveur lors de l\'authentification.', 500);
        } catch (\Exception $e) {
            error_log("Erreur inattendue lors de la connexion: " . $e->getMessage());
            Response::error('Une erreur inattendue est survenue.', 500);
        }
    },
];
