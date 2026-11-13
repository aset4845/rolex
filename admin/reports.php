<?php
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Order.php';
require_once __DIR__ . '/../app/Models/Category.php';

$page_title = 'Отчёты и аналитика - Админ панель';

$productModel = new Product();
$orderModel = new Order();
$categoryModel = new Category();

// Все заказы
$all_orders = $orderModel->getAll();
$total_orders = count($all_orders);
$total_revenue = $orderModel->getTotalRevenue();

// Статистика по статусам
$pending = array_filter($all_orders, fn($o) => $o['status'] === 'қабылданды');
$processing = array_filter($all_orders, fn($o) => $o['status'] === 'дайындалуда');
$completed = array_filter($all_orders, fn($o) => $o['status'] === 'жеткізілді');

// Средний чек
$average_order = $total_orders > 0 ? $total_revenue / $total_orders : 0;

// Товары
$all_products = $productModel->getAll();
$total_products = count($all_products);
$low_stock = array_filter($all_products, fn($p) => $p['stock'] > 0 && $p['stock'] <= 3);
$out_of_stock = array_filter($all_products, fn($p) => $p['stock'] == 0);

// Статистика по категориям
$categories = $categoryModel->getAll();
$category_stats = [];
foreach ($categories as $cat) {
    $cat_products = array_filter($all_products, fn($p) => $p['category_id'] == $cat['id']);
    $category_stats[] = [
        'name' => $cat['name'],
        'count' => count($cat_products),
        'total_stock' => array_sum(array_column($cat_products, 'stock')),
        'total_value' => array_sum(array_map(fn($p) => $p['price'] * $p['stock'], $cat_products))
    ];
}

// Заказы по месяцам (за текущий год)
$orders_by_month = [];
for ($i = 1; $i <= 12; $i++) {
    $month_orders = array_filter($all_orders, function($o) use ($i) {
        return date('n', strtotime($o['created_at'])) == $i &&
               date('Y', strtotime($o['created_at'])) == date('Y');
    });
    $orders_by_month[$i] = [
        'count' => count($month_orders),
        'revenue' => array_sum(array_column($month_orders, 'total_price'))
    ];
}

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4">
    <i class="bi bi-graph-up"></i> Отчёты и аналитика
    <small class="text-muted">за <?= date('Y') ?> год</small>
</h1>

<!-- Основные показатели -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-cart-check display-3 text-success"></i>
                <h2 class="mt-3"><?= $total_orders ?></h2>
                <p class="text-muted mb-0">Всего заказов</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-currency-exchange display-3 text-primary"></i>
                <h4 class="mt-3"><?= formatPrice($total_revenue) ?></h4>
                <p class="text-muted mb-0">Общий доход</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-calculator display-3 text-info"></i>
                <h4 class="mt-3"><?= formatPrice($average_order) ?></h4>
                <p class="text-muted mb-0">Средний чек</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-box-seam display-3 text-warning"></i>
                <h2 class="mt-3"><?= $total_products ?></h2>
                <p class="text-muted mb-0">Товаров</p>
            </div>
        </div>
    </div>
</div>

<!-- График заказов по месяцам -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-bar-chart"></i> Заказы по месяцам (<?= date('Y') ?>)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Месяц</th>
                                <th>Заказов</th>
                                <th>Доход</th>
                                <th>График</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $months = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                                      'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
                            $max_revenue = max(array_column($orders_by_month, 'revenue')) ?: 1;
                            foreach ($orders_by_month as $month => $data):
                                $percent = ($data['revenue'] / $max_revenue) * 100;
                            ?>
                            <tr>
                                <td><strong><?= $months[$month - 1] ?></strong></td>
                                <td><?= $data['count'] ?></td>
                                <td><?= formatPrice($data['revenue']) ?></td>
                                <td>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar"
                                             style="width: <?= $percent ?>%">
                                            <?= round($percent) ?>%
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Статистика по категориям -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-tags"></i> Статистика по категориям
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Категория</th>
                                <th>Товаров</th>
                                <th>На складе</th>
                                <th>Стоимость</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category_stats as $stat): ?>
                            <tr>
                                <td><strong><?= clean($stat['name']) ?></strong></td>
                                <td><?= $stat['count'] ?></td>
                                <td><?= $stat['total_stock'] ?> шт</td>
                                <td><?= formatPrice($stat['total_value']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Статусы заказов -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-pie-chart"></i> Статусы заказов
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Принято</span>
                        <strong><?= count($pending) ?></strong>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-info" style="width: <?= $total_orders > 0 ? (count($pending)/$total_orders)*100 : 0 ?>%">
                            <?= $total_orders > 0 ? round((count($pending)/$total_orders)*100) : 0 ?>%
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>В обработке</span>
                        <strong><?= count($processing) ?></strong>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-warning" style="width: <?= $total_orders > 0 ? (count($processing)/$total_orders)*100 : 0 ?>%">
                            <?= $total_orders > 0 ? round((count($processing)/$total_orders)*100) : 0 ?>%
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Доставлено</span>
                        <strong><?= count($completed) ?></strong>
                    </div>
                    <div class="progress" style="height: 30px;">
                        <div class="progress-bar bg-success" style="width: <?= $total_orders > 0 ? (count($completed)/$total_orders)*100 : 0 ?>%">
                            <?= $total_orders > 0 ? round((count($completed)/$total_orders)*100) : 0 ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Предупреждения о складе -->
<div class="row">
    <div class="col-md-6">
        <div class="card border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-triangle"></i> Товары закончились
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($out_of_stock)): ?>
                    <p class="text-muted text-center">Все товары в наличии</p>
                <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($out_of_stock as $product): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= clean($product['name']) ?>
                        <a href="<?= url('admin/product_edit.php?id=' . $product['id']) ?>"
                           class="btn btn-sm btn-primary">
                            Пополнить
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-exclamation-circle"></i> Товары заканчиваются
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($low_stock)): ?>
                    <p class="text-muted text-center">Все товары в достаточном количестве</p>
                <?php else: ?>
                <ul class="list-group">
                    <?php foreach ($low_stock as $product): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= clean($product['name']) ?>
                        <div>
                            <span class="badge bg-warning text-dark"><?= $product['stock'] ?> шт</span>
                            <a href="<?= url('admin/product_edit.php?id=' . $product['id']) ?>"
                               class="btn btn-sm btn-primary ms-2">
                                Пополнить
                            </a>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Экспорт -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center">
                <h5><i class="bi bi-download"></i> Экспорт данных</h5>
                <p class="text-muted">Скачайте отчёты в удобном формате</p>
                <div class="btn-group">
                    <a href="<?= url('admin/export.php?type=orders&format=csv') ?>"
                       class="btn btn-success">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Заказы (CSV)
                    </a>
                    <a href="<?= url('admin/export.php?type=products&format=csv') ?>"
                       class="btn btn-primary">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Товары (CSV)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
