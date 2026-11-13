<?php
// Включить отображение ошибок
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Тест страницы товара</h1>";

try {
    require_once __DIR__ . '/app/Models/Product.php';
    echo "<p>✅ Product модель загружена</p>";

    require_once __DIR__ . '/app/Models/Review.php';
    echo "<p>✅ Review модель загружена</p>";

    require_once __DIR__ . '/app/Models/ProductImage.php';
    echo "<p>✅ ProductImage модель загружена</p>";

    require_once __DIR__ . '/app/Controllers/CartController.php';
    echo "<p>✅ CartController загружен</p>";

    $productModel = new Product();
    $products = $productModel->getAll();
    echo "<p>✅ Найдено товаров: " . count($products) . "</p>";

    if (!empty($products)) {
        $first_product = $products[0];
        echo "<p>Первый товар: <strong>" . htmlspecialchars($first_product['name']) . "</strong></p>";
        echo "<p><a href='/rolex/public/product.php?id=" . $first_product['id'] . "'>Открыть товар</a></p>";
    }

    $imageModel = new ProductImage();
    echo "<p>✅ ProductImage модель работает</p>";

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Ошибка: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
