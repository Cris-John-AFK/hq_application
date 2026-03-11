@echo off
setlocal enabledelayedexpansion

:: =====================================================================
:: HQ-APP ONE-CLICK START & AUTO-RECOVERY
:: =====================================================================
:: This script finds the current IP and starts the Production Server.
:: =====================================================================

echo [1/3] Detecting Network Identity...
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr "IPv4 Address"') do (
    set IP=%%a
    set IP=!IP:^ =!
    goto :found
)

:found
echo Current IP Address: %IP%

:: Update .env automatically so API links never break
echo [2/3] Syncing Environment Config...
powershell -Command "(gc .env) -replace 'APP_URL=.*', 'APP_URL=http://%IP%:8000' | Out-File -encoding ASCII .env"

echo [3/3] Launching HQ-App Professional Server...
echo ---------------------------------------------------
echo YOUR CENTRAL URL: http://%IP%:8000
echo ---------------------------------------------------

:: Start Laravel Backend in a new hidden/minimized window
:: This will serve the compiled/built frontend assets from public/build
start /min "HQ-SERVER" php artisan serve --host=0.0.0.0 --port=8000

echo.
echo SUCCESS: System is now RUNNING on the local network.
echo.
echo NOTE: Since we are in PRODUCTION mode, the frontend code
echo is compiled and protected from manual editing.
echo.
echo Keep this window open. Close it to STOP the server.
pause

