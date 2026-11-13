<?php
require_once __DIR__ . '/../app/Models/Category.php';

$page_title = 'Категории - Админка';
$categoryModel = new Category();

$success = '';
$errors = [];

// Добавление категории
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        if ($categoryModel->create($name)) {
            $success = 'Категория успешно добавлена!';
        }
    } else {
        $errors[] = 'Введите название категории.';
    }
}

// Обновление категории
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = $_POST['id'];
    $name = trim($_POST['name'] ?? '');
    if ($name !== '') {
        if ($categoryModel->update($id, $name)) {
            $success = 'Категория обновлена!';
        }
    } else {
        $errors[] = 'Введите название категории.';
    }
}

// Удаление категории
if (isset($_GET['delete'])) {
    $categoryModel->delete($_GET['delete']);
    header('Location: ' . url('admin/categories.php'));
    exit;
}

$categories = $categoryModel->getAll();

// Категория для редактирования
$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_category = $categoryModel->getById($_GET['edit']);
}

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4"><i class="bi bi-tags"></i> Категории</h1>

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
    <!-- Форма -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <?= $edit_category ? 'Редактировать' : 'Новая категория' ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($edit_category): ?>
                        <input type="hidden" name="id" value="<?= $edit_category['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label">Название *</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= clean($edit_category['name'] ?? '') ?>"
                               placeholder="Например: Мужские часы" required>
                    </div>

                    <div class="d-grid gap-2">
                        <?php if ($edit_category): ?>
                            <button type="submit" name="edit" class="btn btn-warning">
                                <i class="bi bi-save"></i> Сохранить
                            </button>
                            <a href="<?= url('admin/categories.php') ?>" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Отмена
                            </a>
                        <?php else: ?>
                            <button type="submit" name="add" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Добавить
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Таблица -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Список категорий</h5>
            </div>
            <div class="card-body">
                <?php if (empty($categories)): ?>
                    <p class="text-muted text-center">Категорий пока нет.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="80">ID</th>
                                <th>Название</th>
                                <th>Создана</th>
                                <th width="150">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><strong><?= $cat['id'] ?></strong></td>
                                <td><?= clean($cat['name']) ?></td>
                                <td><?= date('d.m.Y', strtotime($cat['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?edit=<?= $cat['id'] ?>"
                                           class="btn btn-warning" title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="?delete=<?= $cat['id'] ?>"
                                           class="btn btn-danger"
                                           onclick="return confirm('Удалить категорию и связанные товары?')"
                                           title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
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
