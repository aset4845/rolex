<?php
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/ProductImage.php';

$page_title = 'Басты бет - Rolex ЖШС';
$productModel = new Product();
$categoryModel = new Category();
$imageModel = new ProductImage();

$featured_products = $productModel->getFeatured(4);
$categories = $categoryModel->getAll();

require_once __DIR__ . '/../app/Views/header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-4">ROLEX - Уақыттың шебері</h1>
        <p class="lead mb-4">Әлемдегі ең танымал және сапалы сағаттар</p>
        <a href="<?= url('public/products.php') ?>" class="btn btn-light btn-lg">
            <i class="bi bi-shop"></i> Каталогты көру
        </a>
    </div>
</div>

<!-- Категориялар -->
<div class="container my-5">
    <h2 class="text-center mb-4">Категориялар</h2>
    <div class="row">
        <?php foreach ($categories as $category): ?>
        <div class="col-md-4 mb-3">
            <a href="<?= url('public/products.php?category=' . $category['id']) ?>"
               class="text-decoration-none">
                <div class="card h-100 text-center border-primary">
                    <div class="card-body">
                        <i class="bi bi-collection display-3 text-primary"></i>
                        <h5 class="card-title mt-3"><?= clean($category['name']) ?></h5>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Танымал өнімдер -->
<div class="container my-5">
    <h2 class="text-center mb-4">Танымал сағаттар</h2>
    <div class="row">
        <?php foreach ($featured_products as $product): ?>
        <?php
            // Получить главное изображение товара
            $main_image = $imageModel->getMainImage($product['id']);
            $image_src = $main_image ? url('public/' . $main_image['image_path']) : null;
        ?>
        <div class="col-md-3 mb-4">
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
                    <p class="card-text mb-2">
                        <strong class="text-primary fs-5"><?= formatPrice($product['price']) ?></strong>
                    </p>
                    <p class="text-muted small mb-3">
                        <i class="bi bi-box-seam"></i> Қоймада: <?= $product['stock'] ?> дана
                    </p>
                    <a href="<?= url('public/product.php?id=' . $product['id']) ?>"
                       class="btn btn-primary w-100 mt-auto">
                        <i class="bi bi-eye"></i> Көру
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Акциялар -->
<div class="container my-5">
    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body text-center p-5">
                    <h3><i class="bi bi-gift"></i> Жаңа коллекция!</h3>
                    <p>2025 жылғы жаңа модельдер қазір сатылымда</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body text-center p-5">
                    <h3><i class="bi bi-truck"></i> Тегін жеткізу</h3>
                    <p>500,000 ₸ жоғары тапсырыстарға тегін жеткізіледі</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/footer.php'; ?>
