<?php
// server.php

// --- 1. Charger l'autoloader de Composer en premier ---
require_once __DIR__ . '/backend/src/vendor/autoload.php';

// --- 2. Charger la configuration de l'environnement ---
$env = getenv('AKARA_ENV') ?: 'dev'; // Environnement par défaut

$configPath = __DIR__ . '/backend/src/';
$configFile = '';
switch ($env) {
    case 'dev':
        $configFile = $configPath . 'config.dev.php';
        break;
    case 'prod':
        $configFile = $configPath . 'config.prod.php';
        break;
    default:
        error_log("Environnement non reconnu: {$env}. Utilisation de l'environnement de développement par défaut.");
        $configFile = $configPath . 'config.dev.php';
        break;
}

if (file_exists($configFile)) {
    require_once $configFile;
    error_log("Fichier de configuration chargé : " . basename($configFile));
} else {
    error_log("Erreur : Le fichier de configuration '" . basename($configFile) . "' est introuvable.");
    // Vous pouvez choisir de terminer l'exécution ou de continuer avec des valeurs par défaut
    die("Erreur de configuration de l'application.");
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

// --- Servir les fichiers PHP du backend (ex: login.php, admin_dashboard.php) ---
$backendPublicPath = __DIR__ . '/backend/src/public';
$backendFilePath   = $backendPublicPath . $uri;

// Si la requête est pour un fichier PHP existant dans le dossier public du backend
if (file_exists($backendFilePath) && is_file($backendFilePath) && pathinfo($backendFilePath, PATHINFO_EXTENSION) === 'php') {
    error_log("[SERVER] Requête PHP backend détectée. Fichier: {$backendFilePath}");
    require $backendFilePath;
    exit;
}

// --- Servir les fichiers statiques du backend (ex: css, js, images) ---
// Vérifier si le fichier demandé existe dans le répertoire public du backend
if (file_exists($backendFilePath) && !is_dir($backendFilePath)) {
    $extension = pathinfo($backendFilePath, PATHINFO_EXTENSION);
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
        'pdf' => 'application/pdf', // Ajouté pour les fichiers PDF
    ];

    $mimeType = $mimeTypes[$extension] ?? mime_content_type($backendFilePath);
    if ($mimeType) {
        header("Content-Type: {$mimeType}");
    } else {
        // Fallback si mime_content_type échoue ou n'est pas disponible
        header("Content-Type: application/octet-stream");
    }
    error_log("[SERVER] Fichier statique backend détecté. Fichier: {$backendFilePath}, Type MIME: {$mimeType}");
    readfile($backendFilePath);
    exit;
}

// --- Servir l'application Frontend (React) ---
$frontendPath = __DIR__ . '/frontend/public';
$filePath     = $frontendPath . $uri;

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
