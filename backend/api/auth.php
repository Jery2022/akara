<?php
// backend/api/auth.php

require_once __DIR__ . '/../config/db.php';

use Core\Response;
use Firebase\JWT\JWT;

return [
    'POST' => function () {
        // Les en-têtes CORS sont gérés par server.php.
        // Le Content-Type est géré par api/index.php.

        $pdo = getPDO();
        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $email    = $input['email'] ?? null;
        $password = $input['password'] ?? null;


        if (isset($input['email']) && isset($input['password'])) {
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
                $secretKey = $_ENV['JWT_SECRET'] ?: getenv('JWT_SECRET');

                // Log de débogage pour la clé secrète lors de la génération
                error_log("[AUTH] Clé secrète pour génération JWT: " . ($secretKey ? 'DÉFINIE (taille: ' . strlen($secretKey) . ')' : 'NON DÉFINIE'));

                if (empty($secretKey)) {
                    error_log("[AUTH] Erreur: JWT_SECRET n'est pas définie pour la génération de token.");
                    Response::error('Erreur de configuration du serveur lors de la génération du token.', 500);
                    return;
                }

                // Définition du payload du JWT
                $issuedAt       = time();
                $expirationTime = $issuedAt + (60 * 60 * 24);           // Token valide 24 heures
                $issuer         = "https://akara-backend.fly.dev/"; // ou "http://localhost:8000/backend/api/"; // Qui émet le token
                $audience       = "https://akara-frontend.fly.dev/";         // ou "http://localhost:3000";              // Pour qui le token est destiné

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
        } else {
            // Si POST sans email/password, c'est une Bad Request
            Response::badRequest('Email et mot de passe requis pour la connexion.');
        }
    },

    'GET'  => function () {
        // CETTE SECTION EST CELLE QUI EST APPELÉE PAR useEffect `checkAuth`
        // Elle s'attend à trouver le token dans l'en-tête Authorization.
        // Les en-têtes CORS sont gérés par server.php.
        // Le Content-Type est géré par api/index.php.

        $pdo = getPDO();
        if (! $pdo) {
            Response::error('Échec de la connexion à la base de données.', 500);
            return;
        }

        // Récupérer le token de l'en-tête Authorization
        $token = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                $token = $matches[1];
            }
        }

        if (! $token) {
            // Si aucun token, renvoyer Unauthorized (ce qui est normal si l'utilisateur n'est pas connecté)
            Response::unauthorized('Aucun token d\'authentification fourni dans l\'en-tête Authorization.');
            return;
        }

        // --- Logique de validation du token JWT ---  
        $secretKey = $_ENV['JWT_SECRET'] ?: getenv('JWT_SECRET');
        if (empty($secretKey)) {
            error_log("[AUTH] Erreur: JWT_SECRET non définie pour la validation de token.");
            Response::error('Erreur de configuration du serveur.', 500);
            return;
        }

        try {
            // Décoder et valider le token
            $decoded = JWT::decode($token, new \Firebase\JWT\Key($secretKey, 'HS256'));

            // Récupérer les données de l'utilisateur du payload du token
            $user_id = $decoded->data->user_id;
            $email   = $decoded->data->email;
            $role    = $decoded->data->role;

            // ... autres données comme email, role ...

            // Optionnel: vérifier si l'utilisateur existe toujours en base de données
            $stmt = $pdo->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (! $user || $user['email'] !== $email || $user['role'] !== $role) {
                Response::unauthorized('Informations utilisateur obsolètes ou invalides. Veuillez vous reconnecter.');
                return;
            }

            Response::json([
                'message' => 'Session valide',
                'user'    => [
                    'id'    => $user_id,
                    'email' => $decoded->data->email,
                    'role'  => $decoded->data->role,
                ],
            ]);
        } catch (\Firebase\JWT\ExpiredException $e) {
            Response::unauthorized('Votre session a expiré. Veuillez vous reconnecter.');
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            Response::unauthorized('Token d\'authentification invalide.');
        } catch (\Exception $e) {
            Response::unauthorized('Erreur lors de la validation du token.');
        }
    },
];
