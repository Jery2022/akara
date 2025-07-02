<?php
require_once __DIR__ . '/src/vendor/autoload.php';

//define('BASE_PATH', __DIR__);

// require_once BASE_PATH . '/src/vendor/autoload.php';

// $envFile = file_exists(BASE_PATH . '/.env.local') ? '.env.local' : '.env';
// $dotenv  = Dotenv\Dotenv::createImmutable(BASE_PATH, $envFile);
// $dotenv->load();

// function env(string $key, $default = null)
// {
//     return $_ENV[$key] ?? $default;
// }

//var_dump(env('DB_USER'), env('DB_PASS'));exit; //log

// try {
//     $pdo = new PDO(
//         "mysql:host=" . env('DB_HOST') . ";dbname=" . env('DB_NAME') . ";charset=utf8mb4",
//         env('DB_USER'),
//         env('DB_PASS'),
//         [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
//     );
// } catch (PDOException $e) {
//     http_response_code(500);
//     echo json_encode([
//         'error'   => 'Erreur de connexion à la base de données',
//         'details' => $e->getMessage(),
//     ]);
//     exit;
// }

//require_once BASE_PATH . '/functions.php';
