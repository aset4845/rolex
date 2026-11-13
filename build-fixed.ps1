# FIXED PowerShell build script for Rolex Shop
# Fixes the www\rolex\ issue - files go directly to www\

Write-Host "===================================" -ForegroundColor Cyan
Write-Host "Rolex Shop - FIXED Build Script" -ForegroundColor Cyan
Write-Host "===================================" -ForegroundColor Cyan
Write-Host ""

# Get phpdesktop path
$defaultPath = "C:\phpdesktop-chrome-v57.0-rc-php-7.1.3"
$phpDesktopPath = Read-Host "Enter phpdesktop path (or press Enter for: $defaultPath)"
if ([string]::IsNullOrWhiteSpace($phpDesktopPath)) {
    $phpDesktopPath = $defaultPath
}

# Validate
if (-not (Test-Path "$phpDesktopPath\phpdesktop-chrome.exe")) {
    Write-Host ""
    Write-Host "ERROR: phpdesktop-chrome.exe not found at:" -ForegroundColor Red
    Write-Host $phpDesktopPath -ForegroundColor Red
    Write-Host ""
    Write-Host "Please download from:" -ForegroundColor Yellow
    Write-Host "https://github.com/cztomczak/phpdesktop/releases" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "phpdesktop found!" -ForegroundColor Green

$outputPath = "C:\RolexShop-Portable"
$projectPath = "c:\xampp\htdocs\rolex"

Write-Host ""
Write-Host "Configuration:" -ForegroundColor White
Write-Host "  Source: $projectPath" -ForegroundColor Gray
Write-Host "  Output: $outputPath" -ForegroundColor Gray
Write-Host ""
Read-Host "Press Enter to start build"

# Clean output directory
Write-Host ""
if (Test-Path $outputPath) {
    Write-Host "Removing old build..." -ForegroundColor Yellow
    Remove-Item -Path $outputPath -Recurse -Force -ErrorAction SilentlyContinue
    Start-Sleep -Milliseconds 500
}
New-Item -ItemType Directory -Path $outputPath -Force | Out-Null

# Step 1: Copy phpdesktop
Write-Host "[1/5] Copying phpdesktop files..." -ForegroundColor Cyan
Copy-Item -Path "$phpDesktopPath\*" -Destination $outputPath -Recurse -Force
Write-Host "OK - phpdesktop copied" -ForegroundColor Green

# Step 2: Prepare www folder
Write-Host "[2/5] Preparing www folder..." -ForegroundColor Cyan
if (Test-Path "$outputPath\www") {
    Remove-Item -Path "$outputPath\www" -Recurse -Force
}
New-Item -ItemType Directory -Path "$outputPath\www" -Force | Out-Null
Write-Host "OK - www folder ready" -ForegroundColor Green

# Step 3: Copy project folders DIRECTLY to www (NOT to www\rolex!)
Write-Host "[3/5] Copying project files..." -ForegroundColor Cyan
Write-Host "  IMPORTANT: Copying DIRECTLY to www\ (not www\rolex\)" -ForegroundColor Yellow

$folders = @("admin", "app", "assets", "public", "uploads", "backups")
$copiedCount = 0

foreach ($folder in $folders) {
    $sourcePath = Join-Path $projectPath $folder
    if (Test-Path $sourcePath) {
        # Copy DIRECTLY to www\foldername, NOT www\rolex\foldername
        $destPath = Join-Path "$outputPath\www" $folder
        Copy-Item -Path $sourcePath -Destination $destPath -Recurse -Force
        Write-Host "    $folder -> www\$folder" -ForegroundColor Gray
        $copiedCount++
    }
}

Write-Host "OK - $copiedCount folders copied" -ForegroundColor Green

# Step 4: Copy root files
Write-Host "[4/5] Copying root files..." -ForegroundColor Cyan
$rootFiles = @("index.php", "install.php", ".htaccess")
$copiedFiles = 0

foreach ($file in $rootFiles) {
    $sourcePath = Join-Path $projectPath $file
    if (Test-Path $sourcePath) {
        # Copy DIRECTLY to www\, NOT www\rolex\
        Copy-Item -Path $sourcePath -Destination "$outputPath\www\" -Force
        Write-Host "    $file -> www\" -ForegroundColor Gray
        $copiedFiles++
    }
}

# Create database folder
New-Item -ItemType Directory -Path "$outputPath\www\database" -Force | Out-Null
Write-Host "OK - $copiedFiles files copied, database folder created" -ForegroundColor Green

# Step 5: Configure
Write-Host "[5/5] Configuring..." -ForegroundColor Cyan

# Copy settings
$settingsSource = Join-Path $projectPath "phpdesktop-settings.json"
if (Test-Path $settingsSource) {
    Copy-Item -Path $settingsSource -Destination "$outputPath\settings.json" -Force
    Write-Host "    settings.json configured" -ForegroundColor Gray
}

# Rename exe
$exePath = "$outputPath\phpdesktop-chrome.exe"
if (Test-Path $exePath) {
    Rename-Item -Path $exePath -NewName "RolexShop.exe" -Force
    Write-Host "    Renamed to RolexShop.exe" -ForegroundColor Gray
}

# Create helper files
$startBat = @"
@echo off
cd /d "%~dp0"
start RolexShop.exe
exit
"@
Set-Content -Path "$outputPath\Start.bat" -Value $startBat -Encoding ASCII

Write-Host "OK - Configuration complete" -ForegroundColor Green

# Verify structure
Write-Host ""
Write-Host "Verifying structure..." -ForegroundColor Cyan
$indexPhp = "$outputPath\www\index.php"
$installPhp = "$outputPath\www\install.php"
$adminFolder = "$outputPath\www\admin"
$appFolder = "$outputPath\www\app"

$allGood = $true

if (Test-Path $indexPhp) {
    Write-Host "  [OK] index.php found in www\" -ForegroundColor Green
} else {
    Write-Host "  [!!] index.php NOT found in www\" -ForegroundColor Red
    $allGood = $false
}

if (Test-Path $installPhp) {
    Write-Host "  [OK] install.php found in www\" -ForegroundColor Green
} else {
    Write-Host "  [!!] install.php NOT found in www\" -ForegroundColor Red
    $allGood = $false
}

if (Test-Path $adminFolder) {
    Write-Host "  [OK] admin folder found in www\" -ForegroundColor Green
} else {
    Write-Host "  [!!] admin folder NOT found in www\" -ForegroundColor Red
    $allGood = $false
}

if (Test-Path $appFolder) {
    Write-Host "  [OK] app folder found in www\" -ForegroundColor Green
} else {
    Write-Host "  [!!] app folder NOT found in www\" -ForegroundColor Red
    $allGood = $false
}

# Check for incorrect rolex subfolder
$rolexFolder = "$outputPath\www\rolex"
if (Test-Path $rolexFolder) {
    Write-Host "  [WARNING] Unwanted 'rolex' folder found in www\" -ForegroundColor Red
    Write-Host "            This will cause 404 errors!" -ForegroundColor Red
    $allGood = $false
}

Write-Host ""
if ($allGood) {
    Write-Host "===================================" -ForegroundColor Green
    Write-Host "BUILD SUCCESSFUL!" -ForegroundColor Green
    Write-Host "===================================" -ForegroundColor Green
} else {
    Write-Host "===================================" -ForegroundColor Yellow
    Write-Host "BUILD COMPLETED WITH WARNINGS" -ForegroundColor Yellow
    Write-Host "===================================" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Output location: $outputPath" -ForegroundColor White
Write-Host ""
Write-Host "BEFORE RUNNING THE APP:" -ForegroundColor Yellow
Write-Host "1. Start MySQL in XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Make sure MySQL shows GREEN status" -ForegroundColor White
Write-Host "3. Export your database to:" -ForegroundColor White
Write-Host "   $outputPath\www\database\install.sql" -ForegroundColor Gray
Write-Host ""
Write-Host "THEN RUN:" -ForegroundColor Yellow
Write-Host "  $outputPath\RolexShop.exe" -ForegroundColor White
Write-Host ""

$response = Read-Host "Open output folder now? (Y/N)"
if ($response -eq 'Y' -or $response -eq 'y' -or $response -eq '') {
    Start-Process explorer.exe -ArgumentList $outputPath
}

Write-Host ""
Write-Host "Done! Remember to start MySQL before running the app." -ForegroundColor Green
