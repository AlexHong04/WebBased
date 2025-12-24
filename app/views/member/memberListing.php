<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Get raw JSON
  $json = file_get_contents("php://input");

  // Check JSON
  if (!$json) {
    echo "ERROR: NO JSON";
    exit;
  }

  $data = json_decode($json, true);

  // Validate array
  if (!isset($data['customer_ids']) || !is_array($data['customer_ids'])) {
    echo "ERROR: INVALID IDS";
    exit;
  }

  require_once '../../controllers/userController.php';

  $memberController = new userController();
  $success = $memberController->updateStatus($data['customer_ids']);

  if ($success) {
    echo "SUCCESS";
  } else {
    echo "FAILED";
  }

  exit;
}

$title = "Member Listing Page";
$pageCSS = "datalisting.css";

include  '../adminHeader.php';
require_once __DIR__ . '/../../helpers/auth.php';

authenticate();
$memberController = new userController();
$data = $memberController->index();

$filters    = $data['filters'];
$members     = $data['members'];
$pagination = $data['pagination'];

$sort = $filters['sort'] ?? 'customer_id';
$dir  = $filters['dir'] ?? 'asc';
$search  = $filters['search'] ?? '';

$activeStatus = $_GET['activeStatus'] ?? '';
$blockStatus = $_GET['blockStatus'] ?? '';
$activeSelectedText = $activeStatus !== '' ? $activeStatus : 'All';
$blockSelectedText = $blockStatus !== '' ? $blockStatus : 'All';

$baseQuery = http_build_query([
  'activeStatus' => $activeStatus,
  'blockStatus' => $blockStatus,
  'sort'   => $filters['sort'],
  'dir'    => $filters['dir']
]);

$href = http_build_query([
  'page'   => $filters['page'],
  'activeStatus' => $filters['activeStatus'],
  'blockStatus' => $filters['blockStatus'],
]);

function displayValue($value)
{
  return empty($value) && $value !== "0" ? "-" : $value;
}

function activeStatusLabel($value)
{
  if ($value == 1) {
    return "<span style='color: green; font-weight: bold;'>Yes</span>";
  }
  return "<span style='color: red; font-weight: bold;'>No</span>";
}

function blockStatusLabel($value)
{
  if ($value == 1) {
    return "<span style='color: red; font-weight: bold;'>Yes</span>";
  }
  return "<span style='color: green; font-weight: bold;'>No</span>";
}

$fields = [
  'customer_id' => 'Customer ID',
  'email' => 'Email',
  'firstName' => 'First Name',
  'lastName' => 'Last Name',
  'phone' => 'Phone',
  'created_at' => 'Created At',
  'updated_at' => 'Updated At',
  'rewardPoint' => 'Reward Point'
];
?>

<div class='dataListing'>
  <p>Member Listing</p>

  <div class="filters-pagination-container">
    <div class="filters-actions">
      <div class="filters">
        <div class="search-wrapper">
          <input
            type="text"
            id="dataSearch"
            placeholder="Search..."
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" />
          <button type="button" id="clearSearch" aria-label="Clear search">
            &times;
          </button>
        </div>

        <div class="custom-select" id="activeSelect">
          <input type="hidden" id="activeFilter" value="">
          <div class="selected">All Active Status</div>
          <ul class="options">
            <li data-value="">All</li>
            <li data-value="Yes">Active</li>
            <li data-value="No">Inactive</li>
          </ul>
        </div>

        <div class="custom-select" id="blockedSelect">
          <input type="hidden" id="blockedFilter" value="">
          <div class="selected">All Blocked Status</div>
          <ul class="options">
            <li data-value="">All</li>
            <li data-value="Yes">Blocked</li>
            <li data-value="No">Not Blocked</li>
          </ul>
        </div>


      </div>

      <div class="pagination-container">
        <?= $pagination->render($baseQuery); ?>
      </div>

      <div id="actionButtons">
        <button id="viewBtn">View</button>
        <button id="updateBtn">Block/Unblock User</button>
      </div>

    </div>
  </div>

  <table class='data'>
    <thead>
      <tr>
        <th>Select</th>
        <?php table_headers($fields, $sort, $dir, $href); ?>
        <th>Active</th>
        <th>Blocked</th>
      </tr>
    </thead>

    <tbody>
      <?php if (!empty($members)): ?>
        <?php foreach ($members as $index => $m): ?>
          <?php
          $m = (object) $m;
          ?>
          <tr>
            <td><input type="checkbox" name="selected_datas[]" value="<?= $m->customer_id ?>" class="dataCheckbox"></td>
            <!-- <td><?= $index + 1 ?></td> -->
            <td><?= displayValue($m->customer_id) ?></td>
            <td><?= displayValue($m->email) ?></td>
            <td><?= displayValue($m->firstName) ?></td>
            <td><?= displayValue($m->lastName) ?></td>
            <td><?= displayValue($m->phone) ?></td>
            <td><?= displayValue($m->created_at) ?></td>
            <td><?= displayValue($m->updated_at) ?></td>
            <td><?= $m->rewardPoint ?></td>
            <td><?= activeStatusLabel($m->isActive) ?></td>
            <td><?= blockStatusLabel($m->isBlocked) ?></td>
          </tr>
        <?php endforeach ?>
      <?php else: ?>
        <tr>
          <td colspan="11">No members found.</td>
        </tr>
      <?php endif; ?>
    </tbody>

  </table>
</div>

<div id="toast-container"></div>

<script src="../../../public/js/memberActions.js"></script>