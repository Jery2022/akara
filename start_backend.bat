@echo off
setlocal

:: Définir l'environnement par défaut
set "APP_ENV=dev"

:: Vérifier si un argument est passé
if not "%1"=="" (
    set "APP_ENV=%1"
)

echo Lancement de l'application Akara en mode : %APP_ENV%

:: Lancer le serveur PHP avec la variable d'environnement AKARA_ENV
:: Utilisation de 'cmd /c' pour s'assurer que la variable d'environnement est définie pour le processus PHP
cmd /c "set AKARA_ENV=%APP_ENV% && php -S localhost:8000 -t akara akara/server.php"

endlocal
