<?php
require_once __DIR__ . '/../app/Config/Database.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $database = new Database();
    $conn = $database->connect();

    $search = "%{$query}%";

    $sql = "SELECT p.id, p.name, p.price, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.name LIKE :search
            OR p.description LIKE :search
            OR c.name LIKE :search
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':search', $search);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($products);
} catch (Exception $e) {
    echo json_encode([]);
}
?>
