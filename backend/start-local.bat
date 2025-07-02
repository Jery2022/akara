@echo off
setlocal enabledelayedexpansion

:: === MENU ===
:menu
cls
echo ================================
echo     LANCEMENT DE AKARA EN LOCAL
echo ================================
echo.
echo 1. Environnement Local
echo 2. Quitter
echo.

set /p choice=Choisissez une option [1 ou 2] : 

if "%choice%"=="1" (
    set "ENV_NAME=local"
) else if "%choice%"=="2" (
    echo Fermeture du script.
    exit /b 0
) else (
    echo Option invalide. Appuyez sur une touche pour réessayer...
    pause >nul
    goto menu
)

:: === CHARGEMENT DU FICHIER .env ===
set "ENV_FILE=.env.%ENV_NAME%"

if not exist "%ENV_FILE%" (
    echo ❌ Le fichier %ENV_FILE% est introuvable.
    pause
    exit /b 1
)

echo 📦 Chargement des variables depuis %ENV_FILE%...

for /f "usebackq tokens=1,* delims==" %%A in ("%ENV_FILE%") do (
    set "LINE=%%A"
    if not "!LINE!"=="" if "!LINE:~0,1!" NEQ "#" (
        set "KEY=%%A"
        set "VALUE=%%B"
        set "!KEY!=!VALUE!"
    )
)

:: === LANCEMENT DU SERVEUR PHP ===
set "PORT=8000"
set /p PORT=Entrez le port du serveur PHP [8000] : 
if "%PORT%"=="" set "PORT=8000"

echo 🚀 Lancement du serveur PHP sur http://localhost:%PORT% ...
cd src
php -S localhost:%PORT%  

echo ✅ Serveur lancé. Appuyez sur une touche pour quitter ce terminal.
pause