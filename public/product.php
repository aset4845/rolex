<?php
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Review.php';
require_once __DIR__ . '/../app/Models/ProductImage.php';
require_once __DIR__ . '/../app/Controllers/CartController.php';

$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header('Location: ' . url('public/products.php'));
    exit;
}

$productModel = new Product();
$reviewModel = new Review();
$imageModel = new ProductImage();
$product = $productModel->getById($product_id);

if (!$product) {
    header('Location: ' . url('public/products.php'));
    exit;
}

// Получить изображения галереи (только тип 'gallery')
$gallery_images = $imageModel->getGalleryImages($product_id);

// Себетке қосу
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = (int)($_POST['quantity'] ?? 1);
    $result = CartController::add($product_id, $quantity);

    if ($result['success']) {
        $success = $result['message'];
    } else {
        $error = $result['message'];
        if (isset($result['redirect'])) {
            header('Location: ' . $result['redirect']);
            exit;
        }
    }
}

// Добавить отзыв
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_review'])) {
    // Проверка авторизации
    if (!isset($_SESSION['customer_id'])) {
        $review_error = "Пікір қалдыру үшін жүйеге кіріңіз";
    } else {
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        $customer_id = $_SESSION['customer_id'];

        // Проверить, не оставлял ли уже отзыв
        if ($reviewModel->hasReviewed($product_id, $customer_id)) {
            $review_error = "Сіз бұл өнімге пікір қалдырдыңыз";
        } elseif ($rating >= 1 && $rating <= 5 && $comment) {
            if ($reviewModel->create($product_id, $customer_id, $rating, $comment)) {
                $review_success = "Пікіріңіз қосылды! Рақмет!";
            } else {
                $review_error = "Қате орын алды. Қайталап көріңіз.";
            }
        } else {
            $review_error = "Барлық өрістерді толтырыңыз.";
        }
    }
}

// Получить отзывы и рейтинг
$reviews = $reviewModel->getByProduct($product_id);
$rating_data = $reviewModel->getAverageRating($product_id);
$avg_rating = round($rating_data['avg_rating'] ?? 0, 1);
$total_reviews = $rating_data['total_reviews'] ?? 0;

// Получить изображения товара
$page_title = $product['name'] . ' - Rolex';
require_once __DIR__ . '/../app/Views/header.php';
?>

<div class="container my-5">
    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?= $success ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= url('public/index.php') ?>">Басты бет</a></li>
            <li class="breadcrumb-item"><a href="<?= url('public/products.php') ?>">Каталог</a></li>
            <li class="breadcrumb-item active"><?= clean($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Галерея изображений -->
        <div class="col-md-6 mb-4">
            <?php if (empty($gallery_images)): ?>
                <!-- Изображение по умолчанию -->
                <div class="card">
                    <div style="height: 400px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
                         class="d-flex align-items-center justify-content-center">
                        <i class="bi bi-smartwatch display-1 text-white"></i>
                    </div>
                </div>
            <?php else: ?>
                <!-- Главное изображение -->
                <div class="card mb-3">
                    <div id="mainImageContainer" style="height: 400px; overflow: hidden; cursor: zoom-in;"
                         data-bs-toggle="modal" data-bs-target="#imageModal">
                        <?php
                        $main_image = $gallery_images[0];
                        ?>
                        <img id="mainImage" src="<?= url('public/' . $main_image['image_path']) ?>"
                             class="w-100 h-100" style="object-fit: cover;" alt="<?= clean($product['name']) ?>">
                    </div>
                </div>

                <!-- Миниатюры -->
                <?php if (count($gallery_images) > 1): ?>
                <div class="row g-2">
                    <?php foreach ($gallery_images as $image): ?>
                    <div class="col-3">
                        <div class="thumbnail-item" style="cursor: pointer; border: 2px solid transparent; border-radius: 4px; overflow: hidden;"
                             onclick="changeMainImage('<?= url('public/' . $image['image_path']) ?>')">
                            <img src="<?= url('public/' . $image['image_path']) ?>"
                                 class="img-fluid" style="height: 80px; width: 100%; object-fit: cover;"
                                 alt="Thumbnail">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Ақпарат -->
        <div class="col-md-6">
            <h1 class="mb-3"><?= clean($product['name']) ?></h1>
            <p class="text-muted">
                <i class="bi bi-tag"></i> Категория:
                <strong><?= clean($product['category_name']) ?></strong>
            </p>

            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h2 class="text-primary mb-0"><?= formatPrice($product['price']) ?></h2>
                </div>
            </div>

            <div class="mb-3">
                <h5>Сипаттамасы:</h5>
                <p><?= nl2br(clean($product['description'])) ?></p>
            </div>

            <div class="mb-3">
                <h5>Қоймадағы саны:</h5>
                <?php if ($product['stock'] > 0): ?>
                    <span class="badge bg-success fs-6">
                        <i class="bi bi-check-circle"></i> Қоймада бар (<?= $product['stock'] ?> дана)
                    </span>
                <?php else: ?>
                    <span class="badge bg-danger fs-6">
                        <i class="bi bi-x-circle"></i> Қоймада жоқ
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($product['stock'] > 0): ?>
            <form method="POST" class="mt-4">
                <div class="row align-items-end">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Саны:</label>
                        <input type="number" name="quantity" class="form-control"
                               value="1" min="1" max="<?= $product['stock'] ?>" required>
                    </div>
                    <div class="col-md-8 mb-2">
                        <button type="submit" name="add_to_cart" class="btn btn-success btn-lg w-100">
                            <i class="bi bi-cart-plus"></i> Себетке қосу
                        </button>
                    </div>
                </div>
            </form>
            <?php endif; ?>

            <div class="mt-4">
                <a href="<?= url('public/products.php') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Каталогқа оралу
                </a>
            </div>
        </div>
    </div>

    <!-- Отзывы и рейтинг -->
    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-4">
                <i class="bi bi-chat-square-text"></i> Пікірлер мен бағалар
                <?php if ($total_reviews > 0): ?>
                    <span class="badge bg-primary"><?= $total_reviews ?></span>
                <?php endif; ?>
            </h3>

            <?php if ($total_reviews > 0): ?>
            <div class="card bg-light mb-4">
                <div class="card-body text-center">
                    <h2 class="display-4 text-warning mb-2">
                        <i class="bi bi-star-fill"></i> <?= $avg_rating ?>
                    </h2>
                    <p class="text-muted">
                        <?= $total_reviews ?> пікір негізінде орташа рейтинг
                    </p>
                    <div class="text-warning fs-4">
                        <?php for($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= floor($avg_rating)): ?>
                                <i class="bi bi-star-fill"></i>
                            <?php elseif ($i - $avg_rating < 1): ?>
                                <i class="bi bi-star-half"></i>
                            <?php else: ?>
                                <i class="bi bi-star"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Форма добавления отзыва -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Пікір қалдыру</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($review_success)): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <?= $review_success ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($review_error)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?= $review_error ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!isset($_SESSION['customer_id'])): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            Пікір қалдыру үшін жүйеге кіру керек
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= url('public/login.php') ?>" class="btn btn-success">
                                <i class="bi bi-box-arrow-in-right"></i> Кіру
                            </a>
                            <a href="<?= url('public/register.php') ?>" class="btn btn-outline-primary">
                                <i class="bi bi-person-plus"></i> Тіркелу
                            </a>
                        </div>
                    <?php elseif ($reviewModel->hasReviewed($product_id, $_SESSION['customer_id'])): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-check-circle"></i>
                            Сіз бұл өнімге пікір қалдырдыңыз
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <div class="mb-3">
                                <p class="mb-2"><strong>Пікір қалдырушы:</strong> <?= clean($_SESSION['customer_name']) ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Бағалау *</label>
                                <select name="rating" class="form-select" required>
                                    <option value="">Таңдаңыз...</option>
                                    <option value="5">⭐⭐⭐⭐⭐ - Керемет</option>
                                    <option value="4">⭐⭐⭐⭐ - Жақсы</option>
                                    <option value="3">⭐⭐⭐ - Орташа</option>
                                    <option value="2">⭐⭐ - Нашар</option>
                                    <option value="1">⭐ - Өте нашар</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Пікіріңіз *</label>
                                <textarea name="comment" class="form-control" rows="4" required
                                          placeholder="Өнім туралы пікіріңізді жазыңыз..."></textarea>
                            </div>
                            <button type="submit" name="add_review" class="btn btn-success">
                                <i class="bi bi-send"></i> Пікір жіберу
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Список отзывов -->
            <?php if (empty($reviews)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Әлі пікірлер жоқ. Бірінші пікір қалдырыңыз!
                </div>
            <?php else: ?>
                <h4 class="mb-3">Барлық пікірлер (<?= count($reviews) ?>)</h4>
                <?php foreach ($reviews as $review): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="card-title mb-1">
                                    <?= clean($review['customer_name']) ?>
                                </h5>
                                <div class="text-warning mb-2">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $review['rating']): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                    <span class="text-muted ms-2">(<?= $review['rating'] ?>/5)</span>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i>
                                <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?>
                            </small>
                        </div>
                        <p class="card-text mt-2 mb-0"><?= nl2br(clean($review['comment'])) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Модальное окно для просмотра изображений -->
<?php if (!empty($gallery_images)): ?>
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= clean($product['name']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalImage" src="<?= url('public/' . $main_image['image_path']) ?>"
                     class="img-fluid" style="max-height: 80vh;" alt="<?= clean($product['name']) ?>">
            </div>
            <div class="modal-footer justify-content-center">
                <div class="d-flex gap-2 flex-wrap justify-content-center">
                    <?php foreach ($gallery_images as $index => $image): ?>
                    <img src="<?= url('public/' . $image['image_path']) ?>"
                         class="img-thumbnail modal-thumbnail"
                         style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                         onclick="document.getElementById('modalImage').src = this.src"
                         alt="Thumbnail <?= $index + 1 ?>">
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Изменить главное изображение
function changeMainImage(imageUrl) {
    const mainImage = document.getElementById('mainImage');
    const modalImage = document.getElementById('modalImage');

    mainImage.src = imageUrl;
    if (modalImage) {
        modalImage.src = imageUrl;
    }

    // Highlight selected thumbnail
    document.querySelectorAll('.thumbnail-item').forEach(item => {
        item.style.borderColor = 'transparent';
    });
    event.currentTarget.style.borderColor = '#0d6efd';
}

// Обновить изображение в модальном окне при открытии
document.getElementById('mainImageContainer')?.addEventListener('click', function() {
    const mainImageSrc = document.getElementById('mainImage').src;
    document.getElementById('modalImage').src = mainImageSrc;
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../app/Views/footer.php'; ?>
