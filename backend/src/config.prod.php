<?php
// Paramètres de la base de données pour la production
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));

// Secret pour le Hash de mot de passe (si utilisé)
//define('APP_PASSWORD_DEFAULT', 'prod_secret_hash');

// Secret pour JWT (si utilisé)
define('JWT_SECRET', getenv('JWT_SECRET'));

// SMTP pour les emails (optionnel)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'votre_email@gmail.com');
define('SMTP_PASS', 'mot_de_passe_app_gmail');
define('SMTP_PORT', 587);

// Chemin vers les exports
define('EXPORT_DIR', __DIR__ . '/exports/');
