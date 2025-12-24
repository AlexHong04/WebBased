<?php
include '../../controllers/orderController.php';
require_once __DIR__ . '/../../helpers/auth.php';


// Handle AJAX update request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ajaxUpdate"])) {

  $orderController = new orderController();

  foreach ($_POST["orders"] as $orderId) {
    $orderController->updateStatus($orderId, $_POST["status"]);
  }
  echo "success";
  exit;
}

$title = "Order Listing Page";
$pageCSS = "datalisting.css";

include  '../adminHeader.php';
$orderController = new orderController();
$data = $orderController->index();

authenticate();
$filters    = $data['filters'];
$orders     = $data['orders'];
$pagination = $data['pagination'];

$sort = $filters['sort'] ?? 'order_id';
$dir  = $filters['dir'] ?? 'asc';
$search  = $filters['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$selectedText = $statusFilter !== '' ? $statusFilter : 'All Status';

$baseQuery = http_build_query([
  'status' => $statusFilter,
  'sort'   => $filters['sort'],
  'dir'    => $filters['dir']
]);

$href = http_build_query([
  'page'   => $filters['page'],
  'status' => $filters['status']
]);

$statusOption = [
  'Pending',
  'Paid',
  'Packing',
  'Out for Delivery',
  'Delivered',
  'Completed',
  'Cancel Requested',
  'Cancelled',
  'Refunded'
];

$fields = [
  'order_id' => 'Order ID',
  'customer_id' => 'Customer ID',
  'created_datetime' => 'Created At',
  'total_order_qty' => 'Item Quantity',
  'total_amount' => 'Total Amount (RM)',
  'redeemed_point' => 'Redeemed Point'
];
?>

<div class='dataListing'>
  <p>Order Listing</p>

  <div class="filters-pagination-container">
    <div class="filters-actions">
      <div class="filters">
        <div class="search-wrapper">
          <input
            type="text"
            id="dataSearch"
            placeholder="Search ID..."
            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" />
          <button type="button" id="clearSearch" aria-label="Clear search">
            &times;
          </button>
        </div>

        <div class="custom-select" id="statusSelect">
          <input type="hidden" id="statusFilter" value="<?= htmlspecialchars($currentStatus) ?>">
          <div class="selected"><?= htmlspecialchars($selectedText) ?></div>
          <ul class="options">
            <li data-value="">All Status</li>
            <?php foreach ($statusOption as $status): ?>
              <li data-value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>

      </div>

      <div class="pagination-container">
        <?= $pagination->render($baseQuery); ?>
      </div>

      <div id="actionButtons">
        <button id="viewBtn">View</button>
        <button id="updateBtn">Update</button>
      </div>
    </div>
  </div>

  <table class='data'>
    <thead>
      <tr class="thead">
        <th>Select</th>
        <?php table_headers($fields, $sort, $dir, $href); ?>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($orders)): ?>

        <?php foreach ($orders as $index => $o): ?>
          <tr>
            <td>
              <input
                type="checkbox"
                name="selected_orders[]"
                value="<?= $o['order_id'] ?>"
                class="dataCheckbox">
            </td>
            <td><?= $o['order_id'] ?></td>
            <td><?= $o['customer_id'] ?></td>
            <td><?= $o['created_datetime'] ?></td>
            <td><?= $o['total_order_qty'] ?></td>
            <td><?= $o['total_amount'] ?></td>
            <td><?= $o['redeemed_point'] ?></td>
            <td class="order-status"><?= $o['order_status'] ?></td>
          </tr>
        <?php endforeach ?>
      <?php else: ?>
        <tr>
          <td colspan="8">No orders found.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>


  <!-- Status Update Popup -->
  <div id="statusPopup" class="popup-overlay">
    <div class="popup-box">
      <h3>Update Order Status</h3>

      <p id="selectedCount"></p>

      <label>New Status:</label>
      <div class="status-custom-select-wrapper">
        <div class="status-custom-select" id="statusSelectPopup">
          <input type="hidden" id="newStatus" value="Pending">
          <div class="status-selected">Pending</div>
          <ul class="options">
            <?php foreach ($statusOption as $status): ?>
              <li data-value="<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($status) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>

      <div class="popup-actions">
        <button id="popupCancel">Cancel</button>
        <button id="popupConfirm">Confirm</button>
      </div>
    </div>
  </div>

  <div id="successPopup" class="successPopup"></div>

</div>

<script src="../../../public/js/orderActions.js"></script>