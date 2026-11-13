<?php
require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/ProductImage.php';

$database = new Database();
$conn = $database->connect();
$imageModel = new ProductImage();
$productModel = new Product();

echo "<h2>Очистка базы данных от несуществующих изображений</h2>";

// Получить все записи из product_images
$query = "SELECT * FROM product_images ORDER BY product_id";
$stmt = $conn->query($query);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

$deleted_count = 0;
$migrated_count = 0;

foreach ($images as $image) {
    $file_path = __DIR__ . '/' . $image['image_path'];

    if (!file_exists($file_path)) {
        echo "<p style='color: red;'>❌ Удаляем запись ID {$image['id']} (товар {$image['product_id']}): файл не найден - {$image['image_path']}</p>";

        // Удалить запись из БД
        $delete_query = "DELETE FROM product_images WHERE id = :id";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bindParam(':id', $image['id']);
        $delete_stmt->execute();

        $deleted_count++;
    } else {
        echo "<p style='color: green;'>✅ Запись ID {$image['id']} (товар {$image['product_id']}): файл существует</p>";
    }
}

echo "<hr>";
echo "<h3>Результат:</h3>";
echo "<p>Удалено записей: <strong>$deleted_count</strong></p>";

// Теперь проверим товары, у которых нет изображений в галерее
echo "<hr>";
echo "<h3>Проверка товаров без изображений в галерее</h3>";

$products = $productModel->getAll();

foreach ($products as $product) {
    $count = $imageModel->getCountByProduct($product['id']);

    if ($count === 0) {
        echo "<p>Товар ID {$product['id']} ({$product['name']}): нет изображений в галерее</p>";

        // Проверить старое поле image
        if (!empty($product['image']) && $product['image'] !== 'default.jpg') {
            $old_path = __DIR__ . '/../uploads/products/' . $product['image'];

            if (file_exists($old_path)) {
                echo "<p style='color: blue;'>  → Найдено старое изображение: {$product['image']}</p>";
                echo "<p style='color: blue;'>  → Мигрируем в галерею...</p>";

                $imageModel->create($product['id'], 'uploads/products/' . $product['image'], 1, 0);
                $migrated_count++;

                echo "<p style='color: green;'>  ✅ Мигрировано</p>";
            } else {
                echo "<p style='color: orange;'>  → Старое изображение {$product['image']} не найдено на диске</p>";
            }
        }
    }
}

echo "<p>Мигрировано старых изображений: <strong>$migrated_count</strong></p>";

echo "<hr>";
echo "<p><a href='check_images.php'>Проверить результат</a> | <a href='products.php'>Вернуться к каталогу</a></p>";
?>
