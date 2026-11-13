@echo off
cls
echo ===================================
echo Rolex Shop - Quick Test
echo ===================================
echo.

REM Prompt for phpdesktop path
set /p PHPDESKTOP="Enter phpdesktop path (or press Enter for C:\phpdesktop-chrome-v57.0-rc-php-7.1.3): "
if "%PHPDESKTOP%"=="" set PHPDESKTOP=C:\phpdesktop-chrome-v57.0-rc-php-7.1.3

REM Remove trailing slash
if "%PHPDESKTOP:~-1%"=="\" set PHPDESKTOP=%PHPDESKTOP:~0,-1%

echo.
echo Checking phpdesktop at: %PHPDESKTOP%
if not exist "%PHPDESKTOP%\phpdesktop-chrome.exe" (
    echo ERROR: phpdesktop-chrome.exe not found!
    echo Please check the path.
    pause
    exit /b 1
)
echo OK

set PROJECT=%~dp0

echo.
echo Preparing test environment...

REM Clear www folder
if exist "%PHPDESKTOP%\www" (
    echo Clearing old www folder...
    rmdir /s /q "%PHPDESKTOP%\www" 2>nul
)
mkdir "%PHPDESKTOP%\www" 2>nul

echo Copying project files...
if exist "%PROJECT%admin" xcopy "%PROJECT%admin" "%PHPDESKTOP%\www\admin\" /E /I /H /Y >nul 2>&1
if exist "%PROJECT%app" xcopy "%PROJECT%app" "%PHPDESKTOP%\www\app\" /E /I /H /Y >nul 2>&1
if exist "%PROJECT%assets" xcopy "%PROJECT%assets" "%PHPDESKTOP%\www\assets\" /E /I /H /Y >nul 2>&1
if exist "%PROJECT%public" xcopy "%PROJECT%public" "%PHPDESKTOP%\www\public\" /E /I /H /Y >nul 2>&1
if exist "%PROJECT%uploads" xcopy "%PROJECT%uploads" "%PHPDESKTOP%\www\uploads\" /E /I /H /Y >nul 2>&1
if exist "%PROJECT%backups" xcopy "%PROJECT%backups" "%PHPDESKTOP%\www\backups\" /E /I /H /Y >nul 2>&1

echo Copying root files...
copy "%PROJECT%index.php" "%PHPDESKTOP%\www\" >nul 2>&1
copy "%PROJECT%install.php" "%PHPDESKTOP%\www\" >nul 2>&1
if exist "%PROJECT%.htaccess" copy "%PROJECT%.htaccess" "%PHPDESKTOP%\www\" >nul 2>&1

echo Creating database folder...
mkdir "%PHPDESKTOP%\www\database" 2>nul

echo Copying settings...
copy "%PROJECT%phpdesktop-settings.json" "%PHPDESKTOP%\settings.json" >nul 2>&1

echo.
echo ===================================
echo Test environment ready!
echo ===================================
echo.
echo Starting phpdesktop...
echo Check the application window.
echo.
echo If you see 404 error:
echo 1. Check debug.log in phpdesktop folder
echo 2. Make sure MySQL is running
echo 3. Try running as Administrator
echo.

REM Start phpdesktop
cd /d "%PHPDESKTOP%"
start phpdesktop-chrome.exe

echo.
echo phpdesktop started!
echo Check the application window.
echo.
pause
