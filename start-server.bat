@echo off
title Agrinova Symfony Server
cd /d "%~dp0"

echo ====================================
echo   Starting Agrinova Symfony Server
echo ====================================
echo.

REM Check if Symfony CLI is available
where symfony >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo Using Symfony CLI...
    symfony server:start --port=8000
) else (
    echo Symfony CLI not found, using PHP built-in server...
    echo Server will be available at: http://127.0.0.1:8000
    echo.
    php -S 127.0.0.1:8000 -t public
)

pause
