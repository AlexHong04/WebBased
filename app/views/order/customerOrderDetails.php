<?php
session_start();
include '../../controllers/orderController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_order') {
  ob_clean();
  $orderController = new OrderController();
  header('Content-Type: application/json');
  $orderController->completeOrder();
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $orderId = $_POST['order_id'] ?? null;
  $status  = $_POST['status'] ?? null;

  if ($orderId && $status) {
    $orderController = new OrderController();
    $orderController->updateStatus($orderId, $status);
  }
}

$title = "Order Details Page";
$pageCSS = "orderhistorydetails.css";

$orderController = new OrderController();
$orders = $orderController->getOrderDetails();

include '../header.php';

if (!empty($orders)) {
  $orderId = $orders[0]["order_id"];
  $statuses = $orderController->getOrderStatus($orderId);
  $isReviewed = $orderController->getReviewOrders($orderId);
}

$groupedOrders = [];

foreach ($orders as $row) {
  $order_id = $row['order_id'];

  if (!isset($groupedOrders[$order_id])) {
    $groupedOrders[$order_id] = [
      "order_status" => $row['order_status'],
      "total_amount" => $row['total_amount'],
      "tax_amount" => $row['tax_fee'],
      "transaction_time" => $row['earliest_time'],
      "payment_time" => $row['payment_time'],
      "items" => []
    ];
  }

  $groupedOrders[$order_id]["items"][] = [
    "product_name" => $row['product_name'],
    "variant_name" => $row['variant_name'],
    "description" => $row['description'],
    "category_name" => $row['category_name'],
    "img_url" => $row['img_url'],
    "price" => $row['price'],
    "order_qty" => $row['order_qty']
  ];
}

$orders = $groupedOrders;


foreach ($orders as $orderId => $orderData) {
  $status = $orderData['order_status'];
  $statusClass = '';

  switch ($status) {
    case 'Pending':
    case 'Cancel Requested':
      $statusClass = 'status-alert';
      break;

    case 'Paid':
    case 'Packing':
    case 'Out for Delivery':
    case 'Delivered':
      $statusClass = 'status-progress';
      break;

    case 'Completed':
      $statusClass = 'status-success';
      break;

    case 'Cancelled':
    case 'Refunded':
      $statusClass = 'status-final-fail';
      break;

    default:
      $statusClass = 'status-default';
  }
}

function displayValue($value)
{
  return empty($value) && $value !== "0" ? "-" : $value;
}

function calSubtotal($subtotal, $tax)
{
  return $subtotal + $tax + 5.00;
}


?>

<div class="orderHistoryDetails">
  <div class="dataDetailsBox">
    <?php if (!empty($_SESSION['success_message'])): ?>
      <div id="successPopup" class="successPopup show">
        <?= $_SESSION['success_message'] ?>
      </div>
      <?php unset($_SESSION['success_message']); ?>

      <script>
        setTimeout(() => {
          const popup = document.getElementById("successPopup");
          if (popup) popup.classList.remove("show");
        }, 3000);
      </script>
    <?php endif; ?>

    <div class="dataDetailsHeader">
      <a href="#" class="back-link" onclick="history.back(); return false;">&#x293A;</a>
      <h1>Order Details</h1>
    </div>

    <div class="orderList">
      <?php if (!empty($orders)): ?>
        <?php foreach ($orders as $order_id => $orderData): ?>
          <div class="order-card-wrapper">

            <div class="orderHeader">
              <h2>Order ID: <?= $order_id ?></h2>
              <div class="orderStatus <?= $statusClass ?>">
                <h3><?= $orderData['order_status'] ?></h3>
              </div>
            </div>

            <?php foreach ($orderData['items'] as $item): ?>
              <div class="orderCard">

                <div class="col image-col">
                  <img src="/public/images/<?= $item['category_name'] ?>/<?= $item['img_url'] ?>"
                    alt="<?= $item['product_name'] ?>">
                </div>

                <div class="col info-col">
                  <p class="name"><?= $item['product_name'] ?></p>
                  <p class="description"><?= $item['description'] ?></p>
                  <p class="variant">Variant: <?= $item['variant_name'] ?></p>
                </div>

                <div class="col qty-col">
                  <p class="qty">x<?= $item['order_qty'] ?></p>
                </div>

                <div class="col subtotal-col">
                  <p class="subtotal">
                    RM <?= number_format($item['price'] * $item['order_qty'], 2) ?>
                  </p>
                </div>

              </div>
            <?php endforeach; ?>

            <div class="orderSummary">

              <p class="total_before_tax">
                <span class="label">Subtotal (RM):</span>
                <span class="value"><?= number_format($orderData['total_amount'], 2) ?></span>
              </p>
              <p class="order_tax">
                <span class="label">Tax Charges (RM):</span>
                <span class="value"><?= number_format($orderData['tax_amount'], 2) ?></span>
              </p>

              <p class="order_delivery_fee">
                <span class="label">Delivery Fee (RM):</span>
                <span class="value">5.00</span>
              </p>

              <p class="order_total_amount">
                <span class="label">Total (RM):</span>
                <span class="value"><?= number_format(calSubtotal($orderData['total_amount'], $orderData['tax_amount']), 2) ?></span>

              </p>
            </div>

            <div class="transactionSummary">
              <?php foreach ($statuses as $status): ?>
                <p class="order_status_time">
                  <span class="label"><?= $status['order_status'] ?>:</span>
                  <span class="value"><?= $status['created_datetime'] ?></span>
                </p>
              <?php endforeach; ?>
            </div>

            <?php if ($orderData['order_status'] != "Cancel Requested" && $orderData['order_status'] != "Cancelled" && $orderData['order_status'] != "Refunded"): ?>
              <div class="btn-container">
                <?php if ($orderData['order_status'] == "Pending"): ?>
                  <button class="order-action-btn pay-now"
                    onclick="window.location.href='/id=<?= $order_id ?>'">Pay Now</button>

                <?php elseif ($orderData['order_status'] == "Paid"): ?>
                  <button class="order-action-btn cancel-order"
                    onclick="window.location.href='cancelOrder.php?id=<?= $order_id ?>'">Cancel</button>

                <?php elseif ($orderData['order_status'] == "Out For Delivery" || $orderData['order_status'] == "Packing"): ?>
                  <button class="order-action-btn cancel-order"
                    onclick="window.location.href='cancelOrder.php?id=<?= $order_id ?>'">Cancel</button>
                  <button class="order-action-btn track-order"
                    onclick="window.location.href='deliveryTracking.php?id=<?= $order_id ?>'">Track Order</button>

                <?php elseif ($orderData['order_status'] == "Out For Delivery"): ?>
                  <button class="order-action-btn track-order"
                    onclick="window.location.href='deliveryTracking.php?id=<?= $order_id ?>'">Track Order</button>

                <?php elseif ($orderData['order_status'] == "Delivered"): ?>
                  <button class="order-action-btn track-order"
                    onclick="window.location.href='deliveryTracking.php?id=<?= $order_id ?>'">Track Order</button>
                  <button class="order-action-btn received" data-order-id="<?= $order_id ?>">Received</button>
                <?php elseif ($orderData['order_status'] == "Completed" && $isReviewed): ?>
                  <button type="button"
                    class="order-action-btn review"
                    onclick="window.location.href='orderRating.php?id=<?= $order_id ?>'">
                    Review</button>
                <?php endif; ?>

              </div>
            <?php endif; ?>

          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="no-order">No order items found.</p>
      <?php endif; ?>

    </div>
    <div id="receivedModal" class="modal-overlay hidden">
      <div class="modal-box">
        <h3>Confirm Order Received</h3>
        <p>Have you received this order?</p>

        <div class="modal-actions">
          <button id="cancelReceived" class="cancel-btn">Cancel</button>
          <button id="confirmReceived" class="confirm-btn">Received</button>
        </div>
      </div>
    </div>
  </div>

</div>

<script src="../../../public/js/orderDetailsAction.js"></script>

<?php include '../footer.php' ?>