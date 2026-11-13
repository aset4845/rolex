<?php
require_once __DIR__ . '/../Config/helpers.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Админ панель - Rolex' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #212529;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 12px 20px;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #2c3034;
            border-left-color: #0d6efd;
        }
        .sidebar .nav-link i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block sidebar p-0">
                <div class="position-sticky">
                    <div class="text-center text-white p-3 border-bottom">
                        <h4><i class="bi bi-watch"></i> ROLEX</h4>
                        <small>Админ панель</small>
                    </div>

                    <ul class="nav flex-column" style="padding-bottom: 120px;">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/dashboard_new.php') ?>">
                                <i class="bi bi-speedometer2"></i> Дашборд
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/products.php') ?>">
                                <i class="bi bi-box-seam"></i> Товары
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/categories.php') ?>">
                                <i class="bi bi-tags"></i> Категории
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/orders.php') ?>">
                                <i class="bi bi-cart-check"></i> Заказы
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/orders_search.php') ?>">
                                <i class="bi bi-search"></i> Поиск заказов
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/reports.php') ?>">
                                <i class="bi bi-graph-up"></i> Отчёты
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/users.php') ?>">
                                <i class="bi bi-people"></i> Пользователи
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/products_bulk.php') ?>">
                                <i class="bi bi-check2-square"></i> Массовые товары
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/activity_logs.php') ?>">
                                <i class="bi bi-clock-history"></i> Логи активности
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('admin/settings.php') ?>">
                                <i class="bi bi-gear"></i> Настройки
                            </a>
                        </li>
                        <li class="nav-item border-top mt-3">
                            <a class="nav-link" href="<?= url('public/index.php') ?>" target="_blank">
                                <i class="bi bi-globe"></i> Сайт
                            </a>
                        </li>
                    </ul>

                    <!-- User info and logout at bottom -->
                    <div style="position: absolute; bottom: 0; width: 100%; left: 0;">
                        <div class="p-3 border-top text-white-50 small text-center">
                            <i class="bi bi-person-circle"></i>
                            <?= clean($_SESSION['username'] ?? 'Admin') ?>
                        </div>
                        <div class="p-2">
                            <a class="btn btn-danger w-100" href="<?= url('admin/logout.php') ?>">
                                <i class="bi bi-box-arrow-right"></i> Выйти
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-4 py-4">
