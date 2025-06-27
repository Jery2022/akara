<?php
require_once 'db.php';
require_once 'functions.php';

header("Content-Type: application/json");

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Protection contre l'inclusion de fichiers non autorisés
$allowed_routes = [
    'users',
    'suppliers',
    'customers',
    'produits',
    'stock',
    'employees',
    'contrats',
    'entrepots',
    'recettes',
    'payments',
    'entreeStock',
    'sortieStock',
    'auth',
    'index',
];

$method = $_SERVER['REQUEST_METHOD'];
$route  = $_GET['route'] ?? '';

// Validation stricte de la route
if (! in_array($route, $allowed_routes, true)) {
    http_response_code(404);
    echo json_encode([
        'error' => 'Route non trouvée ou non autorisée',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Protection contre les attaques CSRF
if ($method === 'POST' || $method === 'PUT' || $method === 'DELETE') {
    session_start();
    if (! isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] !== ($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode([
            'error' => 'Token CSRF invalide'],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

// Protection contre les attaques XSS sur la route
$route = htmlspecialchars($route, ENT_QUOTES, 'UTF-8');

// Inclusion sécurisée du fichier de route
$filepath = __DIR__ . '/routes/' . $route . '.php';
if ($route === 'auth') {
    $filepath = __DIR__ . '/auth.php';
}

if (file_exists($filepath)) {
    include $filepath;
} else {
    http_response_code(404);
    echo json_encode([
        'error' => 'Fichier de route introuvable',
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
