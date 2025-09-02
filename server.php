<?php
// server.php

// --- 1. Charger l'autoloader de Composer en premier ---
require_once __DIR__ . '/backend/src/vendor/autoload.php';

// --- 2. Définir l'environnement de l'application et charger le fichier .env correspondant ---
$appEnv = getenv('APP_ENV') ?: 'local';
putenv("APP_ENV={$appEnv}");
$_ENV['APP_ENV'] = $appEnv;

$dotenvFilename = '.env.' . $appEnv;

if (file_exists(__DIR__ . '/' . $dotenvFilename)) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__, $dotenvFilename);
    $dotenv->load();
    error_log("[SERVER] Fichier d'environnement '{$dotenvFilename}' chargé avec succès.");
} else {
    error_log("[SERVER] AVERTISSEMENT : Le fichier d'environnement '{$dotenvFilename}' n'a pas été trouvé à " . __DIR__ . ".");
    error_log("[SERVER] Les variables d'environnement requises (DB_HOST, DB_NAME, JWT_SECRET_KEY, etc.) pourraient manquer.");
}

// --- 3. Gérer les requêtes entrantes ---
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD'];

// --- Routeur pour l'API ---
if (strpos($uri, '/backend/api/') === 0) {
    // Définition des en-têtes CORS pour l'API
    header("Access-Control-Allow-Origin: http://localhost:3000");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Max-Age: 86400");

    if ($requestMethod == 'OPTIONS') {
        http_response_code(200);
        exit();
    }

    $apiPath = substr($uri, strlen('/backend/api/'));
    // Ne pas utiliser $_GET car il n'est pas fiable pour les requêtes PUT/DELETE avec le serveur intégré.
    // On passe plutôt une variable locale au script inclus.
    $path = $apiPath;

    error_log("[SERVER] Requête API détectée. URI: {$uri}, Path: {$path}");
    require __DIR__ . '/backend/api/index.php';
    exit;
}

// --- Servir l'application Frontend (React) ---
$frontendPath = __DIR__ . '/frontend/public';
$filePath = $frontendPath . $uri;

// Si la requête est pour un fichier statique existant (css, js, image, etc.)
if (file_exists($filePath) && !is_dir($filePath)) {
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'json' => 'application/json',
        'html' => 'text/html',
    ];

    $mimeType = $mimeTypes[$extension] ?? mime_content_type($filePath);
    header("Content-Type: {$mimeType}");
    readfile($filePath);
    exit;
}

// Pour toutes les autres requêtes, servir le index.html de React (gestion du routage côté client)
$indexPath = $frontendPath . '/index.html';
if (file_exists($indexPath)) {
    readfile($indexPath);
} else {
    http_response_code(404);
    echo "Application non trouvée. Le fichier index.html est manquant.";
}
