<?php
require_once __DIR__ . '/../Config/Database.php';

class Review {
    private $conn;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Получить все отзывы для товара
    public function getByProduct($product_id) {
        $query = "SELECT r.*,
                  COALESCE(c.name, r.customer_name) as customer_name
                  FROM reviews r
                  LEFT JOIN customers c ON r.customer_id = c.id
                  WHERE r.product_id = :product_id
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Получить средний рейтинг товара
    public function getAverageRating($product_id) {
        $query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews
                  FROM reviews
                  WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Добавить отзыв
    public function create($product_id, $customer_id, $rating, $comment) {
        $query = "INSERT INTO reviews (product_id, customer_id, rating, comment)
                  VALUES (:product_id, :customer_id, :rating, :comment)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->bindParam(':rating', $rating);
        $stmt->bindParam(':comment', $comment);
        return $stmt->execute();
    }

    // Проверить, оставлял ли клиент уже отзыв на этот товар
    public function hasReviewed($product_id, $customer_id) {
        $query = "SELECT id FROM reviews
                  WHERE product_id = :product_id AND customer_id = :customer_id
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':customer_id', $customer_id);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Удалить отзыв (для админа)
    public function delete($id) {
        $query = "DELETE FROM reviews WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // Получить все отзывы (для админки)
    public function getAll() {
        $query = "SELECT r.*, p.name as product_name
                  FROM reviews r
                  LEFT JOIN products p ON r.product_id = p.id
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
