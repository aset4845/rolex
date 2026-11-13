<?php
/**
 * Database конфигурациясы
 * MySQL дерекқорына қосылу үшін
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'rolex_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Дерекқорға қосылу
     * @return PDO|null
     */
    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch(PDOException $e) {
            echo "Қосылу қатесі: " . $e->getMessage();
        }

        return $this->conn;
    }
}
