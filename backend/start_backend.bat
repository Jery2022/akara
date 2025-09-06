@echo off
setlocal

:: Définir l'environnement par défaut
set "APP_ENV=dev"

:: Vérifier si un argument est passé et le nettoyer des espaces
if not "%1"=="" (
    for /f "tokens=*" %%a in ("%1") do set "APP_ENV=%%a"
)

echo Lancement de l'application Akara en mode : %APP_ENV%

:: Lancer le serveur PHP avec la variable d'environnement AKARA_ENV
:: Utilisation de 'cmd /c' pour s'assurer que la variable d'environnement est définie pour le processus PHP
cmd /c "cd backend && set AKARA_ENV=%APP_ENV% && php -S localhost:8000 -t . server.php"

endlocal
