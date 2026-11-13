<?php
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/ProductImage.php';

$product_id = $_GET['product_id'] ?? null;

if (!$product_id) {
    header('Location: /rolex/admin/products.php');
    exit;
}

$productModel = new Product();
$imageModel = new ProductImage();

$product = $productModel->getById($product_id);

if (!$product) {
    header('Location: /rolex/admin/products.php');
    exit;
}

$success = '';
$error = '';

// Обработка загрузки изображения
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    $upload_dir = __DIR__ . '/../public/uploads/products/';
    $image_type = $_POST['image_type'] ?? 'gallery'; // Получить тип изображения

    // Создать директорию если не существует
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB

    $uploaded_count = 0;
    $files = $_FILES['images'];

    // Обработка множественной загрузки
    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $file_type = $files['type'][$i];
            $file_size = $files['size'][$i];

            // Проверка типа
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Неверный тип файла. Разрешены: JPG, PNG, GIF, WEBP';
                continue;
            }

            // Проверка размера
            if ($file_size > $max_size) {
                $error = 'Файл слишком большой. Максимум 5MB';
                continue;
            }

            // Генерация имени файла
            $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
            $filename = uniqid('product_' . $product_id . '_') . '.' . $extension;
            $filepath = $upload_dir . $filename;

            // Загрузка файла
            if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                $current_count = $imageModel->getCountByProduct($product_id);
                $is_main = ($current_count === 0) ? 1 : 0;
                $sort_order = $current_count;

                // Сохранить с указанным типом изображения
                $imageModel->create($product_id, 'uploads/products/' . $filename, $is_main, $sort_order, $image_type);
                $uploaded_count++;
            }
        }
    }

    if ($uploaded_count > 0) {
        $success = "Загружено новых изображений ($image_type): $uploaded_count";
    }
}

// Удаление изображения
if (isset($_GET['delete_image'])) {
    $image_id = $_GET['delete_image'];
    if ($imageModel->delete($image_id)) {
        $success = 'Изображение удалено';
    } else {
        $error = 'Ошибка удаления изображения';
    }
}

// Установка главного изображения
if (isset($_GET['set_main'])) {
    $image_id = $_GET['set_main'];
    if ($imageModel->setMainImage($image_id, $product_id)) {
        $success = 'Главное изображение установлено';
    }
}

$images = $imageModel->getByProductId($product_id);

$page_title = 'Галерея изображений - ' . $product['name'];
require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>
                    <i class="bi bi-images"></i> Галерея изображений
                </h1>
                <a href="/rolex/admin/products.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Назад к товарам
                </a>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-box"></i> Товар: <?= clean($product['name']) ?>
                    </h5>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Форма загрузки -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-cloud-upload"></i> Загрузить изображения</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Тип изображения *</label>
                            <select name="image_type" class="form-select" required>
                                <option value="product">Фото продукта (для каталога и главной)</option>
                                <option value="gallery" selected>Галерея продукта (детальная страница)</option>
                            </select>
                            <small class="text-muted">
                                <strong>Фото продукта:</strong> отображается в каталоге и на главной странице<br>
                                <strong>Галерея:</strong> дополнительные фото на детальной странице товара
                            </small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Выберите изображения *</label>
                            <input type="file" name="images[]" class="form-control"
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   multiple required>
                            <small class="text-muted">
                                Разрешены: JPG, PNG, GIF, WEBP. Максимум 5MB на файл.
                                Можно выбрать несколько файлов.
                            </small>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Загрузить
                        </button>
                    </form>
                </div>
            </div>

            <!-- Галерея изображений -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-grid-3x3"></i>
                        Все изображения (<?= count($images) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($images)): ?>
                        <div class="alert alert-info text-center">
                            <i class="bi bi-info-circle fs-1"></i>
                            <h4 class="mt-3">Нет изображений</h4>
                            <p>Загрузите изображения для этого товара</p>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($images as $image): ?>
                            <div class="col-md-3 mb-4">
                                <div class="card <?= $image['is_main'] ? 'border-primary' : ($image['image_type'] === 'product' ? 'border-success' : '') ?>">
                                    <?php if ($image['is_main']): ?>
                                        <div class="card-header bg-primary text-white text-center py-1">
                                            <small><i class="bi bi-star-fill"></i> Главное</small>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-2">
                                        <img src="<?= url('public/' . $image['image_path']) ?>"
                                             class="img-fluid mb-2"
                                             alt="Product Image"
                                             style="width: 100%; height: 200px; object-fit: cover;">

                                        <!-- Тип изображения -->
                                        <div class="mb-2">
                                            <?php if ($image['image_type'] === 'product'): ?>
                                                <span class="badge bg-success w-100">
                                                    <i class="bi bi-image"></i> Фото продукта
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary w-100">
                                                    <i class="bi bi-images"></i> Галерея
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <?php if (!$image['is_main']): ?>
                                                <a href="?product_id=<?= $product_id ?>&set_main=<?= $image['id'] ?>"
                                                   class="btn btn-sm btn-outline-primary"
                                                   onclick="return confirm('Установить как главное?')">
                                                    <i class="bi bi-star"></i> Сделать главным
                                                </a>
                                            <?php endif; ?>
                                            <a href="?product_id=<?= $product_id ?>&delete_image=<?= $image['id'] ?>"
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Удалить это изображение?')">
                                                <i class="bi bi-trash"></i> Удалить
                                            </a>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            Порядок: <?= $image['sort_order'] ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
