# Étape 1: Construire l'application React
# Utilisation de la version LTS la plus récente de Node.js sur une base Alpine pour la sécurité et la taille.
FROM node:24-alpine as buil

WORKDIR /app

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

# Copier la configuration Nginx personnalisée
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copier les fichiers de build de l'étape précédente
COPY --from=build /app/build /usr/share/nginx/html

# Exposer le port 80
EXPOSE 80

# Démarrer Nginx
CMD ["nginx", "-g", "daemon off;"]
