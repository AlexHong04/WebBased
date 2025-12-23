<?php
$title = "Admin Profile Page";
$pageCSS = "profile.css";

require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../helpers/validation.php';
require_once __DIR__ . '/../../controllers/adminController.php';
require_once __DIR__ . '/../../helpers/auth.php';

authenticate();
$controller = new adminController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'updateProfile') {
    $controller->updateAdminProfile();
}

$profileData = $controller->getProfile();

include __DIR__ . '/../adminHeader.php';
?>

<section class="profile-section">
    <h1 class="section-title">My Profile</h1>

    <form class="profile-container" id="profile-form" method="POST" enctype="multipart/form-data">

        <div class="profile-sidebar">

            <div class="profile-avatar" id="avatarDropZone">
                <label for="profile-pic-input" class="avatar-label">
                    <?php
                    $isDefaultImg = empty($profileData['img_url']);
                    $imgSrc = $isDefaultImg
                        ? '/public/images/profile/user.png'
                        : '/public/images/profile/' . htmlspecialchars($profileData['img_url']);

                    $imgClass = $isDefaultImg ? 'default-avatar' : '';
                    ?>
                    <img id="profile-pic-preview"
                        class="<?= $imgClass ?>"
                        src="<?= $imgSrc ?>"
                        alt="Profile Picture">

                    <div class="plus">+</div>
                    <!-- <img id="profile-pic-preview"
                        src="<?= !empty($profileData['img_url'])
                                    ? '/public/images/profile/' . htmlspecialchars($profileData['img_url'])
                                    : '/public/images/profile/user.png' ?>"
                        alt="Profile Picture">
                    <div class="plus">+</div> -->
                </label>

                <input type="file" name="profile_pic" id="profile-pic-input" accept="image/*">
            </div>

            <h2 id="profile-name"><?= htmlspecialchars(($profileData['firstName'] ?? '') . ' ' . ($profileData['lastName'] ?? '')) ?></h2>
            <p id="profile-position"><?= htmlspecialchars($profileData['position'] ?? '') ?></p>

            <div class="profile-nav">
                <a href="profile.php" class="active">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="18" height="18" fill="currentColor" style="margin-right: 10px;">
                        <path d="M224 256c70.7 0 128-57.3 128-128S294.7 0 224 0 96 57.3 96 128s57.3 128 128 128zm89.6 32h-16.7c-22.2 10.2-46.9 16-72.9 16s-50.6-5.8-72.9-16h-16.7C60.2 288 0 348.2 0 422.4V464c0 26.5 21.5 48 48 48h352c26.5 0 48-21.5 48-48v-41.6c0-74.2-60.2-134.4-134.4-134.4z" />
                    </svg>
                    Profile Information
                </a>
            </div>
        </div>

        <div class="profile-content">
            <div class="profile-form-container">
                <h3>Edit Profile Information</h3>

                <input type="hidden" name="action" value="updateProfile">

                <div class="form-group-col">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <div class="input-wrapper">
                            <?php html_text('firstName', 'class="form-control" readonly', $profileData['firstName'] ?? ''); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="16" height="16" fill="currentColor">
                                <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <div class="input-wrapper">
                            <?php html_text('lastName', 'class="form-control" readonly', $profileData['lastName'] ?? ''); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="16" height="16" fill="currentColor">
                                <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="form-group-col">
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <div class="input-wrapper">
                            <?php html_text('phone', 'class="form-control" readonly', $profileData['phone'] ?? ''); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 512 512" width="16" height="16" fill="currentColor">
                                <path d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z" />
                            </svg>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <div class="input-wrapper">
                            <?php html_text('email', 'class="form-control" readonly', $profileData['email'] ?? ''); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 512 512" width="16" height="16" fill="currentColor">
                                <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">address</label>
                    <div class="input-wrapper">
                        <?php html_textarea('address', 'class="form-control" maxlength="1000" style="height:80px;" readonly', $profileData['address'] ?? ''); ?>
                        <svg xmlns="http://www.w3.org/2000/svg" class="input-icon textarea-icon" viewBox="0 0 576 512" width="16" height="16" fill="currentColor">
                            <path d="M575.8 255.5c0 18-15 32.1-32 32.1h-32l.7 160.2c0 2.7-.2 5.4-.5 8.1V472c0 22.1-17.9 40-40 40H456c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1H416 392c-22.1 0-40-17.9-40-40V448 384c0-17.7-14.3-32-32-32H256c-17.7 0-32 14.3-32 32v64 24c0 22.1-17.9 40-40 40H160 128.1c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2H104c-22.1 0-40-17.9-40-40V360c0-.9 0-1.9 .1-2.8V287.6H32c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z" />
                        </svg>
                    </div>
                    <small class="form-text">address, P.O. box, company name</small>
                </div>

                <div class="form-actions">
                    <button type="button" id="edit-btn" class="save-btn">Edit Profile</button>
                    <button type="button" class="cancel-btn" onclick="location.reload();">Cancel</button>
                </div>

                <div id="profile-message" class="message-container"></div>

            </div>
        </div>
    </form>
</section>

<?php showToast(); ?>
<script src="/public/js/validation.js"></script>
<script src="/public/js/profile.js"></script>