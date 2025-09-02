<?php
require_once __DIR__ . '/vendor/autoload.php';

// Définir le chemin de base du projet (akara/backend)
define('BASE_PATH', dirname(__DIR__));

// Charger les variables d'environnement
$envFile = file_exists(BASE_PATH . '/.env.local') ? '.env.local' : '.env';
$dotenv  = Dotenv\Dotenv::createImmutable(BASE_PATH, $envFile);
$dotenv->load();

// Fonction utilitaire pour récupérer une variable d'environnement chargée.
function env(string $key, mixed $default = null): mixed
{
    $value = getenv($key);
    return $value !== false ? $value : ($_ENV[$key] ?? $default);
}
