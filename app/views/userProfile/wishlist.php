<?php
require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../controllers/WishlistController.php';

$controller = new WishlistController();
$wishlistItems = $controller->index();

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
                <h3 style="margin-bottom: 20px; font-size: 1.2rem; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    My Wishlist (<?= count($wishlistItems) ?>)
                </h3>

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
                            <i class="fas fa-trash-alt"></i> Unlike Selected (<span id="selectedCount">0</span>)
                        </button>
                    </div>

                    <div class="wishlist-grid">
                        <?php foreach ($wishlistItems as $item):
                            $isOOS = $item['stock_qty'] <= 0;
                            $vid = $item['product_variant_id'];
                        ?>
                            <div class="wishlist-card" id="item-<?= $vid ?>">
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
                                        <i class="fas fa-trash-alt"></i> Unlike
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="modal-overlay" id="deleteModalOverlay">
    <div class="modal-content">
        <div class="modal-icon warning">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        
        <h2 class="modal-title">Unlike Item?</h2>
        
        <p class="modal-text" id="modal-confirm-text">
            Are you sure you want to <b>unlike</b> this item from your wishlist?
        </p>
        
        <div class="modal-actions">
            <button class="cancel-btn" onclick="closeModal()">No, Keep it</button>
            <button class="confirm-delete-btn" id="btn-confirm-action">Yes, Unlike</button>
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

    window.onload = function() {
        const phpToast = document.getElementById('toast-notification');
        if (phpToast) {
            setTimeout(() => {
                phpToast.style.opacity = '0';
                phpToast.style.transform = 'translateY(-20px)';
                setTimeout(() => phpToast.remove(), 500);
            }, 4000);
        }
    };

    function showToast(message, type = 'success') {
        const oldToast = document.getElementById('js-toast');
        if (oldToast) oldToast.remove();

        const div = document.createElement('div');
        div.id = 'js-toast';
        div.className = `toast-notification toast-${type}`;

        let iconClass = 'fa-check-circle';
        if (type === 'error') iconClass = 'fa-times-circle';
        if (type === 'warning') iconClass = 'fa-exclamation-triangle';

        div.innerHTML = `
            <div class="toast-content">
                <i class="fas ${iconClass} toast-icon"></i>
                <span class="toast-message">${message}</span>
            </div>
        `;
        document.body.appendChild(div);

        setTimeout(() => {
            div.style.opacity = '0';
            div.style.transform = 'translateY(-20px)';
            setTimeout(() => div.remove(), 500);
        }, 4000);
    }

    const modalOverlay = document.getElementById('deleteModalOverlay');

    function openModal(message, actionCallback) {
        if(message) {
            document.getElementById('modal-confirm-text').innerHTML = message;
        }
        modalOverlay.classList.add('show');
        pendingAction = actionCallback;
    }

    function closeModal() {
        modalOverlay.classList.remove('show');
        setTimeout(() => { pendingAction = null; }, 200);
    }

    document.getElementById('btn-confirm-action').addEventListener('click', function() {
        if (pendingAction) pendingAction();
        closeModal();
    });

    modalOverlay.addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    function removeFromWishlist(variantId) {
        const msg = "Are you sure you want to <b>unlike</b> this item from your wishlist?";
        
        openModal(msg, function() {
            const formData = new URLSearchParams();
            formData.append('product_variant_id', variantId);

            fetch(WISHLIST_API_URL + '?action=toggle', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
            })
            .catch(err => {
                console.error(err);
                showToast("An error occurred.", "error");
            });
        });
    }

    function executeBatchDelete() {
        const checked = document.querySelectorAll('.wishlist-checkbox:checked');
        if (checked.length === 0) return;

        const msg = `Are you sure you want to <b>unlike ${checked.length} items</b> from your wishlist?`;

        openModal(msg, function() {
            const ids = Array.from(checked).map(cb => cb.value);
            const formData = new URLSearchParams();
            ids.forEach(id => formData.append('product_variant_ids[]', id));

            fetch(WISHLIST_API_URL + '?action=delete_batch', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
            })
            .catch(err => {
                console.error(err);
                showToast("An error occurred.", "error");
            });
        });
    }

    function removeCardFromDOM(variantId) {
        const card = document.getElementById('item-' + variantId);
        if (card) {
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'translateX(20px)';
            setTimeout(() => {
                card.remove();
                checkEmptyState();
                updateBatchState(); 
            }, 300);
        }
    }

    function checkEmptyState() {
        if (document.querySelectorAll('.wishlist-card').length === 0) {
            location.reload();
        }
    }

    function toggleSelectAll() {
        const mainCb = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.wishlist-checkbox');
        checkboxes.forEach(cb => cb.checked = mainCb.checked);
        updateBatchState();
    }

    function updateBatchState() {
        const checkboxes = document.querySelectorAll('.wishlist-checkbox');
        const checked = document.querySelectorAll('.wishlist-checkbox:checked');
        const btn = document.getElementById('batchDeleteBtn');
        const countSpan = document.getElementById('selectedCount');
        const selectAllCb = document.getElementById('selectAll');

        if(countSpan) countSpan.innerText = checked.length;

        if (checked.length > 0) {
            btn.disabled = false;
            btn.classList.add('active');
        } else {
            btn.disabled = true;
            btn.classList.remove('active');
        }

        if (checkboxes.length > 0 && checkboxes.length === checked.length) {
            selectAllCb.checked = true;
        } else {
            selectAllCb.checked = false;
        }
    }
</script>

<?php include '../footer.php' ?>