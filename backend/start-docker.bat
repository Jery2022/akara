@echo off
setlocal enabledelayedexpansion

:menu
cls
echo ================================
echo   LANCEMENT DE DOCKER COMPOSE
echo ================================
echo.
echo 1. Environnement de Local
echo 2. Environnement de DEV (Doker)
echo 3. Environnement de PROD
echo 4. Quitter
echo.

set /p choice=Choisissez une option [1-4] : 

if "%choice%"=="1" (
    set ENV_NAME=local
) else if "%choice%"=="2" (
    set ENV_NAME=dev
) else if "%choice%"=="3" (
set ENV_NAME=prod
) else if "%choice%"=="4" (
    echo Fermeture du script.
    exit /b 0
) else (
    echo Option invalide. Appuyez sur une touche pour réessayer...
    pause >nul
    goto menu
)

set ENV_FILE=.env.%ENV_NAME%

if not exist %ENV_FILE% (
    echo ❌ Le fichier %ENV_FILE% est introuvable.
    pause
    exit /b 1
)

echo 🔄 Arrêt des services Docker existants...
docker-compose down

echo 📦 Chargement des variables depuis %ENV_FILE%...

:: Charger les variables d'environnement pour cette session
for /f "usebackq tokens=1,* delims==" %%A in ("%ENV_FILE%") do (
    set "KEY=%%A"
    set "VALUE=%%B"
    if not "!KEY!"=="" (
        set "!KEY!=!VALUE!"
    )
)

echo 🚀 Lancement de Docker Compose avec l'environnement %ENV_NAME%...
docker-compose build -t app-akara .
docker-compose up -d