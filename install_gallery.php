<?php
require_once __DIR__ . '/app/Config/Database.php';

echo "<h1>Установка галереи изображений</h1>";

try {
    $database = new Database();
    $conn = $database->connect();

    echo "<p>✅ Подключение к БД успешно</p>";

    // Проверить, существует ли таблица
    $check = $conn->query("SHOW TABLES LIKE 'product_images'");
    if ($check->rowCount() > 0) {
        echo "<p>⚠️ Таблица product_images уже существует</p>";
    } else {
        echo "<p>ℹ️ Создаём таблицу product_images...</p>";

        // Создать таблицу
        $sql = "CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            image_path VARCHAR(255) NOT NULL,
            is_main TINYINT(1) DEFAULT 0,
            sort_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
            INDEX idx_product_id (product_id),
            INDEX idx_sort_order (sort_order)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $conn->exec($sql);
        echo "<p>✅ Таблица product_images создана успешно!</p>";
    }

    // Создать папку для загрузок
    $upload_dir = __DIR__ . '/public/uploads/products/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
        echo "<p>✅ Папка для загрузок создана: public/uploads/products/</p>";
    } else {
        echo "<p>✅ Папка для загрузок уже существует</p>";
    }

    echo "<hr>";
    echo "<h2>Установка завершена!</h2>";
    echo "<p><a href='/rolex/admin/products.php'>Перейти к товарам</a></p>";
    echo "<p><a href='/rolex/public/products.php'>Открыть каталог</a></p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
}
?>
