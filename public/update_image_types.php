<?php
/**
 * Скрипт обновления структуры таблицы product_images
 * Добавляет поле image_type для различия типов изображений
 */

require_once __DIR__ . '/../app/Config/Database.php';

$database = new Database();
$conn = $database->connect();

echo "<h1>🔄 Обновление структуры таблицы product_images</h1>";
echo "<hr>";

try {
    // Проверить, существует ли уже поле image_type
    $check_query = "SHOW COLUMNS FROM product_images LIKE 'image_type'";
    $stmt = $conn->query($check_query);
    $exists = $stmt->fetch();

    if ($exists) {
        echo "<p style='color: orange;'>⚠️ Поле image_type уже существует в таблице</p>";
    } else {
        // Добавить поле image_type
        $alter_query = "ALTER TABLE product_images
                        ADD COLUMN image_type VARCHAR(20) DEFAULT 'gallery' AFTER image_path";
        $conn->exec($alter_query);
        echo "<p style='color: green;'>✅ Поле image_type успешно добавлено</p>";
    }

    // Обновить существующие записи - установить тип на основе is_main
    $update_query = "UPDATE product_images
                     SET image_type = CASE
                         WHEN is_main = 1 THEN 'product'
                         ELSE 'gallery'
                     END";
    $updated = $conn->exec($update_query);
    echo "<p style='color: green;'>✅ Обновлено записей: $updated</p>";

    echo "<hr>";
    echo "<h2>✅ Обновление завершено успешно!</h2>";
    echo "<p><a href='product_images.php?product_id=1'>Перейти к галерее</a></p>";
    echo "<p><a href='products.php'>Перейти в каталог</a></p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
}

echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
a { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
a:hover { background: #0056b3; }
</style>";
?>
