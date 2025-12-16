<?php
$title = "Profile Page";
$pageCSS = "profile.css";

require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../helpers/validation.php';
require_once __DIR__ . '/../../controllers/userController.php';
require_once __DIR__ . '/../../helpers/auth.php';

authenticate();
$controller = new userController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'updateProfile') {
    $controller->updateProfile();
}

$profileData = $controller->getProfile();

include __DIR__ . '/../header.php';
?>

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
                <a href="profile.php" class="active">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18" fill="currentColor" style="margin-right: 10px;">
                        <path d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm89.6 32h-16.7c-22.2 10.2-46.9 16-72.9 16s-50.6-5.8-72.9-16h-16.7C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-74.2-60.2-134.4-134.4-134.4z" />
                    </svg>
                    Profile Information
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
            <div class="profile-form-container">
                <h3>Edit Profile Information</h3>

                <form id="profile-form" method="POST">
                    <input type="hidden" name="action" value="updateProfile">
                    
                    <div class="form-group-col">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <div class="input-wrapper">
                                <?php html_text('firstName', 'class="form-control" readonly', $profileData['firstName'] ?? ''); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="16" height="16" fill="currentColor"><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z" /></svg>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <div class="input-wrapper">
                                <?php html_text('lastName', 'class="form-control" readonly', $profileData['lastName'] ?? ''); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="16" height="16" fill="currentColor"><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-col">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <div class="input-wrapper">
                                <?php html_text('phone', 'class="form-control" readonly', $profileData['phone'] ?? ''); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 512 512" width="16" height="16" fill="currentColor"><path d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z" /></svg>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <div class="input-wrapper">
                                <?php html_text('email', 'class="form-control" readonly', $profileData['email'] ?? ''); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 512 512" width="16" height="16" fill="currentColor"><path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-col">
                        <div class="form-group">
                            <label for="city">City</label>
                            <div class="input-wrapper">
                                <?php html_text('city', 'class="form-control" readonly', $profileData['city'] ?? ''); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 384 512" width="16" height="16" fill="currentColor"><path d="M48 0C21.5 0 0 21.5 0 48V464c0 26.5 21.5 48 48 48h96V432c0-26.5 21.5-48 48-48s48 21.5 48 48v80h96c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48H48zM64 240c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V240zm112-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H176c-8.8 0-16-7.2-16-16V240c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V240zM80 96h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16zm112 16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H192c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V112z" /></svg>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <div class="input-wrapper">
                                <?php html_text('state', 'class="form-control" readonly', $profileData['state'] ?? ''); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 576 512" width="16" height="16" fill="currentColor"><path d="M384 476.1L192 421.2V35.9L384 90.8V476.1zm32-1.2V88.4l149.7 42.8c8.8 2.5 14.3 10.6 14.3 19.7v293c0 14.4-14.4 24.2-27.4 18.7L384 416V474.9zM55.1 40.3L160 70.2V426.9l-149.7-42.8C1.5 381.6-4 373.5-4 364.4v-293c0-14.4 14.4-24.2 27.4-18.7L160 96V38.8L55.1 8.9C36.6 3.6 16.9 19.9 16.9 39.2V332.2L160 373.1V90.8L32 54.2V40.3z" /></svg>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="postcode">Post Code</label>
                            <div class="input-wrapper">
                                <?php html_text('postcode', 'class="form-control" readonly', $profileData['postcode'] ?? ''); ?>
                                <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 384 512" width="16" height="16" fill="currentColor"><path d="M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="streetLine">Street Line</label>
                        <div class="input-wrapper">
                            <?php html_textarea('streetLine', 'class="form-control" maxlength="1000" style="height:80px;" readonly', $profileData['street_line'] ?? ''); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon textarea-icon" viewBox="0 0 576 512" width="16" height="16" fill="currentColor"><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" /></svg>
                        </div>
                        <small class="form-text">Street address, P.O. box, company name</small>
                    </div>

                    <div class="form-actions">
                        <button type="button" id="edit-btn" class="save-btn">Edit Profile</button>
                        <button type="button" class="cancel-btn" onclick="location.reload();">Cancel</button>
                    </div>

                    <div id="profile-message" class="message-container"></div>
                </form> 
                </div>
        </div>
    </div>
</section>
<!-- 
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
            Done
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
        
        <form id="addEditAddressForm" novalidate>
            <input type="hidden" name="address_id" id="addressIdInput">

            <div class="form-group">
                <label class="form-label">Receiver Name <span style="color:red">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="ReceiverName" id="nameInput" class="form-control" placeholder="e.g. Adam Lim">
                    <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="16" height="16" fill="currentColor"><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z" /></svg>
                </div>
                <small class="error-message" id="nameError"></small>
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number <span style="color:red">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="phoneNumber" id="phoneInput" class="form-control" placeholder="e.g. 012-3456789">
                    <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 512 512" width="16" height="16" fill="currentColor"><path d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z" /></svg>
                </div>
                <small class="error-message" id="phoneError"></small>
                <small style="color: #888; font-size: 0.8rem; display:block; margin-top:2px;" id="phoneFormatHint">Format: 011-XXXXXXXX or 01X-XXXXXXX</small>
            </div>
            <div class="form-group">
                <label class="form-label">Street Line <span style="color:red">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="street_line" id="streetInput" class="form-control" placeholder="e.g. 123, Jalan Bunga">
                    <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 576 512" width="16" height="16" fill="currentColor"><path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" /></svg>
                </div>
                <small class="error-message" id="streetError"></small>
            </div>
            <div class="form-group">
                <label class="form-label">Postcode <span style="color:red">*</span></label>
                <div class="input-wrapper">
                    <input type="text" name="postCode" id="postcodeInput" class="form-control" placeholder="e.g. 56000" maxlength="5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 384 512" width="16" height="16" fill="currentColor"><path d="M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z" /></svg>
                </div>
                <small class="error-message" id="postcodeError"></small>
            </div>
            <div class="form-group" style="display: flex; gap: 15px;">
                <div style="flex: 1;">
                    <label class="form-label">City <span style="color:red">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="City" id="cityInput" class="form-control" placeholder="e.g. Kuala Lumpur">
                        <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 384 512" width="16" height="16" fill="currentColor"><path d="M48 0C21.5 0 0 21.5 0 48V464c0 26.5 21.5 48 48 48h96V432c0-26.5 21.5-48 48-48s48 21.5 48 48v80h96c26.5 0 48-21.5 48-48V48c0-26.5-21.5-48-48-48H48zM64 240c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V240zm112-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H176c-8.8 0-16-7.2-16-16V240c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V240zM80 96h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H80c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16zm112 16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H192c-8.8 0-16-7.2-16-16V112c0-8.8 7.2-16 16-16zm80 16c0-8.8 7.2-16 16-16h32c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H272c-8.8 0-16-7.2-16-16V112z" /></svg>
                    </div>
                    <small class="error-message" id="cityError"></small>
                </div>
                <div style="flex: 1;">
                    <label class="form-label">State <span style="color:red">*</span></label>
                    <div class="input-wrapper">
                        <input type="text" name="State" id="stateInput" class="form-control" placeholder="e.g. Selangor">
                        <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 576 512" width="16" height="16" fill="currentColor"><path d="M384 476.1L192 421.2V35.9L384 90.8V476.1zm32-1.2V88.4l149.7 42.8c8.8 2.5 14.3 10.6 14.3 19.7v293c0 14.4-14.4 24.2-27.4 18.7L384 416V474.9zM55.1 40.3L160 70.2V426.9l-149.7-42.8C1.5 381.6-4 373.5-4 364.4v-293c0-14.4 14.4-24.2 27.4-18.7L160 96V38.8L55.1 8.9C36.6 3.6 16.9 19.9 16.9 39.2V332.2L160 373.1V90.8L32 54.2V40.3z" /></svg>
                    </div>
                    <small class="error-message" id="stateError"></small>
                </div>
            </div>
            <input type="hidden" name="address" id="addressInput" value="">
            <button type="submit" id="btnSaveAddress" class="place-order-btn">Save Address</button>
        </form>
    </div>
</div>

<div class="modal-overlay" id="successModalOverlay">
    <div class="modal-content feedback-modal">
        <div class="modal-icon success"><i class="fas fa-check-circle"></i></div>
        <h3 class="modal-title centered">Success!</h3>
        <p id="successMessage" class="modal-message">Action completed successfully.</p>
        <div class="modal-actions">
            <button type="button" class="modal-btn btn-success" onclick="closeSuccessModal()">OK</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="warningModalOverlay">
    <div class="modal-content feedback-modal">
        <div class="modal-icon warning"><i class="fas fa-exclamation-circle"></i></div>
        <h3 class="modal-title centered">Attention</h3>
        <p id="warningMessage" class="modal-message">Something needs your attention.</p>
        <div class="modal-actions">
            <button type="button" class="modal-btn btn-secondary" onclick="closeWarningModal()">Understood</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="deleteConfirmModalOverlay">
    <div class="modal-content feedback-modal">
        <div class="modal-icon danger"><i class="fas fa-trash-alt"></i></div>
        <h3 class="modal-title centered">Delete Address?</h3>
        <p class="modal-message">Are you sure you want to remove this address? This cannot be undone.</p>
        <div class="modal-actions">
            <button type="button" class="modal-btn btn-cancel" onclick="closeDeleteConfirmModal()">Cancel</button>
            <button type="button" class="modal-btn btn-danger" onclick="executeDeleteAddress()">Delete</button>
        </div>
    </div>
</div> -->

<?php include '../footer.php' ?>

<script src="/public/js/validation.js"></script>
<script src="/public/js/profile.js"></script>