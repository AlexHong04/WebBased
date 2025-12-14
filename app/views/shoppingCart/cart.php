<?php

require_once __DIR__ . '/../../controllers/shoppingCartController.php';
require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../helpers/request.php';


$controller = new shoppingCartController();
$data = $controller->getCartData();
extract($data);

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

include '../header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="main-cart-wrapper">

    <a href="index.php" class="back-to-shop">
        <i class="fa fa-store"></i> Back to Shop
    </a>

    <form id="cartForm" action="/app/views/shoppingCart/checkout.php" method="POST">

        <div class="cart-container" id="mainCartWrapper" style="display: <?= $isEmpty ? 'none' : 'flex' ?>;">

            <div class="cart-items-section">
                <div class="cart-toolbar">
                    <div class="cart-search-wrapper">
                        <i class="fas fa-search cart-search-icon"></i>
                        <input type="text"
                            id="cartSearchInput"
                            class="cart-search-input"
                            placeholder="Search items in cart (Use comma ',' for multiple keywords)..."
                            onkeyup="filterCart()">
                    </div>

                    <div class="batch-action-bar" style="margin-bottom: 10px; display: none;" id="batchActionBar">
                        <button type="button" class="batch-delete-btn" onclick="confirmBatchDelete()">
                            <i class="fas fa-trash-alt"></i> Delete Selected (<span id="batchCount">0</span>)
                        </button>
                    </div>
                </div>

                <table class="cart-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                            </th>
                            <th>Product</th>
                            <th>Price(RM)</th>
                            <th>Quantity</th>
                            <th>Total(RM)</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <?php foreach ($cartItems as $item): ?>
                            <?php
                            $isOOS = $item['stock_qty'] <= 0;
                            $rowClass = $isOOS ? 'cart-row out-of-stock' : 'cart-row';
                            $pid = encode($item['product_id']);
                            ?>
                            <tr class="<?= $rowClass ?>" data-price="<?= encode($item['price']) ?>">
                                <td>
                                    <input type="checkbox"
                                        class="item-checkbox"
                                        name="selected_items[]"
                                        value="<?= $pid ?>"
                                        onchange="calculateTotal()"
                                        <?= $isOOS ? 'disabled' : '' ?>>
                                </td>
                                <td>
                                    <div class="product-info">
                                        <img src="<?= encode($item['img_url']) ?>" alt="Img">
                                        <div class="product-name"><?= encode($item['product_name']) ?></div>
                                    </div>
                                    <?php if ($isOOS): ?>
                                        <div class="oos-badge">Out of Stock</div>
                                    <?php endif; ?>
                                </td>
                                <td class="unit-price"><?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <?php if ($isOOS): ?>
                                        <div style="text-align:center; color:#aaa; font-weight:bold;">-</div>
                                        <input type="hidden" class="qty-input" value="0">
                                    <?php else: ?>
                                        <div class="qty-selector">
                                            <button type="button" class="qty-btn" onclick="updateQty(this, -1, '<?= $pid ?>')">-</button>

                                            <input type="number"
                                                name="qty[<?= $pid ?>]"
                                                class="qty-input"
                                                value="<?= encode($item['quantity']) ?>"
                                                onchange="calculateTotal()"
                                                min="1"
                                                max="<?= encode($item['stock_qty']) ?>"
                                                readonly>

                                            <button type="button" class="qty-btn" onclick="updateQty(this, 1, '<?= $pid ?>')">+</button>
                                        </div>
                                        <div style="font-size: 0.8rem; color: #999; text-align:center; margin-top:5px;">
                                            Left: <?= encode($item['stock_qty']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="item-subtotal">
                                        <?= $isOOS ? '-' : '<span class="row-total">' . number_format($item['price'] * $item['quantity'], 2) . '</span>' ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="delete-btn" onclick="removeItem(this, '<?= $pid ?>')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div id="noSearchResult" style="display: none; text-align: center; padding: 30px; color: #888; font-size: 1.1rem;">
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                    No item found!
                </div>

            </div>

            <div class="cart-summary-section">
                <div class="summary-header">Summary</div>
                <div class="summary-items-list" id="summaryItemsList"></div>
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>RM <span id="cartSubtotal">0.00</span></span>
                </div>
                <div class="summary-row">
                    <span>Shipping Fee</span>
                    <span>RM <span id="shippingFee"><?= number_format($deliveryFee, 2) ?></span></span>
                </div>
                <div class="grand-total-area">
                    <div class="grand-total-row">
                        <span class="grand-total-label">Total</span>
                        <span class="total-price">RM <span id="grandTotal">0.00</span></span>
                    </div>
                </div>
                <button type="submit" class="checkout-btn" id="checkoutBtn" disabled>
                    <i class="fas fa-credit-card"></i> PROCEED TO CHECKOUT (<span id="selectedCount">0</span>)
                </button>
            </div>
        </div>
    </form>

    <div class="empty-cart-section" id="emptyCartState" style="display: <?= $isEmpty ? 'block' : 'none' ?>;">
        <div class="empty-icon"><i class="fas fa-shopping-basket"></i></div>
        <h2 class="empty-title">Your cart is currently empty</h2>
        <p class="empty-text">Looks like you haven't made your choice yet.</p>
        <a href="" class="continue-shopping-btn">
            <div style="margin-left: 30px;"><i class="fas fa-arrow-left"></i>&nbsp Start Shopping</div>
        </a>
    </div>

</div>

<div class="modal-overlay" id="deleteModalOverlay" style="display: none;">
    <div class="modal-content delete-modal-content">
        <div class="modal-icon warning">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h3 class="modal-title">Remove Item?</h3>
        <p class="modal-text">
            Are you sure<br> you want to <b>remove</b>
            this item from your cart?
        </p>
        <div class="modal-actions">
            <button class="cancel-btn" onclick="closeDeleteModal()">No, Keep it</button>
            <button class="confirm-delete-btn" onclick="executeDelete()" data-mode="" data-target-id="">Yes, Remove</button>
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
    const deliveryFee = <?= $deliveryFee ?>;
    let debounceTimer;
    const BASE_API_URL = '/app/controllers/cart_router.php';

    let rowToDelete = null;

    window.onload = function() {
        if (document.getElementById('cartTableBody').children.length > 0) {
            calculateTotal();
        }
        refreshCartCount();

        const phpToast = document.getElementById('toast-notification');
        if (phpToast) {
            setTimeout(() => {
                phpToast.style.opacity = '0';
                phpToast.style.transform = 'translateY(-20px)';
                setTimeout(() => phpToast.remove(), 500);
            }, 4000);
        }
    };

    function refreshCartCount() {
        fetch(BASE_API_URL + '?action=count')
            .then(response => response.text())
            .then(data => {
                const cartCountEl = document.getElementById('globalCartCount');
                if (cartCountEl) {
                    cartCountEl.innerText = data;
                    cartCountEl.style.display = (parseInt(data) > 0) ? '' : 'none';
                }
            })
            .catch(err => console.error('Failed to update cart count', err));
    }

    function updateQty(btn, change, variantId) {
        const row = btn.closest('tr');
        const input = row.querySelector('.qty-input');
        let currentVal = parseInt(input.value);
        let maxStock = parseInt(input.getAttribute('max'));
        let newVal = currentVal + change;

        if (newVal < 1) newVal = 1;
        if (newVal > maxStock) {
            showToast("Sorry, maximum stock available is " + maxStock, 'warning');
            newVal = maxStock;
        }

        if (newVal !== currentVal) {
            input.value = newVal;
            const price = parseFloat(row.dataset.price);
            row.querySelector('.row-total').innerText = (price * newVal).toFixed(2);

            calculateTotal();

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                sendUpdateToDatabase(variantId, newVal);
            }, 500);
        }
    }

    function sendUpdateToDatabase(variantId, quantity) {
        const url = BASE_API_URL + '?action=update';
        fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 'product_variant_id': variantId, 'quantity': quantity })
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes("Success")) {
                    refreshCartCount();
                } else {
                    showToast("Failed to update cart.", 'error');
                }
            })
            .catch(error => console.error(error));
    }

    function filterCart() {
        const input = document.getElementById('cartSearchInput');
        const rawFilter = input.value.toLowerCase();
        const searchGroups = rawFilter.split(',').map(s => s.trim()).filter(s => s !== '');
        const rows = document.querySelectorAll('.cart-row');
        let hasVisibleItems = false;

        if (searchGroups.length === 0) {
            rows.forEach(row => row.style.display = "");
            hasVisibleItems = true;
        } else {
            rows.forEach(row => {
                const nameElement = row.querySelector('.product-name');
                if (nameElement) {
                    const txtValue = (nameElement.textContent || nameElement.innerText).toLowerCase();
                    let isMatch = false;
                    for (let group of searchGroups) {
                        const keywords = group.split(/\s+/).filter(k => k !== '');
                        if (keywords.every(keyword => txtValue.includes(keyword))) {
                            isMatch = true;
                            break;
                        }
                    }
                    if (isMatch) {
                        row.style.display = "";
                        hasVisibleItems = true;
                    } else {
                        row.style.display = "none";
                    }
                }
            });
        }

        const noResultMsg = document.getElementById('noSearchResult');
        if (!noResultMsg && !hasVisibleItems) {
            const itemsSection = document.querySelector('.cart-items-section');
            const msgDiv = document.createElement('div');
            msgDiv.id = 'noSearchResult';
            msgDiv.style.cssText = 'text-align: center; padding: 30px; color: #888; font-size: 1.1rem;';
            msgDiv.innerHTML = '<i class="fas fa-search" style="font-size: 2rem; margin-bottom: 10px; display: block; opacity: 0.5;"></i>No item found!';
            itemsSection.appendChild(msgDiv);
        } else if (noResultMsg) {
            noResultMsg.style.display = hasVisibleItems ? 'none' : 'block';
        }

        calculateTotal();
    }

    function calculateTotal() {
        let subtotal = 0;
        let count = 0;
        const rows = document.querySelectorAll('.cart-row');
        const summaryList = document.getElementById('summaryItemsList');
        summaryList.innerHTML = '';

        rows.forEach(row => {
            const checkbox = row.querySelector('.item-checkbox');
            if (checkbox && checkbox.checked && !checkbox.disabled && row.isConnected && row.style.display !== 'none') {
                const price = parseFloat(row.dataset.price);
                const qtyInput = row.querySelector('.qty-input');
                const qty = qtyInput ? parseInt(qtyInput.value) : 0;
                const name = row.querySelector('.product-name').innerText;

                if (qty > 0) {
                    subtotal += price * qty;
                    count++;
                    summaryList.innerHTML += `
                    <div class="summary-item-row" style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:0.9rem;">
                        <span class="summary-item-name">${name} <span class="summary-item-qty" style="color:#999;">x${qty}</span></span>
                        <span>RM ${(price * qty).toFixed(2)}</span>
                    </div>`;
                }
            }
        });

        document.getElementById('cartSubtotal').innerText = subtotal.toFixed(2);
        document.getElementById('selectedCount').innerText = count;
        let finalShipping = (count > 0) ? deliveryFee : 0;
        document.getElementById('shippingFee').innerText = finalShipping.toFixed(2);
        document.getElementById('grandTotal').innerText = (subtotal + finalShipping).toFixed(2);

        const activeCheckboxes = Array.from(document.querySelectorAll('.item-checkbox')).filter(cb => !cb.disabled && cb.closest('tr').style.display !== 'none');
        const checkedActiveCheckboxes = activeCheckboxes.filter(cb => cb.checked);
        const allChecked = activeCheckboxes.length > 0 && activeCheckboxes.length === checkedActiveCheckboxes.length;
        const selectAllCb = document.getElementById('selectAll');
        if (selectAllCb) selectAllCb.checked = (activeCheckboxes.length > 0) && allChecked;

        const btn = document.getElementById('checkoutBtn');
        if (btn) {
            btn.disabled = count === 0;
            btn.style.opacity = (count === 0) ? '0.6' : '1';
        }

        const checkedCount = checkedActiveCheckboxes.length;
        const batchBar = document.getElementById('batchActionBar');
        const batchCountSpan = document.getElementById('batchCount');
        
        if (batchBar && batchCountSpan) {
            batchCountSpan.innerText = checkedCount;
            batchBar.style.display = (checkedCount > 0) ? 'block' : 'none';
        }
    }

    function toggleSelectAll() {
        const mainCb = document.getElementById('selectAll');
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            if (!cb.disabled && cb.closest('tr').style.display !== 'none') {
                cb.checked = mainCb.checked;
            }
        });
        calculateTotal();
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

    function removeItem(btn, variantId) {
        const confirmBtn = document.querySelector('.confirm-delete-btn');
        confirmBtn.dataset.targetId = variantId;
        confirmBtn.dataset.mode = 'single'; 

        rowToDelete = btn.closest('tr');

        document.getElementById('deleteModalOverlay').style.display = 'flex';
    }

    function confirmBatchDelete() {
        const confirmBtn = document.querySelector('.confirm-delete-btn');
        confirmBtn.dataset.mode = 'batch';
        confirmBtn.dataset.targetId = ''; 
        
        document.getElementById('deleteModalOverlay').style.display = 'flex';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModalOverlay').style.display = 'none';
    }

    function executeDelete() {
        const confirmBtn = document.querySelector('.confirm-delete-btn');
        const mode = confirmBtn.dataset.mode;
        const currentId = confirmBtn.dataset.targetId;

        closeDeleteModal();

        if (mode === 'batch') {
            executeBatchDeleteAction();
        } else {
            if (!currentId) {
                console.error("Error: No ID found on confirm button!");
                return;
            }

            const url = BASE_API_URL + '?action=delete';
            const currentRow = rowToDelete; 

            if (currentRow) {
                currentRow.style.transition = 'all 0.3s ease';
                currentRow.style.opacity = '0';
                currentRow.style.transform = 'translateX(20px)';
            }

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ 'product_variant_id': currentId })
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim().includes("Success")) {
                    showToast("Item removed.", 'success');
                    setTimeout(() => {
                        if (currentRow) currentRow.remove();
                        
                        const tbody = document.getElementById('cartTableBody');
                        if (!tbody || tbody.children.length === 0 || tbody.querySelectorAll('tr').length === 0) {
                            showEmptyState();
                        } else {
                            if (typeof calculateTotal === 'function') calculateTotal();
                            if (typeof filterCart === 'function') filterCart();
                        }
                        refreshCartCount();
                    }, 300);
                } else {
                    console.error("Delete Failed:", data);
                    showToast("Failed to delete.", 'error');
                    if (currentRow) {
                        currentRow.style.opacity = '1';
                        currentRow.style.transform = 'none';
                    }
                }
            })
            .catch(err => {
                console.error("Network Error:", err);
                showToast("Network error.", 'error');
                if (currentRow) {
                    currentRow.style.opacity = '1';
                    currentRow.style.transform = 'none';
                }
            });
        }
    }

    function executeBatchDeleteAction() {
        const checkboxes = document.querySelectorAll('.item-checkbox:checked');
        const idsToDelete = Array.from(checkboxes).map(cb => cb.value);

        if (idsToDelete.length === 0) return;

        const formData = new URLSearchParams();
        idsToDelete.forEach(id => formData.append('product_variant_ids[]', id));

        fetch(BASE_API_URL + '?action=delete_batch', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                if (data.trim().includes("Success")) {
                    showToast(idsToDelete.length + " items removed.", 'success');
                    checkboxes.forEach(cb => {
                        const row = cb.closest('tr');
                        row.remove();
                    });

                    const tbody = document.getElementById('cartTableBody');
                    if (!tbody || tbody.children.length === 0) {
                        showEmptyState();
                    } else {
                        calculateTotal();
                        filterCart();
                    }
                    refreshCartCount();

                } else {
                    showToast("Failed to delete batch.", 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast("Network error.", 'error');
            });
    }
    
    function showEmptyState() {
        document.getElementById('mainCartWrapper').style.display = 'none';
        document.getElementById('emptyCartState').style.display = 'block';
    }
</script>

<?php include '../footer.php' ?>