    <?php $site_settings = getSettings(); ?>
    <footer class="footer mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="bi bi-watch"></i> <?= clean($site_settings['site_name']) ?></h5>
                    <p>Сапалы сағаттарды өндіру және сату жөніндегі компания.</p>
                </div>
                <div class="col-md-4">
                    <h5>Байланыс</h5>
                    <p>
                        <i class="bi bi-telephone"></i> <?= clean($site_settings['site_phone']) ?><br>
                        <i class="bi bi-envelope"></i> <?= clean($site_settings['site_email']) ?><br>
                        <i class="bi bi-geo-alt"></i> <?= clean($site_settings['site_address']) ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <h5>Жұмыс уақыты</h5>
                    <p>
                        Дүйсенбі - Сенбі: 9:00 - 20:00<br>
                        Жексенбі: 10:00 - 18:00
                    </p>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p class="mb-0">&copy; <?= date('Y') ?> <?= clean($site_settings['site_name']) ?>. Барлық құқықтар қорғалған.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Обновление счётчиков при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        updateWishlistCount();
        updateCompareCount();
        initSearchAutocomplete();
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

    // Автодополнение поиска
    function initSearchAutocomplete() {
        const searchInput = document.getElementById('searchInput');
        const autocompleteDiv = document.getElementById('searchAutocomplete');

        if (!searchInput || !autocompleteDiv) return;

        let searchTimeout;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            clearTimeout(searchTimeout);

            if (query.length < 2) {
                autocompleteDiv.style.display = 'none';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`<?= url('public/ajax_search.php') ?>?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(products => {
                        if (products.length === 0) {
                            autocompleteDiv.innerHTML = '<div class="search-autocomplete-item text-muted">Ештеңе табылмады</div>';
                            autocompleteDiv.style.display = 'block';
                            return;
                        }

                        let html = '';
                        products.forEach(product => {
                            html += `
                                <div class="search-autocomplete-item" onclick="window.location.href='<?= url('public/product.php') ?>?id=${product.id}'">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-smartwatch fs-4 text-primary me-2"></i>
                                        <div class="flex-grow-1">
                                            <strong>${product.name}</strong>
                                            <br>
                                            <small class="text-muted">${product.category_name}</small>
                                        </div>
                                        <strong class="text-primary">${formatPrice(product.price)}</strong>
                                    </div>
                                </div>
                            `;
                        });

                        autocompleteDiv.innerHTML = html;
                        autocompleteDiv.style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                    });
            }, 300);
        });

        // Закрыть при клике вне поля
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !autocompleteDiv.contains(e.target)) {
                autocompleteDiv.style.display = 'none';
            }
        });
    }

    function formatPrice(price) {
        return new Intl.NumberFormat('kk-KZ').format(price) + ' ₸';
    }
    </script>
</body>
</html>
