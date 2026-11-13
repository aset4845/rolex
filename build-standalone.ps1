# Build STANDALONE version with embedded MySQL
# NO XAMPP REQUIRED!

Write-Host "===================================" -ForegroundColor Cyan
Write-Host "Rolex Shop - STANDALONE Build" -ForegroundColor Cyan
Write-Host "With Embedded MySQL - NO XAMPP!" -ForegroundColor Cyan
Write-Host "===================================" -ForegroundColor Cyan
Write-Host ""

# Paths
$defaultPhpDesktop = "C:\phpdesktop-chrome-v57.0-rc-php-7.1.3"
$defaultMysql = "C:\xampp\mysql"  # Можно взять MySQL из XAMPP
$outputPath = "C:\RolexShop-Standalone"
$projectPath = "c:\xampp\htdocs\rolex"

# Get phpdesktop path
$phpDesktopPath = Read-Host "Enter phpdesktop path (or Enter for: $defaultPhpDesktop)"
if ([string]::IsNullOrWhiteSpace($phpDesktopPath)) {
    $phpDesktopPath = $defaultPhpDesktop
}

if (-not (Test-Path "$phpDesktopPath\phpdesktop-chrome.exe")) {
    Write-Host "ERROR: phpdesktop not found!" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

# Get MySQL path
Write-Host ""
Write-Host "MySQL Options:" -ForegroundColor Yellow
Write-Host "1. Use MySQL from XAMPP (default: $defaultMysql)" -ForegroundColor White
Write-Host "2. Use downloaded MariaDB portable" -ForegroundColor White
Write-Host "3. Skip MySQL (SQLite mode)" -ForegroundColor White
Write-Host ""

$mysqlChoice = Read-Host "Choose option (1/2/3)"
$includeMysql = $true
$mysqlPath = ""

switch ($mysqlChoice) {
    "1" {
        $mysqlPath = $defaultMysql
        if (-not (Test-Path $mysqlPath)) {
            Write-Host "ERROR: MySQL not found at $mysqlPath" -ForegroundColor Red
            Read-Host "Press Enter to exit"
            exit 1
        }
    }
    "2" {
        $mysqlPath = Read-Host "Enter MariaDB path"
        if (-not (Test-Path $mysqlPath)) {
            Write-Host "ERROR: MariaDB not found!" -ForegroundColor Red
            Read-Host "Press Enter to exit"
            exit 1
        }
    }
    "3" {
        $includeMysql = $false
        Write-Host "SQLite mode - MySQL will NOT be included" -ForegroundColor Yellow
    }
    default {
        $mysqlPath = $defaultMysql
    }
}

Write-Host ""
Write-Host "Configuration:" -ForegroundColor White
Write-Host "  phpdesktop: $phpDesktopPath" -ForegroundColor Gray
if ($includeMysql) {
    Write-Host "  MySQL: $mysqlPath" -ForegroundColor Gray
} else {
    Write-Host "  MySQL: DISABLED (SQLite mode)" -ForegroundColor Gray
}
Write-Host "  Output: $outputPath" -ForegroundColor Gray
Write-Host ""
Read-Host "Press Enter to start build"

# Clean output
Write-Host ""
if (Test-Path $outputPath) {
    Write-Host "Removing old build..." -ForegroundColor Yellow
    Remove-Item -Path $outputPath -Recurse -Force -ErrorAction SilentlyContinue
    Start-Sleep -Milliseconds 500
}
New-Item -ItemType Directory -Path $outputPath -Force | Out-Null

# Step 1: Copy phpdesktop
Write-Host "[1/6] Copying phpdesktop..." -ForegroundColor Cyan
Copy-Item -Path "$phpDesktopPath\*" -Destination $outputPath -Recurse -Force
Write-Host "OK" -ForegroundColor Green

# Step 2: Copy MySQL (if enabled)
if ($includeMysql) {
    Write-Host "[2/6] Copying MySQL (this may take a while)..." -ForegroundColor Cyan

    # Copy only necessary MySQL files
    $mysqlDest = "$outputPath\mysql"
    New-Item -ItemType Directory -Path $mysqlDest -Force | Out-Null

    # Copy bin folder
    if (Test-Path "$mysqlPath\bin") {
        Copy-Item -Path "$mysqlPath\bin" -Destination "$mysqlDest\" -Recurse -Force
        Write-Host "  - bin folder copied" -ForegroundColor Gray
    }

    # Copy share folder (if exists)
    if (Test-Path "$mysqlPath\share") {
        Copy-Item -Path "$mysqlPath\share" -Destination "$mysqlDest\" -Recurse -Force
        Write-Host "  - share folder copied" -ForegroundColor Gray
    }

    # Create data folder
    New-Item -ItemType Directory -Path "$mysqlDest\data" -Force | Out-Null

    # Copy data folder (or initialize empty)
    if (Test-Path "$mysqlPath\data") {
        Write-Host "  - Copying database data (may be large)..." -ForegroundColor Gray
        Copy-Item -Path "$mysqlPath\data\*" -Destination "$mysqlDest\data\" -Recurse -Force
    }

    Write-Host "OK - MySQL copied" -ForegroundColor Green
} else {
    Write-Host "[2/6] Skipping MySQL (SQLite mode)" -ForegroundColor Yellow
}

# Step 3: Prepare www
Write-Host "[3/6] Preparing www folder..." -ForegroundColor Cyan
if (Test-Path "$outputPath\www") {
    Remove-Item -Path "$outputPath\www" -Recurse -Force
}
New-Item -ItemType Directory -Path "$outputPath\www" -Force | Out-Null
Write-Host "OK" -ForegroundColor Green

# Step 4: Copy project
Write-Host "[4/6] Copying project files..." -ForegroundColor Cyan
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

# Step 5: Create MySQL config
if ($includeMysql) {
    Write-Host "[5/6] Configuring MySQL..." -ForegroundColor Cyan

    $myIni = @"
[mysqld]
datadir=mysql/data
port=3307
basedir=mysql

max_connections=50
key_buffer_size=16M
max_allowed_packet=16M
table_open_cache=64

character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci

# Disable networking for security
skip-networking
enable-named-pipe
shared-memory

[client]
port=3307
"@
    Set-Content -Path "$outputPath\mysql\my.ini" -Value $myIni -Encoding UTF8
    Write-Host "OK - my.ini created" -ForegroundColor Green
} else {
    Write-Host "[5/6] Skipping MySQL config" -ForegroundColor Yellow
}

# Step 6: Create startup scripts
Write-Host "[6/6] Creating startup scripts..." -ForegroundColor Cyan

# Start.bat
if ($includeMysql) {
    $startBat = @"
@echo off
cd /d "%~dp0"

echo ===================================
echo Rolex Shop - STANDALONE
echo Starting with embedded MySQL...
echo ===================================
echo.

REM Check if MySQL already running
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
} else {
    $startBat = @"
@echo off
cd /d "%~dp0"
echo Starting Rolex Shop (SQLite mode)...
start RolexShop.exe
pause
"@
}

Set-Content -Path "$outputPath\Start.bat" -Value $startBat -Encoding ASCII

# Stop.bat
if ($includeMysql) {
    $stopBat = @"
@echo off
echo Stopping Rolex Shop...

taskkill /F /IM mysqld.exe 2>nul
echo MySQL stopped

taskkill /F /IM RolexShop.exe 2>nul
echo Application stopped

echo.
echo Done!
pause
"@
    Set-Content -Path "$outputPath\Stop.bat" -Value $stopBat -Encoding ASCII
}

# Copy settings
Copy-Item -Path "$projectPath\phpdesktop-settings.json" -Destination "$outputPath\settings.json" -Force

# Rename exe
if (Test-Path "$outputPath\phpdesktop-chrome.exe") {
    Rename-Item -Path "$outputPath\phpdesktop-chrome.exe" -NewName "RolexShop.exe" -Force
}

# Create README
$readme = @"
ROLEX SHOP - STANDALONE EDITION
================================

This is a STANDALONE version with embedded MySQL.
NO XAMPP OR EXTERNAL MYSQL REQUIRED!

HOW TO RUN:
-----------
1. Double-click Start.bat
2. Wait for MySQL to start (3 seconds)
3. Application will open automatically

FIRST RUN:
----------
1. The installation wizard will open
2. Use these settings:
   Host: 127.0.0.1
   Port: 3307
   Database: rolex_db
   User: root
   Password: (leave empty)

TO STOP:
--------
Run Stop.bat or close the application

DATABASE LOCATION:
------------------
mysql\data\rolex_db\

TROUBLESHOOTING:
----------------
- If MySQL doesn't start, check mysql\data\ folder permissions
- Run as Administrator if needed
- Check debug.log for errors

PORTS USED:
-----------
MySQL: 3307
Web: 54007

Enjoy!
"@

Set-Content -Path "$outputPath\README.txt" -Value $readme -Encoding UTF8

Write-Host "OK - Scripts created" -ForegroundColor Green

# Final summary
Write-Host ""
Write-Host "===================================" -ForegroundColor Green
Write-Host "STANDALONE BUILD COMPLETE!" -ForegroundColor Green
Write-Host "===================================" -ForegroundColor Green
Write-Host ""
Write-Host "Location: $outputPath" -ForegroundColor White
Write-Host ""

if ($includeMysql) {
    Write-Host "Features:" -ForegroundColor Yellow
    Write-Host "  [OK] Embedded MySQL (no XAMPP needed!)" -ForegroundColor Green
    Write-Host "  [OK] Fully portable" -ForegroundColor Green
    Write-Host "  [OK] Start.bat for easy launch" -ForegroundColor Green
    Write-Host ""
    Write-Host "IMPORTANT:" -ForegroundColor Yellow
    Write-Host "1. Run Start.bat (NOT RolexShop.exe directly!)" -ForegroundColor White
    Write-Host "2. On first run, configure database with port 3307" -ForegroundColor White
} else {
    Write-Host "SQLite mode - remember to configure app for SQLite!" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Size: ~" -NoNewline -ForegroundColor Gray
$size = (Get-ChildItem -Path $outputPath -Recurse | Measure-Object -Property Length -Sum).Sum / 1MB
Write-Host "$([math]::Round($size, 0)) MB" -ForegroundColor Gray

Write-Host ""
$open = Read-Host "Open output folder? (Y/N)"
if ($open -eq 'Y' -or $open -eq 'y' -or $open -eq '') {
    Start-Process explorer.exe -ArgumentList $outputPath
}

Write-Host ""
Write-Host "Done! Enjoy your STANDALONE application!" -ForegroundColor Green
