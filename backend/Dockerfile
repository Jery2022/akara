# Utiliser une image PHP officielle
FROM php:8.1-apache

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copier le code source dans le conteneur
COPY ./src/ /var/www/html/

# Exposer le port 80
EXPOSE 80