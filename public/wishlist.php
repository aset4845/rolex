<?php
require_once __DIR__ . '/../app/Models/Product.php';

$page_title = 'Таңдаулылар - Rolex';
$productModel = new Product();

require_once __DIR__ . '/../app/Views/header.php';
?>

<div class="container my-5">
    <h1 class="mb-4"><i class="bi bi-heart-fill text-danger"></i> Менің таңдаулыларым</h1>

    <div id="wishlist-container">
        <div class="text-center py-5" id="loading">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Жүктелуде...</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadWishlist();
});

function loadWishlist() {
    const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    const container = document.getElementById('wishlist-container');

    if (wishlist.length === 0) {
        container.innerHTML = `
            <div class="alert alert-info text-center py-5">
                <i class="bi bi-heart fs-1 text-muted"></i>
                <h4 class="mt-3">Таңдаулылар тізімі бос</h4>
                <p class="text-muted">Өнімдерді таңдаулыларға қосу үшін жүрек белгісін басыңыз</p>
                <a href="<?= url('public/products.php') ?>" class="btn btn-primary mt-3">
                    <i class="bi bi-shop"></i> Каталогқа өту
                </a>
            </div>
        `;
        return;
    }

    // Загрузить товары через AJAX
    fetch('<?= url('public/ajax_wishlist.php') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ids: wishlist})
    })
    .then(response => response.json())
    .then(products => {
        if (products.length === 0) {
            container.innerHTML = `
                <div class="alert alert-warning">Өнімдер табылмады</div>
            `;
            return;
        }

        let html = '<div class="row">';
        products.forEach(product => {
            html += `
                <div class="col-md-3 mb-4" data-product-id="${product.id}">
                    <div class="card h-100 shadow-sm">
                        <div class="product-image d-flex align-items-center justify-content-center">
                            <i class="bi bi-smartwatch display-1 text-white"></i>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${product.name}</h5>
                            <p class="text-muted small">${product.category_name}</p>
                            <p class="mb-3">
                                <strong class="text-primary fs-5">${formatPrice(product.price)}</strong>
                            </p>
                            <div class="mt-auto">
                                <div class="d-grid gap-2">
                                    <a href="<?= url('public/product.php') ?>?id=${product.id}"
                                       class="btn btn-primary btn-sm">
                                        <i class="bi bi-eye"></i> Көру
                                    </a>
                                    <button class="btn btn-danger btn-sm" onclick="removeFromWishlist(${product.id})">
                                        <i class="bi bi-trash"></i> Жою
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;
    })
    .catch(error => {
        container.innerHTML = `
            <div class="alert alert-danger">Қате орын алды: ${error.message}</div>
        `;
    });
}

function removeFromWishlist(productId) {
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    wishlist = wishlist.filter(id => id != productId);
    localStorage.setItem('wishlist', JSON.stringify(wishlist));

    // Удалить карточку из DOM
    document.querySelector(`[data-product-id="${productId}"]`).remove();

    // Если пусто, показать сообщение
    if (wishlist.length === 0) {
        loadWishlist();
    }

    updateWishlistCount();
}

function formatPrice(price) {
    return new Intl.NumberFormat('kk-KZ').format(price) + ' ₸';
}

function updateWishlistCount() {
    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
    const badge = document.querySelector('.wishlist-count');
    if (badge) {
        badge.textContent = wishlist.length;
        badge.style.display = wishlist.length > 0 ? 'inline' : 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../app/Views/footer.php'; ?>
