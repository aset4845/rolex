<?php
require_once __DIR__ . '/../Config/helpers.php';
require_once __DIR__ . '/../Models/Product.php';

/**
 * Cart Controller - Себетпен жұмыс істеу
 */
class CartController {

    /**
     * Өнімді себетке қосу
     * @param int $product_id
     * @param int $quantity
     */
    public static function add($product_id, $quantity = 1) {
        // Проверка авторизации
        if (!isset($_SESSION['customer_id'])) {
            return ['success' => false, 'message' => 'Себетке қосу үшін жүйеге кіріңіз', 'redirect' => '/rolex/public/login.php'];
        }

        $productModel = new Product();
        $product = $productModel->getById($product_id);

        if (!$product) {
            return ['success' => false, 'message' => 'Өнім табылмады'];
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Егер өнім себетте болса, санын қосамыз
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => $quantity
            ];
        }

        return ['success' => true, 'message' => 'Өнім себетке қосылды'];
    }

    /**
     * Себеттен өнімді өшіру
     * @param int $product_id
     */
    public static function remove($product_id) {
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
        }
    }

    /**
     * Себетті толығымен тазалау
     */
    public static function clear() {
        $_SESSION['cart'] = [];
    }

    /**
     * Өнім санын жаңарту
     * @param int $product_id
     * @param int $quantity
     */
    public static function updateQuantity($product_id, $quantity) {
        if (isset($_SESSION['cart'][$product_id])) {
            if ($quantity <= 0) {
                self::remove($product_id);
            } else {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            }
        }
    }

    /**
     * Себетті алу
     * @return array
     */
    public static function get() {
        return $_SESSION['cart'] ?? [];
    }
}
