<?php
require_once __DIR__ . '/../app/Models/Product.php';

$page_title = 'Салыстыру - Rolex';
$productModel = new Product();

require_once __DIR__ . '/../app/Views/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4"><i class="bi bi-arrow-left-right text-primary"></i> Өнімдерді салыстыру</h1>

    <div id="compare-container">
        <div class="text-center py-5" id="loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Жүктелуде...</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadCompare();
});

function loadCompare() {
    const compareList = JSON.parse(localStorage.getItem('compare') || '[]');
    const container = document.getElementById('compare-container');

    if (compareList.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info text-center py-5">
                <i class="bi bi-arrow-left-right fs-1 text-muted"></i>
                <h4 class="mt-3">Салыстыру тізімі бос</h4>
                <p class="text-muted">Өнімдерді салыстыруға қосу үшін "Салыстыруға қосу" түймесін басыңыз</p>
                <a href="<?= url('public/products.php') ?>" class="btn btn-primary mt-3">
                    <i class="bi bi-shop"></i> Каталогқа өту
                </a>
            </div>
        `;
        updateCompareCount();
        return;
    }

    // Загрузить товары через AJAX
    fetch('<?= url('public/ajax_wishlist.php') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ids: compareList})
    })
    .then(response => response.json())
    .then(products => {
        if (products.length === 0) {
            container.innerHTML = `<div class="alert alert-warning">Өнімдер табылмады</div>`;
            return;
        }

        let html = `
            <div class="alert alert-warning alert-dismissible fade show">
                <strong>Ескерту:</strong> Ең көбі 4 өнімді салыстыруға болады.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 150px;">Сипаттама</th>
        `;

        products.forEach(product => {
            html += `<th class="text-center">${product.name}</th>`;
        });

        html += `</tr></thead><tbody>`;

        // Изображение
        html += `<tr><td class="fw-bold">Сурет</td>`;
        products.forEach(product => {
            html += `
                <td class="text-center">
                    <div style="height: 150px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
                         class="d-flex align-items-center justify-content-center rounded">
                        <i class="bi bi-smartwatch display-4 text-white"></i>
                    </div>
                </td>
            `;
        });
        html += `</tr>`;

        // Цена
        html += `<tr><td class="fw-bold">Бағасы</td>`;
        products.forEach(product => {
            html += `<td class="text-center"><h4 class="text-primary">${formatPrice(product.price)}</h4></td>`;
        });
        html += `</tr>`;

        // Категория
        html += `<tr><td class="fw-bold">Санаты</td>`;
        products.forEach(product => {
            html += `<td class="text-center">${product.category_name}</td>`;
        });
        html += `</tr>`;

        // Наличие
        html += `<tr><td class="fw-bold">Қоймада</td>`;
        products.forEach(product => {
            if (product.stock > 0) {
                html += `<td class="text-center"><span class="badge bg-success">Бар (${product.stock})</span></td>`;
            } else {
                html += `<td class="text-center"><span class="badge bg-danger">Жоқ</span></td>`;
            }
        });
        html += `</tr>`;

        // Действия
        html += `<tr><td class="fw-bold">Әрекеттер</td>`;
        products.forEach(product => {
            html += `
                <td class="text-center">
                    <div class="d-grid gap-2">
                        <a href="<?= url('public/product.php') ?>?id=${product.id}"
                           class="btn btn-sm btn-primary">
                            <i class="bi bi-eye"></i> Көру
                        </a>
                        <button class="btn btn-sm btn-danger" onclick="removeFromCompare(${product.id})">
                            <i class="bi bi-trash"></i> Жою
                        </button>
                    </div>
                </td>
            `;
        });
        html += `</tr>`;

        html += `</tbody></table></div>`;

        html += `
            <div class="mt-3">
                <button class="btn btn-danger" onclick="clearCompare()">
                    <i class="bi bi-x-circle"></i> Барлығын тазалау
                </button>
                <a href="<?= url('public/products.php') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Каталогқа оралу
                </a>
            </div>
        `;

        container.innerHTML = html;
        updateCompareCount();
    })
    .catch(error => {
        container.innerHTML = `<div class="alert alert-danger">Қате орын алды: ${error.message}</div>`;
    });
}

function removeFromCompare(productId) {
    let compareList = JSON.parse(localStorage.getItem('compare') || '[]');
    compareList = compareList.filter(id => id != productId);
    localStorage.setItem('compare', JSON.stringify(compareList));
    loadCompare();
}

function clearCompare() {
    if (confirm('Барлық өнімдерді тізімнен жоямыз ба?')) {
        localStorage.removeItem('compare');
        loadCompare();
    }
}

function formatPrice(price) {
    return new Intl.NumberFormat('kk-KZ').format(price) + ' ₸';
}

function updateCompareCount() {
    let compareList = JSON.parse(localStorage.getItem('compare') || '[]');
    const badge = document.querySelector('.compare-count');
    if (badge) {
        badge.textContent = compareList.length;
        badge.style.display = compareList.length > 0 ? 'inline' : 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../app/Views/footer.php'; ?>
