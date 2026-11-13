<?php
require_once __DIR__ . '/app/Config/Database.php';

echo "<h1>Установка системы авторизации клиентов</h1>";

try {
    $database = new Database();
    $conn = $database->connect();

    echo "<p>✅ Подключение к БД успешно</p>";

    // Проверить, существует ли таблица customers
    $check = $conn->query("SHOW TABLES LIKE 'customers'");
    if ($check->rowCount() > 0) {
        echo "<p>⚠️ Таблица customers уже существует</p>";
    } else {
        echo "<p>ℹ️ Создаём таблицу customers...</p>";

        $sql = "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            phone VARCHAR(20) NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_phone (phone)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $conn->exec($sql);
        echo "<p>✅ Таблица customers создана успешно!</p>";
    }

    // Проверить, есть ли столбец customer_id в reviews
    $check_column = $conn->query("SHOW COLUMNS FROM reviews LIKE 'customer_id'");
    if ($check_column->rowCount() > 0) {
        echo "<p>⚠️ Столбец customer_id уже существует в таблице reviews</p>";
    } else {
        echo "<p>ℹ️ Добавляем столбец customer_id в таблицу reviews...</p>";

        $sql = "ALTER TABLE reviews
                ADD COLUMN customer_id INT DEFAULT NULL AFTER product_id,
                ADD FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE";

        $conn->exec($sql);
        echo "<p>✅ Столбец customer_id добавлен в таблицу reviews!</p>";
    }

    echo "<hr>";
    echo "<h2>✅ Установка завершена!</h2>";
    echo "<p>Теперь вы можете:</p>";
    echo "<ul>";
    echo "<li><a href='/rolex/public/register.php'>Зарегистрироваться</a></li>";
    echo "<li><a href='/rolex/public/login.php'>Войти</a></li>";
    echo "<li><a href='/rolex/public/products.php'>Перейти в каталог</a></li>";
    echo "</ul>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
