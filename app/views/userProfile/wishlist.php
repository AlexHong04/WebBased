<?php
require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../controllers/WishlistController.php';

$controller = new WishlistController();
$wishlistItems = $controller->index();
$totalItems = count($wishlistItems);

$toastMsg = '';
$toastType = '';

if ($msg = temp('flash_error')) {
    $toastMsg = $msg;
    $toastType = 'error';
} elseif ($msg = temp('flash_warning')) {
    $toastMsg = $msg;
    $toastType = 'warning';
} elseif ($msg = temp('flash_success')) {
    $toastMsg = $msg;
    $toastType = 'success';
}

$title = "My Wishlist";
$pageCSS = "wishlist.css?v=" . time();
include '../header.php';
?>

<link rel="stylesheet" href="/public/css/profile.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<section class="profile-section">
    <h1 class="section-title">My Profile</h1>
    <div class="profile-container">
        <div class="profile-sidebar">
            <div class="profile-avatar">
                <img id="profile-image" src="/public/images/default-avatar.png" alt="Profile Avatar">
            </div>
            <h2 id="profile-name"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></h2>
            <div class="profile-nav">
                <a href="profile.php">Profile Information</a>
                <a href="resetPassword.php">Reset Password</a>
                <a href="wishlist.php" class="active">Wishlist</a>
            </div>
        </div>

        <div class="profile-content">
            <div class="wishlist-container">

                <div class="wishlist-header-row">
                    <h3 style="margin:0; font-size: 1.2rem;">
                        My Wishlist (<span id="total-display"><?= $totalItems ?></span>)
                    </h3>

                    <div class="wishlist-search-wrapper">
                        <i class="fas fa-search wishlist-search-icon"></i>
                        <?php
                        html_text(
                            'wishlistSearchInput',
                            'class="wishlist-search-input" placeholder="Search item..." onkeyup="handleSearch()"',
                            ''
                        );
                        ?>
                    </div>
                </div>

                <?php if (empty($wishlistItems)): ?>
                    <div class="empty-wishlist">
                        <i class="far fa-heart"></i>
                        <p>Your wishlist is empty.</p>
                        <a href="/index.php" class="btn-shop">Start Shopping</a>
                    </div>
                <?php else: ?>

                    <div class="wishlist-toolbar">
                        <label class="select-all-wrapper">
                            <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                            <span>Select All</span>
                        </label>
                        <button id="batchDeleteBtn" class="btn-batch-delete" onclick="executeBatchDelete()" disabled>
                            <i class="fas fa-heart"></i> Unlike Selected (<span id="selectedCount">0</span>)
                        </button>
                    </div>

                    <div class="wishlist-grid" id="wishlistGrid">
                        <?php foreach ($wishlistItems as $item):
                            $isOOS = $item['stock_qty'] <= 0;
                            $vid = $item['product_variant_id'];
                            $keywords = strtolower($item['product_name'] . ' ' . $item['variant_name']);
                        ?>
                            <div class="wishlist-card" id="item-<?= $vid ?>" data-keywords="<?= encode($keywords) ?>">
                                <div class="card-checkbox-wrapper">
                                    <input type="checkbox" class="wishlist-checkbox" value="<?= $vid ?>" onchange="updateBatchState()">
                                </div>
                                <div class="wishlist-img-wrapper">
                                    <img src="<?= encode($item['img_url']) ?>" alt="<?= encode($item['product_name']) ?>">
                                    <?php if ($isOOS): ?>
                                        <div class="sold-out-overlay"><span>Sold Out</span></div>
                                    <?php endif; ?>
                                </div>
                                <div class="wishlist-details">
                                    <h4><?= encode($item['product_name']) ?></h4>
                                    <p class="variant-name"><?= encode($item['variant_name']) ?></p>
                                    <div class="price">RM <?= number_format($item['price'], 2) ?></div>
                                </div>
                                <div class="wishlist-actions">
                                    <?php if (!$isOOS): ?>
                                        <a href="../product/product_details.php?id=<?= $item['product_id'] ?>" class="btn-view">View Product</a>
                                    <?php else: ?>
                                        <button class="btn-view disabled">Sold Out</button>
                                    <?php endif; ?>

                                    <button type="button" class="btn-remove" onclick="removeFromWishlist('<?= $vid ?>')">
                                        <i class="fas fa-heart"></i> Unlike
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div id="noSearchResult" style="display: none; text-align: center; padding: 50px; color: #888;">
                        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; opacity: 0.5;"></i>
                        <p>No items found matching your search.</p>
                    </div>

                    <div class="pagination-container" id="paginationControls"></div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="modal-overlay-wishlist" id="deleteModalOverlay">
    <div class="modal-content-wishlist">
        <div class="modal-icon-wishlist warning">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 class="modal-title-wishlist">Unlike Item?</h2>
        <p class="modal-text-wishlist" id="modal-confirm-text">
            Are you sure you want to <b>unlike</b> this item from your wishlist?
        </p>
        <div class="modal-actions-wishlist">
            <button class="cancel-btn-wishlist" onclick="closeModal()">No</button>
            <button class="confirm-delete-btn-wishlist" id="btn-confirm-action">Yes</button>
        </div>
    </div>
</div>

<?php if ($toastMsg): ?>
    <div id="toast-notification" class="toast-notification toast-<?= $toastType ?>">
        <div class="toast-content">
            <i class="fas <?= ($toastType == 'success' ? 'fa-check-circle' : ($toastType == 'error' ? 'fa-times-circle' : 'fa-exclamation-triangle')) ?> toast-icon"></i>
            <span class="toast-message"><?= encode($toastMsg) ?></span>
        </div>
    </div>
<?php endif; ?>

<script>
    const WISHLIST_API_URL = '/app/controllers/wishlist_router.php';
    let pendingAction = null;

    const itemsPerPage = 4;
    let currentPage = 1;
    let allCards = [];
    let filteredCards = [];

    window.onload = function() {
        const grid = document.getElementById('wishlistGrid');
        if (grid) {
            allCards = Array.from(grid.getElementsByClassName('wishlist-card'));
            filteredCards = allCards;
            renderPage();
        }

        const phpToast = document.getElementById('toast-notification');
        if (phpToast) {
            setTimeout(() => {
                phpToast.style.opacity = '0';
                phpToast.style.transform = 'translateY(-20px)';
                setTimeout(() => phpToast.remove(), 500);
            }, 4000);
        }
    };

    function handleSearch() {
        const input = document.getElementById('wishlistSearchInput');
        const rawFilter = input.value.toLowerCase();

        const searchGroups = rawFilter.split(',').map(s => s.trim()).filter(s => s !== '');

        if (searchGroups.length === 0) {
            filteredCards = allCards;
        } else {
            filteredCards = allCards.filter(card => {
                const keywords = card.getAttribute('data-keywords');
                return searchGroups.some(group => keywords.includes(group));
            });
        }

        currentPage = 1;
        renderPage();
    }

    function renderPage() {
        const totalItems = filteredCards.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        const displayCount = document.getElementById('total-display');
        if (displayCount) displayCount.innerText = totalItems;

        allCards.forEach(card => card.style.display = 'none');

        const noResult = document.getElementById('noSearchResult');
        const paginationControls = document.getElementById('paginationControls');

        if (totalItems === 0 && allCards.length > 0) {
            if (noResult) noResult.style.display = 'block';
            if (paginationControls) paginationControls.innerHTML = '';
            document.getElementById('selectAll').disabled = true;
            return;
        } else {
            if (noResult) noResult.style.display = 'none';
            document.getElementById('selectAll').disabled = false;
        }

        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const itemsToShow = filteredCards.slice(start, end);

        itemsToShow.forEach(card => {
            card.style.display = 'flex';
        });

        renderPaginationHTML(totalPages);
        updateBatchState();
    }

    function renderPaginationHTML(totalPages) {
        const container = document.getElementById('paginationControls');
        container.innerHTML = '';

        if (totalPages <= 1) return;

        //First Page Button (<<)
        const firstBtn = document.createElement('a');
        firstBtn.className = `page-link ${currentPage === 1 ? 'disabled' : ''}`;
        firstBtn.innerHTML = "&laquo;"; // <<
        firstBtn.title = "Go to First Page";
        firstBtn.onclick = function() {
            if (currentPage > 1) {
                currentPage = 1;
                renderPage();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        };
        container.appendChild(firstBtn);

        // Page Numbers with (...)
        const range = 2;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - range && i <= currentPage + range)) {
                const pageBtn = document.createElement('a');
                pageBtn.className = `page-link ${i === currentPage ? 'active' : ''}`;
                pageBtn.innerText = i;
                pageBtn.onclick = function() {
                    currentPage = i;
                    renderPage();
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                };
                container.appendChild(pageBtn);
            } else if (i === currentPage - range - 1 || i === currentPage + range + 1) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'page-ellipsis';
                ellipsis.innerText = '...';
                ellipsis.style.cssText = "display: flex; align-items: flex-end; width: 30px; height: 38px; justify-content: center; padding-bottom: 5px; color: #999; letter-spacing: 2px;";
                container.appendChild(ellipsis);
            }
        }

        //Last Page Button (>>)
        const lastBtn = document.createElement('a');
        lastBtn.className = `page-link ${currentPage === totalPages ? 'disabled' : ''}`;
        lastBtn.innerHTML = "&raquo;"; // >> 
        lastBtn.title = "Go to Last Page";
        lastBtn.onclick = function() {
            if (currentPage < totalPages) {
                currentPage = totalPages;
                renderPage();
                window.scrollTo({
                    top: 5,
                    behavior: 'smooth'
                });
            }
        };
        container.appendChild(lastBtn);
    }

    function showToast(message, type = 'success') {
        const oldToast = document.getElementById('js-toast');
        if (oldToast) oldToast.remove();
        const div = document.createElement('div');
        div.id = 'js-toast';
        div.className = `toast-notification toast-${type}`;
        let iconClass = 'fa-check-circle';
        if (type === 'error') iconClass = 'fa-times-circle';
        if (type === 'warning') iconClass = 'fa-exclamation-triangle';
        div.innerHTML = `<div class="toast-content"><i class="fas ${iconClass} toast-icon"></i><span class="toast-message">${message}</span></div>`;
        document.body.appendChild(div);
        setTimeout(() => {
            div.style.opacity = '0';
            div.style.transform = 'translateY(-20px)';
            setTimeout(() => div.remove(), 500);
        }, 4000);
    }

    const modalOverlay = document.getElementById('deleteModalOverlay');

    function openModal(message, actionCallback) {
        if (message) document.getElementById('modal-confirm-text').innerHTML = message;
        modalOverlay.classList.add('show');
        pendingAction = actionCallback;
    }

    function closeModal() {
        modalOverlay.classList.remove('show');
        setTimeout(() => {
            pendingAction = null;
        }, 200);
    }
    document.getElementById('btn-confirm-action').addEventListener('click', function() {
        if (pendingAction) pendingAction();
        closeModal();
    });
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    function removeFromWishlist(variantId) {
        openModal("Are you sure you want to <b>unlike</b> this item from your wishlist?", function() {
            const formData = new URLSearchParams();
            formData.append('product_variant_id', variantId);
            fetch(WISHLIST_API_URL + '?action=toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.status === 'removed') {
                        removeCardFromDOM(variantId);
                        showToast("Unliked from wishlist", "success");
                    } else {
                        showToast(data.message || "Failed to unlike.", "error");
                    }
                }).catch(err => {
                    console.error(err);
                    showToast("An error occurred.", "error");
                });
        });
    }

    function executeBatchDelete() {
        const checked = document.querySelectorAll('.wishlist-checkbox:checked');
        if (checked.length === 0) return;
        openModal(`Are you sure you want to <b>unlike ${checked.length} items</b>?`, function() {
            const ids = Array.from(checked).map(cb => cb.value);
            const formData = new URLSearchParams();
            ids.forEach(id => formData.append('product_variant_ids[]', id));
            fetch(WISHLIST_API_URL + '?action=delete_batch', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        ids.forEach(id => removeCardFromDOM(id));
                        showToast("Selected items unliked successfully", "success");
                    } else {
                        showToast(data.message || "Batch unlike failed.", "error");
                    }
                }).catch(err => {
                    console.error(err);
                    showToast("An error occurred.", "error");
                });
        });
    }

    function removeCardFromDOM(variantId) {
        const card = document.getElementById('item-' + variantId);
        if (card) {
            allCards = allCards.filter(c => c !== card);
            filteredCards = filteredCards.filter(c => c !== card);

            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';

            setTimeout(() => {
                card.remove();

                const newTotalPages = Math.ceil(filteredCards.length / itemsPerPage);

                if (currentPage > newTotalPages && newTotalPages > 0) {
                    currentPage = newTotalPages;
                }

                renderPage();

                if (allCards.length === 0) {
                    location.reload();
                }
            }, 300);
        }
    }

    function toggleSelectAll() {
        const mainCb = document.getElementById('selectAll');
        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const visibleCards = filteredCards.slice(start, end);
        visibleCards.forEach(card => {
            const cb = card.querySelector('.wishlist-checkbox');
            if (cb) cb.checked = mainCb.checked;
        });
        updateBatchState();
    }

    function updateBatchState() {
        const checked = document.querySelectorAll('.wishlist-checkbox:checked');
        const btn = document.getElementById('batchDeleteBtn');
        const countSpan = document.getElementById('selectedCount');
        const selectAllCb = document.getElementById('selectAll');

        if (countSpan) countSpan.innerText = checked.length;

        if (checked.length > 0) {
            btn.disabled = false;
            btn.classList.add('active');
        } else {
            btn.disabled = true;
            btn.classList.remove('active');
        }

        const start = (currentPage - 1) * itemsPerPage;
        const end = start + itemsPerPage;
        const visibleCards = filteredCards.slice(start, end);
        if (visibleCards.length > 0) {
            const allVisibleChecked = visibleCards.every(card => {
                const cb = card.querySelector('.wishlist-checkbox');
                return cb && cb.checked;
            });
            selectAllCb.checked = allVisibleChecked;
        } else {
            selectAllCb.checked = false;
        }
    }
</script>

<?php include '../footer.php' ?>