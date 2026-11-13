<?php
require_once __DIR__ . '/../app/Models/Order.php';
require_once __DIR__ . '/../app/Models/Customer.php';

$page_title = 'Менің кабинетім - Rolex';

session_start();

// Проверка авторизации
if (!isset($_SESSION['customer_id'])) {
    header('Location: /rolex/public/login.php');
    exit;
}

$customer_id = $_SESSION['customer_id'];
$customerModel = new Customer();
$customer = $customerModel->getById($customer_id);

// Получить заказы клиента по email
$orderModel = new Order();
$all_orders = $orderModel->getAll();

// Фильтр заказов по email клиента
$orders = array_filter($all_orders, function($order) use ($customer) {
    return $order['email'] === $customer['email'];
});

require_once __DIR__ . '/../app/Views/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4"><i class="bi bi-person-circle"></i> Жеке кабинет</h1>

    <!-- Информация о пользователе -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-person"></i> Менің деректерім</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Аты-жөні:</strong> <?= clean($customer['name']) ?></p>
                    <p><strong>Email:</strong> <?= clean($customer['email']) ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Телефон:</strong> <?= clean($customer['phone']) ?></p>
                    <p><strong>Тіркелген күні:</strong> <?= date('d.m.Y', strtotime($customer['created_at'])) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h3 class="display-4"><?= count($orders) ?></h3>
                    <p class="mb-0">Барлық тапсырыстар</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-center bg-warning text-white">
                <div class="card-body">
                    <h3 class="display-4">
                        <?= count(array_filter($orders, fn($o) => $o['status'] === 'дайындалуда')) ?>
                    </h3>
                    <p class="mb-0">Дайындалуда</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h3 class="display-4">
                        <?= count(array_filter($orders, fn($o) => $o['status'] === 'жеткізілді')) ?>
                    </h3>
                    <p class="mb-0">Жеткізілді</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h3 class="display-4">
                        <?= formatPrice(array_sum(array_column($orders, 'total_price'))) ?>
                    </h3>
                    <p class="mb-0">Жалпы сома</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Список заказов -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="bi bi-cart-check"></i> Менің тапсырыстарым</h5>
        </div>
        <div class="card-body">
            <?php if (empty($orders)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle fs-1"></i>
                    <h4 class="mt-3">Тапсырыстар жоқ</h4>
                    <p>Сіз әлі тапсырыс бермедіңіз</p>
                    <a href="<?= url('public/products.php') ?>" class="btn btn-primary mt-2">
                        <i class="bi bi-shop"></i> Каталогқа өту
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>№</th>
                                <th>Күні</th>
                                <th>Мекен-жай</th>
                                <th>Сомасы</th>
                                <th>Статус</th>
                                <th>Әрекет</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong>#<?= $order['id'] ?></strong></td>
                                <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                <td><?= clean(substr($order['address'], 0, 30)) ?>...</td>
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
                                <td>
                                    <button class="btn btn-sm btn-primary view-order"
                                            data-id="<?= $order['id'] ?>">
                                        <i class="bi bi-eye"></i> Көру
                                    </button>
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

<!-- Модальное окно для деталей заказа -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Тапсырыс деректері</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetails">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Просмотр деталей заказа
document.querySelectorAll('.view-order').forEach(btn => {
    btn.addEventListener('click', function() {
        const orderId = this.getAttribute('data-id');
        const modal = new bootstrap.Modal(document.getElementById('orderModal'));
        const detailsDiv = document.getElementById('orderDetails');

        modal.show();
        detailsDiv.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        fetch(`<?= url('public/ajax_order_details.php') ?>?id=${orderId}`)
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    detailsDiv.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    return;
                }

                let html = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p><strong>Тапсырыс №:</strong> #${data.order.id}</p>
                            <p><strong>Күні:</strong> ${new Date(data.order.created_at).toLocaleString('kk-KZ')}</p>
                            <p><strong>Клиент:</strong> ${data.order.customer_name}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Телефон:</strong> ${data.order.phone}</p>
                            <p><strong>Мекен-жай:</strong> ${data.order.address}</p>
                            <p><strong>Статус:</strong> <span class="badge bg-primary">${data.order.status}</span></p>
                        </div>
                    </div>

                    <h6 class="mt-4">Тапсырыс құрамы:</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Өнім</th>
                                <th>Баға</th>
                                <th>Саны</th>
                                <th>Сома</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                data.items.forEach(item => {
                    html += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${formatPrice(item.price)}</td>
                            <td>${item.quantity}</td>
                            <td><strong>${formatPrice(item.price * item.quantity)}</strong></td>
                        </tr>
                    `;
                });

                html += `
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">БАРЛЫҒЫ:</th>
                                <th>${formatPrice(data.order.total_price)}</th>
                            </tr>
                        </tfoot>
                    </table>
                `;

                detailsDiv.innerHTML = html;
            })
            .catch(error => {
                detailsDiv.innerHTML = `<div class="alert alert-danger">Қате: ${error.message}</div>`;
            });
    });
});

function formatPrice(price) {
    return new Intl.NumberFormat('kk-KZ').format(price) + ' ₸';
}
</script>

<?php require_once __DIR__ . '/../app/Views/footer.php'; ?>
