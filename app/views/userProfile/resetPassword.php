<?php
$title = "Profile Page";
$pageCSS = "profile.css";

require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ .  '/../../controllers/userController.php';

$controller = new userController();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reset_password') {
    $controller->resetPassword();
}

require_once __DIR__ . '/../header.php';
?>

<section class="profile-section">
    <h1 class="section-title">My Profile</h1>

    <div class="profile-container">
        <div class="profile-sidebar">
            <div class="profile-avatar">
                <img id="profile-image" src="image/DefaultAvatar.jpg" alt="Profile Avatar">

                <div class="avatar-overlay">
                    <label for="avatar-upload" class="avatar-change-btn">
                        <i class="fas fa-camera"></i>
                    </label>
                    <input type="file" id="avatar-upload" accept="image/*" style="display: none;">
                </div>
            </div>

            <h2 id="profile-name">User Name</h2>
            <p id="profile-email">user@example.com</p>

             <div class="profile-nav">
                <a href="profile.php" ">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18" fill="currentColor" style="margin-right: 10px;">
                        <path d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm89.6 32h-16.7c-22.2 10.2-46.9 16-72.9 16s-50.6-5.8-72.9-16h-16.7C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-74.2-60.2-134.4-134.4-134.4z" />
                    </svg>
                    Profile Information
                </a>
                <a href="#">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="18" height="18" fill="currentColor" style="margin-right: 10px;">
                        <path d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z" />
                    </svg>
                    Address Book
                </a>
                <a href="resetPassword.php" class="active">
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
                <h3>Change Password</h3>
                <form id="profile-form" method="POST">
                    <input type="hidden" name="action" value="reset_password">

                    <div class="form-group">
                        <label for="currentPassword">Current Password</label>
                        <div class="input-wrapper">
                            <?= html_password('currentPassword', 'class="form-control" id="currentPassword"'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="16" height="16" fill="currentColor">
                                <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                            </svg>
                        </div>
                        <small id="currentPasswordError" class="error-message text-danger" style="display:none;"></small>
                    </div>

                    <div class="form-group">
                        <label for="newPassword">New Password</label>
                        <div class="input-wrapper">
                            <?= html_password('newPassword', 'class="form-control" id="newPassword"'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="16" height="16" fill="currentColor">
                                <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                            </svg>
                        </div>
                        <small id="newPasswordError" class="error-message text-danger" style="display:none;"></small>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm New Password</label>
                        <div class="input-wrapper">
                            <?= html_password('confirmPassword', 'class="form-control" id="confirmPassword"'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="16" height="16" fill="currentColor">
                                <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                            </svg>
                        </div>
                        <small id="confirmPasswordError" class="error-message text-danger" style="display:none;"></small>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="save-btn">Save Changes</button>
                        <button type="button" class="cancel-btn">Cancel</button>
                    </div>

                    <div id="profile-message" class="message-container"></div>
                </form>
            </div>
        </div>
    </div>
</section>

<script src="/public/js/validation.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('profile-form');

        if (form) {
            form.addEventListener('submit', function(e) {
                // Run validation
                const isValid = validatePasswordForm(this);

                // If invalid, stop the form from submitting to PHP
                if (!isValid) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
<?php include '../footer.php' ?>