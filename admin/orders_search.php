<?php
require_once __DIR__ . '/../app/Models/Order.php';

$page_title = 'Поиск заказов - Админ панель';
$orderModel = new Order();

// Параметры поиска
$search_name = $_GET['customer_name'] ?? '';
$search_phone = $_GET['phone'] ?? '';
$search_status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Получаем все заказы
$all_orders = $orderModel->getAll();
$filtered_orders = $all_orders;

// Фильтрация
if (!empty($search_name)) {
    $filtered_orders = array_filter($filtered_orders, function($o) use ($search_name) {
        return stripos($o['customer_name'], $search_name) !== false;
    });
}

if (!empty($search_phone)) {
    $filtered_orders = array_filter($filtered_orders, function($o) use ($search_phone) {
        return stripos($o['phone'], $search_phone) !== false;
    });
}

if (!empty($search_status)) {
    $filtered_orders = array_filter($filtered_orders, function($o) use ($search_status) {
        return $o['status'] === $search_status;
    });
}

if (!empty($date_from)) {
    $filtered_orders = array_filter($filtered_orders, function($o) use ($date_from) {
        return date('Y-m-d', strtotime($o['created_at'])) >= $date_from;
    });
}

if (!empty($date_to)) {
    $filtered_orders = array_filter($filtered_orders, function($o) use ($date_to) {
        return date('Y-m-d', strtotime($o['created_at'])) <= $date_to;
    });
}

$total_found = count($filtered_orders);
$total_sum = array_sum(array_column($filtered_orders, 'total_price'));

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4">
    <i class="bi bi-search"></i> Поиск заказов
</h1>

<!-- Форма поиска -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Фильтры</h5>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Имя клиента</label>
                    <input type="text" name="customer_name" class="form-control"
                           value="<?= clean($search_name) ?>"
                           placeholder="Введите имя...">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Телефон</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= clean($search_phone) ?>"
                           placeholder="+7...">
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Все</option>
                        <option value="қабылданды" <?= $search_status === 'қабылданды' ? 'selected' : '' ?>>
                            Принято
                        </option>
                        <option value="дайындалуда" <?= $search_status === 'дайындалуда' ? 'selected' : '' ?>>
                            В обработке
                        </option>
                        <option value="жеткізілді" <?= $search_status === 'жеткізілді' ? 'selected' : '' ?>>
                            Доставлено
                        </option>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">Дата от</label>
                    <input type="date" name="date_from" class="form-control"
                           value="<?= clean($date_from) ?>">
                </div>

                <div class="col-md-2 mb-3">
                    <label class="form-label">Дата до</label>
                    <input type="date" name="date_to" class="form-control"
                           value="<?= clean($date_to) ?>">
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Поиск
                </button>
                <a href="<?= url('admin/orders_search.php') ?>" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Сбросить
                </a>
                <a href="<?= url('admin/orders.php') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Назад
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Результаты -->
<div class="card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-list-check"></i> Результаты поиска
        </h5>
        <div>
            <span class="badge bg-light text-dark"><?= $total_found ?> заказов</span>
            <span class="badge bg-success"><?= formatPrice($total_sum) ?></span>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($filtered_orders)): ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle display-4 d-block mb-3"></i>
                <h5>Заказы не найдены</h5>
                <p>Попробуйте изменить параметры поиска</p>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>№</th>
                        <th>Клиент</th>
                        <th>Телефон</th>
                        <th>Адрес</th>
                        <th>Сумма</th>
                        <th>Статус</th>
                        <th>Дата</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filtered_orders as $order): ?>
                    <tr>
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td><?= clean($order['customer_name']) ?></td>
                        <td><?= clean($order['phone']) ?></td>
                        <td>
                            <small><?= clean(substr($order['address'], 0, 30)) ?>...</small>
                        </td>
                        <td><strong><?= formatPrice($order['total_price']) ?></strong></td>
                        <td>
                            <?php
                            $badge_class = [
                                'қабылданды' => 'info',
                                'дайындалуда' => 'warning',
                                'жеткізілді' => 'success'
                            ];
                            $class = $badge_class[$order['status']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?= $class ?>">
                                <?= clean($order['status']) ?>
                            </span>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                        <td>
                            <a href="<?= url('admin/order_view.php?id=' . $order['id']) ?>"
                               class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="4" class="text-end"><strong>ИТОГО:</strong></td>
                        <td colspan="4">
                            <strong class="text-success fs-5"><?= formatPrice($total_sum) ?></strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
