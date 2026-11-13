<?php
/**
 * Скрипт установки базы данных приложения
 * Запускается при первом запуске приложения
 */

session_start();

// Проверка что установка еще не выполнена
$install_lock = __DIR__ . '/install.lock';
if (file_exists($install_lock)) {
    header('Location: /index.php');
    exit('Установка уже выполнена');
}

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$error = '';
$success = '';

// Шаг 1: Проверка требований
if ($step === 1) {
    $requirements = [
        'PHP Version >= 7.0' => version_compare(PHP_VERSION, '7.0.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL' => extension_loaded('pdo_mysql'),
        'JSON Extension' => extension_loaded('json'),
        'MBString Extension' => extension_loaded('mbstring'),
        'GD Extension' => extension_loaded('gd'),
    ];

    $all_passed = true;
    foreach ($requirements as $name => $passed) {
        if (!$passed) $all_passed = false;
    }
}

// Шаг 2: Настройка базы данных
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_port = $_POST['db_port'] ?? '3306';
    $db_name = $_POST['db_name'] ?? 'rolex_db';
    $db_user = $_POST['db_user'] ?? 'root';
    $db_pass = $_POST['db_pass'] ?? '';

    try {
        // Подключение к MySQL серверу
        $dsn = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Создание базы данных если не существует
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$db_name}`");

        // Проверка наличия SQL файла
        $sql_file = __DIR__ . '/database/install.sql';
        if (file_exists($sql_file)) {
            $sql = file_get_contents($sql_file);
            $pdo->exec($sql);
        }

        // Сохранение конфигурации базы данных
        $config_content = "<?php\n";
        $config_content .= "class Database {\n";
        $config_content .= "    private \$host = '{$db_host}';\n";
        $config_content .= "    private \$db_name = '{$db_name}';\n";
        $config_content .= "    private \$username = '{$db_user}';\n";
        $config_content .= "    private \$password = '{$db_pass}';\n";
        $config_content .= "    private \$conn;\n\n";
        $config_content .= "    public function connect() {\n";
        $config_content .= "        \$this->conn = null;\n";
        $config_content .= "        try {\n";
        $config_content .= "            \$this->conn = new PDO(\n";
        $config_content .= "                \"mysql:host={\$this->host};dbname={\$this->db_name};charset=utf8mb4\",\n";
        $config_content .= "                \$this->username,\n";
        $config_content .= "                \$this->password\n";
        $config_content .= "            );\n";
        $config_content .= "            \$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);\n";
        $config_content .= "            \$this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);\n";
        $config_content .= "        } catch(PDOException \$e) {\n";
        $config_content .= "            echo \"Connection error: \" . \$e->getMessage();\n";
        $config_content .= "        }\n";
        $config_content .= "        return \$this->conn;\n";
        $config_content .= "    }\n";
        $config_content .= "}\n";

        file_put_contents(__DIR__ . '/app/Config/Database.php', $config_content);

        // Создание lock файла
        file_put_contents($install_lock, date('Y-m-d H:i:s'));

        $success = 'База данных успешно настроена!';
        header('Location: install.php?step=3');
        exit;

    } catch (PDOException $e) {
        $error = 'Ошибка подключения: ' . $e->getMessage();
    }
}

// Шаг 3: Завершение
if ($step === 3) {
    // Все готово
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка Rolex Shop</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        .steps {
            display: flex;
            justify-content: space-between;
            margin: 30px 0;
            position: relative;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #666;
            position: relative;
            z-index: 2;
        }
        .step.active {
            background: #667eea;
            color: white;
        }
        .step.completed {
            background: #4caf50;
            color: white;
        }
        .steps::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 1;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input[type="text"],
        input[type="password"],
        input[type="number"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .error {
            background: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
        }
        .success {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
        }
        .requirement {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        .requirement:last-child {
            border-bottom: none;
        }
        .check {
            color: #4caf50;
            font-weight: bold;
        }
        .cross {
            color: #f44336;
            font-weight: bold;
        }
        .info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Установка Rolex Shop</h1>

        <div class="steps">
            <div class="step <?= $step >= 1 ? 'active' : '' ?> <?= $step > 1 ? 'completed' : '' ?>">1</div>
            <div class="step <?= $step >= 2 ? 'active' : '' ?> <?= $step > 2 ? 'completed' : '' ?>">2</div>
            <div class="step <?= $step >= 3 ? 'active' : '' ?>">3</div>
        </div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($step === 1): ?>
            <h2>Проверка требований</h2>
            <div style="margin: 20px 0;">
                <?php foreach ($requirements as $name => $passed): ?>
                    <div class="requirement">
                        <span><?= $name ?></span>
                        <span class="<?= $passed ? 'check' : 'cross' ?>">
                            <?= $passed ? '✓ Пройдено' : '✗ Не пройдено' ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($all_passed): ?>
                <a href="install.php?step=2" class="btn">Далее</a>
            <?php else: ?>
                <div class="error">Не все требования выполнены. Установка невозможна.</div>
            <?php endif; ?>

        <?php elseif ($step === 2): ?>
            <h2>Настройка базы данных</h2>
            <div class="info">
                Убедитесь, что MySQL сервер запущен на вашем компьютере.
            </div>
            <form method="POST" action="install.php?step=2">
                <div class="form-group">
                    <label>Хост базы данных:</label>
                    <input type="text" name="db_host" value="localhost" required>
                </div>
                <div class="form-group">
                    <label>Порт:</label>
                    <input type="number" name="db_port" value="3306" required>
                </div>
                <div class="form-group">
                    <label>Имя базы данных:</label>
                    <input type="text" name="db_name" value="rolex_db" required>
                </div>
                <div class="form-group">
                    <label>Пользователь:</label>
                    <input type="text" name="db_user" value="root" required>
                </div>
                <div class="form-group">
                    <label>Пароль:</label>
                    <input type="password" name="db_pass">
                </div>
                <button type="submit" class="btn">Установить</button>
            </form>

        <?php elseif ($step === 3): ?>
            <h2>Установка завершена!</h2>
            <div class="success">
                <p>База данных успешно настроена.</p>
                <p>Приложение готово к использованию.</p>
            </div>
            <div class="info">
                <strong>Данные для входа в админ-панель:</strong><br>
                URL: <a href="/admin/" target="_blank">/admin/</a><br>
                Логин: admin<br>
                Пароль: [ваш пароль из базы данных]
            </div>
            <a href="/index.php" class="btn">Перейти к приложению</a>
        <?php endif; ?>
    </div>
</body>
</html>
