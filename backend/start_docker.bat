@echo off
echo Construction de l'image Docker pour le backend...
docker build -t akara-backend .
echo Lancement du conteneur Docker pour le backend...
docker run -d -p 6000:80 --name akara-backend-container akara-backend
echo Le backend Akara est maintenant en cours d'exécution dans un conteneur Docker sur le port 80.
echo Vous pouvez y accéder via http://localhost:6000
