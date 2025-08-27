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
$uri           = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER['REQUEST_METHOD']; // Obtenir la méthode HTTP (GET, POST, etc.)

// --- Définir le chemin absolu du dossier public ---
$publicPath = __DIR__ . '/backend/src/public';

// --- Vérifier si la requête est pour un fichier PHP dans le dossier public ---
// Cette partie est la NOUVELLE logique pour gérer les routes de l'application d'administration.
// Elle s'exécute SI le serveur intégré n'a PAS servi le fichier directement (ce qui semble être le cas).
$filePath = $publicPath . $uri; // Construit le chemin complet du fichier demandé

// Si l'URI est pour la racine (/), on pourrait vouloir rediriger vers index.php de l'admin
if ($uri === '/') {
    $filePath = $publicPath . '/index.php';
}

// --- Servir les fichiers statiques (images, CSS, JS, etc.) ---
$staticFile = $publicPath . $uri;

if (file_exists($staticFile) && ! is_dir($staticFile)) {
    $extension = pathinfo($staticFile, PATHINFO_EXTENSION);

    if ($extension !== 'php') {
        $mimeType = mime_content_type($staticFile);
        header("Content-Type: $mimeType");
        readfile($staticFile);
        exit;
    }
}

// Assurez-vous que le fichier est un fichier PHP (pas de .htaccess, .css, etc. qui devraient être servis statiquement)
// Et qu'il existe réellement.
if (pathinfo($filePath, PATHINFO_EXTENSION) === 'php' && file_exists($filePath)) {
    $_SERVER['SCRIPT_FILENAME'] = $filePath;
    $_SERVER['SCRIPT_NAME']     = $uri; // Rend l'URI originale disponible comme nom de script
    // Note: $_SERVER['PHP_SELF'] peut être problématique, utilisez SCRIPT_NAME ou REQUEST_URI

    require $filePath;
    exit; // Arrête l'exécution après avoir servi le fichier PHP d'administration
}

// --- Si ce n'était pas un fichier PHP dans public, vérifier les routes API ---
if (strpos($uri, '/backend/api/') === 0) {
    // Extraire le chemin de la route pour que index.php puisse le lire.
    // Exemple: de "/backend/api/auth" on extrait "auth".
    $_GET['path'] = substr($uri, strlen('/backend/api/'));

    require __DIR__ . '/backend/api/index.php';
    exit; // Arrête l'exécution après que l'API ait traité la requête.
}

// --- Si la requête ne correspond à aucune route connue (fichier PHP ou API), renvoie une 404. ---
http_response_code(404);
echo "Page non trouvée.";
