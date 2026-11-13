@echo off
REM Build script for Rolex Shop phpdesktop package
REM This script must be saved in Windows-1251 encoding

REM Set paths
set PHPDESKTOP_PATH=C:\phpdesktop-chrome-v57.0-rc-php-7.1.3
set OUTPUT_PATH=C:\RolexShop-Portable
set PROJECT_PATH=%~dp0

echo ===================================
echo Rolex Shop - Package Builder
echo ===================================
echo.

REM Check if phpdesktop exists
if not exist "%PHPDESKTOP_PATH%" (
    echo ERROR: phpdesktop not found at: %PHPDESKTOP_PATH%
    echo.
    echo Please:
    echo 1. Download phpdesktop from https://github.com/cztomczak/phpdesktop/releases
    echo 2. Extract to %PHPDESKTOP_PATH%
    echo 3. Or edit PHPDESKTOP_PATH variable in this script
    echo.
    pause
    exit /b 1
)

echo Creating output directory...
if exist "%OUTPUT_PATH%" (
    echo Removing old version...
    rmdir /s /q "%OUTPUT_PATH%"
)
mkdir "%OUTPUT_PATH%"

echo.
echo Copying phpdesktop files...
xcopy "%PHPDESKTOP_PATH%\*" "%OUTPUT_PATH%\" /E /I /H /Y >nul

echo.
echo Clearing www folder...
if exist "%OUTPUT_PATH%\www" (
    rmdir /s /q "%OUTPUT_PATH%\www"
)
mkdir "%OUTPUT_PATH%\www"

echo.
echo Copying project files...
echo - admin folder
xcopy "%PROJECT_PATH%admin" "%OUTPUT_PATH%\www\admin\" /E /I /H /Y >nul
echo - app folder
xcopy "%PROJECT_PATH%app" "%OUTPUT_PATH%\www\app\" /E /I /H /Y >nul
echo - assets folder
xcopy "%PROJECT_PATH%assets" "%OUTPUT_PATH%\www\assets\" /E /I /H /Y >nul
echo - public folder
xcopy "%PROJECT_PATH%public" "%OUTPUT_PATH%\www\public\" /E /I /H /Y >nul
echo - uploads folder
xcopy "%PROJECT_PATH%uploads" "%OUTPUT_PATH%\www\uploads\" /E /I /H /Y >nul

REM Copy root files
echo - root files
copy "%PROJECT_PATH%index.php" "%OUTPUT_PATH%\www\" >nul
if exist "%PROJECT_PATH%.htaccess" (
    copy "%PROJECT_PATH%.htaccess" "%OUTPUT_PATH%\www\" >nul
)
copy "%PROJECT_PATH%install.php" "%OUTPUT_PATH%\www\" >nul

REM Create database folder
mkdir "%OUTPUT_PATH%\www\database"

echo.
echo Copying phpdesktop settings...
copy "%PROJECT_PATH%phpdesktop-settings.json" "%OUTPUT_PATH%\settings.json" >nul

echo.
echo Renaming exe file...
if exist "%OUTPUT_PATH%\phpdesktop-chrome.exe" (
    ren "%OUTPUT_PATH%\phpdesktop-chrome.exe" "RolexShop.exe"
    echo - Renamed to RolexShop.exe
)

echo.
echo Creating startup script...
(
echo @echo off
echo cd /d "%%~dp0"
echo start RolexShop.exe
echo exit
) > "%OUTPUT_PATH%\Start.bat"

echo.
echo Creating README file...
(
echo ROLEX SHOP - Portable Application
echo ================================
echo.
echo To start the application:
echo 1. Run Start.bat or RolexShop.exe
echo.
echo First Run:
echo 1. Make sure MySQL server is running on your computer
echo 2. Import database from www/database/install.sql
echo 3. Configure connection in www/app/Config/Database.php
echo.
echo Admin Panel:
echo URL: http://127.0.0.1:54007/admin/
echo Login: admin
echo Password: [your password]
echo.
echo Technical Info:
echo - Web Server: 127.0.0.1:54007
echo - PHP Version: built-in PHP
echo - Logs: debug.log
echo.
) > "%OUTPUT_PATH%\README.txt"

echo.
echo Creating database placeholder...
(
echo -- Place your database dump here
echo -- Use: mysqldump -u root -p rolex_db ^> install.sql
echo.
) > "%OUTPUT_PATH%\www\database\install.sql"

echo.
echo ===================================
echo Package created successfully!
echo ===================================
echo.
echo Output directory: %OUTPUT_PATH%
echo.
echo Next steps:
echo 1. Export MySQL database to %OUTPUT_PATH%\www\database\install.sql
echo 2. Test by running RolexShop.exe
echo 3. Configure www\app\Config\Database.php if needed
echo 4. Create installer with Inno Setup (optional)
echo.
echo Press any key to open output folder...
pause >nul
explorer "%OUTPUT_PATH%"
