<?php
// Paramètres de la base de données pour le développement local
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'akara_local');

// Secret pour le Hash de mot de passe (si utilisé)
//define('APP_PASSWORD_DEFAULT', 'dev_secret_hash');

// Secret pour JWT (si utilisé)
define('JWT_SECRET', 'LeMarcheestsuperCoolEn2010@');

// SMTP pour les emails (optionnel)
define('SMTP_HOST', 'smtp.mailtrap.io');
define('SMTP_USER', 'your_mailtrap_user');
define('SMTP_PASS', 'your_mailtrap_password');
define('SMTP_PORT', 2525);

// Chemin vers les exports
define('EXPORT_DIR', __DIR__ . '/exports/');
