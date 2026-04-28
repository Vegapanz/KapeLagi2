@echo off
setlocal

cd /d "%~dp0"
echo ==========================================
echo KapeLagi Migration Setup
echo ==========================================
echo.

echo Installing Composer dependencies...
if exist composer.json (
    composer install
) else (
    echo composer.json not found.
    goto :end
)

echo.
echo Running database setup...
php config\setup_db.php

echo.
echo Migration setup complete.
echo If you moved this project to another device, update config\db.php with the new database credentials before running the site.

:end
pause
