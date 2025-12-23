<?php
require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../controllers/checkoutController.php';
require_once __DIR__ . '/../../helpers/auth.php';

authenticate();
$controller = new CheckoutController();
$data = $controller->getCheckoutData();
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

$currentUser = $customer_info;
$checkoutItems = $items;
$initialAddressId = $currentUser['address_id'] ?? '';
$userPoints = $data['user_points'] ?? 0;

include __DIR__ . '/../header.php';
?>

<?php
$GLOBALS['address_id'] = $initialAddressId;
$GLOBALS['receiver_name'] = $currentUser['username'] ?? '';
$GLOBALS['receiver_phone'] = $currentUser['phone'] ?? '';
$GLOBALS['receiver_address'] = $currentUser['address'] ?? '';
$GLOBALS['points_redeemed'] = 0;
$GLOBALS['discount_amount'] = 0;
?>


<?php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$apiPath = dirname(dirname($scriptDir)) . '/controllers/address_router.php';
$fullApiUrl = $protocol . $host . $apiPath;
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="/public/css/checkout.css">

<div class="checkout-wrapper">
    <div class="container">

        <div class="main-section">
            <a href="cart.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Cart
            </a>

            <form action="/app/controllers/checkout_router.php?action=placeOrder" method="POST" id="checkoutForm">
                <div class="card">
                    <h2 class="section-title">
                        <i class="fas fa-truck"></i> Shipping Information
                    </h2>

                    <div class="address-card-wrapper" id="selectedAddressCard" onclick="openAddressModal()">
                        <div class="address-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="address-details">
                            <strong id="cardName"><?= encode($currentUser['username'] ?? 'No Address') ?></strong>
                            <span class="contact-info" id="cardPhone">(<?= encode($currentUser['phone'] ?? '') ?>)</span>
                            <div class="full-address" id="cardAddress"><?= nl2br(encode($currentUser['address'] ?? 'Please Select Address')) ?></div>
                        </div>
                        <button type="button" class="edit-address-btn" onclick="event.stopPropagation(); openAddressModal()">
                            <i class="fas fa-pen"></i>
                        </button>
                    </div>
                    <div style="font-size: 0.9rem; color: var(--text-gray); margin-top: 10px;">
                        Click the address card to select or manage saved addresses.
                    </div>
                </div>

                <div class="card">
                    <h2 class="section-title"><i class="fas fa-shopping-bag"></i> Order Items</h2>
                    <?php foreach ($checkoutItems as $item): ?>
                        <div class="order-item">
                            <img src="<?= encode($item['img_url']) ?>" class="item-img" alt="Product">
                            <div class="item-details">
                                <span class="item-name"><?= encode($item['product_name']) ?></span>
                                <span class="item-meta">Quantity: <?= encode($item['quantity']) ?></span>
                            </div>
                            <div class="item-price">
                                RM <?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
                            </div>
                            <input type="hidden" name="variant_ids[]" value="<?= encode($item['variant_id']) ?>">
                            <input type="hidden" name="item_qtys[]" value="<?= encode($item['quantity']) ?>">
                            <input type="hidden" name="item_prices[]" value="<?= encode($item['unit_price']) ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="card">
                    <div class="redeem-row">
                        <div class="coins-text">
                            <i class="fas fa-coins"></i>
                            Redeem Points (Balance: <span id="userBalance"><?= $userPoints ?></span>)
                        </div>
                        <label class="switch">
                            <input type="checkbox" id="redeemToggle" onchange="togglePointRedemption()">
                            <span class="slider round"></span>
                        </label>
                    </div>
                    <p style="font-size: 0.85rem; color: #999; margin-top: 5px;">
                        100 Points = RM 1.00 (Max redeem 1000 points / RM 10.00 per transaction)
                    </p>
                    <div id="redemptionInfo" style="display:none; color: var(--primary-pink); font-size: 0.9rem; margin-top: 5px;">
                        Discount applied: -RM <span id="displayDiscount">0.00</span>
                    </div>
                </div>

                <div class="card">
                    <h2 class="section-title"><i class="fas fa-credit-card"></i> Payment Method</h2>
                    <div class="payment-options">
                        <label class="payment-card">
                            <input type="radio" name="payment_method" value="Credit Card" class="payment-radio" checked>
                            <i class="fas fa-credit-card payment-icon"></i>
                            <span class="payment-content">Credit / Debit Card</span>
                        </label>
                        <label class="payment-card">
                            <input type="radio" name="payment_method" value="TNG" class="payment-radio">
                            <i class="fas fa-wallet payment-icon" style="color: #005eb8;"></i>
                            <span class="payment-content">Touch 'n Go eWallet</span>
                        </label>
                    </div>
                </div>

                <?php
                html_hidden('subtotal');
                html_hidden('shippingFee');
                html_hidden('taxFee');
                ?>

                <input type="hidden" name="address_id" id="address_id" value="<?= $initialAddressId ?>">

                <input type="hidden" name="points_redeemed" id="inputPointsRedeemed" value="0">
                <input type="hidden" name="discount_amount" id="inputDiscountAmount" value="0">
                <input type="hidden" name="total_amount" id="inputTotalAmount" value="<?= $totalAmount ?>">
            </form>
        </div>

        <div class="sidebar-section">
            <div class="card" style="position: sticky; top: 70px; margin-top: 55px;">
                <h2 class="section-title">Order Summary</h2>
                <div class="summary-row">
                    <span>Subtotal (<?= encode($totalItemCount) ?> items)</span>
                    <span>RM <?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping Fee</span>
                    <span>RM <?= number_format($shippingFee, 2) ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax (<?= encode($taxPercentage) ?>%)</span>
                    <span>RM <?= number_format($taxFee, 2) ?></span>
                </div>

                <div class="summary-row" id="summaryDiscountRow" style="display: none; color: #fc84a3;">
                    <span>Point Redeemed</span>
                    <span>- RM <span id="summaryDiscountVal">0.00</span></span>
                </div>

                <div class="summary-row total">
                    <span>Total Payment</span>
                    <span>RM <span id="summaryGrandTotal"><?= number_format($totalAmount, 2) ?></span></span>
                </div>
                <button type="submit" form="checkoutForm" class="place-order-btn">
                    Place Order
                </button>
                <div style="margin-top: 15px; text-align: center; font-size: 0.8rem; color: #999;">
                    <i class="fas fa-lock"></i> Secure Checkout
                </div>
            </div>
        </div>

    </div>
</div>

<div class="modal-overlay" id="addressModalOverlay">
    <div class="modal-content" style="text-align:left;">
        <div class="modal-header">
            <h3 class="modal-title">Select Address</h3>
            <button type="button" class="close-modal-btn" onclick="closeAddressModal()">&times;</button>
        </div>
        <div class="address-list" id="addressListContainer">
            <p style="text-align: center; color: #777;">Loading...</p>
        </div>
        <a href="#" class="add-address-link" onclick="openAddAddressModal(event)">
            <i class="fas fa-plus-circle"></i> Add a new address
        </a>
        <button type="button" class="place-order-btn" style="margin-top: 15px;" onclick="closeAddressModal()">
            Confirm Selection
        </button>
    </div>
</div>

<div class="modal-overlay" id="addEditAddressModalOverlay">
    <div class="modal-content" style="text-align:left;">
        <div class="modal-header">
            <h3 class="modal-title" id="addEditModalTitle">Add New Address</h3>
            <button type="button" class="close-modal-btn" onclick="closeAddEditAddressModal()">&times;</button>
        </div>
        <div class="general-error" id="generalErrorMsg"></div>

        <div id="osm-map"></div>
        <button type="button" class="btn-locate" onclick="locateUser()">
            <i class="fas fa-crosshairs"></i> Use My Current Location
        </button>

        <form id="addEditAddressForm" novalidate>
            <input type="hidden" name="address_id" id="addressIdInput">
            <div class="form-group">
                <label class="form-label">Receiver Name <span style="color:red">*</span></label>
                <input type="text" name="ReceiverName" id="nameInput" class="form-control" placeholder="e.g. Adam Lim">
                <small class="error-message" id="nameError"></small>
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number <span style="color:red">*</span></label>
                <input type="text" name="phoneNumber" id="phoneInput" class="form-control" placeholder="e.g. 012-3456789">
                <small class="error-message" id="phoneError"></small>
                <small style="color: #888; font-size: 0.8rem; display:block; margin-top:2px;" id="phoneFormatHint">Format: 011-XXXXXXXX or 01X-XXXXXXX</small>
            </div>
            <div class="form-group">
                <label class="form-label">Street Line <span style="color:red">*</span></label>
                <input type="text" name="street_line" id="streetInput" class="form-control" placeholder="e.g. 123, Jalan Bunga">
                <small class="error-message" id="streetError"></small>
            </div>
            <div class="form-group">
                <label class="form-label">Postcode <span style="color:red">*</span></label>
                <input type="text" name="postCode" id="postcodeInput" class="form-control" placeholder="e.g. 56000" maxlength="5">
                <small class="error-message" id="postcodeError"></small>
            </div>

            <div class="form-group" style="display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label class="form-label">City <span style="color:red">*</span></label>
                    <input type="text" name="City" id="cityInput" class="form-control" placeholder="e.g. Kuala Lumpur">
                    <small class="error-message" id="cityError"></small>
                </div>
                <div style="flex: 1;">
                    <label class="form-label">State <span style="color:red">*</span></label>
                    <input type="text" name="State" id="stateInput" class="form-control" placeholder="e.g. Selangor">
                    <small class="error-message" id="stateError"></small>
                </div>
            </div>

            <button type="button" id="btnSaveAddress" class="place-order-btn">Save Address</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="successModalOverlay">
    <div class="modal-content feedback-modal">
        <div class="modal-icon success">
            <i class="fas fa-check-circle"></i>
        </div>
        <h3 class="modal-title centered">Success!</h3>
        <p id="successMessage" class="modal-message">Action completed successfully.</p>
        <div class="modal-actions">
            <button type="button" class="modal-btn btn-success" onclick="closeSuccessModal()">
                OK
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="warningModalOverlay">
    <div class="modal-content feedback-modal">
        <div class="modal-icon warning">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h3 class="modal-title centered">Attention</h3>
        <p id="warningMessage" class="modal-message">Something needs your attention.</p>
        <div class="modal-actions">
            <button type="button" class="modal-btn btn-secondary" onclick="closeWarningModal()">
                Understood
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteConfirmModalOverlay">
    <div class="modal-content feedback-modal">
        <div class="modal-icon danger">
            <i class="fas fa-trash-alt"></i>
        </div>
        <h3 class="modal-title centered">Delete Address?</h3>
        <p class="modal-message">Are you sure you want to remove this address? This cannot be undone.</p>

        <div class="modal-actions">
            <button type="button" class="modal-btn btn-cancel" onclick="closeDeleteConfirmModal()">
                Cancel
            </button>
            <button type="button" class="modal-btn btn-danger" onclick="executeDeleteAddress()">
                Delete
            </button>
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
    <script>
        setTimeout(() => {
            const t = document.getElementById('toast-notification');
            if (t) {
                t.style.opacity = '0';
                setTimeout(() => t.remove(), 500);
            }
        }, 4000);
    </script>
<?php endif; ?>

<script>
    const BASE_ADDRESS_API_URL = '<?= $fullApiUrl ?>';
    let currentSelectedAddressId = '<?= $initialAddressId ?>';
    let addressToDeleteId = null;
    const userPoints = <?= (int)$userPoints ?>;
    const initialTotal = <?= (float)$totalAmount ?>;

    // --- Points Redemption Logic ---
    function togglePointRedemption() {
        const toggle = document.getElementById('redeemToggle');
        const summaryRow = document.getElementById('summaryDiscountRow');
        const summaryVal = document.getElementById('summaryDiscountVal');
        const grandTotalEl = document.getElementById('summaryGrandTotal');
        const displayInfo = document.getElementById('redemptionInfo');
        const displayVal = document.getElementById('displayDiscount');

        const inputPoints = document.getElementById('inputPointsRedeemed');
        const inputDiscount = document.getElementById('inputDiscountAmount');
        const inputTotal = document.getElementById('inputTotalAmount');

        if (toggle.checked) {
            // Rules: Max 1000 points per transaction, can't exceed total amount
            const maxPointsPerTransaction = 1000;
            let pointsToUse = Math.min(userPoints, maxPointsPerTransaction);
            const maxPointsByValue = Math.floor(initialTotal * 100);
            pointsToUse = Math.min(pointsToUse, maxPointsByValue);

            if (pointsToUse <= 0) {
                showWarning("Insufficient points or total amount too low to redeem.");
                toggle.checked = false;
                return;
            }

            const discountRM = pointsToUse / 100;
            const newTotal = initialTotal - discountRM;

            // Update UI
            summaryRow.style.display = 'flex';
            summaryVal.innerText = discountRM.toFixed(2);
            grandTotalEl.innerText = newTotal.toFixed(2);

            if (displayInfo) displayInfo.style.display = 'block';
            if (displayVal) displayVal.innerText = discountRM.toFixed(2);

            // Update hidden inputs for backend
            if (inputPoints) inputPoints.value = pointsToUse;
            if (inputDiscount) inputDiscount.value = discountRM.toFixed(2);
            if (inputTotal) inputTotal.value = newTotal.toFixed(2);

        } else {
            // Reset UI
            summaryRow.style.display = 'none';
            grandTotalEl.innerText = initialTotal.toFixed(2);
            if (displayInfo) displayInfo.style.display = 'none';

            // Reset Inputs
            if (inputPoints) inputPoints.value = 0;
            if (inputDiscount) inputDiscount.value = 0;
            if (inputTotal) inputTotal.value = initialTotal.toFixed(2);
        }
    }

    async function fetchAndParseJSON(url, options = {}) {
        const response = await fetch(url, options);
        const text = await response.text();
        try {
            const jsonStartIndex = text.indexOf('{');
            if (jsonStartIndex >= 0) {
                const cleanText = text.substring(jsonStartIndex);
                return JSON.parse(cleanText);
            }
            return JSON.parse(text);
        } catch (e) {
            console.error("JSON Error:", text);
            throw new Error("Server error.");
        }
    }

    // --- Modal Controls ---
    function openAddressModal() {
        document.getElementById('addressModalOverlay').style.display = 'flex';
        loadAddressList();
    }

    function closeAddressModal() {
        document.getElementById('addressModalOverlay').style.display = 'none';
    }

    function closeAllModals() {
        document.querySelectorAll('.modal-overlay').forEach(el => el.style.display = 'none');
    }

    // Feedback Modals
    function showSuccess(msg) {
        closeAllModals();
        document.getElementById('successMessage').innerText = msg;
        document.getElementById('successModalOverlay').style.display = 'flex';
    }

    function closeSuccessModal() {
        document.getElementById('successModalOverlay').style.display = 'none';
        openAddressModal();
    }

    function showWarning(msg) {
        document.getElementById('warningMessage').innerText = msg;
        document.getElementById('warningModalOverlay').style.display = 'flex';
    }

    function closeWarningModal() {
        document.getElementById('warningModalOverlay').style.display = 'none';
    }

    //Add/Edit Address Form 
    function openAddAddressModal(event, address = null) {
        if (event) event.preventDefault();
        const form = document.getElementById('addEditAddressForm');
        clearErrors(form);

        document.getElementById('addEditModalTitle').innerText = address ? 'Edit Address' : 'Add New Address';
        document.getElementById('addressIdInput').value = address ? address.id : '';
        document.getElementById('nameInput').value = address ? address.name : '';
        document.getElementById('phoneInput').value = address ? address.phone : '';
        document.getElementById('streetInput').value = address ? (address.street_line || '') : '';
        document.getElementById('postcodeInput').value = address ? (address.postcode || '') : '';
        document.getElementById('cityInput').value = address ? (address.city || '') : '';
        document.getElementById('stateInput').value = address ? (address.state || '') : '';

        document.getElementById('addEditAddressModalOverlay').style.display = 'flex';
        document.getElementById('addressModalOverlay').style.display = 'none';
    }

    function closeAddEditAddressModal() {
        document.getElementById('addEditAddressModalOverlay').style.display = 'none';
        openAddressModal();
    }

    // Form Validation
    function showError(inputId, message) {
        const input = document.getElementById(inputId);
        const errorSmall = document.getElementById(inputId.replace('Input', 'Error'));

        if (input && errorSmall) {
            input.classList.add('is-invalid');
            errorSmall.textContent = message;
            errorSmall.style.display = 'block';

            if (inputId === 'phoneInput') {
                const hint = document.getElementById('phoneFormatHint');
                if (hint) hint.style.display = 'none';
            }
        }
    }

    function clearErrors(form) {
        form.querySelectorAll('.form-control').forEach(input => input.classList.remove('is-invalid'));
        form.querySelectorAll('.error-message').forEach(small => {
            small.style.display = 'none';
        });
        const genErr = document.getElementById('generalErrorMsg');
        if (genErr) genErr.style.display = 'none';
        const hint = document.getElementById('phoneFormatHint');
        if (hint) hint.style.display = 'block';
    }

    function validateAddressForm(form) {
        clearErrors(form);
        let isValid = true;

        const name = form.ReceiverName.value.trim();
        const phone = form.phoneNumber.value.trim();
        const street = form.street_line.value.trim();
        const postcode = form.postCode.value.trim();
        const city = form.City.value.trim();
        const state = form.State.value.trim();

        if (!name) {
            showError('nameInput', 'Required');
            isValid = false;
        }
        if (!phone) {
            showError('phoneInput', 'Required');
            isValid = false;
        }
        if (!street) {
            showError('streetInput', 'Required');
            isValid = false;
        }
        if (!postcode) {
            showError('postcodeInput', 'Required');
            isValid = false;
        }
        if (!city) {
            showError('cityInput', 'Required');
            isValid = false;
        }
        if (!state) {
            showError('stateInput', 'Required');
            isValid = false;
        }

        if (isValid) {
            if (/\d/.test(name)) {
                showError('nameInput', 'Name cannot contain numbers');
                isValid = false;
            }
            if (/\d/.test(city)) {
                showError('cityInput', 'City cannot contain numbers');
                isValid = false;
            }
            if (/\d/.test(state)) {
                showError('stateInput', 'State cannot contain numbers');
                isValid = false;
            }

            const cleanPhone = phone.replace(/-/g, '');

            if (!/^\d+$/.test(cleanPhone)) {
                showError('phoneInput', 'Phone must contain only numbers and dashes');
                isValid = false;
            } else if (cleanPhone.startsWith('011')) {
                if (cleanPhone.length !== 11) {
                    showError('phoneInput', '011 numbers must have 11 digits (e.g. 011-12345678)');
                    isValid = false;
                }
            } else if (/^01[02-9]/.test(cleanPhone)) {
                if (cleanPhone.length !== 10) {
                    showError('phoneInput', '012-019 numbers must have 10 digits (e.g. 012-3456789)');
                    isValid = false;
                }
            } else {
                showError('phoneInput', 'Invalid prefix. Must start with 01X');
                isValid = false;
            }

            if (!/^\d{5}$/.test(postcode)) {
                showError('postcodeInput', 'Must be 5 digits');
                isValid = false;
            }
        }
        return isValid;
    }

    //Address Selection Logic
    function selectAddress(id, name, phone, address) {
        currentSelectedAddressId = id;

        // Update UI Card
        const cardName = document.getElementById('cardName');
        const cardPhone = document.getElementById('cardPhone');
        const cardAddress = document.getElementById('cardAddress');

        if (cardName) cardName.innerText = name;
        if (cardPhone) cardPhone.innerText = `(${phone})`;
        if (cardAddress) cardAddress.innerHTML = address.replace(/\n/g, '<br>');

        // Update Hidden Inputs
        const inputId = document.getElementById('address_id');
        const inputName = document.getElementById('receiver_name');
        const inputPhone = document.getElementById('receiver_phone');
        const inputAddr = document.getElementById('receiver_address');

        if (inputId) {
            console.log("Updating address_id to: " + id);
            inputId.value = id;
        } else {
            console.error("Undefined address_id input!");
        }
        if (inputName) inputName.value = name;
        if (inputPhone) inputPhone.value = phone;
        if (inputAddr) inputAddr.value = address;

        document.querySelectorAll('.address-list-item').forEach(item => {
            item.classList.remove('selected');
            if (item.dataset.id == id) item.classList.add('selected');
        });
    }

    // --- Address Actions ---
    function editAddress(id) {
        fetchAndParseJSON(BASE_ADDRESS_API_URL + '?action=get&id=' + id)
            .then(res => {
                if (res.success) openAddAddressModal(null, res.data);
                else alert(res.message);
            });
    }

    function deleteAddress(id, isDefault) {
        if (isDefault == 1 || isDefault == '1') {
            showWarning("You cannot delete the default address.");
            return;
        }
        addressToDeleteId = id;
        document.getElementById('deleteConfirmModalOverlay').style.display = 'flex';
    }

    function closeDeleteConfirmModal() {
        document.getElementById('deleteConfirmModalOverlay').style.display = 'none';
        addressToDeleteId = null;
    }

    function executeDeleteAddress() {
        const id = addressToDeleteId;
        if (!id) return;
        closeDeleteConfirmModal();

        const formData = new URLSearchParams({
            address_id: id
        });
        fetchAndParseJSON(BASE_ADDRESS_API_URL + '?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData
        }).then(res => {
            if (res.success) loadAddressList();
            else showWarning("Failed: " + res.message);
        }).catch(err => showWarning(err.message));
    }

    function loadAddressList() {
        const container = document.getElementById('addressListContainer');
        container.innerHTML = '<p style="text-align: center; color: #777;">Loading...</p>';

        fetchAndParseJSON(BASE_ADDRESS_API_URL + '?action=list')
            .then(res => {
                if (res.success && res.data.addresses.length > 0) {
                    const addresses = res.data.addresses;
                    let html = '';

                    // auto-select Default if current selection is invalid
                    const stillExists = addresses.some(a => a.id == currentSelectedAddressId);

                    if (!stillExists || !currentSelectedAddressId) {
                        const def = addresses[0];
                        const defAddr = (def.address || '').replace(/\"/g, '&quot;');
                        selectAddress(def.id, def.name, def.phone, defAddr);
                    }

                    addresses.forEach(addr => {
                        const isSelected = (addr.id == currentSelectedAddressId) ? 'selected' : '';
                        const addrLines = (addr.address || '').replace(/\"/g, '&quot;').replace(/\n/g, '\\n');
                        const isDef = addr.is_default == 1 ? 1 : 0;

                        html += `
                        <div class="address-list-item ${isSelected}" data-id="${addr.id}" onclick="selectAddress('${addr.id}', '${addr.name}', '${addr.phone}', '${addrLines}')">
                            <div class="address-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="address-list-text">
                                <strong>${addr.name}</strong> (${addr.phone})
                                ${isDef ? '<span style="color:var(--primary-pink); margin-left:10px; font-size:0.8rem;">[Default]</span>' : ''}
                                <p>${(addr.address || '').replace(/\n/g, '<br>')}</p>
                            </div>
                            <div class="address-action-buttons">
                                <i class="fas fa-edit" onclick="event.stopPropagation(); editAddress('${addr.id}')"></i>
                                <i class="fas fa-trash-alt" onclick="event.stopPropagation(); deleteAddress('${addr.id}', ${isDef})"></i>
                            </div>
                        </div>`;
                    });
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<p style="text-align: center;">No addresses found.</p>';
                    selectAddress('', 'No Address Selected', '-', 'Please add a new address.');
                }
            }).catch(err => {
                container.innerHTML = '<p style="text-align: center; color: red;">Failed to load.</p>';
                console.error(err);
            });
    }

    // --- Save Address Handler ---
    document.getElementById('btnSaveAddress').addEventListener('click', function(e) {
        e.preventDefault();

        const form = document.getElementById('addEditAddressForm');
        
        if (!validateAddressForm(form)) {
            return;
        }

        const id = form.address_id.value;
        const action = id ? 'update' : 'add';
        const formData = new URLSearchParams(new FormData(form));
        formData.append('action', action);

        const btn = document.getElementById('btnSaveAddress');
        const originalText = btn.innerText;
        btn.innerText = 'Saving...';
        btn.disabled = true;

        fetchAndParseJSON(BASE_ADDRESS_API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData
        }).then(res => {
            btn.innerText = originalText;
            btn.disabled = false;

            if (res.success) {
                const d = res.data;
                const fullAddr = form.street_line.value + '\n' + form.postCode.value + ' ' + form.City.value + '\n' + form.State.value;

                selectAddress(d.address_id, form.ReceiverName.value, form.phoneNumber.value, fullAddr);

                document.getElementById('addEditAddressModalOverlay').style.display = 'none';
                showSuccess("Address has been saved successfully!");
            } else {
                const errDiv = document.getElementById('generalErrorMsg');
                if (errDiv) {
                    errDiv.innerText = res.message || "Failed.";
                    errDiv.style.display = 'block';
                } else {
                    alert(res.message);
                }
            }
        }).catch(err => {
            btn.innerText = originalText;
            btn.disabled = false;
            alert("Error: " + err.message);
        });
    });

    // Payment Selection
    window.onload = function() {
        const cards = document.querySelectorAll('.payment-card');
        const radios = document.querySelectorAll('.payment-radio');

        function updateStyle() {
            cards.forEach(c => {
                const r = c.querySelector('input');
                if (r.checked) {
                    c.style.borderColor = 'var(--primary-pink)';
                    // c.style.backgroundColor = '#fffbfd';
                } else {
                    c.style.borderColor = '#ddd';
                    // c.style.backgroundColor = '#fff';
                }
            });
        }
        radios.forEach(r => r.addEventListener('change', updateStyle));
        updateStyle();
    };

    let map, marker;
    const defaultLat = 3.140853; // Default Latitude
    const defaultLng = 101.693207; // Default Longitude

    // Typing timer for debounce logic
    let typingTimer;
    const doneTypingInterval = 1500; // Wait for 1.5 seconds after user stops typing

    const addressInputIds = ['streetInput', 'cityInput', 'stateInput', 'postcodeInput'];

    document.addEventListener("DOMContentLoaded", function() {
        addressInputIds.forEach(id => {
            const inputElement = document.getElementById(id);

            if (inputElement) {
                inputElement.addEventListener('input', function() {
                    clearTimeout(typingTimer);
                    typingTimer = setTimeout(triggerMapUpdateFromInput, doneTypingInterval);
                });

                // Listen for change (e.g., losing focus or selecting autocomplete)
                inputElement.addEventListener('change', function() {
                    clearTimeout(typingTimer);
                    triggerMapUpdateFromInput();
                });
            }
        });
    });

    // Initializes the Leaflet map and marker
    function initMap() {
        // If map instance exists, just fix the rendering size 
        if (map) {
            setTimeout(function() {
                map.invalidateSize();
            }, 200);
            return;
        }

        // Initialize Map instance
        map = L.map('osm-map').setView([defaultLat, defaultLng], 13);

        // Add OpenStreetMap Tile Layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Add Draggable Marker
        marker = L.marker([defaultLat, defaultLng], {
            draggable: true
        }).addTo(map);

        // User stops dragging the marker -> Update address fields
        marker.on('dragend', function(event) {
            const position = marker.getLatLng();
            fetchAddressFromAPI(position.lat, position.lng);
        });

        // User clicks on the map -> Move marker and update fields
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            fetchAddressFromAPI(e.latlng.lat, e.latlng.lng);
        });
    }

    // Geolocation API
    function locateUser() {
        const btn = document.querySelector('.btn-locate');
        const originalText = '<i class="fas fa-crosshairs"></i> Use My Current Location';

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Locating...';
        btn.disabled = true;

        if (!navigator.geolocation) {
            alert("Geolocation is not supported by this browser.");
            btn.innerHTML = originalText;
            btn.disabled = false;
            return;
        }

        const options = {
            enableHighAccuracy: true,
            timeout: 10000, // 10 seconds timeout
            maximumAge: 0
        };

        navigator.geolocation.getCurrentPosition(
            (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Move map view and marker to user location
                if (map) {
                    map.setView([lat, lng], 17);
                    if (marker) marker.setLatLng([lat, lng]);
                }

                // Reverse geocode to get address text
                fetchAddressFromAPI(lat, lng);

                btn.innerHTML = originalText;
                btn.disabled = false;
            },
            (error) => {
                let errorMsg = "";
                switch (error.code) {
                    case error.PERMISSION_DENIED:
                        errorMsg = "Permission Denied. Please enable location access in your browser settings.";
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMsg = "Position Unavailable. GPS signal is weak.";
                        break;
                    case error.TIMEOUT:
                        errorMsg = "Request Timeout. Please try again.";
                        break;
                    default:
                        errorMsg = "Unknown Error: " + error.message;
                        break;
                }
                alert(errorMsg);
                btn.innerHTML = originalText;
                btn.disabled = false;
            },
            options
        );
    }

    // Converts Lat/Lng coordinates to Address Text.
    function fetchAddressFromAPI(lat, lng) {
        const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`;

        fetch(url, {
                headers: {
                    'User-Agent': 'LovineWeb/1.0',
                    'Accept-Language': 'en-US,en;q=0.9,ms;q=0.8' // Prefer English/Malay
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data && data.address) {
                    const addr = data.address;

                    // Fill Form Fields
                    document.getElementById('postcodeInput').value = addr.postcode || '';

                    // Logic to find the best City name from OSM data
                    const city = addr.city || addr.town || addr.village || addr.county || addr.district || '';
                    document.getElementById('cityInput').value = city;

                    document.getElementById('stateInput').value = addr.state || '';

                    // Construct full Street Line
                    let streetParts = [];
                    if (addr.building) streetParts.push(addr.building);
                    if (addr.house_number) streetParts.push(addr.house_number);
                    if (addr.road) streetParts.push(addr.road);
                    if (addr.suburb) streetParts.push(addr.suburb);
                    if (addr.neighbourhood) streetParts.push(addr.neighbourhood);

                    const streetLine = streetParts.join(', ');
                    document.getElementById('streetInput').value = streetLine || data.display_name.split(',')[0];
                }
            })
            .catch(err => console.error("Geocoding error:", err));
    }
    // Triggered after user stops typing in address fields
    function triggerMapUpdateFromInput() {
        const street = document.getElementById('streetInput').value.trim();
        const city = document.getElementById('cityInput').value.trim();
        const state = document.getElementById('stateInput').value.trim();
        const postcode = document.getElementById('postcodeInput').value.trim();

        // Prevent search if address is too short to avoid random jumps
        if (street.length < 5 && city.length < 3) {
            return;
        }

        // Construct search string: Street, Postcode City, State, Malaysia
        let searchStr = "";
        if (street) searchStr += street + ", ";
        if (postcode) searchStr += postcode + " ";
        if (city) searchStr += city + ", ";
        if (state) searchStr += state + ", ";
        searchStr += "Malaysia";

        searchAddressOnMap(searchStr);
    }

    // Searches for an address string and moves the map/marker if found
    function searchAddressOnMap(addressString) {
        if (!addressString) return;

        const mapDiv = document.getElementById('osm-map');
        mapDiv.style.opacity = '0.5'; // Indicate loading

        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(addressString)}&limit=1`;

        fetch(url, {
                headers: {
                    'User-Agent': 'LovineWeb/1.0'
                }
            })
            .then(response => response.json())
            .then(data => {
                mapDiv.style.opacity = '1'; // Restore opacity

                if (data && data.length > 0) {
                    const lat = data[0].lat;
                    const lng = data[0].lon;

                    if (map && marker) {
                        const newLatLng = new L.LatLng(lat, lng);
                        map.setView(newLatLng, 17);
                        marker.setLatLng(newLatLng);
                    }
                } else {
                    console.warn("Address not found on map.");
                }
            })
            .catch(err => {
                console.error("Search Error:", err);
                mapDiv.style.opacity = '1';
            });
    }

    const originalOpenAddAddressModal = openAddAddressModal;

    openAddAddressModal = function(event, address = null) {
        originalOpenAddAddressModal(event, address);

        // Delay map initialization to ensure modal is fully rendere
        setTimeout(() => {
            initMap();

            if (address) {
                let searchStr = "";
                if (address.street_line) searchStr += address.street_line + ", ";
                if (address.city) searchStr += address.city + ", ";
                if (address.state) searchStr += address.state + ", ";
                searchStr += "Malaysia";
                searchAddressOnMap(searchStr);

            } else {
                if (map) {
                    map.setView([defaultLat, defaultLng], 13);
                    marker.setLatLng([defaultLat, defaultLng]);
                }
            }
        }, 100);
    };
</script>