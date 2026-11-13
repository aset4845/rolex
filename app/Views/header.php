<?php require_once __DIR__ . '/../Config/helpers.php'; ?>
<!DOCTYPE html>
<html lang="kk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? getSetting('site_name', 'Rolex ЖШС') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .product-card {
            transition: transform 0.3s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .product-image {
            height: 250px;
            object-fit: cover;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .hero-section {
            background: linear-gradient(135deg, rgba(0,0,0,0.45) 0%, rgba(0,0,0,0.65) 100%),
                        url('https://cdn2.ethoswatches.com/rolex/pub/media/newtheme/desktop/rolex-gmt-master-mid-full-landscape.jpg') center/cover no-repeat;
            color: white;
            padding: 80px 0;
        }
        .footer {
            background-color: #212529;
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
        .search-autocomplete {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .search-autocomplete-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }
        .search-autocomplete-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?= url('public/index.php') ?>">
                <i class="bi bi-watch"></i> <?= strtoupper(clean(getSetting('site_name', 'ROLEX'))) ?>
            </a>

            <!-- Поиск с автодополнением -->
            <form class="d-flex mx-auto" style="width: 400px; position: relative;" action="<?= url('public/products.php') ?>" method="GET">
                <input type="text" name="search" id="searchInput" class="form-control"
                       placeholder="Сағат іздеу..." autocomplete="off">
                <button class="btn btn-outline-light ms-2" type="submit">
                    <i class="bi bi-search"></i>
                </button>
                <div id="searchAutocomplete" class="search-autocomplete" style="display: none;"></div>
            </form>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('public/index.php') ?>">
                            <i class="bi bi-house"></i> Басты бет
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('public/products.php') ?>">
                            <i class="bi bi-grid"></i> Каталог
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('public/wishlist.php') ?>">
                            <i class="bi bi-heart"></i> Таңдаулылар
                            <span class="badge bg-danger wishlist-count" style="display: none;">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('public/compare.php') ?>">
                            <i class="bi bi-arrow-left-right"></i> Салыстыру
                            <span class="badge bg-info compare-count" style="display: none;">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('public/cart.php') ?>">
                            <i class="bi bi-cart3"></i> Себет
                            <?php $count = getCartCount(); if ($count > 0): ?>
                                <span class="badge bg-danger"><?= $count ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= url('public/account.php') ?>">
                            <i class="bi bi-person-circle"></i> Кабинет
                        </a>
                    </li>
                    <?php if (isset($_SESSION['customer_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-check"></i> <?= clean($_SESSION['customer_name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item" href="<?= url('public/account.php') ?>">
                                    <i class="bi bi-person-circle"></i> Менің кабинетім
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= url('public/logout.php') ?>">
                                    <i class="bi bi-box-arrow-right"></i> Шығу
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light btn-sm ms-2" href="<?= url('public/login.php') ?>">
                                <i class="bi bi-box-arrow-in-right"></i> Кіру
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
