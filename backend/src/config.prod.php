<?php
// Paramètres de la base de données pour la production
define('DB_HOST', 'mysql-ejyr.alwaysdata.net');
define('DB_USER', 'ejyr_admin_btp');
define('DB_PASS', 'Kevazingo01');
define('DB_NAME', 'ejyr_akara_prod');

// Secret pour le Hash de mot de passe (si utilisé)
//define('APP_PASSWORD_DEFAULT', 'prod_secret_hash');

// Secret pour JWT (si utilisé)
define('JWT_SECRET', 'LeMarcheestsuperCoolEn2010@');

// SMTP pour les emails (optionnel)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'votre_email@gmail.com');
define('SMTP_PASS', 'mot_de_passe_app_gmail');
define('SMTP_PORT', 587);

// Chemin vers les exports
define('EXPORT_DIR', __DIR__ . '/exports/');
