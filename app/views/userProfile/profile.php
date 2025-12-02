<?php
$title = "Profile Page";
$pageCSS = "profile.css";

include '../header.php';
include '../../helpers/html.php'; 
include __DIR__ . '/../../controllers/userController.php';

$controller = new userController();
$controller->updateProfile();
$profileData = $controller->getProfile();

// if (!$profileData) {
//     header("Location: ../security/signIn.php");
//     exit();
// }

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

            <h2 id="profile-name"><?= htmlspecialchars(($profileData['firstName'] ?? '') . ' ' . ($profileData['lastName'] ?? '')) ?></h2>
            <p id="profile-gender"><?= htmlspecialchars($profileData['gender'] ?? '') ?></p>

            <div class="profile-nav">
                <a href="profile.php" class="active">Profile Information</a>
                <a href="resetPassword.php">Reset Password</a>
                <a href="wishlist.html">Wishlist</a>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-form-container">
                <h3>Edit Profile Information</h3>

                <form id="profile-form" method="POST">
                    <div class="form-group-col">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <?php 
                           
                            html_text('firstName', 'class="form-control" readonly', $profileData['firstName'] ?? ''); ?>
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <?php html_text('lastName', 'class="form-control" readonly', $profileData['lastName'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="form-group-col">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <?php html_text('phone', 'class="form-control" readonly', $profileData['phone'] ?? ''); ?>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <?php html_text('email', 'class="form-control" readonly', $profileData['email'] ?? ''); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="addressLine">Address Line</label>
                        <textarea name="addressLine" id="addressLine" class="form-control" maxlength="1000" style="height:80px;" readonly><?= htmlspecialchars($profileData['address'] ?? '') ?></textarea>
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

<?php include '../footer.php' ?>
<script src="../../../public/js/profile.js"></script>