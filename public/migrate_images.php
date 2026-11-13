<?php
/**
 * Миграция изображений в новую систему
 *
 * Этот скрипт:
 * 1. Переносит все файлы из /uploads/products/ в /public/uploads/products/
 * 2. Очищает таблицу product_images от несуществующих записей
 * 3. Добавляет все старые изображения из products.image в галерею
 * 4. Устанавливает первое изображение как главное для каждого товара
 */

require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/ProductImage.php';

$database = new Database();
$conn = $database->connect();
$productModel = new Product();
$imageModel = new ProductImage();

$old_dir = __DIR__ . '/../uploads/products/';
$new_dir = __DIR__ . '/uploads/products/';

echo "<h1>🔄 Миграция системы изображений</h1>";
echo "<hr>";

// Создать новую директорию если не существует
if (!is_dir($new_dir)) {
    mkdir($new_dir, 0777, true);
    echo "<p>✅ Создана директория: $new_dir</p>";
}

// ШАГ 1: Очистка несуществующих записей из БД
echo "<h2>Шаг 1: Очистка базы данных</h2>";
$query = "SELECT * FROM product_images";
$stmt = $conn->query($query);
$all_images = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cleaned = 0;
foreach ($all_images as $img) {
    $file_path = $new_dir . basename($img['image_path']);

    if (!file_exists($file_path)) {
        $delete_query = "DELETE FROM product_images WHERE id = :id";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bindParam(':id', $img['id']);
        $delete_stmt->execute();

        echo "<p style='color: orange;'>🗑️ Удалена запись ID {$img['id']}: файл не найден - {$img['image_path']}</p>";
        $cleaned++;
    }
}
echo "<p><strong>Очищено записей: $cleaned</strong></p>";
echo "<hr>";

// ШАГ 2: Перенос файлов из старой директории
echo "<h2>Шаг 2: Перенос файлов</h2>";
$moved = 0;
$skipped = 0;

if (is_dir($old_dir)) {
    $files = scandir($old_dir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;

        $old_path = $old_dir . $file;
        $new_path = $new_dir . $file;

        // Пропустить если не файл
        if (!is_file($old_path)) continue;

        // Пропустить если уже существует
        if (file_exists($new_path)) {
            echo "<p style='color: blue;'>⏭️ Файл уже существует: $file</p>";
            $skipped++;
            continue;
        }

        // Копировать файл
        if (copy($old_path, $new_path)) {
            echo "<p style='color: green;'>✅ Скопирован: $file</p>";
            $moved++;
        } else {
            echo "<p style='color: red;'>❌ Ошибка копирования: $file</p>";
        }
    }
}

echo "<p><strong>Перенесено файлов: $moved</strong></p>";
echo "<p><strong>Пропущено (уже существуют): $skipped</strong></p>";
echo "<hr>";

// ШАГ 3: Добавление старых изображений в галерею
echo "<h2>Шаг 3: Миграция данных из products.image</h2>";
$products = $productModel->getAll();
$migrated = 0;

foreach ($products as $product) {
    $product_id = $product['id'];
    $old_image = $product['image'];

    // Пропустить если нет изображения или default
    if (empty($old_image) || $old_image === 'default.jpg') {
        echo "<p>⏭️ Товар #{$product_id} ({$product['name']}): нет изображения</p>";
        continue;
    }

    // Проверить, есть ли уже изображения в галерее
    $existing_count = $imageModel->getCountByProduct($product_id);

    if ($existing_count > 0) {
        echo "<p>⏭️ Товар #{$product_id} ({$product['name']}): галерея уже существует ($existing_count фото)</p>";
        continue;
    }

    // Проверить существование файла
    $file_path = $new_dir . $old_image;

    if (!file_exists($file_path)) {
        echo "<p style='color: orange;'>⚠️ Товар #{$product_id} ({$product['name']}): файл не найден - $old_image</p>";
        continue;
    }

    // Добавить в галерею
    $image_path = 'uploads/products/' . $old_image;
    $imageModel->create($product_id, $image_path, 1, 0);

    echo "<p style='color: green;'>✅ Товар #{$product_id} ({$product['name']}): добавлено изображение $old_image</p>";
    $migrated++;
}

echo "<p><strong>Мигрировано товаров: $migrated</strong></p>";
echo "<hr>";

// ШАГ 4: Проверка результатов
echo "<h2>Шаг 4: Итоговая проверка</h2>";

$products = $productModel->getAll();
$with_images = 0;
$without_images = 0;

foreach ($products as $product) {
    $count = $imageModel->getCountByProduct($product['id']);

    if ($count > 0) {
        $with_images++;
    } else {
        $without_images++;
        echo "<p style='color: orange;'>⚠️ Товар #{$product['id']} ({$product['name']}): нет изображений</p>";
    }
}

echo "<hr>";
echo "<h2>📊 Итоговая статистика</h2>";
echo "<ul>";
echo "<li>Товаров с изображениями: <strong style='color: green;'>$with_images</strong></li>";
echo "<li>Товаров без изображений: <strong style='color: orange;'>$without_images</strong></li>";
echo "<li>Файлов перенесено: <strong>$moved</strong></li>";
echo "<li>Товаров мигрировано: <strong>$migrated</strong></li>";
echo "<li>Записей очищено: <strong>$cleaned</strong></li>";
echo "</ul>";

echo "<hr>";
echo "<h2>✅ Миграция завершена!</h2>";
echo "<p><a href='check_images.php' class='btn'>Проверить результат</a></p>";
echo "<p><a href='products.php' class='btn'>Перейти в каталог</a></p>";

echo "<style>
body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
.btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
.btn:hover { background: #0056b3; }
</style>";
?>
