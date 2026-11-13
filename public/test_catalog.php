<?php
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/ProductImage.php';
require_once __DIR__ . '/../app/Config/helpers.php';

$productModel = new Product();
$imageModel = new ProductImage();

$products = $productModel->getAll();

echo "<h2>Тест отображения товаров в каталоге</h2>";

foreach ($products as $product) {
    echo "<hr>";
    echo "<h3>Товар ID: {$product['id']} - {$product['name']}</h3>";

    // Получить изображение из галереи
    $main_image = $imageModel->getMainImage($product['id']);

    echo "<p><strong>Поле product.image:</strong> " . htmlspecialchars($product['image']) . "</p>";
    echo "<p><strong>Фото в галерее:</strong> " . ($main_image ? "Да (путь: " . htmlspecialchars($main_image['image_path']) . ")" : "Нет") . "</p>";

    // Определить источник изображения
    $image_src = null;
    $source = "Нет фото";

    if ($main_image) {
        $image_src = url('public/' . $main_image['image_path']);
        $source = "Галерея (product_images)";
    } elseif (!empty($product['image']) && $product['image'] !== 'default.jpg') {
        $image_src = url('uploads/products/' . $product['image']);
        $source = "Старое поле (product.image)";
    }

    echo "<p><strong>Источник:</strong> $source</p>";

    if ($image_src) {
        echo "<p><strong>URL изображения:</strong> <code>" . htmlspecialchars($image_src) . "</code></p>";

        // Проверить существование файла для старого пути
        if (!$main_image && !empty($product['image'])) {
            $old_path = __DIR__ . '/../uploads/products/' . $product['image'];
            echo "<p><strong>Физический путь (старый):</strong> $old_path</p>";
            echo "<p><strong>Файл существует:</strong> " . (file_exists($old_path) ? "✅ ДА" : "❌ НЕТ") . "</p>";
        }

        echo "<p><strong>Изображение:</strong></p>";
        echo "<img src='$image_src' style='max-width: 200px; border: 2px solid blue;' onerror='this.style.border=\"2px solid red\"; this.alt=\"Ошибка загрузки\"' />";
    } else {
        echo "<p><strong>Изображение:</strong> Иконка по умолчанию</p>";
    }
}
?>
