<?php
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Category.php';
require_once __DIR__ . '/../app/Models/ProductImage.php';

$page_title = 'Жаңа өнім қосу - Админ панель';
$productModel = new Product();
$categoryModel = new Category();
$imageModel = new ProductImage();

$categories = $categoryModel->getAll();
$errors = [];
$success = false;
$new_product_id = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $category_id = trim($_POST['category_id'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $stock = trim($_POST['stock'] ?? '');

    // Валидация
    if (empty($name)) $errors[] = "Атауын енгізіңіз";
    if (empty($price) || $price <= 0) $errors[] = "Дұрыс баға енгізіңіз";
    if (empty($category_id)) $errors[] = "Категорияны таңдаңыз";
    if (empty($stock) || $stock < 0) $errors[] = "Қойма санын енгізіңіз";

    if (empty($errors)) {
        $productModel->name = $name;
        $productModel->price = $price;
        $productModel->category_id = $category_id;
        $productModel->description = $description;
        $productModel->stock = $stock;
        $productModel->image = 'default.jpg'; // Не используется, но требуется для совместимости

        $new_product_id = $productModel->create();
        if ($new_product_id) {
            // Обработка загрузки изображения в галерею
            if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../public/uploads/products/';

                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid("product_{$new_product_id}_") . '.' . $extension;
                $target_file = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    // Добавить изображение как фото продукта (для каталога)
                    $imageModel->create($new_product_id, 'uploads/products/' . $file_name, 1, 0, 'product');
                }
            }

            $success = true;
        } else {
            $errors[] = "Өнім қосу кезінде қате шықты";
        }
    }
}

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4"><i class="bi bi-plus-circle"></i> Жаңа өнім қосу</h1>

<?php if ($success): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle"></i> Өнім сәтті қосылды!
        <a href="<?= url('admin/products.php') ?>" class="alert-link">Тізімге оралу</a>
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
                               value="<?= clean($_POST['name'] ?? '') ?>"
                               placeholder="Мысалы: Rolex Submariner" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Бағасы (₸) *</label>
                            <input type="number" name="price" class="form-control"
                                   value="<?= clean($_POST['price'] ?? '') ?>"
                                   placeholder="0.00" step="0.01" min="0" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Категория *</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Таңдаңыз...</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"
                                        <?= ($_POST['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                        <?= clean($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Қоймадағы саны *</label>
                        <input type="number" name="stock" class="form-control"
                               value="<?= clean($_POST['stock'] ?? '0') ?>"
                               min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Сипаттамасы</label>
                        <textarea name="description" class="form-control" rows="5"
                                  placeholder="Өнімнің толық сипаттамасы..."><?= clean($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Сурет</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                        <small class="text-muted">JPG, PNG форматтары қолданылады</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Сақтау
                        </button>
                        <a href="<?= url('admin/products.php') ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Болдырмау
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
