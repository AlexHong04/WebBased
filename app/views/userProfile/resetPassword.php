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
                <a href="profile.php">Profile Information</a>
                <a href="resetPassword.php" class="active">Reset Password</a>
                <a href="wishlist.php">Wishlist</a>
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