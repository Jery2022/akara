<?php

define('BASE_PATH', __DIR__);
//include 'bootstrap.php';
require_once BASE_PATH . '/src/vendor/autoload.php';
// backend/bootstrap.php
//require_once __DIR__ . '/vendor/autoload.php';

$envFile = file_exists(BASE_PATH . '/.env.local') ? '.env.local' : '.env';
$dotenv  = Dotenv\Dotenv::createImmutable(BASE_PATH, $envFile);
$dotenv->load();

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
