# Запуск БЕЗ XAMPP - Полностью автономное приложение

## Вариант 1: Встроенный Portable MySQL (MariaDB)

### Преимущества:
- ✅ Полностью автономное приложение
- ✅ Не требует установки MySQL
- ✅ Пользователю нужен только один exe-файл
- ✅ База данных внутри приложения

### Шаги:

#### 1. Скачайте Portable MariaDB

**Скачать:**
- https://downloads.mariadb.org/mariadb/
- Выберите "Windows ZIP file"
- Версия: 10.11.x или 11.x (стабильная)
- Скачайте файл типа: `mariadb-11.x.x-winx64.zip`

**Или используйте MySQL Portable:**
- https://www.apachefriends.org/download.html (скачайте XAMPP Portable)
- Извлеките только папку `mysql` из XAMPP

#### 2. Структура приложения

```
RolexShop-Standalone\
├── RolexShop.exe           ← Главный файл
├── Start.bat               ← Запускает MySQL, затем приложение
├── Stop.bat                ← Останавливает все
├── settings.json
│
├── mysql\                  ← Портативный MySQL
│   ├── bin\
│   │   ├── mysqld.exe
│   │   └── mysql.exe
│   ├── data\               ← База данных здесь
│   └── my.ini              ← Конфигурация
│
├── php\
│   └── php-cgi.exe
│
└── www\
    ├── index.php
    ├── app\
    └── database\
        └── rolex_db.sql    ← Готовая БД
```

#### 3. Создайте конфигурацию MySQL (my.ini)

Сохраните как `mysql\my.ini`:

```ini
[mysqld]
# Путь к данным (относительно корня приложения)
datadir=mysql/data

# Порт
port=3307

# Настройки производительности
max_connections=50
key_buffer_size=16M
max_allowed_packet=16M
table_open_cache=64
sort_buffer_size=512K
net_buffer_length=8K
read_buffer_size=256K
read_rnd_buffer_size=512K
myisam_sort_buffer_size=8M

# Не использовать сеть (только localhost)
skip-networking
# Использовать named pipes для Windows
enable-named-pipe
shared-memory

# Логи (опционально, отключить в production)
# log-error=mysql/error.log

# Кодировка
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci

[client]
port=3307
```

#### 4. Создайте Start.bat (главный файл запуска)

```batch
@echo off
cd /d "%~dp0"

echo ===================================
echo Rolex Shop - Starting...
echo ===================================
echo.

REM Check if MySQL is already running
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo MySQL already running
) else (
    echo Starting MySQL...
    start /B mysql\bin\mysqld.exe --defaults-file=mysql\my.ini --standalone

    REM Wait for MySQL to start
    timeout /t 3 /nobreak >nul
    echo MySQL started
)

echo.
echo Starting Rolex Shop...
start RolexShop.exe

echo.
echo Application started!
echo Close this window when you're done.
echo.
pause
```

#### 5. Создайте Stop.bat

```batch
@echo off
echo Stopping Rolex Shop...

REM Stop MySQL
taskkill /F /IM mysqld.exe 2>nul
echo MySQL stopped

REM Stop phpdesktop
taskkill /F /IM RolexShop.exe 2>nul
echo Application stopped

echo.
echo Done!
pause
```

#### 6. Обновите Database.php для автономного режима

```php
<?php
class Database {
    private $host = '127.0.0.1';
    private $port = 3307;  // Изменен на 3307
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
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => 5
                ]
            );
        } catch(PDOException $e) {
            die("Connection error: " . $e->getMessage());
        }

        return $this->conn;
    }
}
```

---

## Вариант 2: SQLite (БЕЗ MySQL вообще)

### Преимущества:
- ✅ Еще проще - файловая БД
- ✅ Не нужен MySQL вообще
- ✅ Одна база = один файл
- ✅ Меньше размер приложения

### Недостатки:
- ❌ Нужно переписать код для SQLite
- ❌ Ограниченная производительность при высокой нагрузке

### Шаги:

#### 1. Создайте новый Database.php для SQLite

```php
<?php
class Database {
    private $db_file;
    private $conn;

    public function __construct() {
        // База данных в папке приложения
        $this->db_file = __DIR__ . '/../../database/rolex.db';
    }

    public function connect() {
        $this->conn = null;

        try {
            // Создать папку если не существует
            $dir = dirname($this->db_file);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }

            // Подключение к SQLite
            $this->conn = new PDO(
                "sqlite:" . $this->db_file,
                null,
                null,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

            // Включить Foreign Keys для SQLite
            $this->conn->exec('PRAGMA foreign_keys = ON');

        } catch(PDOException $e) {
            die("Connection error: " . $e->getMessage());
        }

        return $this->conn;
    }
}
```

#### 2. Конвертируйте MySQL в SQLite

**Онлайн конвертер:**
- https://www.rebasedata.com/ (MySQL to SQLite)

**Или вручную через команду:**
```bash
# Экспорт из MySQL
mysqldump -u root -p rolex_db > rolex.sql

# Конвертация (нужен скрипт mysql2sqlite)
./mysql2sqlite rolex.sql | sqlite3 rolex.db
```

---

## Автоматический скрипт сборки STANDALONE версии

Создам PowerShell скрипт для автоматической сборки:

