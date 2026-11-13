<?php
require_once __DIR__ . '/../app/Models/User.php';

$page_title = 'Пользователи - Админ панель';
$userModel = new User();

$success = '';
$errors = [];

// Добавление нового пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = $_POST['role'] ?? 'admin';

    if (empty($username)) $errors[] = "Введите логин";
    if (empty($password)) $errors[] = "Введите пароль";
    if (strlen($password) < 6) $errors[] = "Пароль минимум 6 символов";

    if (empty($errors)) {
        if ($userModel->create($username, $password, $role)) {
            $success = "Пользователь успешно добавлен!";
        } else {
            $errors[] = "Ошибка при добавлении. Возможно логин уже занят.";
        }
    }
}

// Удаление пользователя
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Проверка - нельзя удалить себя
    if ($delete_id == $_SESSION['user_id']) {
        $errors[] = "Нельзя удалить свой аккаунт!";
    } else {
        $query = "DELETE FROM users WHERE id = :id";
        $database = new Database();
        $conn = $database->connect();
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $delete_id);
        if ($stmt->execute()) {
            $success = "Пользователь удален!";
        }
    }
}

$users = $userModel->getAll();

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4"><i class="bi bi-people"></i> Управление пользователями</h1>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?= $success ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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
    <!-- Форма добавления -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-person-plus"></i> Добавить пользователя
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Логин *</label>
                        <input type="text" name="username" class="form-control"
                               placeholder="Введите логин" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Пароль *</label>
                        <input type="password" name="password" class="form-control"
                               placeholder="Минимум 6 символов" required minlength="6">
                        <small class="text-muted">Минимум 6 символов</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Роль *</label>
                        <select name="role" class="form-select" required>
                            <option value="admin">Администратор</option>
                            <option value="user">Пользователь</option>
                        </select>
                    </div>

                    <div class="d-grid">
                        <button type="submit" name="add" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Добавить
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Информация -->
        <div class="card mt-3">
            <div class="card-body">
                <h6><i class="bi bi-info-circle"></i> Информация</h6>
                <p class="small mb-0">
                    <strong>Роли:</strong><br>
                    <span class="badge bg-danger">admin</span> - полный доступ к админ-панели<br>
                    <span class="badge bg-secondary">user</span> - ограниченный доступ
                </p>
            </div>
        </div>
    </div>

    <!-- Список пользователей -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-list"></i> Все пользователи
                </h5>
                <span class="badge bg-light text-dark"><?= count($users) ?> чел.</span>
            </div>
            <div class="card-body">
                <?php if (empty($users)): ?>
                    <p class="text-muted text-center">Пользователей нет</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Логин</th>
                                <th>Роль</th>
                                <th>Дата создания</th>
                                <th width="100">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td><strong><?= $user['id'] ?></strong></td>
                                <td>
                                    <i class="bi bi-person-circle"></i>
                                    <?= clean($user['username']) ?>
                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                        <span class="badge bg-info">Вы</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($user['role'] === 'admin'): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-shield-check"></i> Админ
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-person"></i> Пользователь
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete=<?= $user['id'] ?>"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Удалить пользователя <?= clean($user['username']) ?>?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-secondary" disabled>
                                            <i class="bi bi-lock"></i>
                                        </button>
                                    <?php endif; ?>
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
</div>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
