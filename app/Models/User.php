<?php
require_once __DIR__ . '/../Config/Database.php';

/**
 * User Model - Пайдаланушылар кестесімен жұмыс
 */
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $username;
    public $password_hash;
    public $role;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Авторизация - логин және пароль бойынша тексеру
     * @param string $username
     * @param string $password
     * @return array|false
     */
    public function login($username, $password) {
        $query = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return false;
    }

    /**
     * Жаңа пайдаланушы қосу
     * @param string $username
     * @param string $password
     * @param string $role
     * @return bool
     */
    public function create($username, $password, $role = 'user') {
        $query = "INSERT INTO {$this->table} (username, password_hash, role) VALUES (:username, :password_hash, :role)";
        $stmt = $this->conn->prepare($query);

        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':role', $role);

        return $stmt->execute();
    }

    /**
     * Барлық пайдаланушыларды алу
     * @return array
     */
    public function getAll() {
        $query = "SELECT id, username, role, created_at FROM {$this->table} ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * ID бойынша пайдаланушыны алу
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
}
