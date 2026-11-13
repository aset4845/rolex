<?php
require_once __DIR__ . '/../Config/Database.php';

/**
 * Order Model - Тапсырыстар кестесімен жұмыс
 */
class Order {
    private $conn;
    private $table = 'orders';
    private $items_table = 'order_items';

    public $id;
    public $customer_name;
    public $phone;
    public $address;
    public $total_price;
    public $status;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Жаңа тапсырыс жасау
     * @param array $cart
     * @return int|false - тапсырыс ID немесе false
     */
    public function create($cart) {
        try {
            $this->conn->beginTransaction();

            // 1. Тапсырысты сақтау
            $query = "INSERT INTO {$this->table}
                      (customer_name, phone, address, total_price, status)
                      VALUES (:customer_name, :phone, :address, :total_price, 'қабылданды')";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':customer_name', $this->customer_name);
            $stmt->bindParam(':phone', $this->phone);
            $stmt->bindParam(':address', $this->address);
            $stmt->bindParam(':total_price', $this->total_price);
            $stmt->execute();

            $order_id = $this->conn->lastInsertId();

            // 2. Тапсырыс элементтерін сақтау
            $query = "INSERT INTO {$this->items_table}
                      (order_id, product_id, quantity, price)
                      VALUES (:order_id, :product_id, :quantity, :price)";

            $stmt = $this->conn->prepare($query);

            foreach ($cart as $item) {
                $stmt->bindParam(':order_id', $order_id);
                $stmt->bindParam(':product_id', $item['id']);
                $stmt->bindParam(':quantity', $item['quantity']);
                $stmt->bindParam(':price', $item['price']);
                $stmt->execute();

                // 3. Қойманы жаңарту
                $update_query = "UPDATE products SET stock = stock - :quantity WHERE id = :product_id";
                $update_stmt = $this->conn->prepare($update_query);
                $update_stmt->bindParam(':quantity', $item['quantity']);
                $update_stmt->bindParam(':product_id', $item['id']);
                $update_stmt->execute();
            }

            $this->conn->commit();
            return $order_id;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    /**
     * Барлық тапсырыстарды алу
     * @return array
     */
    public function getAll() {
        $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * ID бойынша тапсырысты алу
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
     * Тапсырыс элементтерін алу
     * @param int $order_id
     * @return array
     */
    public function getItems($order_id) {
        $query = "SELECT oi.*, p.name as product_name
                  FROM {$this->items_table} oi
                  LEFT JOIN products p ON oi.product_id = p.id
                  WHERE oi.order_id = :order_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Тапсырыс статусын жаңарту
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        $query = "UPDATE {$this->table} SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Статистика - жалпы тапсырыстар саны
     * @return int
     */
    public function getTotalCount() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Статистика - жалпы сату сомасы
     * @return float
     */
    public function getTotalRevenue() {
        $query = "SELECT SUM(total_price) as revenue FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch();
        return $result['revenue'] ?? 0;
    }
}
