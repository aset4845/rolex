<?php
require_once __DIR__ . '/../app/Config/helpers.php';
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Order.php';

requireAdmin();

$type = $_GET['type'] ?? 'orders';
$format = $_GET['format'] ?? 'csv';

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="rolex_' . $type . '_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    // UTF-8 BOM для Excel
    fwrite($output, "\xEF\xBB\xBF");

    if ($type === 'orders') {
        // Экспорт заказов
        $orderModel = new Order();
        $orders = $orderModel->getAll();

        // Заголовки
        fputcsv($output, ['№', 'Клиент', 'Телефон', 'Адрес', 'Сумма', 'Статус', 'Дата'], ';');

        // Данные
        foreach ($orders as $order) {
            fputcsv($output, [
                $order['id'],
                $order['customer_name'],
                "=\"" . $order['phone'] . "\"", // Формула для текста в Excel
                $order['address'],
                $order['total_price'],
                $order['status'],
                ' ' . date('d.m.Y H:i', strtotime($order['created_at'])) // Пробел для текстового формата
            ], ';');
        }

    } elseif ($type === 'products') {
        // Экспорт товаров
        $productModel = new Product();
        $products = $productModel->getAll();

        // Заголовки
        fputcsv($output, ['ID', 'Название', 'Категория', 'Цена', 'Склад', 'Описание', 'Дата'], ';');

        // Данные
        foreach ($products as $product) {
            fputcsv($output, [
                $product['id'],
                $product['name'],
                $product['category_name'],
                $product['price'],
                $product['stock'],
                $product['description'],
                ' ' . date('d.m.Y', strtotime($product['created_at'])) // Пробел для текстового формата
            ], ';');
        }
    }

    fclose($output);
    exit;
}
?>
