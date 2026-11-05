@echo off
title Custom PC ERP - Local Server
cd /d "%~dp0"

echo ========================================================
echo   Custom PC ERP - Supply Chain & Inventory Server
echo ========================================================
echo.

:: Add PHP to PATH if needed
set "PATH=%PATH%;%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe;C:\Users\%USERNAME%\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe"

where php >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] PHP executable was not found.
    pause
    exit /b 1
)

echo Starting PHP built-in web server at http://localhost:8000
echo Press Ctrl+C to stop the server.
echo.

php -S localhost:8000 index.php
pause
