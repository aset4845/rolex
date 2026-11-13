<?php
require_once __DIR__ . '/../app/Config/helpers.php';
require_once __DIR__ . '/../app/Config/Database.php';

requireAdmin();

$page_title = 'Резервная копия БД - Админ панель';

$success = '';
$errors = [];
$backup_file = '';

// Создание бэкапа
if (isset($_GET['create'])) {
    try {
        $database = new Database();
        $conn = $database->connect();

        // Параметры подключения
        $db_host = 'localhost';
        $db_name = 'rolex_db';
        $db_user = 'root';
        $db_pass = '';

        // Папка для бэкапов
        $backup_dir = __DIR__ . '/../backups/';
        if (!file_exists($backup_dir)) {
            mkdir($backup_dir, 0777, true);
        }

        // Имя файла
        $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backup_path = $backup_dir . $backup_file;

        // Получить все таблицы
        $tables_query = $conn->query("SHOW TABLES");
        $tables = $tables_query->fetchAll(PDO::FETCH_COLUMN);

        $output = "-- Резервная копия базы данных: {$db_name}\n";
        $output .= "-- Дата создания: " . date('Y-m-d H:i:s') . "\n";
        $output .= "-- Создано через админ-панель Rolex\n\n";
        $output .= "SET NAMES utf8mb4;\n";
        $output .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        // Для каждой таблицы
        foreach ($tables as $table) {
            // Структура таблицы
            $create_table = $conn->query("SHOW CREATE TABLE `{$table}`")->fetch();
            $output .= "-- Структура таблицы `{$table}`\n";
            $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $output .= $create_table[1] . ";\n\n";

            // Данные таблицы
            $rows = $conn->query("SELECT * FROM `{$table}`");
            $row_count = $rows->rowCount();

            if ($row_count > 0) {
                $output .= "-- Данные таблицы `{$table}` ({$row_count} записей)\n";

                while ($row = $rows->fetch(PDO::FETCH_ASSOC)) {
                    $values = array_map(function($val) use ($conn) {
                        return $val === null ? 'NULL' : $conn->quote($val);
                    }, array_values($row));

                    $columns = '`' . implode('`, `', array_keys($row)) . '`';
                    $output .= "INSERT INTO `{$table}` ({$columns}) VALUES (" . implode(', ', $values) . ");\n";
                }

                $output .= "\n";
            }
        }

        $output .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        // Сохранить в файл
        if (file_put_contents($backup_path, $output)) {
            $success = "Резервная копия успешно создана!";

            // Логирование
            if (class_exists('ActivityLog')) {
                $log = new ActivityLog();
                $log->log('backup', 'database', null, "Создан бэкап: {$backup_file}");
            }
        } else {
            $errors[] = "Ошибка при сохранении файла";
        }

    } catch (Exception $e) {
        $errors[] = "Ошибка: " . $e->getMessage();
    }
}

// Скачивание бэкапа
if (isset($_GET['download'])) {
    $file = basename($_GET['download']);
    $filepath = __DIR__ . '/../backups/' . $file;

    if (file_exists($filepath)) {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }
}

// Удаление бэкапа
if (isset($_GET['delete'])) {
    $file = basename($_GET['delete']);
    $filepath = __DIR__ . '/../backups/' . $file;

    if (file_exists($filepath) && unlink($filepath)) {
        $success = "Файл удалён";
    }
}

// Список существующих бэкапов
$backup_dir = __DIR__ . '/../backups/';
$backups = [];
if (file_exists($backup_dir)) {
    $files = scandir($backup_dir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
            $backups[] = [
                'name' => $file,
                'size' => filesize($backup_dir . $file),
                'date' => filemtime($backup_dir . $file)
            ];
        }
    }
    // Сортировать по дате (новые первыми)
    usort($backups, fn($a, $b) => $b['date'] - $a['date']);
}

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4">
    <i class="bi bi-database"></i> Резервное копирование
</h1>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?= $success ?>
        <?php if ($backup_file): ?>
            <br><strong>Файл:</strong> <?= $backup_file ?>
            <br><a href="?download=<?= $backup_file ?>" class="alert-link">
                <i class="bi bi-download"></i> Скачать
            </a>
        <?php endif; ?>
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

<!-- Создание бэкапа -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle"></i> Создать резервную копию
                </h5>
            </div>
            <div class="card-body">
                <p>
                    Резервная копия будет содержать все данные из базы данных <code>rolex_db</code>:
                </p>
                <ul>
                    <li>Структуру всех таблиц</li>
                    <li>Все данные (товары, заказы, категории, пользователи)</li>
                    <li>SQL команды для восстановления</li>
                </ul>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Рекомендация:</strong> Создавайте резервные копии регулярно, особенно перед важными изменениями.
                </div>

                <a href="?create=1" class="btn btn-primary btn-lg"
                   onclick="return confirm('Создать резервную копию базы данных?')">
                    <i class="bi bi-database"></i> Создать бэкап сейчас
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Список существующих бэкапов -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-folder"></i> Существующие резервные копии
                </h5>
                <span class="badge bg-light text-dark"><?= count($backups) ?> файлов</span>
            </div>
            <div class="card-body">
                <?php if (empty($backups)): ?>
                    <p class="text-muted text-center">Резервных копий пока нет</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Файл</th>
                                <th>Размер</th>
                                <th>Дата создания</th>
                                <th width="200">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($backups as $backup): ?>
                            <tr>
                                <td>
                                    <i class="bi bi-file-earmark-zip"></i>
                                    <code><?= clean($backup['name']) ?></code>
                                </td>
                                <td>
                                    <?php
                                    $size = $backup['size'];
                                    if ($size < 1024) {
                                        echo $size . ' B';
                                    } elseif ($size < 1024 * 1024) {
                                        echo round($size / 1024, 2) . ' KB';
                                    } else {
                                        echo round($size / (1024 * 1024), 2) . ' MB';
                                    }
                                    ?>
                                </td>
                                <td><?= date('d.m.Y H:i:s', $backup['date']) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="?download=<?= $backup['name'] ?>"
                                           class="btn btn-success" title="Скачать">
                                            <i class="bi bi-download"></i> Скачать
                                        </a>
                                        <a href="?delete=<?= $backup['name'] ?>"
                                           class="btn btn-danger"
                                           onclick="return confirm('Удалить этот файл?')"
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

<!-- Инструкции -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="bi bi-question-circle"></i> Как восстановить бэкап?
                </h6>
            </div>
            <div class="card-body">
                <p class="small"><strong>Способ 1: PHPMyAdmin</strong></p>
                <ol class="small">
                    <li>Скачайте файл бэкапа</li>
                    <li>Откройте PHPMyAdmin</li>
                    <li>Выберите БД <code>rolex_db</code></li>
                    <li>Перейдите на вкладку "Import"</li>
                    <li>Выберите скачанный .sql файл</li>
                    <li>Нажмите "Go"</li>
                </ol>

                <p class="small"><strong>Способ 2: Командная строка</strong></p>
                <pre class="small bg-dark text-white p-2"><code>mysql -u root -p rolex_db < backup.sql</code></pre>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="bi bi-exclamation-triangle"></i> Важные замечания
                </h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li><strong>Храните бэкапы в безопасном месте</strong> - не только на сервере</li>
                    <li><strong>Регулярность:</strong> создавайте бэкапы минимум раз в неделю</li>
                    <li><strong>Перед обновлениями:</strong> всегда делайте бэкап</li>
                    <li><strong>Тестируйте восстановление:</strong> периодически проверяйте, что бэкапы работают</li>
                    <li><strong>Автоматизация:</strong> настройте cron для автоматических бэкапов</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-12">
        <a href="<?= url('admin/settings.php') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к настройкам
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
