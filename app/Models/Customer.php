<?php
require_once __DIR__ . '/../Config/Database.php';

class Customer {
    private $conn;
    private $table = 'customers';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Регистрация нового клиента
    public function register($name, $email, $phone, $password) {
        // Проверить, существует ли email
        if ($this->emailExists($email)) {
            return ['success' => false, 'message' => 'Email уже зарегистрирован'];
        }

        // Хешировать пароль
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO {$this->table} (name, email, phone, password)
                  VALUES (:name, :email, :phone, :password)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':password', $hashed_password);

        if ($stmt->execute()) {
            return ['success' => true, 'customer_id' => $this->conn->lastInsertId()];
        }

        return ['success' => false, 'message' => 'Ошибка регистрации'];
    }

    // Вход клиента
    public function login($email, $password) {
        $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($customer && password_verify($password, $customer['password'])) {
            return ['success' => true, 'customer' => $customer];
        }

        return ['success' => false, 'message' => 'Неверный email или пароль'];
    }

    // Проверить, существует ли email
    public function emailExists($email) {
        $query = "SELECT id FROM {$this->table} WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Получить клиента по ID
    public function getById($id) {
        $query = "SELECT * FROM {$this->table} WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Получить клиента по email
    public function getByEmail($email) {
        $query = "SELECT * FROM {$this->table} WHERE email = :email LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Обновить профиль клиента
    public function updateProfile($id, $name, $phone) {
        $query = "UPDATE {$this->table}
                  SET name = :name, phone = :phone
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':phone', $phone);

        return $stmt->execute();
    }

    // Изменить пароль
    public function changePassword($id, $old_password, $new_password) {
        $customer = $this->getById($id);

        if (!$customer || !password_verify($old_password, $customer['password'])) {
            return ['success' => false, 'message' => 'Неверный старый пароль'];
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $query = "UPDATE {$this->table} SET password = :password WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', $hashed_password);

        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Пароль успешно изменен'];
        }

        return ['success' => false, 'message' => 'Ошибка изменения пароля'];
    }
}
