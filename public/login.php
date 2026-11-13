<?php
require_once __DIR__ . '/../app/Models/Customer.php';

$page_title = 'Кіру - Rolex';

session_start();

// Если уже авторизован, перенаправить
if (isset($_SESSION['customer_id'])) {
    header('Location: /rolex/public/account.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email және құпия сөзді енгізіңіз';
    } else {
        $customerModel = new Customer();
        $result = $customerModel->login($email, $password);

        if ($result['success']) {
            // Сохранить данные в сессию
            $_SESSION['customer_id'] = $result['customer']['id'];
            $_SESSION['customer_name'] = $result['customer']['name'];
            $_SESSION['customer_email'] = $result['customer']['email'];

            // Перенаправить на личный кабинет
            header('Location: /rolex/public/account.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

require_once __DIR__ . '/../app/Views/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0"><i class="bi bi-box-arrow-in-right"></i> Кіру</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                   placeholder="your@email.com" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Құпия сөз *</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle"></i> Кіру
                        </button>
                    </form>

                    <hr>

                    <div class="text-center">
                        <p class="mb-0">Аккаунт жоқ па?</p>
                        <a href="<?= url('public/register.php') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-person-plus"></i> Тіркелу
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/footer.php'; ?>
