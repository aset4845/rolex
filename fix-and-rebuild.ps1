# FIXED BUILD - Исправляет все проблемы
# 1. Правильная структура папок (НЕ www\rolex\)
# 2. Правильный порт MySQL (3307)
# 3. Автозапуск MySQL через Start.bat

Write-Host "===================================" -ForegroundColor Red
Write-Host "FIXING & REBUILDING" -ForegroundColor Red
Write-Host "===================================" -ForegroundColor Red
Write-Host ""

$phpDesktopPath = "C:\phpdesktop-chrome-v57.0-rc-php-7.1.3"
$mysqlPath = "c:\xampp\mysql"
$outputPath = "C:\RolexShop-Standalone"
$projectPath = "c:\xampp\htdocs\rolex"

# Check phpdesktop
if (-not (Test-Path "$phpDesktopPath\phpdesktop-chrome.exe")) {
    Write-Host "ERROR: phpdesktop not found!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please download from:" -ForegroundColor Yellow
    Write-Host "https://github.com/cztomczak/phpdesktop/releases" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Download: phpdesktop-chrome-57.0-rc-php-7.1.3.zip" -ForegroundColor White
    Write-Host "Extract to: C:\phpdesktop-chrome-v57.0-rc-php-7.1.3\" -ForegroundColor White
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "[OK] phpdesktop found" -ForegroundColor Green

# Clean output completely
Write-Host ""
Write-Host "Cleaning old build..." -ForegroundColor Yellow
if (Test-Path $outputPath) {
    Remove-Item -Path $outputPath -Recurse -Force -ErrorAction SilentlyContinue
    Start-Sleep -Seconds 1
}
New-Item -ItemType Directory -Path $outputPath -Force | Out-Null

Write-Host ""
Write-Host "[1/7] Copying phpdesktop..." -ForegroundColor Cyan
Copy-Item -Path "$phpDesktopPath\*" -Destination $outputPath -Recurse -Force
Write-Host "[OK]" -ForegroundColor Green

Write-Host "[2/7] Copying MySQL..." -ForegroundColor Cyan
if (Test-Path $mysqlPath) {
    $mysqlDest = "$outputPath\mysql"
    New-Item -ItemType Directory -Path $mysqlDest -Force | Out-Null

    Copy-Item -Path "$mysqlPath\bin" -Destination "$mysqlDest\" -Recurse -Force
    if (Test-Path "$mysqlPath\share") {
        Copy-Item -Path "$mysqlPath\share" -Destination "$mysqlDest\" -Recurse -Force
    }

    New-Item -ItemType Directory -Path "$mysqlDest\data" -Force | Out-Null
    Copy-Item -Path "$mysqlPath\data\*" -Destination "$mysqlDest\data\" -Recurse -Force

    Write-Host "[OK] MySQL copied" -ForegroundColor Green
} else {
    Write-Host "[SKIP] MySQL not found" -ForegroundColor Yellow
}

Write-Host "[3/7] Preparing www..." -ForegroundColor Cyan
if (Test-Path "$outputPath\www") {
    Remove-Item -Path "$outputPath\www" -Recurse -Force
}
New-Item -ItemType Directory -Path "$outputPath\www" -Force | Out-Null
Write-Host "[OK]" -ForegroundColor Green

Write-Host "[4/7] Copying project DIRECTLY to www..." -ForegroundColor Cyan
Write-Host "      IMPORTANT: Files go to www\, NOT www\rolex\!" -ForegroundColor Yellow

$folders = @("admin", "app", "assets", "public", "uploads")
foreach ($folder in $folders) {
    $source = Join-Path $projectPath $folder
    if (Test-Path $source) {
        # CRITICAL: Copy DIRECTLY to www\folder
        $dest = "$outputPath\www\$folder"
        Copy-Item -Path $source -Destination $dest -Recurse -Force
        Write-Host "      $folder -> www\$folder" -ForegroundColor Gray
    }
}

# Copy root files DIRECTLY to www\
Copy-Item -Path "$projectPath\index.php" -Destination "$outputPath\www\index.php" -Force
Copy-Item -Path "$projectPath\install.php" -Destination "$outputPath\www\install.php" -Force
if (Test-Path "$projectPath\.htaccess") {
    Copy-Item -Path "$projectPath\.htaccess" -Destination "$outputPath\www\.htaccess" -Force
}

New-Item -ItemType Directory -Path "$outputPath\www\database" -Force | Out-Null
Write-Host "[OK]" -ForegroundColor Green

Write-Host "[5/7] Creating updated Database.php for port 3307..." -ForegroundColor Cyan
$dbFile = "$outputPath\www\app\Config\Database.php"
if (Test-Path $dbFile) {
    # Create new Database.php with correct port
    $dbContent = @'
<?php
class Database {
    private $host = '127.0.0.1';
    private $port = 3307;
    private $db_name = 'rolex_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            die("Connection error: " . $e->getMessage());
        }

        return $this->conn;
    }
}
'@
    Set-Content -Path $dbFile -Value $dbContent -Encoding UTF8
    Write-Host "[OK] Database.php created with port 3307" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Database.php not found" -ForegroundColor Yellow
}

Write-Host "[6/7] Creating configuration..." -ForegroundColor Cyan

# MySQL config
$myIni = @"
[mysqld]
datadir=mysql/data
port=3307
basedir=mysql
max_connections=50
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
skip-networking
enable-named-pipe

[client]
port=3307
"@
Set-Content -Path "$outputPath\mysql\my.ini" -Value $myIni -Encoding UTF8

# phpdesktop settings
$settings = Get-Content "$projectPath\phpdesktop-settings.json" -Raw | ConvertFrom-Json
$settings.debugging.show_console = $false
$settings.debugging.subprocess_show_console = $false
$settings.application.main_url = "http://127.0.0.1:54007/index.php"
$settings | ConvertTo-Json -Depth 10 | Set-Content "$outputPath\settings.json" -Encoding UTF8

# Start.bat with proper MySQL startup
$startBat = @"
@echo off
cd /d "%~dp0"
cls

echo ====================================
echo   ROLEX SHOP - STANDALONE
echo ====================================
echo.

REM Kill any existing MySQL
taskkill /F /IM mysqld.exe 2>nul >nul

REM Wait a moment
timeout /t 1 /nobreak >nul

echo [*] Starting MySQL on port 3307...
start /MIN mysql\bin\mysqld.exe --defaults-file=mysql\my.ini --standalone --console

REM Wait for MySQL to start
echo [*] Waiting for MySQL to initialize...
timeout /t 5 /nobreak >nul

echo [*] Starting Rolex Shop...
start "" RolexShop.exe

echo.
echo [OK] Application started!
echo.
echo MySQL Port: 3307
echo Web Port: 54007
echo.
echo To stop everything, run Stop.bat
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
echo.
echo Stopped!
pause
"@
Set-Content -Path "$outputPath\Stop.bat" -Value $stopBat -Encoding ASCII

# Rename exe
if (Test-Path "$outputPath\phpdesktop-chrome.exe") {
    Rename-Item -Path "$outputPath\phpdesktop-chrome.exe" -NewName "RolexShop.exe" -Force
}

Write-Host "[OK]" -ForegroundColor Green

Write-Host "[7/7] Verifying structure..." -ForegroundColor Cyan

$checks = @{
    "index.php" = "$outputPath\www\index.php"
    "app folder" = "$outputPath\www\app"
    "Database.php" = "$outputPath\www\app\Config\Database.php"
    "RolexShop.exe" = "$outputPath\RolexShop.exe"
    "Start.bat" = "$outputPath\Start.bat"
    "my.ini" = "$outputPath\mysql\my.ini"
}

$allOk = $true
foreach ($name in $checks.Keys) {
    if (Test-Path $checks[$name]) {
        Write-Host "  [OK] $name" -ForegroundColor Green
    } else {
        Write-Host "  [!!] $name MISSING" -ForegroundColor Red
        $allOk = $false
    }
}

# Check for wrong structure
if (Test-Path "$outputPath\www\rolex") {
    Write-Host "  [ERROR] Found www\rolex\ - WRONG!" -ForegroundColor Red
    $allOk = $false
}

Write-Host ""
if ($allOk) {
    Write-Host "===================================" -ForegroundColor Green
    Write-Host "BUILD SUCCESSFUL!" -ForegroundColor Green
    Write-Host "===================================" -ForegroundColor Green
} else {
    Write-Host "===================================" -ForegroundColor Yellow
    Write-Host "BUILD COMPLETED WITH WARNINGS" -ForegroundColor Yellow
    Write-Host "===================================" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Location: $outputPath" -ForegroundColor White
Write-Host ""
Write-Host "HOW TO RUN:" -ForegroundColor Yellow
Write-Host "  1. Go to: $outputPath" -ForegroundColor White
Write-Host "  2. Double-click: Start.bat" -ForegroundColor White
Write-Host "  3. Wait 5 seconds for MySQL to start" -ForegroundColor White
Write-Host "  4. Application will open automatically" -ForegroundColor White
Write-Host ""
Write-Host "FIRST TIME SETUP:" -ForegroundColor Yellow
Write-Host "  Host: 127.0.0.1" -ForegroundColor White
Write-Host "  Port: 3307 (NOT 3306!)" -ForegroundColor White
Write-Host "  Database: rolex_db" -ForegroundColor White
Write-Host "  User: root" -ForegroundColor White
Write-Host "  Password: (empty)" -ForegroundColor White
Write-Host ""

$open = Read-Host "Open folder now? (Y/N)"
if ($open -eq 'Y' -or $open -eq 'y' -or $open -eq '') {
    Start-Process explorer.exe -ArgumentList $outputPath
}

Write-Host ""
Write-Host "REMEMBER: Always use Start.bat to launch!" -ForegroundColor Yellow
Write-Host "Done!" -ForegroundColor Green
