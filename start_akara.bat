@echo off
set PROJECT_ROOT=%~dp0
cd /d "%PROJECT_ROOT%"

echo [DEBUG] Le dossier racine du projet est : "%PROJECT_ROOT%"

REM --- Confirmez le chemin de PHP.exe (il manquait dans votre version) ---
REM C'est crucial que php.exe soit trouvé, sinon le serveur ne démarre pas.
set PHP_EXE="C:\xampp\php\php.exe"
echo [DEBUG] Le chemin de PHP.exe est défini à : %PHP_EXE%
if not exist %PHP_EXE% (
    echo [ERREUR] php.exe n'a pas été trouvé à %PHP_EXE%. VEUILLEZ VÉRIFIER CE CHEMIN ET LE CORRIGER.
    pause
    exit /b 1
)


REM --- Confirmez le chemin du dossier public (-t) ---
REM **CORRECTION MAJEURE ICI : PRÉFIXER AVEC %PROJECT_ROOT%**
set PUBLIC_DOC_ROOT="%PROJECT_ROOT%backend\src\public"
echo [DEBUG] Le chemin de la racine des documents (-t) est : %PUBLIC_DOC_ROOT%
if not exist %PUBLIC_DOC_ROOT% (
    echo [ERREUR] Le dossier public n'existe PAS à : %PUBLIC_DOC_ROOT%. VEUILLEZ VÉRIFIER CE CHEMIN.
    pause
    exit /b 1
)

REM --- Confirmez le chemin de server.php (fallback script) ---
set FALLBACK_SCRIPT="%PROJECT_ROOT%server.php"
echo [DEBUG] Le chemin du script de fallback (server.php) est : %FALLBACK_SCRIPT%
if not exist %FALLBACK_SCRIPT% (
    echo [ERREUR] server.php n'existe PAS à : %FALLBACK_SCRIPT%. VEUILLEZ VÉRIFIER CE CHEMIN.
    pause
    exit /b 1
)

echo.
echo [INFO] Démarrage du serveur PHP sur localhost:8000
echo [INFO] Racine des documents: %PUBLIC_DOC_ROOT%
echo [INFO] Script de fallback: %FALLBACK_SCRIPT%
echo.

php -S localhost:8000 -t %PUBLIC_DOC_ROOT% %FALLBACK_SCRIPT%

echo.
echo Le serveur PHP a été arrêté.
pause
