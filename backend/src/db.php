<?php

function loadEnv(string $envFile)
{
    if (! file_exists($envFile)) {
        throw new RuntimeException("Fichier d'environnement introuvable : $envFile");
    }
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)]  = trim($value);
    }
}

// Charge le bon fichier .env selon APP_ENV
$envType = getenv('APP_ENV') ?: 'demo';      // demo, prod, local, etc.
$envFile = __DIR__ . '/../.env.' . $envType; // à modifier selon si en local ou conteneur dockeur $envFile = __DIR__ . '.env.' . $envType

loadEnv($envFile);

function env(string $key, $default = null)
{
    return $_ENV[$key] ?? $default;
}

/**
 * Retourne une instance PDO connectée à la base de données.
 */
function getPDO(): PDO
{
    $host   = env('DB_HOST');
    $dbname = env('DB_NAME');
    $user   = env('DB_USER');
    $pass   = env('DB_PASS');

    if (! $host || ! $dbname || ! $user) {
        throw new RuntimeException("Paramètres de connexion manquants.");
    }

    return new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}
