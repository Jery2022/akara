#!/bin/sh

set -e

# Exécuter envsubst pour remplacer les variables d'environnement dans la configuration Nginx
envsubst '\$NGINX_BACKEND_HOST' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

# Exécuter la commande originale de Nginx
exec "$@"
