<?php
require_once __DIR__ . '/../app/Models/Product.php';
require_once __DIR__ . '/../app/Models/Category.php';

$page_title = 'Массовые операции - Админ панель';
$productModel = new Product();
$categoryModel = new Category();

$success = '';
$errors = [];

// Массовые операции
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['selected_products'])) {
    $selected = $_POST['selected_products'];
    $action = $_POST['bulk_action'] ?? '';

    switch ($action) {
        case 'delete':
            // Массовое удаление
            foreach ($selected as $id) {
                $productModel->delete($id);
            }
            $success = count($selected) . " товаров удалено";
            break;

        case 'change_category':
            // Смена категории
            $new_category = $_POST['new_category'] ?? '';
            if ($new_category) {
                $database = new Database();
                $conn = $database->connect();
                $query = "UPDATE products SET category_id = :cat WHERE id IN (" . implode(',', array_map('intval', $selected)) . ")";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':cat', $new_category);
                $stmt->execute();
                $success = count($selected) . " товаров перемещено в новую категорию";
            }
            break;

        case 'update_stock':
            // Изменение остатка
            $stock_action = $_POST['stock_action'] ?? 'set';
            $stock_value = (int)($_POST['stock_value'] ?? 0);

            $database = new Database();
            $conn = $database->connect();

            foreach ($selected as $id) {
                if ($stock_action === 'set') {
                    $query = "UPDATE products SET stock = :stock WHERE id = :id";
                } elseif ($stock_action === 'add') {
                    $query = "UPDATE products SET stock = stock + :stock WHERE id = :id";
                } elseif ($stock_action === 'subtract') {
                    $query = "UPDATE products SET stock = GREATEST(0, stock - :stock) WHERE id = :id";
                }

                $stmt = $conn->prepare($query);
                $stmt->bindParam(':stock', $stock_value);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            }
            $success = "Склад обновлён для " . count($selected) . " товаров";
            break;

        case 'update_price':
            // Изменение цены
            $price_action = $_POST['price_action'] ?? 'increase';
            $price_value = (float)($_POST['price_value'] ?? 0);

            $database = new Database();
            $conn = $database->connect();

            foreach ($selected as $id) {
                if ($price_action === 'increase') {
                    $query = "UPDATE products SET price = price + :value WHERE id = :id";
                } elseif ($price_action === 'decrease') {
                    $query = "UPDATE products SET price = GREATEST(0, price - :value) WHERE id = :id";
                } elseif ($price_action === 'percent_increase') {
                    $query = "UPDATE products SET price = price * (1 + :value / 100) WHERE id = :id";
                } elseif ($price_action === 'percent_decrease') {
                    $query = "UPDATE products SET price = price * (1 - :value / 100) WHERE id = :id";
                }

                $stmt = $conn->prepare($query);
                $stmt->bindParam(':value', $price_value);
                $stmt->bindParam(':id', $id);
                $stmt->execute();
            }
            $success = "Цены обновлены для " . count($selected) . " товаров";
            break;
    }
}

$products = $productModel->getAll();
$categories = $categoryModel->getAll();

require_once __DIR__ . '/../app/Views/admin_header.php';
?>

<h1 class="mb-4">
    <i class="bi bi-check2-square"></i> Массовые операции с товарами
</h1>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> <?= $success ?>
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

<form method="POST" id="bulkForm">
    <!-- Панель массовых операций -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-tools"></i> Выберите действие</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Действие *</label>
                    <select name="bulk_action" id="bulkAction" class="form-select" required>
                        <option value="">Выберите...</option>
                        <option value="delete">Удалить выбранные</option>
                        <option value="change_category">Изменить категорию</option>
                        <option value="update_stock">Изменить остаток</option>
                        <option value="update_price">Изменить цену</option>
                    </select>
                </div>

                <!-- Опции для смены категории -->
                <div class="col-md-3 mb-3" id="categoryOptions" style="display: none;">
                    <label class="form-label">Новая категория</label>
                    <select name="new_category" class="form-select">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= clean($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Опции для изменения остатка -->
                <div id="stockOptions" style="display: none;">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Операция</label>
                        <select name="stock_action" class="form-select">
                            <option value="set">Установить</option>
                            <option value="add">Добавить</option>
                            <option value="subtract">Вычесть</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Значение</label>
                        <input type="number" name="stock_value" class="form-control" min="0" value="0">
                    </div>
                </div>

                <!-- Опции для изменения цены -->
                <div id="priceOptions" style="display: none;">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Операция</label>
                        <select name="price_action" class="form-select">
                            <option value="increase">Увеличить на сумму</option>
                            <option value="decrease">Уменьшить на сумму</option>
                            <option value="percent_increase">Увеличить на %</option>
                            <option value="percent_decrease">Уменьшить на %</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Значение</label>
                        <input type="number" name="price_value" class="form-control" min="0" step="0.01" value="0">
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary" id="applyBtn" disabled>
                    <i class="bi bi-check2-all"></i> Применить к выбранным
                </button>
                <button type="button" class="btn btn-outline-primary" onclick="selectAll()">
                    <i class="bi bi-check-all"></i> Выбрать все
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="deselectAll()">
                    <i class="bi bi-x-circle"></i> Снять выбор
                </button>
                <a href="<?= url('admin/products.php') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Назад
                </a>
            </div>
        </div>
    </div>

    <!-- Список товаров с чекбоксами -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">
                <i class="bi bi-list-check"></i> Товары
                <span class="badge bg-light text-dark"><?= count($products) ?> шт</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllCheckbox" onclick="toggleAll(this)">
                            </th>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Категория</th>
                            <th>Цена</th>
                            <th>Склад</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_products[]"
                                       value="<?= $product['id'] ?>"
                                       class="product-checkbox"
                                       onchange="updateButton()">
                            </td>
                            <td><strong><?= $product['id'] ?></strong></td>
                            <td><?= clean($product['name']) ?></td>
                            <td><span class="badge bg-secondary"><?= clean($product['category_name']) ?></span></td>
                            <td><?= formatPrice($product['price']) ?></td>
                            <td>
                                <?php if ($product['stock'] > 0): ?>
                                    <span class="badge bg-success"><?= $product['stock'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Нет</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>

<script>
// Показать/скрыть опции в зависимости от выбранного действия
document.getElementById('bulkAction').addEventListener('change', function() {
    document.getElementById('categoryOptions').style.display = 'none';
    document.getElementById('stockOptions').style.display = 'none';
    document.getElementById('priceOptions').style.display = 'none';

    if (this.value === 'change_category') {
        document.getElementById('categoryOptions').style.display = 'block';
    } else if (this.value === 'update_stock') {
        document.getElementById('stockOptions').style.display = 'block';
    } else if (this.value === 'update_price') {
        document.getElementById('priceOptions').style.display = 'block';
    }
});

// Выбрать все
function selectAll() {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = true);
    document.getElementById('selectAllCheckbox').checked = true;
    updateButton();
}

// Снять выбор
function deselectAll() {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateButton();
}

// Переключить все
function toggleAll(checkbox) {
    document.querySelectorAll('.product-checkbox').forEach(cb => cb.checked = checkbox.checked);
    updateButton();
}

// Обновить кнопку
function updateButton() {
    const checked = document.querySelectorAll('.product-checkbox:checked').length;
    const btn = document.getElementById('applyBtn');
    btn.disabled = checked === 0;
    btn.innerHTML = `<i class="bi bi-check2-all"></i> Применить к выбранным (${checked})`;
}

// Подтверждение удаления
document.getElementById('bulkForm').addEventListener('submit', function(e) {
    const action = document.getElementById('bulkAction').value;
    const checked = document.querySelectorAll('.product-checkbox:checked').length;

    if (action === 'delete') {
        if (!confirm(`Вы уверены, что хотите удалить ${checked} товаров? Это действие необратимо!`)) {
            e.preventDefault();
        }
    }
});
</script>

<?php require_once __DIR__ . '/../app/Views/admin_footer.php'; ?>
