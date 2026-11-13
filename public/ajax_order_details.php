<?php
require_once __DIR__ . '/../app/Config/Database.php';

header('Content-Type: application/json');

$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    echo json_encode(['error' => 'ID не указан']);
    exit;
}

try {
    $database = new Database();
    $conn = $database->connect();

    // Получить заказ
    $query = "SELECT * FROM orders WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':id', $order_id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['error' => 'Заказ не найден']);
        exit;
    }

    // Получить товары заказа
    $query = "SELECT oi.*, p.name as product_name
              FROM order_items oi
              LEFT JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = :order_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'order' => $order,
        'items' => $items
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
