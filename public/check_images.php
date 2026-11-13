<?php
require_once __DIR__ . '/../app/Config/Database.php';
require_once __DIR__ . '/../app/Config/helpers.php';

$database = new Database();
$conn = $database->connect();

// Получить все записи из product_images
$query = "SELECT * FROM product_images ORDER BY product_id";
$stmt = $conn->query($query);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>Проверка изображений в галерее</h2>";
echo "<p>Всего записей: " . count($images) . "</p>";

foreach ($images as $image) {
    echo "<hr>";
    echo "<h3>ID записи: {$image['id']} | Товар: {$image['product_id']}</h3>";
    echo "<p><strong>Путь в БД:</strong> " . htmlspecialchars($image['image_path']) . "</p>";
    echo "<p><strong>Главное:</strong> " . ($image['is_main'] ? 'Да' : 'Нет') . "</p>";

    // Проверить файл с префиксом public/
    $file_path_with_public = __DIR__ . '/' . $image['image_path'];
    $exists_with_public = file_exists($file_path_with_public);

    echo "<p><strong>Физический путь (с public/):</strong> $file_path_with_public</p>";
    echo "<p><strong>Файл существует:</strong> " . ($exists_with_public ? "✅ ДА" : "❌ НЕТ") . "</p>";

    // Проверить URL
    $url_path = url('public/' . $image['image_path']);
    echo "<p><strong>URL:</strong> <code>$url_path</code></p>";

    if ($exists_with_public) {
        echo "<p><strong>Изображение:</strong></p>";
        echo "<img src='$url_path' style='max-width: 200px; border: 2px solid green;' onerror='this.style.border=\"2px solid red\"; this.alt=\"Ошибка загрузки\"' />";
    } else {
        echo "<p style='color: red;'><strong>⚠️ ФАЙЛ НЕ НАЙДЕН НА ДИСКЕ!</strong></p>";
    }
}
?>
