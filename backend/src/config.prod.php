<?php
// Paramètres de la base de données pour la production
define('DB_HOST', env('DB_HOST'));
define('DB_USER', env('DB_USER'));
define('DB_PASS', env('DB_PASS'));
define('DB_NAME', env('DB_NAME'));

// Secret pour le Hash de mot de passe (si utilisé)
//define('APP_PASSWORD_DEFAULT', 'prod_secret_hash');

// Secret pour JWT (si utilisé)
define('JWT_SECRET', 'LeMarcheestsuperCoolEn2010@');

define('REACT_APP_API_URL', 'https://akara-backend.fly.dev');

// SMTP pour les emails (optionnel)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'votre_email@gmail.com');
define('SMTP_PASS', 'mot_de_passe_app_gmail');
define('SMTP_PORT', 587);

// Chemin vers les exports
define('EXPORT_DIR', __DIR__ . '/exports/');
