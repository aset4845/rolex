<?php
require_once __DIR__ . '/../app/Models/Customer.php';

$page_title = 'Тіркелу - Rolex';

session_start();

// Если уже авторизован, перенаправить
if (isset($_SESSION['customer_id'])) {
    header('Location: /rolex/public/account.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Валидация
    if (empty($name) || empty($email) || empty($phone) || empty($password)) {
        $error = 'Барлық өрістерді толтырыңыз';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email дұрыс емес';
    } elseif (strlen($password) < 6) {
        $error = 'Құпия сөз кемінде 6 таңбадан тұруы керек';
    } elseif ($password !== $password_confirm) {
        $error = 'Құпия сөздер сәйкес келмейді';
    } else {
        $customerModel = new Customer();
        $result = $customerModel->register($name, $email, $phone, $password);

        if ($result['success']) {
            $success = 'Тіркелу сәтті! Енді кіре аласыз.';
            // Автоматически войти
            $_SESSION['customer_id'] = $result['customer_id'];
            $_SESSION['customer_name'] = $name;
            $_SESSION['customer_email'] = $email;

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
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person-plus"></i> Тіркелу</h4>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <?= $success ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Аты-жөніңіз *</label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-control"
                                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            <small class="text-muted">Кіру үшін қолданылады</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Телефон *</label>
                            <input type="tel" name="phone" class="form-control"
                                   placeholder="+7 (777) 123-45-67"
                                   value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Құпия сөз *</label>
                            <input type="password" name="password" class="form-control"
                                   minlength="6" required>
                            <small class="text-muted">Кемінде 6 таңба</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Құпия сөзді растау *</label>
                            <input type="password" name="password_confirm" class="form-control"
                                   minlength="6" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i> Тіркелу
                        </button>
                    </form>

                    <hr>

                    <div class="text-center">
                        <p class="mb-0">Аккаунт бар ма?</p>
                        <a href="<?= url('public/login.php') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-box-arrow-in-right"></i> Кіру
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/footer.php'; ?>
