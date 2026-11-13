<?php
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/ProductImage.php';

$page_title = 'Сағаттар каталогы - Rolex';
$productModel = new Product();
$categoryModel = new Category();
$imageModel = new ProductImage();

$category_id = !empty($_GET['category']) ? (int)$_GET['category'] : null;
$search = !empty($_GET['search']) ? trim($_GET['search']) : null;
$min_price = !empty($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$max_price = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$sort = $_GET['sort'] ?? 'default';
$in_stock = isset($_GET['in_stock']) ? true : false;

// Получить все товары
$all_products = $productModel->getAll($category_id, $search);

// Применить фильтры
$products = array_filter($all_products, function($product) use ($min_price, $max_price, $in_stock) {
    // Фильтр по минимальной цене
    if ($min_price !== null && (float)$product['price'] < $min_price) {
        return false;
    }

    // Фильтр по максимальной цене
    if ($max_price !== null && (float)$product['price'] > $max_price) {
        return false;
    }

    // Фильтр по наличию на складе
    if ($in_stock && (int)$product['stock'] <= 0) {
        return false;
    }

    return true;
});

// Сортировка
switch ($sort) {
    case 'price_asc':
        usort($products, fn($a, $b) => $a['price'] <=> $b['price']);
        break;
    case 'price_desc':
        usort($products, fn($a, $b) => $b['price'] <=> $a['price']);
        break;
    case 'name_asc':
        usort($products, fn($a, $b) => strcmp($a['name'], $b['name']));
        break;
    case 'name_desc':
        usort($products, fn($a, $b) => strcmp($b['name'], $a['name']));
        break;
}

$categories = $categoryModel->getAll();

require_once __DIR__ . '/../app/Views/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4">Сағаттар каталогы <span class="badge bg-secondary"><?= count($products) ?></span></h1>

    <div class="row">
        <!-- Боковая панель фильтров -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-funnel"></i> Сүзгілер</h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" id="filterForm">
                        <!-- Поиск -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Іздеу</label>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Атауы бойынша..."
                                   value="<?= clean($search ?? '') ?>">
                        </div>

                        <!-- Категория -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Санат</label>
                            <select name="category" class="form-select">
                                <option value="">Барлығы</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                                    <?= clean($cat['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Цена -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Баға диапазоны</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" class="form-control form-control-sm"
                                           placeholder="Мин" value="<?= $min_price ?? '' ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" class="form-control form-control-sm"
                                           placeholder="Макс" value="<?= $max_price ?? '' ?>">
                                </div>
                            </div>
                        </div>

                        <!-- В наличии -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="in_stock" id="in_stock"
                                       <?= $in_stock ? 'checked' : '' ?>>
                                <label class="form-check-label" for="in_stock">
                                    Тек қоймада бар
                                </label>
                            </div>
                        </div>

                        <!-- Сортировка -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Сұрыптау</label>
                            <select name="sort" class="form-select">
                                <option value="default" <?= $sort === 'default' ? 'selected' : '' ?>>Әдепкі</option>
                                <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Бағасы: арзаннан қымбатқа</option>
                                <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Бағасы: қымбаттан арзанға</option>
                                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Атауы: А-Я</option>
                                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Атауы: Я-А</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-check-circle"></i> Қолдану
                        </button>
                        <a href="<?= url('public/products.php') ?>" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle"></i> Тазалау
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Товары -->
        <div class="col-md-9">
            <?php if (empty($products)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle fs-1"></i>
                    <h4 class="mt-2">Өнімдер табылмады</h4>
                    <p>Іздеу параметрлерін өзгертіп көріңіз</p>
                </div>
            <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                <?php
                    // Получить главное изображение товара из галереи
                    $main_image = $imageModel->getMainImage($product['id']);
                    $image_src = $main_image ? url('public/' . $main_image['image_path']) : null;
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card product-card h-100 shadow-sm">
                        <div class="product-image d-flex align-items-center justify-content-center position-relative">
                            <?php if ($image_src): ?>
                                <img src="<?= $image_src ?>"
                                     alt="<?= clean($product['name']) ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <i class="bi bi-smartwatch display-1 text-white"></i>
                            <?php endif; ?>
                            <?php if ($product['stock'] <= 0): ?>
                                <span class="position-absolute top-0 end-0 badge bg-danger m-2">Жоқ</span>
                            <?php elseif ($product['stock'] <= 3): ?>
                                <span class="position-absolute top-0 end-0 badge bg-warning m-2">Аз қалды</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= clean($product['name']) ?></h5>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-tag"></i> <?= clean($product['category_name']) ?>
                            </p>
                            <p class="card-text text-truncate" style="max-height: 50px;">
                                <?= clean(substr($product['description'] ?? '', 0, 80)) ?>...
                            </p>
                            <div class="mt-auto">
                                <p class="mb-3">
                                    <strong class="text-primary fs-5"><?= formatPrice($product['price']) ?></strong>
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="<?= url('public/product.php?id=' . $product['id']) ?>"
                                       class="btn btn-primary">
                                        <i class="bi bi-eye"></i> Толығырақ
                                    </a>
                                    <div class="btn-group w-100" role="group">
                                        <button class="btn btn-outline-success btn-sm add-to-wishlist"
                                                data-id="<?= $product['id'] ?>" title="Таңдаулыларға қосу">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                        <button class="btn btn-outline-info btn-sm add-to-compare"
                                                data-id="<?= $product['id'] ?>" title="Салыстыруға қосу">
                                            <i class="bi bi-arrow-left-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Добавление в избранное (wishlist)
document.querySelectorAll('.add-to-wishlist').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.getAttribute('data-id');
        let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');

        if (wishlist.includes(productId)) {
            wishlist = wishlist.filter(id => id !== productId);
            this.innerHTML = '<i class="bi bi-heart"></i>';
            this.classList.remove('btn-success');
            this.classList.add('btn-outline-success');
        } else {
            wishlist.push(productId);
            this.innerHTML = '<i class="bi bi-heart-fill"></i>';
            this.classList.remove('btn-outline-success');
            this.classList.add('btn-success');
        }

        localStorage.setItem('wishlist', JSON.stringify(wishlist));
        updateWishlistCount();
    });

    // Проверить при загрузке
    const productId = btn.getAttribute('data-id');
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    if (wishlist.includes(productId)) {
        btn.innerHTML = '<i class="bi bi-heart-fill"></i>';
        btn.classList.remove('btn-outline-success');
        btn.classList.add('btn-success');
    }
});

// Добавление в сравнение (compare)
document.querySelectorAll('.add-to-compare').forEach(btn => {
    btn.addEventListener('click', function() {
        const productId = this.getAttribute('data-id');
        let compareList = JSON.parse(localStorage.getItem('compare') || '[]');

        if (compareList.includes(productId)) {
            compareList = compareList.filter(id => id !== productId);
            this.innerHTML = '<i class="bi bi-arrow-left-right"></i>';
            this.classList.remove('btn-info');
            this.classList.add('btn-outline-info');
        } else {
            if (compareList.length >= 4) {
                alert('Ең көбі 4 өнімді салыстыруға болады');
                return;
            }
            compareList.push(productId);
            this.innerHTML = '<i class="bi bi-check2"></i>';
            this.classList.remove('btn-outline-info');
            this.classList.add('btn-info');
        }

        localStorage.setItem('compare', JSON.stringify(compareList));
        updateCompareCount();
    });

    // Проверить при загрузке
    const productId = btn.getAttribute('data-id');
    let compareList = JSON.parse(localStorage.getItem('compare') || '[]');
    if (compareList.includes(productId)) {
        btn.innerHTML = '<i class="bi bi-check2"></i>';
        btn.classList.remove('btn-outline-info');
        btn.classList.add('btn-info');
    }
});

function updateWishlistCount() {
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    const badge = document.querySelector('.wishlist-count');
    if (badge) {
        badge.textContent = wishlist.length;
        badge.style.display = wishlist.length > 0 ? 'inline' : 'none';
    }
}

function updateCompareCount() {
    let compareList = JSON.parse(localStorage.getItem('compare') || '[]');
    const badge = document.querySelector('.compare-count');
    if (badge) {
        badge.textContent = compareList.length;
        badge.style.display = compareList.length > 0 ? 'inline' : 'none';
    }
}

// Обновить счётчики при загрузке
updateCompareCount();
</script>

<?php require_once __DIR__ . '/../app/Views/footer.php'; ?>
