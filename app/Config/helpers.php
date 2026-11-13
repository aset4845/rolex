<?php
/**
 * Көмекші функциялар
 */

// Сессияны бастау
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Админ авторизациясын тексеру
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Авторизацияны тексеру
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Админ бетіне редирект (қорғау)
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /rolex/admin/login.php');
        exit;
    }
}

/**
 * XSS шабуылынан қорғау
 * @param string $data
 * @return string
 */
function clean($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * URL жасау
 * @param string $path
 * @return string
 */
function url($path = '') {
    return '/rolex/' . ltrim($path, '/');
}

/**
 * Асет жолын қайтару
 * @param string $path
 * @return string
 */
function asset($path = '') {
    return '/rolex/assets/' . ltrim($path, '/');
}

/**
 * Себетті алу (сессиядан)
 * @return array
 */
function getCart() {
    return $_SESSION['cart'] ?? [];
}

/**
 * Себеттегі өнімдер санын санау
 * @return int
 */
function getCartCount() {
    $cart = getCart();
    $count = 0;
    foreach ($cart as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

/**
 * Себеттегі жалпы бағаны есептеу
 * @return float
 */
function getCartTotal() {
    $cart = getCart();
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

/**
 * Бағаны форматтау
 * @param float $price
 * @return string
 */
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' ₸';
}

/**
 * Загрузить настройки сайта из JSON файла
 * @return array
 */
function getSettings() {
    static $settings = null;

    if ($settings !== null) {
        return $settings;
    }

    $settings_file = __DIR__ . '/settings.json';

    if (file_exists($settings_file)) {
        $settings = json_decode(file_get_contents($settings_file), true);
    } else {
        // Настройки по умолчанию
        $settings = [
            'site_name' => 'Rolex ЖШС',
            'site_phone' => '+7 (727) 123-45-67',
            'site_email' => 'info@rolex.kz',
            'site_address' => 'Алматы қ., Абай даңғ., 150',
            'free_delivery_from' => 500000,
            'currency' => '₸',
            'products_per_page' => 12,
            'enable_registration' => false
        ];
    }

    return $settings;
}

/**
 * Получить конкретную настройку
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function getSetting($key, $default = null) {
    $settings = getSettings();
    return $settings[$key] ?? $default;
}
