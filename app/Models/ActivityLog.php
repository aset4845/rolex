<?php
require_once __DIR__ . '/../Config/Database.php';

/**
 * ActivityLog Model - Логирование действий администраторов
 */
class ActivityLog {
    private $conn;
    private $table = 'activity_logs';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Записать лог
     * @param string $action - действие (create, update, delete, login, etc.)
     * @param string $entity_type - тип сущности (product, order, category, user)
     * @param int|null $entity_id - ID сущности
     * @param string $description - описание действия
     * @return bool
     */
    public function log($action, $entity_type, $entity_id = null, $description = '') {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        $query = "INSERT INTO {$this->table}
                  (user_id, username, action, entity_type, entity_id, description, ip_address, user_agent)
                  VALUES (:user_id, :username, :action, :entity_type, :entity_id, :description, :ip_address, :user_agent)";

        $stmt = $this->conn->prepare($query);

        $user_id = $_SESSION['user_id'];
        $username = $_SESSION['username'] ?? 'Unknown';
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':entity_type', $entity_type);
        $stmt->bindParam(':entity_id', $entity_id);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':ip_address', $ip_address);
        $stmt->bindParam(':user_agent', $user_agent);

        return $stmt->execute();
    }

    /**
     * Получить все логи
     * @param int $limit - ограничение количества
     * @param int $offset - смещение
     * @return array
     */
    public function getAll($limit = 100, $offset = 0) {
        $query = "SELECT * FROM {$this->table}
                  ORDER BY created_at DESC
                  LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Получить логи по пользователю
     * @param int $user_id
     * @param int $limit
     * @return array
     */
    public function getByUser($user_id, $limit = 50) {
        $query = "SELECT * FROM {$this->table}
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Получить логи по типу действия
     * @param string $action
     * @param int $limit
     * @return array
     */
    public function getByAction($action, $limit = 50) {
        $query = "SELECT * FROM {$this->table}
                  WHERE action = :action
                  ORDER BY created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':action', $action);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Получить логи по типу сущности
     * @param string $entity_type
     * @param int|null $entity_id
     * @param int $limit
     * @return array
     */
    public function getByEntity($entity_type, $entity_id = null, $limit = 50) {
        $query = "SELECT * FROM {$this->table}
                  WHERE entity_type = :entity_type";

        if ($entity_id !== null) {
            $query .= " AND entity_id = :entity_id";
        }

        $query .= " ORDER BY created_at DESC LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':entity_type', $entity_type);

        if ($entity_id !== null) {
            $stmt->bindParam(':entity_id', $entity_id);
        }

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Очистить старые логи
     * @param int $days - удалить логи старше X дней
     * @return bool
     */
    public function cleanOld($days = 90) {
        $query = "DELETE FROM {$this->table}
                  WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Получить статистику
     * @return array
     */
    public function getStats() {
        $query = "SELECT
                    COUNT(*) as total_logs,
                    COUNT(DISTINCT user_id) as unique_users,
                    COUNT(CASE WHEN action = 'login' THEN 1 END) as login_count,
                    COUNT(CASE WHEN action = 'create' THEN 1 END) as create_count,
                    COUNT(CASE WHEN action = 'update' THEN 1 END) as update_count,
                    COUNT(CASE WHEN action = 'delete' THEN 1 END) as delete_count
                  FROM {$this->table}";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch();
    }
}
