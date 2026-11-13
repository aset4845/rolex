<?php
require_once __DIR__ . '/../app/Models/ActivityLog.php';

$page_title = 'История изменений - Админ панель';
$logModel = new ActivityLog();

// Фильтры
$filter_action = $_GET['action'] ?? '';
$filter_user = $_GET['user'] ?? '';
$limit = (int)($_GET['limit'] ?? 50);

// Получить логи
$logs = $logModel->getAll($limit);

// Фильтрация
if ($filter_action) {
    $logs = array_filter($logs, fn($log) => $log['action'] === $filter_action);
}

if ($filter_user) {
    $logs = array_filter($logs, fn($log) => stripos($log['username'], $filter_user) !== false);
}

// Статистика
$stats = $logModel->getStats();

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4">
    <i class="bi bi-clock-history"></i> История изменений
    <small class="text-muted">Логи активности</small>
</h1>

<!-- Статистика -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-journal-text display-4 text-primary"></i>
                <h3 class="mt-2"><?= $stats['total_logs'] ?></h3>
                <p class="text-muted mb-0">Всего записей</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-people display-4 text-success"></i>
                <h3 class="mt-2"><?= $stats['unique_users'] ?></h3>
                <p class="text-muted mb-0">Пользователей</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-plus-circle display-4 text-info"></i>
                <h3 class="mt-2"><?= $stats['create_count'] ?></h3>
                <p class="text-muted mb-0">Создано</p>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="bi bi-trash display-4 text-danger"></i>
                <h3 class="mt-2"><?= $stats['delete_count'] ?></h3>
                <p class="text-muted mb-0">Удалено</p>
            </div>
        </div>
    </div>
</div>

<!-- Фильтры -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Фильтры</h5>
    </div>
    <div class="card-body">
        <form method="GET">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Действие</label>
                    <select name="action" class="form-select">
                        <option value="">Все действия</option>
                        <option value="login" <?= $filter_action === 'login' ? 'selected' : '' ?>>Вход</option>
                        <option value="create" <?= $filter_action === 'create' ? 'selected' : '' ?>>Создание</option>
                        <option value="update" <?= $filter_action === 'update' ? 'selected' : '' ?>>Изменение</option>
                        <option value="delete" <?= $filter_action === 'delete' ? 'selected' : '' ?>>Удаление</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Пользователь</label>
                    <input type="text" name="user" class="form-control"
                           value="<?= clean($filter_user) ?>"
                           placeholder="Имя пользователя">
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">Количество</label>
                    <select name="limit" class="form-select">
                        <option value="50" <?= $limit === 50 ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $limit === 100 ? 'selected' : '' ?>>100</option>
                        <option value="200" <?= $limit === 200 ? 'selected' : '' ?>>200</option>
                        <option value="500" <?= $limit === 500 ? 'selected' : '' ?>>500</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Применить
                        </button>
                    </div>
                </div>
            </div>

            <a href="<?= url('admin/activity_logs.php') ?>" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle"></i> Сбросить
            </a>
        </form>
    </div>
</div>

<!-- Логи -->
<div class="card">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="bi bi-list"></i> Журнал активности
        </h5>
        <span class="badge bg-light text-dark"><?= count($logs) ?> записей</span>
    </div>
    <div class="card-body p-0">
        <?php if (empty($logs)): ?>
            <p class="text-muted text-center p-4">Записей нет</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Дата</th>
                        <th>Пользователь</th>
                        <th>Действие</th>
                        <th>Тип</th>
                        <th>ID</th>
                        <th>Описание</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>
                            <small><?= date('d.m.Y H:i:s', strtotime($log['created_at'])) ?></small>
                        </td>
                        <td>
                            <i class="bi bi-person-circle"></i>
                            <strong><?= clean($log['username']) ?></strong>
                        </td>
                        <td>
                            <?php
                            $action_badges = [
                                'login' => ['class' => 'info', 'icon' => 'box-arrow-in-right', 'text' => 'Вход'],
                                'logout' => ['class' => 'secondary', 'icon' => 'box-arrow-right', 'text' => 'Выход'],
                                'create' => ['class' => 'success', 'icon' => 'plus-circle', 'text' => 'Создание'],
                                'update' => ['class' => 'warning', 'icon' => 'pencil', 'text' => 'Изменение'],
                                'delete' => ['class' => 'danger', 'icon' => 'trash', 'text' => 'Удаление'],
                            ];
                            $badge = $action_badges[$log['action']] ?? ['class' => 'secondary', 'icon' => 'circle', 'text' => $log['action']];
                            ?>
                            <span class="badge bg-<?= $badge['class'] ?>">
                                <i class="bi bi-<?= $badge['icon'] ?>"></i>
                                <?= $badge['text'] ?>
                            </span>
                        </td>
                        <td><span class="badge bg-light text-dark"><?= clean($log['entity_type']) ?></span></td>
                        <td><?= $log['entity_id'] ? '#' . $log['entity_id'] : '-' ?></td>
                        <td>
                            <small><?= clean($log['description']) ?></small>
                        </td>
                        <td>
                            <small class="text-muted"><?= clean($log['ip_address']) ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Управление логами -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0"><i class="bi bi-tools"></i> Управление логами</h6>
            </div>
            <div class="card-body">
                <p class="mb-3">
                    <strong>Внимание!</strong> Логи занимают место в БД. Рекомендуется периодически очищать старые записи.
                </p>
                <div class="btn-group">
                    <button class="btn btn-outline-warning" disabled>
                        <i class="bi bi-trash"></i> Удалить старше 90 дней
                    </button>
                    <button class="btn btn-outline-danger" disabled>
                        <i class="bi bi-x-circle"></i> Очистить все
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
