# Инструкция по упаковке проекта Rolex в exe-файл

## Требования
- Скачанный phpdesktop-chrome (https://github.com/cztomczak/phpdesktop/releases)
- Ваш проект Rolex

## Шаги упаковки

### 1. Распакуйте phpdesktop-chrome
Распакуйте скачанный архив phpdesktop в отдельную папку, например `C:\phpdesktop-rolex\`

### 2. Скопируйте файлы проекта
Скопируйте все файлы вашего проекта в папку `www` внутри phpdesktop:
```
C:\phpdesktop-rolex\www\
```

### 3. Настройте settings.json
Замените файл `settings.json` в корне phpdesktop на файл `phpdesktop-settings.json` из этого проекта.

### 4. Настройте базу данных
Поскольку приложение будет использовать встроенный PHP, вам нужно:

#### Вариант А: SQLite (рекомендуется для standalone приложения)
1. Конвертируйте вашу MySQL базу в SQLite
2. Обновите файл `app/Config/Database.php` для использования SQLite

#### Вариант Б: MySQL (требует установки MySQL на компьютере пользователя)
1. Создайте скрипт установки базы данных
2. Включите дамп базы данных в папку `www/database/`
3. Создайте install.php для первого запуска

### 5. Обновите конфигурацию базы данных для portable режима

Создайте файл `www/app/Config/Database.portable.php`:
```php
<?php
class Database {
    private $host = '127.0.0.1';
    private $port = 3306;
    private $db_name = 'rolex_shop';
    private $username = 'root';
    private $password = '';

    // Для SQLite используйте:
    // private $dsn = 'sqlite:' . __DIR__ . '/../../database/rolex.db';

    public function getConnection() {
        // MySQL подключение
        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";

        try {
            $conn = new PDO($dsn, $this->username, $this->password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch(PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }
}
```

### 6. Настройте пути загрузок
Убедитесь, что пути для загрузки файлов настроены относительно:
```php
$upload_dir = __DIR__ . '/../../public/uploads/products/';
```

### 7. Переименуйте exe-файл (опционально)
Переименуйте `phpdesktop-chrome.exe` в `RolexShop.exe`

### 8. Создайте инсталлятор (опционально)
Используйте Inno Setup для создания установщика:
- Скачайте Inno Setup: https://jrsoftware.org/isdl.php
- Создайте скрипт установки

### 9. Тестирование
1. Запустите `RolexShop.exe`
2. Проверьте все функции:
   - Авторизация
   - Каталог продуктов
   - Корзина
   - Оформление заказа
   - Админ-панель

## Структура финальной папки
```
phpdesktop-rolex/
├── phpdesktop-chrome.exe (или RolexShop.exe)
├── settings.json
├── php/
│   ├── php-cgi.exe
│   ├── php.ini
│   └── ext/
├── www/
│   ├── index.php
│   ├── admin/
│   ├── public/
│   ├── app/
│   ├── assets/
│   ├── uploads/
│   └── database/ (если используете SQLite)
├── webcache/
└── debug.log
```

## Рекомендации для MySQL

### Вариант 1: Портативный MySQL
Если вам нужна полностью standalone версия с MySQL:
1. Скачайте MySQL Portable
2. Включите MySQL в папку приложения
3. Создайте bat-файл для запуска MySQL перед phpdesktop

Создайте `start.bat`:
```batch
@echo off
cd /d "%~dp0"

REM Запуск MySQL
start /MIN mysql\bin\mysqld.exe --defaults-file=mysql\my.ini

REM Ждем запуска MySQL
timeout /t 3

REM Запуск приложения
start RolexShop.exe

exit
```

### Вариант 2: Внешний MySQL
Приложение подключается к существующему MySQL серверу на машине пользователя.

## Распространение

### Для тестирования
Просто заархивируйте папку `phpdesktop-rolex` в zip-архив.

### Для конечных пользователей
Создайте установщик с помощью:
- Inno Setup (бесплатно)
- NSIS (бесплатно)
- Advanced Installer (платно, но удобнее)

## Размер приложения
Ожидайте размер около:
- Только phpdesktop + проект: ~100-150 МБ
- С портативным MySQL: ~250-300 МБ

## Безопасность
1. Отключите debug режим в production
2. Используйте уникальные пароли базы данных
3. Настройте правильные права доступа к файлам
4. Обфусцируйте PHP код если необходимо (ionCube, Zend Guard)

## Лицензирование
Убедитесь, что соблюдаете лицензии:
- phpdesktop (Apache License 2.0)
- PHP (PHP License)
- MySQL (GPL или коммерческая)
