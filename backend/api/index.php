<?php
// backend/api/index.php

// Active l'affichage des erreurs PHP (utile en développement)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// définition uniquement du Content-Type.
header("Content-Type: application/json");

// Inclure l'autoloader de Composer pour charger les dépendances (ex: Firebase JWT)
require_once __DIR__ . '/../src/vendor/autoload.php';
require_once __DIR__ . '/../src/bootstrap.php';

// Autoloader pour les classes locales (comme Core\Response)
spl_autoload_register(function ($className) {
    // Convertit le namespace en chemin de fichier (ex: Core\Response -> Core/Response.php)
    // __DIR__ ici est backend/api/
    $file = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Inclure explicitement Response au cas où l'autoloader ne la trouverait pas immédiatement.
// si l'autoload pas bien configuré.
require_once __DIR__ . '/core/Response.php';

// Utilisation des classes nécessaires avec les namespaces
use Core\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Fonction utilitaire pour charger un fichier de route et exécuter le handler.
 *
 * @param string $filePath Le chemin complet vers le fichier de définition des routes (ex: 'backend/api/routes/users.php').
 * @param string $method Le nom de la méthode HTTP (ex: 'GET', 'POST_ID').
 * @param array $params Un tableau associatif contenant les paramètres de l'URL (ex: ['id' => 123]).
 * @param ?object $currentUser Un objet contenant les données de l'utilisateur authentifié (issues du JWT),
 * ou null si la route est publique ou l'authentification a échoué.
 */
function loadRouteFile(string $filePath, string $method, array $params = [], ?object $currentUser = null): void
{
    if (! file_exists($filePath)) {
        Response::notFound("Route de fichier non trouvée.");
        return;
    }

    $handlers = require $filePath;

    if (! is_array($handlers) || ! isset($handlers[$method])) {
        Response::error("Méthode HTTP '{$method}' non supportée pour cette ressource.", 405);
        return;
    }

    call_user_func($handlers[$method], $params, $currentUser);
}

/**
 * Middleware d'authentification JWT.
 *
 * @return object|null Retourne l'objet décodé du JWT (contenant user_id, role, etc.)
 * si l'authentification est réussie.
 * Si l'authentification échoue, la fonction appelle Response::unauthorized()
 * qui contient un 'exit;', donc le code suivant ne devrait pas être atteint.
 */
function authenticateRequest(): ?object
{
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    // Fallback pour les serveurs qui ne peuplent pas $_SERVER['HTTP_AUTHORIZATION']
    if (empty($authHeader) && function_exists('getallheaders')) {
        $headers = getallheaders();
        // La casse de l'en-tête peut varier
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    }

    // Débogage : Vérifie la présence et le format du header Authorization
    error_log("[AUTH] Auth Header reçu: " . (empty($authHeader) ? "VIDE" : $authHeader));

    if (empty($authHeader) || ! preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        error_log("[AUTH] Token d'authentification manquant ou mal formé (pas de Bearer).");
        Response::unauthorized('Token d\'authentification manquant ou mal formé.');
        return null;
    }

    $jwt = $matches[1];
    error_log("[AUTH] JWT extrait: " . substr($jwt, 0, 20) . '...'); // Débogage partiel du JWT

    // Récupère la clé secrète JWT_SECRET définie dans votre .env.
    $secretKey = $_ENV['JWT_SECRET'] ?: getenv('JWT_SECRET');

    // Débogage : Vérifie si la clé secrète est chargée
    error_log("[AUTH] Clé secrète JWT via getenv(): " . ($secretKey ? 'DÉFINIE (taille: ' . strlen($secretKey) . ')' : 'NON DÉFINIE'));

    if (empty($secretKey)) {
        error_log("[AUTH] Erreur : JWT_SECRET n'est pas définie dans l'environnement.");
        Response::error('Erreur de configuration du serveur.', 500); // 500 car c'est une erreur serveur
        return null;
    }

    try {
        $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
        error_log("[AUTH] JWT décodé avec succès."); // Débogage de succès
        return $decoded->data;                          // Retourne l'objet 'data' du payload, contenant user_id, email, role.
    } catch (\Firebase\JWT\ExpiredException $e) {
        error_log("[AUTH ERROR] Token JWT expiré: " . $e->getMessage());
        Response::unauthorized('Token expiré: ' . $e->getMessage());
        return null;
    } catch (\Firebase\JWT\SignatureInvalidException $e) {
        error_log("[AUTH ERROR] Signature JWT invalide: " . $e->getMessage());
        Response::unauthorized('Signature du token invalide: ' . $e->getMessage());
        return null;
    } catch (\UnexpectedValueException $e) {
        error_log("[AUTH ERROR] Token invalide (UnexpectedValueException): " . $e->getMessage());
        Response::unauthorized('Token invalide: ' . $e->getMessage());
        return null;
    } catch (\Exception $e) {
        error_log("[AUTH ERROR] Erreur inattendue lors de la validation du JWT: " . $e->getMessage());
        Response::error('Erreur interne du serveur lors de l\'authentification.', 500);
        return null;
    }
}

// --- DÉBUT DU ROUTAGE PRINCIPAL ---
try {
    // --- Détermination robuste du chemin de la ressource ---
    // Cette logique fonctionne à la fois avec Apache/.htaccess et le serveur de dev PHP.
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    // Extrait le chemin sans les paramètres de requête (ex: /backend/api/suppliers/123)
    $pathOnly = parse_url($requestUri, PHP_URL_PATH);

    $apiPrefix = '/backend/api/';
    $uri = '';

    // Vérifie si l'URL commence par le préfixe de l'API et extrait le chemin de la ressource
    if (is_string($pathOnly) && strpos($pathOnly, $apiPrefix) === 0) {
        $uri = substr($pathOnly, strlen($apiPrefix));
    }

    // Divise l'URI en segments et nettoie les segments vides.
    $routeSegments = array_values(array_filter(explode('/', trim($uri, '/'))));

    $endpoint = $routeSegments[0] ?? '';
    $id       = null;
    if (isset($routeSegments[1]) && is_numeric($routeSegments[1])) {
        $id = (int) $routeSegments[1];
    }
    $params = ['id' => $id];

    $method = $_SERVER['REQUEST_METHOD'];

    // Définition des routes publiques (qui ne nécessitent pas d'authentification)
    $publicRoutes = ['auth'];

    $currentUser = null;

    // Application du middleware d'authentification si la route n'est pas publique
    if (! in_array($endpoint, $publicRoutes)) {
        $currentUser = authenticateRequest(); // Si l'authentification échoue, un exit est appelé ici.
    }

    // Logique de chargement des handlers de route
    if (! empty($endpoint)) {
        // Les routes "core" sont celles qui sont directement dans backend/api/ (ex: auth.php, me.php)
        $coreApiRoutes = ['auth', 'me']; // 'me' devrait être protégé si c'est pour l'utilisateur courant

        if (in_array($endpoint, $coreApiRoutes)) {
            loadRouteFile(__DIR__ . '/' . $endpoint . '.php', $method, $params, $currentUser);
        } else {
            // Toutes les autres routes (ex: 'users', 'products', 'suppliers')
            // sont censées se trouver dans le sous-dossier 'routes/'.
            $filePath = __DIR__ . '/routes/' . $endpoint . '.php';

            // Le handler lui-même est maintenant responsable de la gestion de l'ID.
            $handlerMethod = $method;

            // Log de débogage pour vérifier la méthode du handler
            error_log("[ROUTER] Tentative de chargement du handler: Endpoint='{$endpoint}', File='{$filePath}', HandlerMethod='{$handlerMethod}'");

            loadRouteFile($filePath, $handlerMethod, $params, $currentUser);
        }
    } else {
        // Si l'URI est vide (par exemple, un accès direct à http://localhost:8000/backend/api/ ou votre URL réelle https://akara-backend.fly.dev/ en production),
        Response::json(['message' => 'Bienvenue sur votre API RESTful !', 'version' => '1.0'], 200);
    }
} catch (\Throwable $e) {
    error_log('Unhandled exception in router: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
    Response::error('Une erreur inattendue est survenue.', 500);
}
