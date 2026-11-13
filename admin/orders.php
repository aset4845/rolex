<?php
require_once __DIR__ . '/../app/Models/Order.php';

$page_title = 'Заказы - Админка';
$orderModel = new Order();

// Обновление статуса заказа
if (isset($_GET['update_status']) && isset($_GET['id']) && isset($_GET['status'])) {
    $order_id = (int)$_GET['id'];
    $status = $_GET['status'];

    if ($orderModel->updateStatus($order_id, $status)) {
        $_SESSION['success_message'] = 'Статус заказа обновлён!';
    }

    header('Location: /rolex/admin/orders.php');
    exit;
}

$orders = $orderModel->getAll();

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4"><i class="bi bi-cart-check"></i> Заказы</h1>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?= $_SESSION['success_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (empty($orders)): ?>
    <div class="alert alert-info text-center">
        <i class="bi bi-info-circle"></i> Заказов пока нет.
    </div>
<?php else: ?>
<div class="card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Список заказов (<?= count($orders) ?>)</h5>
        <a href="<?= url('admin/orders_search.php') ?>" class="btn btn-outline-light btn-sm">
            <i class="bi bi-search"></i> Поиск заказов
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Покупатель</th>
                    <th>Телефон</th>
                    <th>Адрес</th>
                    <th>Сумма</th>
                    <th>Статус</th>
                    <th>Создан</th>
                    <th>Действия</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order): ?>
                    <?php
                    $badge_class = [
                        'қабылданды' => 'warning',
                        'дайындалуда' => 'info',
                        'жеткізілді' => 'success'
                    ];
                    $status_labels = [
                        'қабылданды' => 'Принят',
                        'дайындалуда' => 'Готовится',
                        'жеткізілді' => 'Доставлен'
                    ];
                    $status_ru = $status_labels[$order['status']] ?? $order['status'];
                    $class = $badge_class[$order['status']] ?? 'secondary';
                    ?>
                    <tr>
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td><?= clean($order['customer_name']) ?></td>
                        <td><?= clean($order['phone']) ?></td>
                        <td><?= clean($order['address']) ?></td>
                        <td><strong><?= number_format($order['total_price'], 0, ',', ' ') ?> ₸</strong></td>
                        <td>
                            <span class="badge bg-<?= $class ?>"><?= $status_ru ?></span>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="order_view.php?id=<?= $order['id'] ?>" class="btn btn-primary" title="Просмотр">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <?php if ($order['status'] !== 'жеткізілді'): ?>
                                    <a href="?update_status&id=<?= $order['id'] ?>&status=қабылданды"
                                       class="btn btn-outline-warning" title="Отметить как принят">
                                        <i class="bi bi-arrow-repeat"></i>
                                    </a>
                                    <a href="?update_status&id=<?= $order['id'] ?>&status=дайындалуда"
                                       class="btn btn-outline-info" title="Отметить как готовится">
                                        <i class="bi bi-hourglass-split"></i>
                                    </a>
                                    <a href="?update_status&id=<?= $order['id'] ?>&status=жеткізілді"
                                       class="btn btn-outline-success" title="Отметить как доставлен">
                                        <i class="bi bi-check2-circle"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
