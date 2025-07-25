<?php
// backend/config/db.php

/**
 * Fonction utilitaire pour récupérer une variable d'environnement chargée.
 *
 * Cette fonction vérifie d'abord getenv() (variables d'environnement système ou putenv),
 * puis $_ENV (variables chargées par phpdotenv dans le script PHP).
 *
 * @param string $key La clé de la variable d'environnement à récupérer.
 * @param mixed $default La valeur par défaut à retourner si la clé n'est pas trouvée.
 * @return mixed La valeur de la variable d'environnement ou la valeur par défaut.
 */
function env(string $key, mixed $default = null): mixed
{
    // Priorise getenv() car il est souvent utilisé pour les variables système.
    // phpdotenv met les variables à la fois dans $_ENV et getenv().
    $value = getenv($key);
    return $value !== false ? $value : ($_ENV[$key] ?? $default);
}

/**
 * Retourne une instance PDO connectée à la base de données.
 *
 * Cette fonction utilise les variables d'environnement (chargées par server.php)
 * pour établir la connexion à la base de données. Elle gère également les erreurs
 * de connexion et assure une configuration PDO robuste.
 *
 * @return PDO Une instance de PDO connectée.
 * @throws RuntimeException Si les paramètres de connexion à la base de données sont manquants ou si la connexion échoue.
 */
function getPDO(): PDO
{
    $dbHost    = env('DB_HOST');
    $dbName    = env('DB_NAME');
    $dbUser    = env('DB_USER');
    $dbPass    = env('DB_PASS');
    $dbCharset = env('DB_CHARSET') ?: 'utf8mb4'; // Définit utf8mb4 par défaut si non spécifié

    // Vérification cruciale : assurez-vous que les paramètres essentiels sont définis.
    if (! $dbHost || ! $dbName || ! $dbUser) {
        error_log("[DB] ERREUR CRITIQUE: Paramètres de connexion à la base de données manquants. Vérifiez DB_HOST, DB_NAME, DB_USER dans votre fichier .env.");
        throw new RuntimeException("Paramètres de connexion à la base de données manquants. Vérifiez votre configuration.");
    }

    // Construction de la chaîne DSN (Data Source Name) pour PDO.
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";

    // Options PDO pour une gestion d'erreurs robuste et un comportement de fetch cohérent.
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lève des exceptions en cas d'erreurs SQL.
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Récupère les résultats sous forme de tableau associatif.
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Désactive l'émulation des requêtes préparées pour plus de sécurité et de performance.
    ];

    try {
        // Tente de créer une nouvelle instance PDO (connexion à la base de données).
        $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
        error_log("[DB] Connexion à la base de données établie avec succès."); // Log de succès pour le débogage
        return $pdo;
    } catch (\PDOException $e) {
        // En cas d'échec de la connexion PDO, log l'erreur détaillée.
        error_log("[DB ERROR] Échec de la connexion à la base de données. Détails: " . $e->getMessage());
        // Relance une RuntimeException pour que l'erreur puisse être capturée et traitée par le routeur principal (index.php).
        throw new RuntimeException("Échec de la connexion à la base de données. Veuillez contacter l'administrateur.", 0, $e);
    }
}
