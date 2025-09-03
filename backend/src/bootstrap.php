<?php
require_once __DIR__ . '/vendor/autoload.php';

// Définir le chemin de base du projet (akara/backend)
define('BASE_PATH', dirname(__DIR__));

// Charger les variables d'environnement uniquement si AKARA_ENV n'est pas défini,
// ce qui signifie que nous ne sommes pas dans un contexte où server.php a déjà chargé la configuration.
if (! getenv('AKARA_ENV')) {
    $envFile = file_exists(BASE_PATH . '/.env.local') ? '.env.local' : '.env';
    $dotenv  = Dotenv\Dotenv::createImmutable(BASE_PATH, $envFile);
    $dotenv->load();
}

// Fonction utilitaire pour récupérer une variable d'environnement chargée.
function env(string $key, mixed $default = null): mixed
{
    // Vérifier si la constante est définie (chargée par config.dev.php ou config.prod.php via server.php)
    if (defined($key)) {
        return constant($key);
    }

    // Sinon, essayer de récupérer depuis les variables d'environnement système ou .env si utilisé
    $value = getenv($key);
    return $value !== false ? $value : ($_ENV[$key] ?? $default);
}
