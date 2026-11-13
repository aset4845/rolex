<?php
require_once __DIR__ . '/../app/Models/Order.php';

$page_title = 'Просмотр заказа - Админка';
$orderModel = new Order();

$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header('Location: ' . url('admin/orders.php'));
    exit;
}

$order = $orderModel->getById($order_id);
$items = $orderModel->getItems($order_id);

if (!$order) {
    header('Location: ' . url('admin/orders.php'));
    exit;
}

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-receipt"></i> Заказ #<?= $order['id'] ?></h1>
    <a href="<?= url('admin/orders.php') ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Назад к заказам
    </a>
</div>

<div class="row">
    <!-- Информация о заказе -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person"></i> Клиент</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Имя</th>
                        <td><?= clean($order['customer_name']) ?></td>
                    </tr>
                    <tr>
                        <th>Телефон</th>
                        <td><?= clean($order['phone']) ?></td>
                    </tr>
                    <tr>
                        <th>Адрес</th>
                        <td><?= nl2br(clean($order['address'])) ?></td>
                    </tr>
                    <tr>
                        <th>Статус</th>
                        <?php
                        $status_labels = [
                            'қабылданды' => 'Принят',
                            'дайындалуда' => 'Готовится',
                            'жеткізілді' => 'Доставлен'
                        ];
                        $badge_class = [
                            'қабылданды' => 'warning',
                            'дайындалуда' => 'info',
                            'жеткізілді' => 'success'
                        ];
                        $status_ru = $status_labels[$order['status']] ?? $order['status'];
                        $class = $badge_class[$order['status']] ?? 'secondary';
                        ?>
                        <td><span class="badge bg-<?= $class ?>"><?= $status_ru ?></span></td>
                    </tr>
                    <tr>
                        <th>Создан</th>
                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Итог -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-cash-stack"></i> Итог по заказу</h5>
            </div>
            <div class="card-body">
                <h4 class="text-success mb-3">
                    <?= number_format($order['total_price'], 0, ',', ' ') ?> ₸
                </h4>
                <p class="text-muted mb-1">
                    <i class="bi bi-box-seam"></i> Всего позиций: <?= count($items) ?>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Товары в заказе -->
<div class="card">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="bi bi-bag-check"></i> Состав заказа</h5>
    </div>
    <div class="card-body">
        <?php if (empty($items)): ?>
            <p class="text-muted text-center">В заказе нет товаров.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Товар</th>
                        <th>Количество</th>
                        <th>Цена</th>
                        <th>Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= $item['product_id'] ?></td>
                        <td><?= clean($item['product_name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= number_format($item['price'], 0, ',', ' ') ?> ₸</td>
                        <td><?= number_format($item['price'] * $item['quantity'], 0, ',', ' ') ?> ₸</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
