<?php
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Order.php';
require_once __DIR__ . '/../app/Models/Category.php';

$page_title = 'Dashboard - Админ панель';

$productModel = new Product();
$orderModel = new Order();
$categoryModel = new Category();

// Основная статистика
$total_products = count($productModel->getAll());
$total_orders = $orderModel->getTotalCount();
$total_revenue = $orderModel->getTotalRevenue();
$total_categories = count($categoryModel->getAll());

// Расширенная статистика
$all_orders = $orderModel->getAll();
$pending_orders = array_filter($all_orders, fn($o) => $o['status'] === 'қабылданды');
$processing_orders = array_filter($all_orders, fn($o) => $o['status'] === 'дайындалуда');
$completed_orders = array_filter($all_orders, fn($o) => $o['status'] === 'жеткізілді');

$pending_count = count($pending_orders);
$processing_count = count($processing_orders);
$completed_count = count($completed_orders);

// Товары с низким запасом
$all_products = $productModel->getAll();
$low_stock = array_filter($all_products, fn($p) => $p['stock'] > 0 && $p['stock'] <= 3);
$out_of_stock = array_filter($all_products, fn($p) => $p['stock'] == 0);

// Средний чек
$average_order = $total_orders > 0 ? $total_revenue / $total_orders : 0;

// Последние заказы
$recent_orders = array_slice($all_orders, 0, 5);

// Топ товары (по цене - имитация популярности)
$top_products = array_slice($all_products, 0, 5);

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4">
    <i class="bi bi-speedometer2"></i> Dashboard
    <small class="text-muted">Расширенная статистика</small>
</h1>

<!-- Основные метрики -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Өнімдер</h6>
                        <h2 class="mb-0"><?= $total_products ?></h2>
                        <small><?= count($low_stock) ?> мало, <?= count($out_of_stock) ?> нет</small>
                    </div>
                    <i class="bi bi-box-seam display-4 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?= url('admin/products.php') ?>" class="text-white text-decoration-none">
                    Перейти <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Тапсырыстар</h6>
                        <h2 class="mb-0"><?= $total_orders ?></h2>
                        <small>Активных: <?= $pending_count + $processing_count ?></small>
                    </div>
                    <i class="bi bi-cart-check display-4 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?= url('admin/orders.php') ?>" class="text-white text-decoration-none">
                    Перейти <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Кіріс</h6>
                        <h3 class="mb-0 small"><?= formatPrice($total_revenue) ?></h3>
                        <small>Средний: <?= formatPrice($average_order) ?></small>
                    </div>
                    <i class="bi bi-currency-exchange display-4 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?= url('admin/reports.php') ?>" class="text-white text-decoration-none">
                    Отчёты <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1">Категориялар</h6>
                        <h2 class="mb-0"><?= $total_categories ?></h2>
                        <small>Всего разделов</small>
                    </div>
                    <i class="bi bi-tags display-4 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="<?= url('admin/categories.php') ?>" class="text-white text-decoration-none">
                    Управление <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Статусы заказов -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Статусы заказов</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <i class="bi bi-clock-history display-4 text-info"></i>
                            <h3 class="mt-2"><?= $pending_count ?></h3>
                            <p class="text-muted mb-0">Қабылданды</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <i class="bi bi-hourglass-split display-4 text-warning"></i>
                            <h3 class="mt-2"><?= $processing_count ?></h3>
                            <p class="text-muted mb-0">Дайындалуда</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded">
                            <i class="bi bi-check-circle display-4 text-success"></i>
                            <h3 class="mt-2"><?= $completed_count ?></h3>
                            <p class="text-muted mb-0">Жеткізілді</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Предупреждения -->
<?php if (!empty($low_stock) || !empty($out_of_stock)): ?>
<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-warning">
            <h5><i class="bi bi-exclamation-triangle"></i> Предупреждения</h5>
            <?php if (!empty($out_of_stock)): ?>
                <p class="mb-2">
                    <strong><?= count($out_of_stock) ?></strong> товаров закончилось на складе:
                    <?php foreach (array_slice($out_of_stock, 0, 3) as $p): ?>
                        <span class="badge bg-danger"><?= clean($p['name']) ?></span>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($low_stock)): ?>
                <p class="mb-0">
                    <strong><?= count($low_stock) ?></strong> товаров заканчивается (≤3 шт.):
                    <?php foreach (array_slice($low_stock, 0, 3) as $p): ?>
                        <span class="badge bg-warning"><?= clean($p['name']) ?> (<?= $p['stock'] ?>)</span>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Последние заказы -->
    <div class="col-xl-8 mb-4">
        <div class="card h-100">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Соңғы тапсырыстар</h5>
                <a href="<?= url('admin/orders.php') ?>" class="btn btn-sm btn-light">Все</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recent_orders)): ?>
                    <p class="text-muted text-center p-4">Заказов нет</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Клиент</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Дата</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><strong>#<?= $order['id'] ?></strong></td>
                                <td><?= clean($order['customer_name']) ?></td>
                                <td><?= formatPrice($order['total_price']) ?></td>
                                <td>
                                    <?php
                                    $badge_class = [
                                        'қабылданды' => 'info',
                                        'дайындалуда' => 'warning',
                                        'жеткізілді' => 'success'
                                    ];
                                    $class = $badge_class[$order['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $class ?>"><?= clean($order['status']) ?></span>
                                </td>
                                <td><?= date('d.m.Y', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <a href="<?= url('admin/order_view.php?id=' . $order['id']) ?>"
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Топ товары -->
    <div class="col-xl-4 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-star"></i> Премиум товары</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php foreach ($top_products as $product): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1"><?= clean($product['name']) ?></h6>
                                <small class="text-muted">
                                    <?= clean($product['category_name']) ?>
                                </small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success"><?= formatPrice($product['price']) ?></div>
                                <small class="text-muted">
                                    <?php if ($product['stock'] > 0): ?>
                                        <span class="text-success">В наличии</span>
                                    <?php else: ?>
                                        <span class="text-danger">Нет</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Быстрые действия -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Быстрые действия</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <a href="<?= url('admin/product_add.php') ?>" class="text-decoration-none">
                            <div class="p-4 border rounded hover-shadow">
                                <i class="bi bi-plus-circle display-1 text-primary"></i>
                                <h6 class="mt-3">Добавить товар</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= url('admin/orders.php') ?>" class="text-decoration-none">
                            <div class="p-4 border rounded hover-shadow">
                                <i class="bi bi-cart-check display-1 text-success"></i>
                                <h6 class="mt-3">Заказы</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= url('admin/reports.php') ?>" class="text-decoration-none">
                            <div class="p-4 border rounded hover-shadow">
                                <i class="bi bi-graph-up display-1 text-info"></i>
                                <h6 class="mt-3">Отчёты</h6>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= url('admin/users.php') ?>" class="text-decoration-none">
                            <div class="p-4 border rounded hover-shadow">
                                <i class="bi bi-people display-1 text-warning"></i>
                                <h6 class="mt-3">Пользователи</h6>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: all 0.3s;
}
.hover-shadow:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
    transform: translateY(-3px);
}
</style>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
