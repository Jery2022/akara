# Utiliser une image PHP officielle
FROM php:8.2-apache

WORKDIR /app

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Installer l'extension intl
RUN apt-get update && apt-get install -y libicu-dev \
    && docker-php-ext-install intl

# Activer le module rewrite d'Apache
RUN a2enmod rewrite

# Copier tout le code dans /app (pour accès aux .env, src, etc.)
COPY . /app

COPY ./src/db.php /var/www/db.php
COPY ./src/functions.php /var/www/functions.php
COPY ./src/bootstrap.php /var/www/bootstrap.php
COPY ./src/views/ /var/www/views/
COPY ./.env.local /var/www/.env.local
COPY ./.env.demo /var/www/.env.demo
COPY ./.env.prod /var/www/.env.prod

# Copier le dossier public dans la racine web d'Apache
COPY ./src/public/ /var/www/html/

# Copier le .htaccess pour le rewriting
COPY ./src/public/.htaccess /var/www/html/.htaccess

# Exposer le port 80
EXPOSE 80