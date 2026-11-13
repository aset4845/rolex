<?php
require_once __DIR__ . '/../app/Controllers/CartController.php';
require_once __DIR__ . '/../app/Models/Order.php';
require_once __DIR__ . '/../app/Models/Customer.php';

$page_title = 'Тапсырыс рәсімдеу - Rolex';

session_start();

// Проверка авторизации
if (!isset($_SESSION['customer_id'])) {
    header('Location: /rolex/public/login.php');
    exit;
}

$cart = CartController::get();
$total = getCartTotal();

// Егер себет бос болса
if (empty($cart)) {
    header('Location: ' . url('public/cart.php'));
    exit;
}

// Получить данные клиента
$customerModel = new Customer();
$customer = $customerModel->getById($_SESSION['customer_id']);

$errors = [];
$success = false;

// Тапсырыс жасау
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    // Валидация
    if (empty($customer_name)) {
        $errors[] = "Аты-жөніңізді енгізіңіз";
    }
    if (empty($phone)) {
        $errors[] = "Телефон нөміріңізді енгізіңіз";
    }
    if (empty($address)) {
        $errors[] = "Мекен-жайыңызды енгізіңіз";
    }

    if (empty($errors)) {
        $orderModel = new Order();
        $orderModel->customer_name = $customer_name;
        $orderModel->phone = $phone;
        $orderModel->address = $address;
        $orderModel->total_price = $total;

        $order_id = $orderModel->create($cart);

        if ($order_id) {
            CartController::clear();
            $success = true;
        } else {
            $errors[] = "Тапсырыс жасау кезінде қате шықты. Қайта көріңіз.";
        }
    }
}

require_once __DIR__ . '/../app/Views/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4"><i class="bi bi-credit-card"></i> Тапсырыс рәсімдеу</h1>

    <?php if ($success): ?>
        <div class="alert alert-success text-center">
            <i class="bi bi-check-circle display-1 d-block mb-3 text-success"></i>
            <h3>Тапсырыс сәтті рәсімделді!</h3>
            <p>Тапсырыс нөмірі: <strong>#<?= $order_id ?></strong></p>
            <p>Біздің менеджер жақын арада сізге хабарласады.</p>
            <hr>
            <a href="<?= url('public/index.php') ?>" class="btn btn-primary">
                <i class="bi bi-house"></i> Басты бетке оралу
            </a>
        </div>
    <?php else: ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Тапсырыс формасы -->
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-person"></i> Жеткізу ақпараты</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Аты-жөніңіз *</label>
                                <input type="text" name="customer_name" class="form-control"
                                       value="<?= clean($_POST['customer_name'] ?? $customer['name'] ?? '') ?>"
                                       placeholder="Мысалы: Нұрлан Әбдіров" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Телефон нөміріңіз *</label>
                                <input type="tel" name="phone" class="form-control"
                                       value="<?= clean($_POST['phone'] ?? $customer['phone'] ?? '') ?>"
                                       placeholder="+7 (___) ___-__-__" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control"
                                       value="<?= clean($customer['email'] ?? '') ?>" disabled>
                                <small class="text-muted">Email берілген аккаунттан</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Жеткізу мекен-жайы *</label>
                                <textarea name="address" class="form-control" rows="3"
                                          placeholder="Қала, көше, үй нөмірі" required><?= clean($_POST['address'] ?? '') ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle"></i> Тапсырысты растау
                                </button>
                                <a href="<?= url('public/cart.php') ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Себетке оралу
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Тапсырыс деректері -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="bi bi-basket"></i> Сіздің тапсырысыңыз</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <?php foreach ($cart as $item): ?>
                            <tr>
                                <td><?= clean($item['name']) ?></td>
                                <td class="text-end">×<?= $item['quantity'] ?></td>
                                <td class="text-end"><?= formatPrice($item['price'] * $item['quantity']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-light">
                                <td colspan="2"><strong>ЖАЛПЫ:</strong></td>
                                <td class="text-end">
                                    <strong class="text-primary"><?= formatPrice($total) ?></strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card mt-3 bg-light">
                    <div class="card-body">
                        <h6><i class="bi bi-info-circle"></i> Ақпарат</h6>
                        <ul class="small mb-0">
                            <li>Жеткізу: 1-3 жұмыс күні</li>
                            <li>Төлем: Қолма-қол</li>
                            <li>Кепілдік: 2 жыл</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../app/Views/footer.php'; ?>
