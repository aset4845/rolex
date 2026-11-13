<?php
/**
 * Диагностика авторизации
 */

require_once __DIR__ . '/app/Config/Database.php';
require_once __DIR__ . '/app/Models/User.php';

echo "<h2>Диагностика авторизации</h2>";
echo "<hr>";

// 1. Проверка подключения к БД
try {
    $database = new Database();
    $conn = $database->connect();
    echo "<p style='color: green;'>✓ Подключение к БД успешно</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Ошибка подключения к БД: " . $e->getMessage() . "</p>";
    exit;
}

// 2. Проверка наличия пользователя admin
$query = "SELECT * FROM users WHERE username = 'admin'";
$stmt = $conn->prepare($query);
$stmt->execute();
$user = $stmt->fetch();

if ($user) {
    echo "<p style='color: green;'>✓ Пользователь 'admin' найден в БД</p>";
    echo "<pre>";
    echo "ID: " . $user['id'] . "\n";
    echo "Username: " . $user['username'] . "\n";
    echo "Role: " . $user['role'] . "\n";
    echo "Password Hash: " . $user['password_hash'] . "\n";
    echo "</pre>";
} else {
    echo "<p style='color: red;'>✗ Пользователь 'admin' НЕ найден в БД!</p>";
    echo "<p><strong>Решение:</strong> Запустите <a href='fix_admin.php'>fix_admin.php</a></p>";
    exit;
}

echo "<hr>";

// 3. Проверка пароля
$test_password = 'admin123';
echo "<h3>Тестирование пароля: <code>$test_password</code></h3>";

if (password_verify($test_password, $user['password_hash'])) {
    echo "<p style='color: green; font-weight: bold;'>✓ Пароль '$test_password' ПРАВИЛЬНЫЙ!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ Пароль '$test_password' НЕПРАВИЛЬНЫЙ!</p>";
    echo "<p><strong>Решение:</strong> Пароль хэш в БД неверный. Запустите <a href='fix_admin.php'>fix_admin.php</a></p>";
}

echo "<hr>";

// 4. Тестирование через User Model
echo "<h3>Тестирование через User Model</h3>";
$userModel = new User();
$login_result = $userModel->login('admin', 'admin123');

if ($login_result) {
    echo "<p style='color: green; font-weight: bold;'>✓ Авторизация через Model работает!</p>";
    echo "<pre>";
    print_r($login_result);
    echo "</pre>";
} else {
    echo "<p style='color: red; font-weight: bold;'>✗ Авторизация через Model НЕ работает!</p>";
}

echo "<hr>";

// 5. Генерация нового хэша
echo "<h3>Генерация нового хэша для 'admin123'</h3>";
$new_hash = password_hash('admin123', PASSWORD_BCRYPT);
echo "<p>Новый хэш: <code>$new_hash</code></p>";

echo "<hr>";
echo "<h3>Инструкции:</h3>";
echo "<ol>";
echo "<li>Если пароль неправильный, запустите <a href='fix_admin.php' style='color: blue;'>fix_admin.php</a></li>";
echo "<li>Или вручную обновите в PHPMyAdmin таблицу <code>users</code>, установив password_hash = <code>$new_hash</code></li>";
echo "<li>После исправления попробуйте <a href='admin/login.php' style='color: green;'>войти в админ панель</a></li>";
echo "</ol>";

echo "<p style='color: red;'><strong>ВАЖНО:</strong> После исправления удалите этот файл (test_login.php)</p>";
?>
