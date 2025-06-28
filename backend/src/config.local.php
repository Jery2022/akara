<?php
// Paramètres de la base de données sur Alwaysdata
define('DB_HOST', 'mysql-votre-site.alwaysdata.net');
define('DB_USER', 'votre_user_alwaysdata');
define('DB_PASS', 'mot_de_passe_alwaysdata');
define('DB_NAME', 'votre_db_alwaysdata');

// Secret pour le Hash de mot de passe (si utilisé)
define('PASSWORD_DEFAULT', '');

// Secret pour JWT (si utilisé)
define('JWT_SECRET', 'super_secret_key_123');

// SMTP pour les emails (optionnel)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'votre_email@gmail.com');
define('SMTP_PASS', 'mot_de_passe_app_gmail');
define('SMTP_PORT', 587);

// Chemin vers les exports
define('EXPORT_DIR', __DIR__ . '/exports/');
