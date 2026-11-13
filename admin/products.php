<?php
require_once __DIR__ . '/../app/Models/Product.php';

$page_title = 'Өнімдер - Админ панель';
$productModel = new Product();

// Өнімді өшіру
if (isset($_GET['delete'])) {
    $productModel->delete($_GET['delete']);
    header('Location: ' . url('admin/products.php'));
    exit;
}

$products = $productModel->getAll();

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-box-seam"></i> Өнімдер</h1>
    <a href="<?= url('admin/product_add.php') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Жаңа өнім қосу
    </a>
</div>

<?php if (empty($products)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> Өнімдер жоқ. Жаңа өнім қосыңыз.
    </div>
<?php else: ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Атауы</th>
                        <th>Категория</th>
                        <th>Бағасы</th>
                        <th>Қоймада</th>
                        <th>Күні</th>
                        <th width="150">Әрекеттер</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><strong><?= $product['id'] ?></strong></td>
                        <td><?= clean($product['name']) ?></td>
                        <td><span class="badge bg-secondary"><?= clean($product['category_name']) ?></span></td>
                        <td><?= formatPrice($product['price']) ?></td>
                        <td>
                            <?php if ($product['stock'] > 0): ?>
                                <span class="badge bg-success"><?= $product['stock'] ?></span>
                            <?php else: ?>
                                <span class="badge bg-danger">Жоқ</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d.m.Y', strtotime($product['created_at'])) ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= url('admin/product_edit.php?id=' . $product['id']) ?>"
                                   class="btn btn-warning" title="Өзгерту">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?= url('admin/product_images.php?product_id=' . $product['id']) ?>"
                                   class="btn btn-info" title="Галерея">
                                    <i class="bi bi-images"></i>
                                </a>
                                <a href="?delete=<?= $product['id'] ?>"
                                   class="btn btn-danger"
                                   onclick="return confirm('Өнімді өшіруге сенімдісіз бе?')"
                                   title="Өшіру">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
