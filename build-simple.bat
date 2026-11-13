@echo off
cls
echo ===================================
echo Rolex Shop Package Builder
echo ===================================
echo.

REM Prompt for phpdesktop path
set /p PHPDESKTOP_PATH="Enter path to phpdesktop folder (or press Enter for default C:\phpdesktop-chrome-v57.0-rc-php-7.1.3): "
if "%PHPDESKTOP_PATH%"=="" set PHPDESKTOP_PATH=C:\phpdesktop-chrome-v57.0-rc-php-7.1.3

REM Remove trailing slash if exists
if "%PHPDESKTOP_PATH:~-1%"=="\" set PHPDESKTOP_PATH=%PHPDESKTOP_PATH:~0,-1%

echo.
echo Checking phpdesktop at: %PHPDESKTOP_PATH%
if not exist "%PHPDESKTOP_PATH%\phpdesktop-chrome.exe" (
    echo.
    echo ERROR: phpdesktop-chrome.exe not found!
    echo.
    echo Please download phpdesktop from:
    echo https://github.com/cztomczak/phpdesktop/releases
    echo.
    echo Extract it and run this script again.
    echo.
    pause
    exit /b 1
)
echo OK - phpdesktop found!

REM Set output path
set OUTPUT_PATH=C:\RolexShop-Portable
set PROJECT_PATH=%~dp0

echo.
echo Output will be created at: %OUTPUT_PATH%
echo.
pause

REM Create output directory
if exist "%OUTPUT_PATH%" (
    echo Removing old version...
    rmdir /s /q "%OUTPUT_PATH%" 2>nul
)
mkdir "%OUTPUT_PATH%"

REM Copy phpdesktop
echo.
echo [1/8] Copying phpdesktop files...
xcopy "%PHPDESKTOP_PATH%\*" "%OUTPUT_PATH%\" /E /I /H /Y >nul
if errorlevel 1 (
    echo ERROR: Failed to copy phpdesktop files
    pause
    exit /b 1
)
echo OK

REM Clear www
echo [2/8] Preparing www folder...
if exist "%OUTPUT_PATH%\www" (
    rmdir /s /q "%OUTPUT_PATH%\www"
)
mkdir "%OUTPUT_PATH%\www"
echo OK

REM Copy project folders
echo [3/8] Copying project folders...
if exist "%PROJECT_PATH%admin" xcopy "%PROJECT_PATH%admin" "%OUTPUT_PATH%\www\admin\" /E /I /H /Y >nul
if exist "%PROJECT_PATH%app" xcopy "%PROJECT_PATH%app" "%OUTPUT_PATH%\www\app\" /E /I /H /Y >nul
if exist "%PROJECT_PATH%assets" xcopy "%PROJECT_PATH%assets" "%OUTPUT_PATH%\www\assets\" /E /I /H /Y >nul
if exist "%PROJECT_PATH%public" xcopy "%PROJECT_PATH%public" "%OUTPUT_PATH%\www\public\" /E /I /H /Y >nul
if exist "%PROJECT_PATH%uploads" xcopy "%PROJECT_PATH%uploads" "%OUTPUT_PATH%\www\uploads\" /E /I /H /Y >nul
if exist "%PROJECT_PATH%backups" xcopy "%PROJECT_PATH%backups" "%OUTPUT_PATH%\www\backups\" /E /I /H /Y >nul
echo OK

REM Copy root files
echo [4/8] Copying root files...
copy "%PROJECT_PATH%index.php" "%OUTPUT_PATH%\www\" >nul 2>&1
copy "%PROJECT_PATH%install.php" "%OUTPUT_PATH%\www\" >nul 2>&1
if exist "%PROJECT_PATH%.htaccess" copy "%PROJECT_PATH%.htaccess" "%OUTPUT_PATH%\www\" >nul 2>&1
echo OK

REM Create folders
echo [5/8] Creating database folder...
mkdir "%OUTPUT_PATH%\www\database" 2>nul
echo OK

REM Copy settings
echo [6/8] Configuring phpdesktop...
copy "%PROJECT_PATH%phpdesktop-settings.json" "%OUTPUT_PATH%\settings.json" >nul
echo OK

REM Rename exe
echo [7/8] Renaming executable...
if exist "%OUTPUT_PATH%\phpdesktop-chrome.exe" (
    ren "%OUTPUT_PATH%\phpdesktop-chrome.exe" "RolexShop.exe"
    echo Renamed to RolexShop.exe
) else (
    echo WARNING: phpdesktop-chrome.exe not found
)

REM Create additional files
echo [8/8] Creating helper files...
echo @echo off > "%OUTPUT_PATH%\Start.bat"
echo cd /d "%%~dp0" >> "%OUTPUT_PATH%\Start.bat"
echo start RolexShop.exe >> "%OUTPUT_PATH%\Start.bat"
echo -- SQL dump placeholder > "%OUTPUT_PATH%\www\database\install.sql"
echo OK

echo.
echo ===================================
echo SUCCESS!
echo ===================================
echo.
echo Package created at: %OUTPUT_PATH%
echo.
echo NEXT STEPS:
echo 1. Export your database:
echo    mysqldump -u root -p rolex_db ^> install.sql
echo.
echo 2. Copy install.sql to:
echo    %OUTPUT_PATH%\www\database\
echo.
echo 3. Test by running:
echo    %OUTPUT_PATH%\RolexShop.exe
echo.
echo Press any key to open the output folder...
pause >nul
explorer "%OUTPUT_PATH%"
