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

  $memberController = new UserController();
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
// include  '../../controllers/memberController.php';

$memberController = new UserController();
$data = $memberController->index();
$members = $data["members"];
$pagination = $data["pagination"];

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

$page = $_GET['page'] ?? 1;
$sort = $_GET['sort'] ?? 'customer_id';
$dir  = $_GET['dir'] ?? 'asc';
$href = "page=$page";

usort($members, function ($a, $b) use ($sort, $dir) {
  $valA = is_object($a) ? $a->$sort : $a[$sort];
  $valB = is_object($b) ? $b->$sort : $b[$sort];

  if ($valA == $valB) return 0;

  if ($dir === 'asc') {
    return ($valA < $valB) ? -1 : 1;
  } else {
    return ($valA > $valB) ? -1 : 1;
  }
});
?>

<div class='dataListing'>
  <p>Member Listing</p>

  <div class="filters-pagination-container">
    <div class="filters-actions">
      <div class="filters">
        <input type="text" id="dataSearch" placeholder="Search customer ID, name...">

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
        <?= $pagination->render(); ?>
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
        <!-- <th>Customer Id</th>
        <th>Email</th>
        <th>First Name</th>
        <th>Last Name</th>
        <th>Phone</th>
        <th>Created At</th>
        <th>Updated At</th>
        <th>Reward Point</th> -->
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
            <td><?= displayValue($m->rewardPoint) ?></td>
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