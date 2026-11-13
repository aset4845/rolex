<?php
require_once __DIR__ . '/../Config/Database.php';

/**
 * Product Model - Өнімдер кестесімен жұмыс
 */
class Product {
    private $conn;
    private $table = 'products';

    public $id;
    public $name;
    public $price;
    public $category_id;
    public $description;
    public $image;
    public $stock;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Барлық өнімдерді алу (категория атауымен)
     * @param int|null $category_id
     * @param string|null $search
     * @return array
     */
    public function getAll($category_id = null, $search = null) {
        $query = "SELECT p.*, c.name as category_name
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE 1=1";

        if ($category_id) {
            $query .= " AND p.category_id = :category_id";
        }

        if ($search) {
            $query .= " AND p.name LIKE :search";
        }

        $query .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($query);

        if ($category_id) {
            $stmt->bindParam(':category_id', $category_id);
        }

        if ($search) {
            $search_term = "%{$search}%";
            $stmt->bindParam(':search', $search_term);
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * ID бойынша өнімді алу
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $query = "SELECT p.*, c.name as category_name
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Жаңа өнім қосу
     * @return bool|int Возвращает ID нового товара или false при ошибке
     */
    public function create() {
        $query = "INSERT INTO {$this->table}
                  (name, price, category_id, description, image, stock)
                  VALUES (:name, :price, :category_id, :description, :image, :stock)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':stock', $this->stock);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }

        return false;
    }

    /**
     * Өнімді жаңарту
     * @return bool
     */
    public function update() {
        $query = "UPDATE {$this->table}
                  SET name = :name,
                      price = :price,
                      category_id = :category_id,
                      description = :description,
                      image = :image,
                      stock = :stock
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':price', $this->price);
        $stmt->bindParam(':category_id', $this->category_id);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':image', $this->image);
        $stmt->bindParam(':stock', $this->stock);
        $stmt->bindParam(':id', $this->id);

        return $stmt->execute();
    }

    /**
     * Өнімді өшіру
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Танымал өнімдерді алу (бағасы жоғары немесе қоймада бар)
     * @param int $limit
     * @return array
     */
    public function getFeatured($limit = 4) {
        $query = "SELECT p.*, c.name as category_name
                  FROM {$this->table} p
                  LEFT JOIN categories c ON p.category_id = c.id
                  WHERE p.stock > 0
                  ORDER BY p.price DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Қойманы жаңарту (тапсырыстан кейін)
     * @param int $id
     * @param int $quantity
     * @return bool
     */
    public function updateStock($id, $quantity) {
        $query = "UPDATE {$this->table} SET stock = stock - :quantity WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
