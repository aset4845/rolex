# AUTO-DOWNLOAD phpdesktop and BUILD standalone version
# This script downloads everything needed and builds the app

Write-Host "===================================" -ForegroundColor Cyan
Write-Host "Rolex Shop - AUTO BUILD" -ForegroundColor Cyan
Write-Host "Downloading & Building..." -ForegroundColor Cyan
Write-Host "===================================" -ForegroundColor Cyan
Write-Host ""

$tempDir = "C:\Temp\RolexBuild"
$phpDesktopUrl = "https://github.com/cztomczak/phpdesktop/releases/download/chrome-v57.0-final/phpdesktop-chrome-57.0-rc-php-7.1.3.zip"
$phpDesktopZip = "$tempDir\phpdesktop.zip"
$phpDesktopPath = "$tempDir\phpdesktop-chrome-57.0-rc-php-7.1.3"
$outputPath = "C:\RolexShop-Standalone"
$projectPath = "c:\xampp\htdocs\rolex"

# Create temp directory
Write-Host "[1/8] Creating temp directory..." -ForegroundColor Cyan
if (-not (Test-Path $tempDir)) {
    New-Item -ItemType Directory -Path $tempDir -Force | Out-Null
}
Write-Host "OK" -ForegroundColor Green

# Download phpdesktop
Write-Host "[2/8] Downloading phpdesktop (~82 MB)..." -ForegroundColor Cyan
Write-Host "This may take a few minutes..." -ForegroundColor Yellow

if (-not (Test-Path $phpDesktopPath)) {
    try {
        # Download
        $ProgressPreference = 'SilentlyContinue'
        Invoke-WebRequest -Uri $phpDesktopUrl -OutFile $phpDesktopZip -UseBasicParsing

        # Extract
        Write-Host "Extracting..." -ForegroundColor Yellow
        Expand-Archive -Path $phpDesktopZip -DestinationPath $tempDir -Force

        Write-Host "OK - phpdesktop downloaded and extracted" -ForegroundColor Green
    } catch {
        Write-Host "ERROR: Failed to download phpdesktop" -ForegroundColor Red
        Write-Host "Please download manually from:" -ForegroundColor Yellow
        Write-Host "https://github.com/cztomczak/phpdesktop/releases" -ForegroundColor Yellow
        Read-Host "Press Enter to exit"
        exit 1
    }
} else {
    Write-Host "OK - phpdesktop already downloaded" -ForegroundColor Green
}

# Clean output
Write-Host "[3/8] Preparing output directory..." -ForegroundColor Cyan
if (Test-Path $outputPath) {
    Remove-Item -Path $outputPath -Recurse -Force -ErrorAction SilentlyContinue
}
New-Item -ItemType Directory -Path $outputPath -Force | Out-Null
Write-Host "OK" -ForegroundColor Green

# Copy phpdesktop
Write-Host "[4/8] Copying phpdesktop..." -ForegroundColor Cyan
Copy-Item -Path "$phpDesktopPath\*" -Destination $outputPath -Recurse -Force
Write-Host "OK" -ForegroundColor Green

# Copy MySQL from XAMPP
Write-Host "[5/8] Copying MySQL from XAMPP..." -ForegroundColor Cyan
$xamppMysql = "c:\xampp\mysql"

if (Test-Path $xamppMysql) {
    $mysqlDest = "$outputPath\mysql"
    New-Item -ItemType Directory -Path $mysqlDest -Force | Out-Null

    # Copy necessary folders
    Copy-Item -Path "$xamppMysql\bin" -Destination "$mysqlDest\" -Recurse -Force
    Write-Host "  - bin copied" -ForegroundColor Gray

    if (Test-Path "$xamppMysql\share") {
        Copy-Item -Path "$xamppMysql\share" -Destination "$mysqlDest\" -Recurse -Force
        Write-Host "  - share copied" -ForegroundColor Gray
    }

    # Create data folder
    New-Item -ItemType Directory -Path "$mysqlDest\data" -Force | Out-Null

    # Copy data
    Write-Host "  - Copying database data..." -ForegroundColor Gray
    Copy-Item -Path "$xamppMysql\data\*" -Destination "$mysqlDest\data\" -Recurse -Force

    Write-Host "OK - MySQL copied" -ForegroundColor Green
} else {
    Write-Host "WARNING: XAMPP MySQL not found at $xamppMysql" -ForegroundColor Yellow
    Write-Host "App will be built without embedded MySQL" -ForegroundColor Yellow
}

# Prepare www
Write-Host "[6/8] Copying project files..." -ForegroundColor Cyan
if (Test-Path "$outputPath\www") {
    Remove-Item -Path "$outputPath\www" -Recurse -Force
}
New-Item -ItemType Directory -Path "$outputPath\www" -Force | Out-Null

# Copy folders
$folders = @("admin", "app", "assets", "public", "uploads")
foreach ($folder in $folders) {
    $source = Join-Path $projectPath $folder
    if (Test-Path $source) {
        $dest = Join-Path "$outputPath\www" $folder
        Copy-Item -Path $source -Destination $dest -Recurse -Force
        Write-Host "  - $folder" -ForegroundColor Gray
    }
}

# Copy root files
Copy-Item -Path "$projectPath\index.php" -Destination "$outputPath\www\" -Force
Copy-Item -Path "$projectPath\install.php" -Destination "$outputPath\www\" -Force
if (Test-Path "$projectPath\.htaccess") {
    Copy-Item -Path "$projectPath\.htaccess" -Destination "$outputPath\www\" -Force
}

New-Item -ItemType Directory -Path "$outputPath\www\database" -Force | Out-Null
Write-Host "OK" -ForegroundColor Green

# Configure
Write-Host "[7/8] Configuring..." -ForegroundColor Cyan

# MySQL config
$myIni = @"
[mysqld]
datadir=mysql/data
port=3307
basedir=mysql

max_connections=50
key_buffer_size=16M
max_allowed_packet=16M

character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci

skip-networking
enable-named-pipe
shared-memory

[client]
port=3307
"@
Set-Content -Path "$outputPath\mysql\my.ini" -Value $myIni -Encoding UTF8

# Start.bat
$startBat = @"
@echo off
cd /d "%~dp0"

echo ===================================
echo Rolex Shop - STANDALONE
echo ===================================
echo.

REM Check MySQL
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MySQL already running
) else (
    echo [*] Starting MySQL...
    start /B mysql\bin\mysqld.exe --defaults-file=mysql\my.ini --standalone
    timeout /t 3 /nobreak >nul
    echo [OK] MySQL started
)

echo.
echo [*] Starting Rolex Shop...
start RolexShop.exe

echo.
echo [OK] Application started!
echo.
echo To stop: run Stop.bat
echo.
pause
"@
Set-Content -Path "$outputPath\Start.bat" -Value $startBat -Encoding ASCII

# Stop.bat
$stopBat = @"
@echo off
echo Stopping Rolex Shop...
taskkill /F /IM mysqld.exe 2>nul
taskkill /F /IM RolexShop.exe 2>nul
echo Done!
pause
"@
Set-Content -Path "$outputPath\Stop.bat" -Value $stopBat -Encoding ASCII

# Copy settings
Copy-Item -Path "$projectPath\phpdesktop-settings.json" -Destination "$outputPath\settings.json" -Force

# Rename exe
if (Test-Path "$outputPath\phpdesktop-chrome.exe") {
    Rename-Item -Path "$outputPath\phpdesktop-chrome.exe" -NewName "RolexShop.exe" -Force
}

# README
$readme = @"
ROLEX SHOP - STANDALONE
========================

ПОЛНОСТЬЮ АВТОНОМНОЕ ПРИЛОЖЕНИЕ!
НЕ ТРЕБУЕТ XAMPP ИЛИ УСТАНОВКИ MySQL!

ЗАПУСК:
-------
Запустите: Start.bat

ПЕРВАЯ НАСТРОЙКА:
-----------------
Host: 127.0.0.1
Port: 3307
Database: rolex_db
User: root
Password: (пусто)

ОСТАНОВКА:
----------
Запустите: Stop.bat

База данных находится в: mysql\data\

Наслаждайтесь!
"@
Set-Content -Path "$outputPath\README.txt" -Value $readme -Encoding UTF8

Write-Host "OK" -ForegroundColor Green

# Export database
Write-Host "[8/8] Exporting database..." -ForegroundColor Cyan
$exportSql = "$outputPath\www\database\rolex_db.sql"

try {
    $mysqldumpPath = "c:\xampp\mysql\bin\mysqldump.exe"
    if (Test-Path $mysqldumpPath) {
        & $mysqldumpPath -u root rolex_db > $exportSql 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-Host "OK - Database exported" -ForegroundColor Green
        } else {
            Write-Host "WARNING - Could not export database" -ForegroundColor Yellow
        }
    } else {
        Write-Host "WARNING - mysqldump not found" -ForegroundColor Yellow
    }
} catch {
    Write-Host "WARNING - Could not export database" -ForegroundColor Yellow
}

# Summary
Write-Host ""
Write-Host "===================================" -ForegroundColor Green
Write-Host "BUILD COMPLETE!" -ForegroundColor Green
Write-Host "===================================" -ForegroundColor Green
Write-Host ""
Write-Host "Location: $outputPath" -ForegroundColor White
Write-Host ""
Write-Host "Features:" -ForegroundColor Yellow
Write-Host "  [OK] Embedded MySQL" -ForegroundColor Green
Write-Host "  [OK] Fully standalone" -ForegroundColor Green
Write-Host "  [OK] No XAMPP needed!" -ForegroundColor Green
Write-Host ""
Write-Host "TO RUN:" -ForegroundColor Yellow
Write-Host "  1. Go to: $outputPath" -ForegroundColor White
Write-Host "  2. Run: Start.bat" -ForegroundColor White
Write-Host "  3. Configure with port 3307" -ForegroundColor White
Write-Host ""

$size = (Get-ChildItem -Path $outputPath -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB
Write-Host "Size: $([math]::Round($size, 0)) MB" -ForegroundColor Gray

Write-Host ""
$open = Read-Host "Open folder now? (Y/N)"
if ($open -eq 'Y' -or $open -eq 'y' -or $open -eq '') {
    Start-Process explorer.exe -ArgumentList $outputPath
}

Write-Host ""
Write-Host "Done! Your standalone app is ready!" -ForegroundColor Green
Write-Host ""
Write-Host "REMEMBER: Run Start.bat (NOT RolexShop.exe directly!)" -ForegroundColor Yellow
