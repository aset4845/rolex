<?php
require_once __DIR__ . '/../app/Config/Database.php';

$page_title = 'Настройки - Админ панель';

// Файл для хранения настроек
$settings_file = __DIR__ . '/../app/Config/settings.json';

// Загрузка настроек
if (file_exists($settings_file)) {
    $settings = json_decode(file_get_contents($settings_file), true);
} else {
    $settings = [
        'site_name' => 'Rolex ЖШС',
        'site_phone' => '+7 (727) 123-45-67',
        'site_email' => 'info@rolex.kz',
        'site_address' => 'Алматы қ., Абай даңғ., 150',
        'free_delivery_from' => 500000,
        'currency' => '₸',
        'products_per_page' => 12,
        'enable_registration' => false
    ];
}

$success = '';
$errors = [];

// Сохранение настроек
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_settings = [
        'site_name' => trim($_POST['site_name'] ?? 'Rolex ЖШС'),
        'site_phone' => trim($_POST['site_phone'] ?? ''),
        'site_email' => trim($_POST['site_email'] ?? ''),
        'site_address' => trim($_POST['site_address'] ?? ''),
        'free_delivery_from' => (int)($_POST['free_delivery_from'] ?? 0),
        'currency' => $_POST['currency'] ?? '₸',
        'products_per_page' => (int)($_POST['products_per_page'] ?? 12),
        'enable_registration' => isset($_POST['enable_registration'])
    ];

    if (file_put_contents($settings_file, json_encode($new_settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
        $settings = $new_settings;
        $success = "Настройки успешно сохранены!";
    } else {
        $errors[] = "Ошибка при сохранении настроек";
    }
}

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4">
    <i class="bi bi-gear"></i> Настройки сайта
</h1>

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
    <div class="col-md-8">
        <form method="POST">
            <!-- Основные настройки -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Основная информация</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Название сайта *</label>
                        <input type="text" name="site_name" class="form-control"
                               value="<?= clean($settings['site_name']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Телефон *</label>
                        <input type="text" name="site_phone" class="form-control"
                               value="<?= clean($settings['site_phone']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="site_email" class="form-control"
                               value="<?= clean($settings['site_email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Адрес *</label>
                        <textarea name="site_address" class="form-control" rows="2" required><?= clean($settings['site_address']) ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Настройки магазина -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-shop"></i> Настройки магазина</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Бесплатная доставка от (₸)</label>
                        <input type="number" name="free_delivery_from" class="form-control"
                               value="<?= $settings['free_delivery_from'] ?>" min="0">
                        <small class="text-muted">0 = бесплатная доставка отключена</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Валюта</label>
                        <select name="currency" class="form-select">
                            <option value="₸" <?= $settings['currency'] === '₸' ? 'selected' : '' ?>>₸ (Тенге)</option>
                            <option value="₽" <?= $settings['currency'] === '₽' ? 'selected' : '' ?>>₽ (Рубль)</option>
                            <option value="$" <?= $settings['currency'] === '$' ? 'selected' : '' ?>>$ (Доллар)</option>
                            <option value="€" <?= $settings['currency'] === '€' ? 'selected' : '' ?>>€ (Евро)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Товаров на странице</label>
                        <input type="number" name="products_per_page" class="form-control"
                               value="<?= $settings['products_per_page'] ?>" min="4" max="100">
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="enable_registration"
                               id="enable_registration" <?= $settings['enable_registration'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="enable_registration">
                            Разрешить регистрацию пользователей
                        </label>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-save"></i> Сохранить настройки
                </button>
            </div>
        </form>
    </div>

    <!-- Информация -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Информация</h6>
            </div>
            <div class="card-body">
                <p class="small mb-2">
                    <strong>Версия PHP:</strong> <?= phpversion() ?>
                </p>
                <p class="small mb-2">
                    <strong>Сервер:</strong> <?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?>
                </p>
                <p class="small mb-2">
                    <strong>База данных:</strong> MySQL
                </p>
                <p class="small mb-0">
                    <strong>Кодировка:</strong> UTF-8
                </p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-database"></i> Резервная копия</h6>
            </div>
            <div class="card-body">
                <p class="small">
                    Регулярно создавайте резервные копии базы данных
                </p>
                <a href="<?= url('admin/backup.php') ?>" class="btn btn-warning btn-sm w-100">
                    <i class="bi bi-download"></i> Создать backup
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="bi bi-trash"></i> Опасная зона</h6>
            </div>
            <div class="card-body">
                <p class="small text-danger">
                    <strong>Внимание!</strong> Эти действия необратимы
                </p>
                <button class="btn btn-danger btn-sm w-100" disabled>
                    <i class="bi bi-exclamation-triangle"></i> Очистить БД
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
