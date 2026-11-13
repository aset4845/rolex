<?php
require_once __DIR__ . '/../app/Config/helpers.php';
require_once __DIR__ . '/../app/Models/User.php';

// Егер админ авторизацияланса, dashboard-қа жіберу
if (isAdmin()) {
    header('Location: ' . url('admin/dashboard.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        $userModel = new User();
        $user = $userModel->login($username, $password);

        if ($user && $user['role'] === 'admin') {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('Location: ' . url('admin/dashboard.php'));
            exit;
        } else {
            $error = 'Логин немесе пароль қате немесе админ емессіз!';
        }
    } else {
        $error = 'Барлық өрістерді толтырыңыз!';
    }
}
?>
<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ авторизациясы - Rolex</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock display-1 text-primary"></i>
                    <h3 class="mt-3">Админ панель</h3>
                    <p class="text-muted">Rolex ЖШС</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Логин</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                            <input type="text" name="username" class="form-control"
                                   placeholder="Логиніңізді енгізіңіз" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Пароль</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control"
                                   placeholder="Парольді енгізіңіз" required>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right"></i> Кіру
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <a href="<?= url('public/index.php') ?>" class="text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Басты бетке оралу
                    </a>
                </div>

                <div class="alert alert-info mt-3 small">
                    <strong>Тесттік деректер:</strong><br>
                    Логин: <code>admin</code><br>
                    Пароль: <code>admin123</code>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
