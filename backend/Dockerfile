# Utiliser une image PHP officielle avec Apache
FROM php:8.2-apache

# Définir le répertoire de travail sur la racine web d'Apache
WORKDIR /var/www/html

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libzip-dev \
    libmariadb-dev \
    libonig-dev \
    git \
    curl \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configurer et installer l'extension GD
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Installer les extensions PHP de base pour la base de données et l'internationalisation
RUN docker-php-ext-install -j$(nproc) \
    mysqli \
    pdo \
    pdo_mysql \
    intl

# Installer l'extension PHP fileinfo
RUN docker-php-ext-install -j$(nproc) fileinfo

# Installer l'extension PHP zip
RUN docker-php-ext-install -j$(nproc) zip


# Installer Composer (gestionnaire de dépendances PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier les fichiers de l'application
COPY . .

# Installer les dépendances Composer
WORKDIR /var/www/html/src
RUN composer install --no-interaction --no-dev --optimize-autoloader
WORKDIR /var/www/html

# Définir les permissions pour Apache
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html -type f -exec chmod 644 {} \;

# Activer le module rewrite d'Apache
RUN a2enmod rewrite

# Appliquer la configuration Apache personnalisée
COPY docker/apache-conf.conf /etc/apache2/sites-available/000-default.conf

# Redémarrer la configuration proprement
RUN a2ensite 000-default.conf && \
    service apache2 restart

# Exposer le port HTTP
EXPOSE 80
