<?php
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/ProductImage.php';

$page_title = 'Өнімді өзгерту - Админ панель';
$productModel = new Product();
$categoryModel = new Category();
$imageModel = new ProductImage();

$product_id = $_GET['id'] ?? null;

if (!$product_id) {
    header('Location: ' . url('admin/products.php'));
    exit;
}

$product = $productModel->getById($product_id);
if (!$product) {
    header('Location: ' . url('admin/products.php'));
    exit;
}

$categories = $categoryModel->getAll();
$images = $imageModel->getByProductId($product_id);
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $stock = trim($_POST['stock'] ?? '');

    if (empty($name)) $errors[] = "Атауын енгізіңіз";
    if (empty($price) || $price <= 0) $errors[] = "Дұрыс баға енгізіңіз";
    if (empty($category_id)) $errors[] = "Категорияны таңдаңыз";
    if (empty($stock) || $stock < 0) $errors[] = "Қойма санын енгізіңіз";

    if (empty($errors)) {
        $productModel->id = $product_id;
        $productModel->name = $name;
        $productModel->price = $price;
        $productModel->category_id = $category_id;
        $productModel->description = $description;
        $productModel->stock = $stock;
        $productModel->image = $product['image']; // Оставляем без изменений

        if ($productModel->update()) {
            $success = true;
            $product = $productModel->getById($product_id);
        } else {
            $errors[] = "Өнімді жаңарту кезінде қате шықты";
        }
    }
}

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4"><i class="bi bi-pencil"></i> Өнімді өзгерту</h1>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle"></i> Өнім сәтті жаңартылды!
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
        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Атауы *</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= clean($product['name']) ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Бағасы (₸) *</label>
                            <input type="number" name="price" class="form-control"
                                   value="<?= $product['price'] ?>"
                                   step="0.01" min="0" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Категория *</label>
                            <select name="category_id" class="form-select" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= $product['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= clean($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Қоймадағы саны *</label>
                        <input type="number" name="stock" class="form-control"
                               value="<?= $product['stock'] ?>" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Сипаттамасы</label>
                        <textarea name="description" class="form-control" rows="5"><?= clean($product['description']) ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Сақтау
                        </button>
                        <a href="<?= url('admin/products.php') ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Тізімге оралу
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Галерея изображений -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">
                    <i class="bi bi-images"></i> Изображения товара
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($images)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-image display-4"></i>
                        <p class="mt-2">Нет изображений</p>
                    </div>
                <?php else: ?>
                    <div class="row g-2">
                        <?php foreach ($images as $image): ?>
                        <div class="col-6">
                            <div class="position-relative">
                                <img src="<?= url('public/' . $image['image_path']) ?>"
                                     class="img-fluid rounded"
                                     style="width: 100%; height: 100px; object-fit: cover;">
                                <?php if ($image['is_main']): ?>
                                    <span class="position-absolute top-0 start-0 badge bg-primary m-1">
                                        <i class="bi bi-star-fill"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="d-grid mt-3">
                    <a href="<?= url('admin/product_images.php?product_id=' . $product_id) ?>"
                       class="btn btn-info">
                        <i class="bi bi-images"></i> Управление галереей
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
