# Тестирование с phpdesktop БЕЗ упаковки

Если вы хотите протестировать как будет работать приложение с phpdesktop до полной упаковки, следуйте этим инструкциям:

## Вариант 1: Быстрый тест (рекомендуется)

### Шаг 1: Скачайте phpdesktop
Скачайте и распакуйте phpdesktop в любую папку, например:
```
C:\phpdesktop-chrome-v57.0-rc-php-7.1.3\
```

### Шаг 2: Создайте тестовую конфигурацию
Скопируйте содержимое из файла ниже и сохраните как:
```
C:\phpdesktop-chrome-v57.0-rc-php-7.1.3\settings.json
```

Замените существующий файл.

### Шаг 3: Скопируйте проект в www
Скопируйте все файлы вашего проекта в:
```
C:\phpdesktop-chrome-v57.0-rc-php-7.1.3\www\
```

### Шаг 4: Запустите
Запустите:
```
C:\phpdesktop-chrome-v57.0-rc-php-7.1.3\phpdesktop-chrome.exe
```

---

## Вариант 2: Запуск из текущей папки проекта

### Если phpdesktop уже установлен:

1. Скопируйте все файлы из папки phpdesktop в корень вашего проекта:
   ```
   c:\xampp\htdocs\rolex\
   ```

2. Должно получиться так:
   ```
   c:\xampp\htdocs\rolex\
   ├── phpdesktop-chrome.exe
   ├── settings.json (используйте phpdesktop-settings.json)
   ├── php\
   ├── www\
   │   └── (сюда скопируйте все файлы проекта)
   └── ...
   ```

3. Создайте папку `www` и скопируйте туда файлы проекта

4. Запустите `phpdesktop-chrome.exe`

---

## Конфигурация для теста (settings.json)

```json
{
    "application": {
        "main_url": "http://127.0.0.1:54007/install.php",
        "web_server": "127.0.0.1:54007",
        "single_instance_application": true
    },
    "chrome": {
        "cache_path": "webcache/",
        "log_file": "debug.log",
        "log_severity": "debug",
        "remote_debugging_port": 0,
        "command_line_switches": {
            "disable-web-security": "",
            "allow-file-access-from-files": "",
            "enable-local-storage": ""
        }
    },
    "main_window": {
        "title": "Rolex Shop - TEST",
        "default_size": [1200, 800],
        "minimum_size": [800, 600],
        "center_on_screen": true,
        "start_maximized": false
    },
    "web_server": {
        "listen_on": ["127.0.0.1", 54007],
        "www_directory": "www/",
        "index_files": ["install.php", "index.php", "index.html"],
        "cgi_interpreter": "php/php-cgi.exe",
        "cgi_extensions": ["php"],
        "cgi_temp_dir": ""
    },
    "debugging": {
        "show_console": true,
        "subprocess_show_console": true,
        "log_level": "DEBUG",
        "log_file": "debug.log"
    }
}
```

---

## Устранение проблемы 404

Если вы видите "Error 404: Not Found", проверьте:

### 1. Структура папок
Убедитесь что структура правильная:
```
phpdesktop-root/
├── phpdesktop-chrome.exe
├── settings.json
├── php/
│   └── php-cgi.exe
└── www/              ← Важно!
    ├── index.php
    ├── install.php
    ├── admin/
    ├── public/
    └── app/
```

### 2. Проверьте настройки в settings.json
```json
"www_directory": "www/"    ← Должно быть именно так
"main_url": "http://127.0.0.1:54007/install.php"
```

### 3. Проверьте debug.log
Откройте файл `debug.log` в корне phpdesktop и посмотрите ошибки.

### 4. Консоль
Если включили `"show_console": true`, смотрите вывод в консоли.

### 5. Права доступа
Запустите phpdesktop-chrome.exe от имени администратора.

---

## Быстрый BAT скрипт для теста

Создайте файл `test-phpdesktop.bat` в корне проекта:

```batch
@echo off
echo Creating test structure...

REM Set paths
set PHPDESKTOP=C:\phpdesktop-chrome-v57.0-rc-php-7.1.3
set PROJECT=%~dp0

REM Clear www folder
if exist "%PHPDESKTOP%\www" rmdir /s /q "%PHPDESKTOP%\www"
mkdir "%PHPDESKTOP%\www"

REM Copy project files
xcopy "%PROJECT%admin" "%PHPDESKTOP%\www\admin\" /E /I /H /Y >nul
xcopy "%PROJECT%app" "%PHPDESKTOP%\www\app\" /E /I /H /Y >nul
xcopy "%PROJECT%assets" "%PHPDESKTOP%\www\assets\" /E /I /H /Y >nul
xcopy "%PROJECT%public" "%PHPDESKTOP%\www\public\" /E /I /H /Y >nul
xcopy "%PROJECT%uploads" "%PHPDESKTOP%\www\uploads\" /E /I /H /Y >nul

copy "%PROJECT%index.php" "%PHPDESKTOP%\www\" >nul
copy "%PROJECT%install.php" "%PHPDESKTOP%\www\" >nul

REM Copy settings
copy "%PROJECT%phpdesktop-settings.json" "%PHPDESKTOP%\settings.json" >nul

echo Test structure created!
echo Starting phpdesktop...

REM Start phpdesktop
cd /d "%PHPDESKTOP%"
start phpdesktop-chrome.exe

echo Done!
pause
```

Измените путь `PHPDESKTOP` на ваш и запустите.

---

## Проверка работоспособности

1. После запуска должно открыться окно приложения
2. Должна загрузиться страница установки (install.php)
3. Если видите 404 - проверьте структуру папок
4. Если видите белый экран - проверьте debug.log

---

## После успешного теста

Если тест прошел успешно:
1. Используйте полную упаковку с `build-package.ps1`
2. Распространяйте готовое приложение пользователям

Если возникли проблемы:
1. Проверьте debug.log
2. Убедитесь что PHP работает
3. Проверьте порт 54007 (должен быть свободен)
4. Попробуйте запустить от администратора
