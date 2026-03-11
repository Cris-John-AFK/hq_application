@echo off
:: =====================================================================
:: REMOTE HQ-APP UPDATE SCRIPT
:: =====================================================================
:: This script pulls the latest changes from GitHub and updates the server.
:: IMPORTANT: This ONLY works if you used "git clone" to install the app.
:: =====================================================================

echo [1/3] Pulling latest code from GitHub...
git pull origin main

echo [2/3] Updating database schema...
php artisan migrate --force

echo [3/3] Re-building frontend (Security Scramble)...
call npm run build

echo.
echo SUCCESS: The system has been updated to the latest version.
echo You can now run START HQ-APP.bat to enjoy the updates.
pause