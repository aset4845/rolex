<?php
require_once __DIR__ . '/../Config/Database.php';

/**
 * Category Model - Категориялар кестесімен жұмыс
 */
class Category {
    private $conn;
    private $table = 'categories';

    public $id;
    public $name;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Барлық категорияларды алу
     * @return array
     */
    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * ID бойынша категорияны алу
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Жаңа категория қосу
     * @param string $name
     * @return bool
     */
    public function create($name) {
        $query = "INSERT INTO {$this->table} (name) VALUES (:name)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        return $stmt->execute();
    }

    /**
     * Категорияны жаңарту
     * @param int $id
     * @param string $name
     * @return bool
     */
    public function update($id, $name) {
        $query = "UPDATE {$this->table} SET name = :name WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Категорияны өшіру
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
