<?php
// require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../Core/Response.php';

use Core\Response;

// Fonction pour charger un fichier de route et exécuter la fonction selon la méthode HTTP
function loadRouteFile(string $filePath, string $method): void
{
    if (! file_exists($filePath)) {
        Response::error("Route non trouvée : " . basename($filePath), 404);
        return;
    }

    // Inclure le fichier, qui doit retourner un tableau associatif ['GET' => fn(), 'POST' => fn(), ...]
    $handlers = require $filePath;

    if (! is_array($handlers) || ! isset($handlers[$method])) {
        Response::error("Méthode HTTP non autorisée pour cette route", 405);
        return;
    }

    // Exécuter le handler correspondant
    call_user_func($handlers[$method]);
}

// Extraire la route demandée
$uri   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$route = trim($uri, '/');

// Extraire le premier segment
$segments = explode('/', $route);
$endpoint = $segments[0];

// Routes spéciales à la racine de src/
$specialRoutes = [
    'admin_dashboard'    => __DIR__ . '/../admin_dashboard.php',
    'employee_dashboard' => __DIR__ . '/../employee_dashboard.php',
    'login'              => __DIR__ . '/../login.php',
    'logout'             => __DIR__ . '/../logout.php',
];

// Routes API classiques
$coreRoutes = ['auth', 'me'];

$method = $_SERVER['REQUEST_METHOD'];

if (array_key_exists($endpoint, $specialRoutes)) {
    loadRouteFile($specialRoutes[$endpoint], $method);
} elseif (in_array($endpoint, $coreRoutes)) {
    loadRouteFile(__DIR__ . '/' . $endpoint . '.php', $method);
} else {
    loadRouteFile(__DIR__ . '/routes/' . $endpoint . '.php', $method);
}
