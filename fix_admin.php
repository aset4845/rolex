<?php
/**
 * Админ пайдаланушысын түзету скрипті
 * Бұл файлды бір рет іске қосып, содан кейін өшіріңіз!
 */

require_once __DIR__ . '/app/Config/Database.php';

$database = new Database();
$conn = $database->connect();

// Жаңа пароль хэшін жасау
$username = 'admin';
$password = 'admin123';
$password_hash = password_hash($password, PASSWORD_BCRYPT);
$role = 'admin';

echo "<h2>Админ пайдаланушысын түзету</h2>";

// Бұрынғы админді өшіру
$delete_query = "DELETE FROM users WHERE username = :username";
$stmt = $conn->prepare($delete_query);
$stmt->bindParam(':username', $username);
$stmt->execute();
echo "<p>✓ Ескі админ өшірілді</p>";

// Жаңа админ қосу
$insert_query = "INSERT INTO users (username, password_hash, role) VALUES (:username, :password_hash, :role)";
$stmt = $conn->prepare($insert_query);
$stmt->bindParam(':username', $username);
$stmt->bindParam(':password_hash', $password_hash);
$stmt->bindParam(':role', $role);

if ($stmt->execute()) {
    echo "<p style='color: green; font-weight: bold;'>✓ Жаңа админ сәтті жасалды!</p>";
    echo "<hr>";
    echo "<h3>Деректер:</h3>";
    echo "<p><strong>Логин:</strong> admin</p>";
    echo "<p><strong>Пароль:</strong> admin123</p>";
    echo "<p><strong>Пароль хэші:</strong> $password_hash</p>";
    echo "<hr>";
    echo "<p style='color: red;'><strong>МАҢЫЗДЫ:</strong> Енді осы файлды (fix_admin.php) өшіріңіз!</p>";
    echo "<p><a href='admin/login.php' style='color: blue; text-decoration: underline;'>Админ панельге кіру →</a></p>";
} else {
    echo "<p style='color: red;'>✗ Қате шықты!</p>";
}
?>
