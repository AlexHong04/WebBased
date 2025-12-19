<?php
$title = "Address Book";
$pageCSS = "profile.css";

require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../controllers/AddressController.php';

$controller = new AddressController();
$addresses = $controller->index();

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$apiPath = '/app/controllers/address_router.php';
$fullApiUrl = $protocol . $host . $apiPath;

include __DIR__ . '/../header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="/public/css/addressBook.css">


<section class="profile-section">
    <h1 class="section-title">My Profile</h1>

    <div class="profile-container">
        <div class="profile-sidebar">
            <div class="profile-avatar">
                <img id="profile-image" src="/images/DefaultAvatar.png" alt="Profile Avatar">
                <div class="avatar-overlay">
                    <label for="avatar-upload" class="avatar-change-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="24" height="24" fill="currentColor">
                            <path d="M149.1 64.8L138.7 96H64C28.7 96 0 124.7 0 160V416c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V160c0-35.3-28.7-64-64-64H373.3L362.9 64.8C356.4 45.2 338.1 32 317.4 32H194.6c-20.7 0-39 13.2-45.5 32.8zM256 192a96 96 0 1 1 0 192 96 96 0 1 1 0-192z" />
                        </svg>
                    </label>
                    <input type="file" id="avatar-upload" accept="image/*" style="display: none;">
                </div>
            </div>

            <h2 id="profile-name"><?= htmlspecialchars(($profileData['firstName'] ?? '') . ' ' . ($profileData['lastName'] ?? '')) ?></h2>
            <p id="profile-gender"><?= htmlspecialchars($profileData['gender'] ?? '') ?></p>

            <div class="profile-nav">
                <a href="profile.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18" fill="currentColor" style="margin-right: 10px;">
                        <path d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm89.6 32h-16.7c-22.2 10.2-46.9 16-72.9 16s-50.6-5.8-72.9-16h-16.7C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-74.2-60.2-134.4-134.4-134.4z" />
                    </svg>
                    Profile Information
                </a>
                <a href="addressBook.php" class="active">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="18" height="18" fill="currentColor" style="margin-right: 10px;">
                        <path d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z" />
                    </svg>
                    Address Book
                </a>
                <a href="resetPassword.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18" fill="currentColor" style="margin-right: 10px;">
                        <path d="M400 224h-24v-72C376 68.2 307.8 0 224 0S72 68.2 72 152v72H48c-26.5 0-48 21.5-48 48v192c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48V272c0-26.5-21.5-48-48-48zm-104 0H152v-72c0-39.7 32.3-72 72-72s72 32.3 72 72v72z" />
                    </svg>
                    Reset Password
                </a>
                <a href="wishlist.php">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="18" height="18" fill="currentColor" style="margin-right: 10px;">
                        <path d="M462.3 62.6C407.5 15.9 326 24.3 275.7 76.2L256 96.5l-19.7-20.3C186.1 24.3 104.5 15.9 49.7 62.6c-62.8 53.6-66.1 149.8-9.9 207.9l193.5 199.8c12.5 12.9 32.8 12.9 45.3 0l193.5-199.8c56.3-58.1 53-154.3-9.8-207.9z" />
                    </svg>
                    Wishlist
                </a>
            </div>
        </div>

        <div class="profile-content">
            <div class="section-header">
                <h2 class="section-title">Address Book</h2>
                <button class="btn-add-new" onclick="openAddAddressModal(event)">
                    <i class="fas fa-plus"></i> Add New Address
                </button>
            </div>

            <div class="address-list">
                <?php if (empty($addresses)): ?>
                    <div class="empty-state">
                        <i class="fas fa-map-marker-alt"></i>
                        <p>You haven't added any addresses yet.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($addresses as $addr):
                        $isDefault = $addr['is_default'] == 1;
                        $addrId = $addr['address_id'];
                        $fullAddress = ($addr['street_line'] ?? '') . ', ' . ($addr['postcode'] ?? '') . ' ' . ($addr['city'] ?? '') . ', ' . ($addr['state'] ?? '');

                        $addrJson = htmlspecialchars(json_encode([
                            'id' => $addr['address_id'],
                            'name' => $addr['recipient_name'],
                            'phone' => $addr['recipient_phone'],
                            'street_line' => $addr['street_line'],
                            'postcode' => $addr['postcode'],
                            'city' => $addr['city'],
                            'state' => $addr['state'],
                            'is_default' => $isDefault
                        ]), ENT_QUOTES, 'UTF-8');
                    ?>
                        <div class="address-card <?= $isDefault ? 'is-default' : '' ?>" id="addr-<?= $addrId ?>">
                            <div class="card-icon">
                                <i class="fas <?= $isDefault ? 'fa-star' : 'fa-map-marker-alt' ?>"></i>
                            </div>

                            <div class="card-content">
                                <div class="card-header-row">
                                    <span class="card-name"><?= htmlspecialchars($addr['recipient_name']) ?></span>
                                    <span class="card-phone">(<?= htmlspecialchars($addr['recipient_phone']) ?>)</span>
                                    <?php if ($isDefault): ?>
                                        <span class="default-tag">Default</span>
                                    <?php endif; ?>
                                </div>

                                <div class="card-address">
                                    <?= htmlspecialchars($fullAddress) ?>
                                </div>

                                <?php if (!$isDefault): ?>
                                    <button class="btn-set-default" onclick="confirmSetDefault('<?= $addrId ?>')">
                                        Set as Default
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="card-actions">
                                <i class="fas fa-pen action-icon" onclick='openAddAddressModal(event, <?= $addrJson ?>)'></i>
                                <i class="fas fa-trash-alt action-icon delete" onclick="deleteAddress('<?= $addrId ?>', <?= $isDefault ? 1 : 0 ?>)"></i>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="modal-overlay" id="addEditAddressModalOverlay">
    <div class="modal-content">
        <h3 id="addEditModalTitle" class="modal-title">Add New Address</h3>
        <div class="general-error" id="generalErrorMsg"></div>

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

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeAddEditAddressModal()">Cancel</button>
                <button type="submit" id="btnSaveAddress" class="btn-save">Save Address</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-overlay" id="successModalOverlay">
    <div class="modal-content feedback-modal">
        <div class="modal-icon success"><i class="fas fa-check-circle"></i></div>
        <h3 class="modal-title centered">Success!</h3>
        <p id="successMessage" class="modal-message">Action completed successfully.</p>
        <div class="modal-actions">
            <button type="button" class="btn-save" style="background: #2ecc71; width:100px;" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="warningModalOverlay">
    <div class="modal-content feedback-modal">
        <div class="modal-icon warning"><i class="fas fa-exclamation-circle"></i></div>
        <h3 class="modal-title centered">Attention</h3>
        <p id="warningMessage" class="modal-message">Something needs your attention.</p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" style="width:100px;" onclick="closeWarningModal()">Understood</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteConfirmModalOverlay">
    <div class="modal-content feedback-modal">
        <div class="modal-icon danger"><i class="fas fa-trash-alt"></i></div>
        <h3 class="modal-title centered">Delete Address?</h3>
        <p class="modal-message">Are you sure you want to delete this address? This cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeDeleteConfirmModal()">Cancel</button>
            <button type="button" class="btn-save" style="background: #e74c3c;" onclick="executeDeleteAddress()">Delete</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="setDefaultConfirmModalOverlay">
    <div class="modal-content feedback-modal">
        <div class="modal-icon warning"><i class="fas fa-star"></i></div>
        <h3 class="modal-title centered">Set as Default?</h3>
        <p class="modal-message">Do you want to set this address as your primary shipping address?</p>
        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeSetDefaultConfirmModal()">Cancel</button>
            <button type="button" class="btn-save" onclick="executeSetDefault()">Yes</button>
        </div>
    </div>
</div>

<script>
    const BASE_ADDRESS_API_URL = '<?= $fullApiUrl ?>';

    let addressToDeleteId = null;
    let addressToSetDefaultId = null;

    async function fetchAndParseJSON(url, options = {}) {
        const response = await fetch(url, options);
        const text = await response.text();
        try {
            const jsonStartIndex = text.indexOf('{');
            if (jsonStartIndex >= 0) return JSON.parse(text.substring(jsonStartIndex));
            return JSON.parse(text);
        } catch (e) {
            console.error("JSON Error:", text);
            throw new Error("Server error.");
        }
    }

    function closeAllModals() {
        document.querySelectorAll('.modal-overlay').forEach(el => el.style.display = 'none');
    }

    function showSuccess(msg) {
        closeAllModals();
        document.getElementById('successMessage').innerText = msg;
        document.getElementById('successModalOverlay').style.display = 'flex';
    }

    function closeSuccessModal() {
        document.getElementById('successModalOverlay').style.display = 'none';
        location.reload();
    }

    function showWarning(msg) {
        document.getElementById('warningMessage').innerText = msg;
        document.getElementById('warningModalOverlay').style.display = 'flex';
    }

    function closeWarningModal() {
        document.getElementById('warningModalOverlay').style.display = 'none';
    }

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
    }

    function closeAddEditAddressModal() {
        document.getElementById('addEditAddressModalOverlay').style.display = 'none';
    }

    function showError(inputId, message) {
        const input = document.getElementById(inputId);
        const errorSmall = document.getElementById(inputId.replace('Input', 'Error'));
        if (input && errorSmall) {
            input.classList.add('is-invalid');
            errorSmall.innerText = message;
            errorSmall.style.display = 'block';

            if (inputId === 'phoneInput') {
                const hint = document.getElementById('phoneFormatHint');
                if (hint) hint.style.display = 'none';
            }
        }
    }

    function clearErrors(form) {
        form.querySelectorAll('.form-control').forEach(input => input.classList.remove('is-invalid'));
        form.querySelectorAll('.error-message').forEach(small => small.style.display = 'none');
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

    document.getElementById('btnSaveAddress').addEventListener('click', function(e) {
        e.preventDefault();
        const form = document.getElementById('addEditAddressForm');
        if (!validateAddressForm(form)) return;

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
            if (res.success) {
                document.getElementById('addEditAddressModalOverlay').style.display = 'none';
                showSuccess("Address saved successfully!");
            } else {
                btn.innerText = originalText;
                btn.disabled = false;
                const errDiv = document.getElementById('generalErrorMsg');
                errDiv.innerText = res.message || "Failed.";
                errDiv.style.display = 'block';
            }
        }).catch(err => {
            btn.innerText = originalText;
            btn.disabled = false;
            showWarning("Error: " + err.message);
        });
    });

    function deleteAddress(id, isDefault) {
        if (isDefault) {
            showWarning("Cannot delete default address.");
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
        if (!addressToDeleteId) return;
        const idToDelete = addressToDeleteId;
        closeDeleteConfirmModal();

        const formData = new URLSearchParams({
            address_id: idToDelete
        });
        fetchAndParseJSON(BASE_ADDRESS_API_URL + '?action=delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData
        }).then(res => {
            if (res.success) showSuccess("Address deleted.");
            else showWarning(res.message);
        }).catch(err => showWarning(err.message));
    }

    function confirmSetDefault(id) {
        if (!id) {
            showWarning("Error: ID missing");
            return;
        }
        addressToSetDefaultId = id;
        document.getElementById('setDefaultConfirmModalOverlay').style.display = 'flex';
    }

    function closeSetDefaultConfirmModal() {
        document.getElementById('setDefaultConfirmModalOverlay').style.display = 'none';
        addressToSetDefaultId = null;
    }

    function executeSetDefault() {
        if (!addressToSetDefaultId) {
            showWarning("Error: No address selected.");
            return;
        }

        const idToSend = addressToSetDefaultId;
        closeSetDefaultConfirmModal();

        const formData = new URLSearchParams({
            address_id: idToSend
        });
        fetchAndParseJSON(BASE_ADDRESS_API_URL + '?action=set_default', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData
        }).then(res => {
            if (res.success) showSuccess("Default address updated.");
            else showWarning(res.message || "Failed to update.");
        }).catch(err => showWarning("Server Error: " + err.message));
    }
</script>

<?php include '../footer.php' ?>