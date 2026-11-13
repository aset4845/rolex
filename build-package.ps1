# PowerShell script for packaging Rolex Shop with phpdesktop
# Run with: powershell -ExecutionPolicy Bypass -File build-package.ps1

Write-Host "===================================" -ForegroundColor Cyan
Write-Host "Rolex Shop - Package Builder" -ForegroundColor Cyan
Write-Host "===================================" -ForegroundColor Cyan
Write-Host ""

# Configuration
$defaultPhpDesktopPath = "C:\phpdesktop-chrome-v57.0-rc-php-7.1.3"
$outputPath = "C:\RolexShop-Portable"
$projectPath = $PSScriptRoot

# Prompt for phpdesktop path
$phpDesktopPath = Read-Host "Enter phpdesktop path (or press Enter for default: $defaultPhpDesktopPath)"
if ([string]::IsNullOrWhiteSpace($phpDesktopPath)) {
    $phpDesktopPath = $defaultPhpDesktopPath
}

# Check if phpdesktop exists
if (-not (Test-Path "$phpDesktopPath\phpdesktop-chrome.exe")) {
    Write-Host ""
    Write-Host "ERROR: phpdesktop-chrome.exe not found at: $phpDesktopPath" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please download phpdesktop from:" -ForegroundColor Yellow
    Write-Host "https://github.com/cztomczak/phpdesktop/releases" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "phpdesktop found at: $phpDesktopPath" -ForegroundColor Green
Write-Host ""
Write-Host "Output will be created at: $outputPath"
Write-Host ""
Read-Host "Press Enter to continue"

# Remove old output if exists
if (Test-Path $outputPath) {
    Write-Host "Removing old version..." -ForegroundColor Yellow
    Remove-Item -Path $outputPath -Recurse -Force -ErrorAction SilentlyContinue
}

# Create output directory
New-Item -ItemType Directory -Path $outputPath -Force | Out-Null

# Copy phpdesktop files
Write-Host ""
Write-Host "[1/8] Copying phpdesktop files..." -ForegroundColor Cyan
Copy-Item -Path "$phpDesktopPath\*" -Destination $outputPath -Recurse -Force
Write-Host "OK" -ForegroundColor Green

# Clear www folder
Write-Host "[2/8] Preparing www folder..." -ForegroundColor Cyan
if (Test-Path "$outputPath\www") {
    Remove-Item -Path "$outputPath\www" -Recurse -Force
}
New-Item -ItemType Directory -Path "$outputPath\www" -Force | Out-Null
Write-Host "OK" -ForegroundColor Green

# Copy project folders DIRECTLY to www (not to www\rolex!)
Write-Host "[3/8] Copying project folders..." -ForegroundColor Cyan
$folders = @("admin", "app", "assets", "public", "uploads", "backups")
foreach ($folder in $folders) {
    $sourcePath = Join-Path $projectPath $folder
    if (Test-Path $sourcePath) {
        # IMPORTANT: Copy to www\folder, NOT www\rolex\folder
        $destPath = Join-Path "$outputPath\www" $folder
        Copy-Item -Path $sourcePath -Destination $destPath -Recurse -Force
        Write-Host "  - $folder" -ForegroundColor Gray
    }
}
Write-Host "OK" -ForegroundColor Green

# Copy root files
Write-Host "[4/8] Copying root files..." -ForegroundColor Cyan
$rootFiles = @("index.php", "install.php", ".htaccess")
foreach ($file in $rootFiles) {
    $sourcePath = Join-Path $projectPath $file
    if (Test-Path $sourcePath) {
        Copy-Item -Path $sourcePath -Destination "$outputPath\www\" -Force
        Write-Host "  - $file" -ForegroundColor Gray
    }
}
Write-Host "OK" -ForegroundColor Green

# Create database folder
Write-Host "[5/8] Creating database folder..." -ForegroundColor Cyan
New-Item -ItemType Directory -Path "$outputPath\www\database" -Force | Out-Null
Write-Host "OK" -ForegroundColor Green

# Copy settings
Write-Host "[6/8] Configuring phpdesktop..." -ForegroundColor Cyan
$settingsSource = Join-Path $projectPath "phpdesktop-settings.json"
if (Test-Path $settingsSource) {
    Copy-Item -Path $settingsSource -Destination "$outputPath\settings.json" -Force
    Write-Host "OK" -ForegroundColor Green
} else {
    Write-Host "WARNING: phpdesktop-settings.json not found" -ForegroundColor Yellow
}

# Rename exe
Write-Host "[7/8] Renaming executable..." -ForegroundColor Cyan
$exePath = "$outputPath\phpdesktop-chrome.exe"
if (Test-Path $exePath) {
    Rename-Item -Path $exePath -NewName "RolexShop.exe" -Force
    Write-Host "Renamed to RolexShop.exe" -ForegroundColor Green
} else {
    Write-Host "WARNING: phpdesktop-chrome.exe not found" -ForegroundColor Yellow
}

# Create helper files
Write-Host "[8/8] Creating helper files..." -ForegroundColor Cyan

# Start.bat
$startBat = @"
@echo off
cd /d "%~dp0"
start RolexShop.exe
exit
"@
Set-Content -Path "$outputPath\Start.bat" -Value $startBat -Encoding ASCII

# README.txt
$readme = @"
ROLEX SHOP - Portable Application
==================================

To start the application:
1. Run Start.bat or RolexShop.exe

First Run:
1. Make sure MySQL server is running on your computer
2. The application will open installation wizard
3. Follow the wizard steps to configure database

Admin Panel:
URL: http://127.0.0.1:54007/admin/
Login: admin
Password: [your password from database]

Technical Info:
- Web Server: 127.0.0.1:54007
- PHP Version: built-in PHP
- Logs: debug.log

Troubleshooting:
- If the app doesn't start, check debug.log
- Make sure port 54007 is not in use
- Run as Administrator if needed
"@
Set-Content -Path "$outputPath\README.txt" -Value $readme -Encoding UTF8

# Database placeholder
$sqlPlaceholder = @"
-- Place your database dump here
-- Export with: mysqldump -u root -p rolex_db > install.sql
-- Or use phpMyAdmin to export your database
"@
Set-Content -Path "$outputPath\www\database\install.sql" -Value $sqlPlaceholder -Encoding UTF8

Write-Host "OK" -ForegroundColor Green

# Summary
Write-Host ""
Write-Host "===================================" -ForegroundColor Cyan
Write-Host "SUCCESS!" -ForegroundColor Green
Write-Host "===================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Package created at: $outputPath" -ForegroundColor White
Write-Host ""
Write-Host "NEXT STEPS:" -ForegroundColor Yellow
Write-Host "1. Export your database:" -ForegroundColor White
Write-Host "   mysqldump -u root -p rolex_db > install.sql" -ForegroundColor Gray
Write-Host ""
Write-Host "2. Copy install.sql to:" -ForegroundColor White
Write-Host "   $outputPath\www\database\" -ForegroundColor Gray
Write-Host ""
Write-Host "3. Test by running:" -ForegroundColor White
Write-Host "   $outputPath\RolexShop.exe" -ForegroundColor Gray
Write-Host ""

# Open output folder
$response = Read-Host "Open output folder now? (Y/N)"
if ($response -eq 'Y' -or $response -eq 'y') {
    Start-Process explorer.exe -ArgumentList $outputPath
}

Write-Host ""
Write-Host "Done!" -ForegroundColor Green
