# Решение проблемы 404 и ошибок подключения

## Проблема 1: MySQL не запущен

**Ошибка:**
```
SQLSTATE[HY000] [2002] Подключение не установлено, т.к. конечный компьютер отверг запрос на подключение
```

**Решение:**

### Шаг 1: Запустите MySQL
1. Откройте XAMPP Control Panel
2. Нажмите "Start" напротив MySQL
3. Дождитесь зеленого индикатора

ИЛИ через командную строку:
```batch
cd c:\xampp
.\mysql\bin\mysqld.exe --console
```

### Шаг 2: Проверьте подключение
```batch
cd c:\xampp\mysql\bin
mysql -u root -p
```
Если подключается - MySQL работает.

---

## Проблема 2: Неправильная структура папок

**Проблема:**
```
C:\RolexShop-Portable\www\rolex\...
                          ^^^^^^ - ЛИШНЯЯ ПАПКА!
```

**Должно быть:**
```
C:\RolexShop-Portable\www\admin\
C:\RolexShop-Portable\www\public\
C:\RolexShop-Portable\www\app\
```

**А не:**
```
C:\RolexShop-Portable\www\rolex\admin\  ❌ НЕПРАВИЛЬНО
```

---

## БЫСТРОЕ РЕШЕНИЕ

### Вариант A: Пересоберите правильно

Запустите заново `build-package.ps1` или используйте исправленный скрипт ниже.

### Вариант B: Исправьте вручную

1. Откройте `C:\RolexShop-Portable\www\`
2. Если там есть папка `rolex`, то:
   - Зайдите в `C:\RolexShop-Portable\www\rolex\`
   - Вырежьте ВСЕ файлы и папки
   - Вставьте их в `C:\RolexShop-Portable\www\`
   - Удалите пустую папку `rolex`

3. Итоговая структура:
```
C:\RolexShop-Portable\www\
├── index.php          ← Должен быть здесь!
├── install.php
├── admin\
├── public\
├── app\
├── assets\
└── uploads\
```

---

## ИСПРАВЛЕННЫЙ СКРИПТ УПАКОВКИ

Сохраните как `build-fixed.bat`:

```batch
@echo off
cls
echo ===================================
echo Rolex Shop - Fixed Build
echo ===================================
echo.

set /p PHPDESKTOP="Enter phpdesktop path: "
if "%PHPDESKTOP%"=="" set PHPDESKTOP=C:\phpdesktop-chrome-v57.0-rc-php-7.1.3

if "%PHPDESKTOP:~-1%"=="\" set PHPDESKTOP=%PHPDESKTOP:~0,-1%

if not exist "%PHPDESKTOP%\phpdesktop-chrome.exe" (
    echo ERROR: phpdesktop not found!
    pause
    exit /b 1
)

set OUTPUT_PATH=C:\RolexShop-Portable
set PROJECT_PATH=c:\xampp\htdocs\rolex

echo Output: %OUTPUT_PATH%
echo Project: %PROJECT_PATH%
pause

REM Clean output
if exist "%OUTPUT_PATH%" rmdir /s /q "%OUTPUT_PATH%"
mkdir "%OUTPUT_PATH%"

REM Copy phpdesktop
echo [1/4] Copying phpdesktop...
xcopy "%PHPDESKTOP%\*" "%OUTPUT_PATH%\" /E /I /H /Y >nul

REM Clear and create www
echo [2/4] Preparing www folder...
if exist "%OUTPUT_PATH%\www" rmdir /s /q "%OUTPUT_PATH%\www"
mkdir "%OUTPUT_PATH%\www"

REM Copy project files DIRECTLY to www (not to www\rolex!)
echo [3/4] Copying project files...
xcopy "%PROJECT_PATH%\admin" "%OUTPUT_PATH%\www\admin\" /E /I /H /Y >nul
xcopy "%PROJECT_PATH%\app" "%OUTPUT_PATH%\www\app\" /E /I /H /Y >nul
xcopy "%PROJECT_PATH%\assets" "%OUTPUT_PATH%\www\assets\" /E /I /H /Y >nul
xcopy "%PROJECT_PATH%\public" "%OUTPUT_PATH%\www\public\" /E /I /H /Y >nul
xcopy "%PROJECT_PATH%\uploads" "%OUTPUT_PATH%\www\uploads\" /E /I /H /Y >nul

copy "%PROJECT_PATH%\index.php" "%OUTPUT_PATH%\www\" >nul
copy "%PROJECT_PATH%\install.php" "%OUTPUT_PATH%\www\" >nul
if exist "%PROJECT_PATH%\.htaccess" copy "%PROJECT_PATH%\.htaccess" "%OUTPUT_PATH%\www\" >nul

mkdir "%OUTPUT_PATH%\www\database"

REM Copy settings
echo [4/4] Configuring...
copy "%PROJECT_PATH%\phpdesktop-settings.json" "%OUTPUT_PATH%\settings.json" >nul

if exist "%OUTPUT_PATH%\phpdesktop-chrome.exe" (
    ren "%OUTPUT_PATH%\phpdesktop-chrome.exe" "RolexShop.exe"
)

echo.
echo ===================================
echo SUCCESS!
echo ===================================
echo.
echo IMPORTANT - Before running:
echo 1. Start MySQL in XAMPP Control Panel
echo 2. Make sure MySQL is running (green indicator)
echo.
echo Then run: %OUTPUT_PATH%\RolexShop.exe
echo.
pause
explorer "%OUTPUT_PATH%"
```

---

## ИСПРАВЛЕННЫЙ PowerShell СКРИПТ

Сохраните как `build-fixed.ps1`:

```powershell
Write-Host "===================================" -ForegroundColor Cyan
Write-Host "Rolex Shop - Fixed Build" -ForegroundColor Cyan
Write-Host "===================================" -ForegroundColor Cyan

$phpDesktopPath = Read-Host "Enter phpdesktop path (or Enter for default)"
if ([string]::IsNullOrWhiteSpace($phpDesktopPath)) {
    $phpDesktopPath = "C:\phpdesktop-chrome-v57.0-rc-php-7.1.3"
}

if (-not (Test-Path "$phpDesktopPath\phpdesktop-chrome.exe")) {
    Write-Host "ERROR: phpdesktop not found!" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

$outputPath = "C:\RolexShop-Portable"
$projectPath = "c:\xampp\htdocs\rolex"

Write-Host ""
Write-Host "Output: $outputPath" -ForegroundColor White
Write-Host "Project: $projectPath" -ForegroundColor White
Write-Host ""
Read-Host "Press Enter to continue"

# Clean output
if (Test-Path $outputPath) {
    Remove-Item -Path $outputPath -Recurse -Force -ErrorAction SilentlyContinue
}
New-Item -ItemType Directory -Path $outputPath -Force | Out-Null

# Copy phpdesktop
Write-Host "[1/4] Copying phpdesktop..." -ForegroundColor Cyan
Copy-Item -Path "$phpDesktopPath\*" -Destination $outputPath -Recurse -Force
Write-Host "OK" -ForegroundColor Green

# Clear www
Write-Host "[2/4] Preparing www..." -ForegroundColor Cyan
if (Test-Path "$outputPath\www") {
    Remove-Item -Path "$outputPath\www" -Recurse -Force
}
New-Item -ItemType Directory -Path "$outputPath\www" -Force | Out-Null
Write-Host "OK" -ForegroundColor Green

# Copy project DIRECTLY to www (not www\rolex!)
Write-Host "[3/4] Copying project files..." -ForegroundColor Cyan
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
Write-Host "[4/4] Configuring..." -ForegroundColor Cyan
Copy-Item -Path "$projectPath\phpdesktop-settings.json" -Destination "$outputPath\settings.json" -Force

if (Test-Path "$outputPath\phpdesktop-chrome.exe") {
    Rename-Item -Path "$outputPath\phpdesktop-chrome.exe" -NewName "RolexShop.exe" -Force
}
Write-Host "OK" -ForegroundColor Green

Write-Host ""
Write-Host "===================================" -ForegroundColor Cyan
Write-Host "SUCCESS!" -ForegroundColor Green
Write-Host "===================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "IMPORTANT - Before running:" -ForegroundColor Yellow
Write-Host "1. Start MySQL in XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Make sure MySQL is running (green)" -ForegroundColor White
Write-Host ""
Write-Host "Then run: $outputPath\RolexShop.exe" -ForegroundColor White
Write-Host ""

$open = Read-Host "Open output folder? (Y/N)"
if ($open -eq 'Y' -or $open -eq 'y') {
    Start-Process explorer.exe -ArgumentList $outputPath
}
```

---

## ПРОВЕРОЧНЫЙ СПИСОК

После сборки проверьте структуру:

```
C:\RolexShop-Portable\
├── RolexShop.exe
├── settings.json
├── php\
│   └── php-cgi.exe
└── www\
    ├── index.php         ← Должен быть ЗДЕСЬ!
    ├── install.php       ← А НЕ в www\rolex\!
    ├── admin\
    ├── public\
    ├── app\
    │   ├── Config\
    │   │   └── Database.php
    │   └── Models\
    ├── assets\
    └── uploads\
```

**Если видите `www\rolex\` - это НЕПРАВИЛЬНО!**

---

## ЗАПУСК ПРИЛОЖЕНИЯ

### Перед запуском RolexShop.exe:

1. **Запустите MySQL:**
   - XAMPP Control Panel → MySQL → Start
   - Дождитесь зеленого индикатора

2. **Проверьте MySQL работает:**
   ```batch
   cd c:\xampp\mysql\bin
   mysql -u root -p
   ```

3. **Запустите приложение:**
   ```
   C:\RolexShop-Portable\RolexShop.exe
   ```

4. **При первом запуске:**
   - Откроется мастер установки
   - Введите данные MySQL:
     - Host: `localhost`
     - Port: `3306`
     - Database: `rolex_db`
     - User: `root`
     - Password: (ваш пароль или пусто)

---

## ЕСЛИ ВСЕ ЕЩЕ НЕ РАБОТАЕТ

### Проверьте debug.log:
```
C:\RolexShop-Portable\debug.log
```

### Включите показ консоли:
В `settings.json` установите:
```json
"show_console": true
```

### Попробуйте другой порт:
В `settings.json` измените:
```json
"main_url": "http://127.0.0.1:54008/install.php",
"web_server": "127.0.0.1:54008",
"listen_on": ["127.0.0.1", 54008]
```

### Запустите от администратора:
ПКМ на RolexShop.exe → Запуск от имени администратора

---

Теперь все должно работать правильно!
