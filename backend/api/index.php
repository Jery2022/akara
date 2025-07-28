<?php
// backend/api/index.php

// Active l'affichage des erreurs PHP (utile en développement)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 1. Définir l'origine autorisée (votre frontend React)
// EN DÉVELOPPEMENT: Mettre l'URL exacte de votre frontend React
header("Access-Control-Allow-Origin: http://localhost:3000");

// EN PRODUCTION: Remplacez par votre domaine réel de production, ou gérez les multiples origines avec prudence
// $allowed_origins = ['https://votre-domaine-frontend.com', 'http://localhost:3000'];
// if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed_origins)) {
//     header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
// } else {
//     // Si l'origine n'est pas autorisée, refuser l'accès ou envoyer une origine par défaut non permissive
//     header("Access-Control-Allow-Origin: http://localhost:3000"); // Ou une URL non valide pour bloquer
// }

// 2. Définir les méthodes HTTP autorisées
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

// 3. Définir les en-têtes qui peuvent être envoyés par le client
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// 4. Autoriser l'envoi de cookies et d'identifiants (très important car votre React utilise credentials: 'include')
header("Access-Control-Allow-Credentials: true");

// 5. Définir la durée de validité du preflight (en secondes)
header("Access-Control-Max-Age: 86400"); // Cache la réponse preflight pendant 24 heures

// 6. Gérer la requête OPTIONS (le "preflight" du navigateur)
// Si la requête est de type OPTIONS, nous avons juste besoin d'envoyer les en-têtes CORS
// et de terminer l'exécution du script, car il s'agit d'une vérification préalable.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200); // Répondre avec un statut 200 OK
    exit();                  // Terminer le script ici
}

// 7. Définir le type de contenu de la réponse pour les requêtes réelles (si votre API renvoie du JSON)
header("Content-Type: application/json");

// Votre autoloader pour les classes locales (comme Core\Response)
spl_autoload_register(function ($className) {
    // Convertit le namespace en chemin de fichier (ex: Core\Response -> Core/Response.php)
    // __DIR__ ici est backend/api/
    $file = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Inclure explicitement Response au cas où l'autoloader ne la trouverait pas immédiatement.
// C'est redondant avec l'autoload si bien configuré, mais ne fait pas de mal.
require_once __DIR__ . '/Core/Response.php';

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

    // Débogage : Vérifie la présence et le format du header Authorization
    error_log("[AUTH] Auth Header reçu: " . (empty($authHeader) ? "VIDE" : $authHeader));

    if (empty($authHeader) || ! preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        error_log("[AUTH] Token d'authentification manquant ou mal formé (pas de Bearer).");
        Response::unauthorized('Token d\'authentification manquant ou mal formé.');
        return null;
    }

    $jwt = $matches[1];
    error_log("[AUTH] JWT extrait: " . substr($jwt, 0, 20) . '...'); // Log partiel du JWT

    // Récupère la clé secrète. Assurez-vous que JWT_SECRET_KEY est définie dans votre .env.
    $secretKey = $_ENV['JWT_SECRET_KEY'] ?: getenv('JWT_SECRET_KEY');

    // Débogage : Vérifie si la clé secrète est chargée
    error_log("[AUTH] Clé secrète JWT via getenv(): " . ($secretKey ? 'DÉFINIE (taille: ' . strlen($secretKey) . ')' : 'NON DÉFINIE'));

    if (empty($secretKey)) {
        error_log("[AUTH] Erreur : JWT_SECRET n'est pas définie dans l'environnement.");
        Response::error('Erreur de configuration du serveur.', 500); // 500 car c'est une erreur serveur
        return null;
    }

    try {
        $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));
        error_log("[AUTH] JWT décodé avec succès."); // Log de succès
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

$uri       = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$apiPrefix = '/backend/api';

// Supprime le préfixe de l'API de l'URI pour obtenir le chemin de la route interne.
if (strpos($uri, $apiPrefix) === 0) {
    $uri = substr($uri, strlen($apiPrefix));
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

        // Détermine la méthode à appeler dans le fichier de route.
        // Si un ID est présent, la méthode peut être suffixée par '_ID' (ex: 'GET_ID').
        // C'est une convention que vous avez choisie et que vos fichiers de route doivent respecter.
        $methodToCall = $method;
        if ($id !== null) {
            $methodToCall = $method . '_ID';
        }

        loadRouteFile($filePath, $methodToCall, $params, $currentUser);
    }
} else {
    // Si l'URI est vide (par exemple, un accès direct à http://localhost:8000/backend/api/),
    Response::json(['message' => 'Bienvenue sur votre API RESTful !', 'version' => '1.0'], 200);
}
