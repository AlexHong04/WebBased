<?php
$title = "Member Details Page";
$pageCSS = "adminmember.css";

session_start();

include '../../controllers/userController.php';

$controller = new UserController();
$data = $controller->getMembers();
$member = (object)$data;

$addresses = $controller->getMemberAddress();

$controller->updateMember();

include '../adminHeader.php';

function displayValue($value)
{
  return empty($value) && $value !== "0" ? "-" : $value;
}
?>

<section class="member-profile-section">
  <div class="memberProfileHeader">
    <a href="/app/views/member/memberListing.php" class="back-link">&#x293A;</a>
    <h1>Member Details</h1>
  </div>

  <?php if (isset($_SESSION['success_message'])): ?>
    <div id="successPopup" class="successPopup show">
      <?= $_SESSION['success_message']; ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error_message'])): ?>
    <div id="customPopup" class="customPopup show">
      <?= $_SESSION['error_message']; ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="customer_id" value="<?= $member->customer_id ?>">

    <div class="profile-container">

      <!-- Sidebar -->
      <div class="profile-sidebar">
        <div class="profile-avatar" id="avatarDropZone">
          <label for="profile-pic-input" class="avatar-label">
            <img id="profile-pic-preview"
              src="<?= (!empty($member->img_url) && trim($member->img_url) !== '')
                      ? '/public/images/profile/' . $member->img_url
                      : '/public/images/profile/user.png' ?>"
              alt="Profile Picture">
            <div class="plus">+</div>
            <div class="drop-overlay">Drop to Upload</div>
          </label>
          <input type="file" name="profile_pic" id="profile-pic-input" accept="image/*" style="display:none;">
        </div>


        <div class="sidebar-wrapper">
          <div class="profile-nav">
            <a href="#" class="tab active" data-target="profile-info">Profile Information</a>
          </div>
          <div class="address-nav">
            <a href="#" class="tab" data-target="address-info">Address Information</a>
          </div>


          <input type="hidden" name="isBlocked" id="isBlockedInput" value="<?= $member->isBlocked ?>">
          <div class="status-row">
            <label>Status:</label>
            <div id="statusDisplay" class="<?= $member->isBlocked == 1 ? 'blocked' : 'normal' ?>">
              <?= $member->isBlocked == 1 ? 'Blocked' : 'Normal' ?>
            </div>
          </div>

        </div>
      </div>

      <!-- Profile Information -->
      <div class="profile-content">
        <div class="profile-form-container" id="profile-info">
          <h3>Member Information</h3>
          <div class="profile-info-grid">
            <div class="info-group">
              <label>First Name</label>
              <input type="text" name="firstName" value="<?= htmlspecialchars($member->firstName) ?>">
            </div>
            <div class="info-group">
              <label>Last Name</label>
              <input type="text" name="lastName" value="<?= htmlspecialchars($member->lastName) ?>">
            </div>
            <div class="info-group">
              <label>Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($member->email) ?>">
            </div>
            <div class="info-group">
              <label>Phone</label>
              <input type="text" name="phone" value="<?= htmlspecialchars($member->phone) ?>">
            </div>
            <div class="info-group">
              <label>Reward Points</label>
              <input type="number" name="rewardPoint" value="<?= htmlspecialchars($member->rewardPoint) ?>">
            </div>
          </div>
          <div class="save-btn-wrapper">
            <button type="submit" class="save-btn">Save Changes</button>
          </div>

        </div>

        <!-- Address Information -->
        <div class="profile-form-container" id="address-info" style="display: none;">
          <h3>Address Information</h3>
          <?php if (!empty($addresses)): ?>
            <?php foreach ($addresses as $i => $addr): ?>
              <div class="address-wrapper <?= $addr['is_default'] == 1 ? 'default-address' : '' ?>">
                <p class="address-label"><?= $addr['is_default'] == 1 ? "Default Address" : "Address " . ($i + 1) ?></p>
                <div class="address-grid">
                  <input type="hidden" name="addresses[<?= $i ?>][address_id]" value="<?= $addr['address_id'] ?>">
                  <div class="info-group">
                    <label>Street</label>
                    <input type="text" name="addresses[<?= $i ?>][street_line]" value="<?= htmlspecialchars($addr['street_line']) ?>">
                  </div>
                  <div class="info-group">
                    <label>City</label>
                    <input type="text" name="addresses[<?= $i ?>][city]" value="<?= htmlspecialchars($addr['city']) ?>">
                  </div>
                  <div class="info-group">
                    <label>State</label>
                    <input type="text" name="addresses[<?= $i ?>][state]" value="<?= htmlspecialchars($addr['state']) ?>">
                  </div>
                  <div class="info-group">
                    <label>Postcode</label>
                    <input type="text" name="addresses[<?= $i ?>][postcode]" value="<?= htmlspecialchars($addr['postcode']) ?>">
                  </div>
                  <div class="info-group">
                    <label>Recipient Phone</label>
                    <input type="text" name="addresses[<?= $i ?>][recipient_phone]" value="<?= htmlspecialchars($addr['recipient_phone']) ?>">
                  </div>
                </div>
                <div class="save-btn-wrapper">
                  <button type="submit" class="save-btn">Save Changes</button>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>No addresses found.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </form>
  <!-- Status Confirmation Modal -->
  <div id="statusModal" class="modal">
    <div class="modal-content">
      <h3>Confirm Status Change</h3>
      <p id="modalMessage">Are you sure you want to update this member's status?</p>
      <div class="modal-buttons">
        <button id="cancelBtn" class="modal-btn cancel">Cancel</button>
        <button id="confirmBtn" class="modal-btn confirm">Confirm</button>
      </div>
    </div>
  </div>

</section>

<script src="../../../public/js/memberDetailsAction.js"></script>