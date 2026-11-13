<?php
require_once __DIR__ . '/../app/Controllers/CartController.php';

$page_title = 'Себет - Rolex';

session_start();

// Проверка авторизации
if (!isset($_SESSION['customer_id'])) {
    header('Location: /rolex/public/login.php');
    exit;
}

// Себеттен өшіру
if (isset($_GET['remove'])) {
    CartController::remove($_GET['remove']);
    header('Location: ' . url('public/cart.php'));
    exit;
}

// Санын жаңарту
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        CartController::updateQuantity($product_id, (int)$quantity);
    }
    header('Location: ' . url('public/cart.php'));
    exit;
}

$cart = CartController::get();
$total = getCartTotal();

require_once __DIR__ . '/../app/Views/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4"><i class="bi bi-cart3"></i> Себет</h1>

    <?php if (empty($cart)): ?>
        <div class="alert alert-info text-center">
            <i class="bi bi-info-circle display-1 d-block mb-3"></i>
            <h4>Себет бос</h4>
            <p>Сіз әлі өнімдерді қоспадыңыз</p>
            <a href="<?= url('public/products.php') ?>" class="btn btn-primary">
                <i class="bi bi-shop"></i> Каталогқа өту
            </a>
        </div>
    <?php else: ?>
        <form method="POST">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Өнім</th>
                            <th>Бағасы</th>
                            <th width="150">Саны</th>
                            <th>Жалпы</th>
                            <th width="100"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $product_id => $item): ?>
                        <tr>
                            <td>
                                <strong><?= clean($item['name']) ?></strong>
                            </td>
                            <td><?= formatPrice($item['price']) ?></td>
                            <td>
                                <input type="number" name="quantity[<?= $product_id ?>]"
                                       class="form-control" value="<?= $item['quantity'] ?>"
                                       min="1" max="99">
                            </td>
                            <td>
                                <strong><?= formatPrice($item['price'] * $item['quantity']) ?></strong>
                            </td>
                            <td>
                                <a href="?remove=<?= $product_id ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Өнімді себеттен өшіруге сенімдісіз бе?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="3" class="text-end"><strong>ЖАЛПЫ:</strong></td>
                            <td colspan="2">
                                <strong class="text-primary fs-4"><?= formatPrice($total) ?></strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <a href="<?= url('public/products.php') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Сатып алуды жалғастыру
                    </a>
                </div>
                <div class="col-md-6 text-end">
                    <button type="submit" name="update_cart" class="btn btn-warning me-2">
                        <i class="bi bi-arrow-repeat"></i> Жаңарту
                    </button>
                    <a href="<?= url('public/checkout.php') ?>" class="btn btn-success btn-lg">
                        <i class="bi bi-credit-card"></i> Тапсырыс рәсімдеу
                    </a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../app/Views/footer.php'; ?>
