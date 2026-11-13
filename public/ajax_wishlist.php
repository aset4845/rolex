<?php
require_once __DIR__ . '/../app/Config/Database.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];

if (empty($ids)) {
    echo json_encode([]);
    exit;
}

try {
    $database = new Database();
    $conn = $database->connect();

    // Подготовить placeholders
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';

    $query = "SELECT p.id, p.name, p.price, p.stock, c.name as category_name
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.id
              WHERE p.id IN ($placeholders)";

    $stmt = $conn->prepare($query);
    $stmt->execute($ids);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($products);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
