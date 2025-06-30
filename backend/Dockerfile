# Utiliser une image PHP officielle
FROM php:8.1-apache

# Installer les extensions PHP nécessaires
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Installer l'extension intl
RUN apt-get update && apt-get install -y libicu-dev \
    && docker-php-ext-install intl

# Copier le code source dans le conteneur
COPY ./src/ /var/www/html/

# Exposer le port 80
EXPOSE 80 