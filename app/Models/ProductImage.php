<?php
require_once __DIR__ . '/../Config/Database.php';

class ProductImage {
    private $conn;
    private $table = 'product_images';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function getByProductId($product_id) {
        $query = "SELECT * FROM {$this->table}
                  WHERE product_id = :product_id
                  ORDER BY is_main DESC, sort_order ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Возвращает главное изображение товара.
     * Приоритет: image_type=product -> is_main -> sort_order/id, с проверкой наличия файла.
     */
    public function getMainImage($product_id) {
        $query = "SELECT * FROM {$this->table}
                  WHERE product_id = :product_id
                  ORDER BY (image_type = 'product') DESC,
                           is_main DESC,
                           sort_order ASC,
                           id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($images as $image) {
            $filePath = __DIR__ . '/../../public/' . ltrim($image['image_path'], '/');
            if (file_exists($filePath)) {
                return $image;
            }
        }

        return $images[0] ?? null;
    }

    public function getGalleryImages($product_id) {
        // Берём все изображения товара: product‑типы выводим первыми, затем по sort_order/id
        $query = "SELECT * FROM {$this->table}
                  WHERE product_id = :product_id
                  ORDER BY (image_type = 'product') DESC,
                           sort_order ASC,
                           id ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Отфильтровываем записи без файла на диске; если всё отфильтровали — возвращаем исходный список
        $withFiles = [];
        foreach ($images as $image) {
            $filePath = __DIR__ . '/../../public/' . ltrim($image['image_path'], '/');
            if (file_exists($filePath)) {
                $withFiles[] = $image;
            }
        }

        return !empty($withFiles) ? $withFiles : $images;
    }

    public function create($product_id, $image_path, $is_main = 0, $sort_order = 0, $image_type = 'gallery') {
        if ($is_main) {
            $this->resetMainImage($product_id);
        }

        $query = "INSERT INTO {$this->table}
                  (product_id, image_path, image_type, is_main, sort_order)
                  VALUES (:product_id, :image_path, :image_type, :is_main, :sort_order)";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->bindParam(':image_path', $image_path);
        $stmt->bindParam(':image_type', $image_type);
        $stmt->bindParam(':is_main', $is_main);
        $stmt->bindParam(':sort_order', $sort_order);

        return $stmt->execute();
    }

    public function delete($id) {
        $query = "SELECT image_path, product_id FROM {$this->table} WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($image) {
            $filename = basename($image['image_path']);
            $file_path = __DIR__ . '/../../public/uploads/products/' . $filename;

            if (file_exists($file_path)) {
                unlink($file_path);
            }

            $query = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $result = $stmt->execute();

            if ($result) {
                $this->ensureMainImage($image['product_id']);
            }

            return $result;
        }

        return false;
    }

    private function ensureMainImage($product_id) {
        $query = "SELECT COUNT(*) as count FROM {$this->table}
                  WHERE product_id = :product_id AND is_main = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['count'] == 0) {
            $query = "SELECT id FROM {$this->table}
                      WHERE product_id = :product_id
                      ORDER BY sort_order ASC LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':product_id', $product_id);
            $stmt->execute();
            $first = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($first) {
                $query = "UPDATE {$this->table} SET is_main = 1 WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $first['id']);
                $stmt->execute();
            }
        }
    }

    public function setMainImage($id, $product_id) {
        $this->resetMainImage($product_id);

        $query = "UPDATE {$this->table} SET is_main = 1 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    private function resetMainImage($product_id) {
        $query = "UPDATE {$this->table} SET is_main = 0 WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        return $stmt->execute();
    }

    public function updateSortOrder($id, $sort_order) {
        $query = "UPDATE {$this->table} SET sort_order = :sort_order WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':sort_order', $sort_order);
        return $stmt->execute();
    }

    public function getCountByProduct($product_id) {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE product_id = :product_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':product_id', $product_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}
