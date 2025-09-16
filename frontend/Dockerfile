# Étape 1: Construire l'application React
# Utilisation de la version LTS la plus récente de Node.js sur une base Alpine pour la sécurité et la taille.
FROM node:24-alpine as build

WORKDIR /app

# Définir l'URL de l'API pour le frontend
# Utiliser un chemin relatif pour que Nginx puisse agir comme proxy inverse.
ENV REACT_APP_API_URL=/api
ENV NGINX_BACKEND_HOST=akara-backend.internal

# Copier package.json et package-lock.json pour profiter de la mise en cache
COPY package*.json ./

# Utiliser npm ci pour des installations déterministes et plus rapides
RUN npm ci

# Copier le reste des fichiers de l'application
COPY . .

# Construire l'application pour la production
RUN npm run build

# Étape 2: Servir l'application avec Nginx
# Utilisation d'une version Nginx spécifique et récente sur une base Alpine.
FROM nginx:alpine3.22-perl

RUN apk add --no-cache dos2unix

# Copier la configuration Nginx personnalisée en tant que template
COPY nginx.conf /etc/nginx/conf.d/default.conf.template

# Copier les fichiers de build de l'étape précédente
COPY --from=build /app/build /usr/share/nginx/html

# Exposer le port 80
EXPOSE 80

# Démarrer Nginx, en exécutant envsubst au démarrage pour injecter les variables d'environnement
# Copier le script d'entrée personnalisé et le rendre exécutable
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN dos2unix /docker-entrypoint.sh
RUN chmod +x /docker-entrypoint.sh
RUN ls -l /docker-entrypoint.sh
RUN ls -l /

# Utiliser le script d'entrée personnalisé
ENTRYPOINT ["sh", "/docker-entrypoint.sh"]
CMD ["nginx", "-g", "daemon off;"]
