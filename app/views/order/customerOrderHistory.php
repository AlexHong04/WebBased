<?php
include '../../controllers/orderController.php';
require_once __DIR__ . '/../../helpers/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();
authenticate();

$customerId = $_SESSION['customerId'] ?? null;

$title = "Order History Page";
$pageCSS = "orderhistory.css";

if (isset($_GET['status']) && $_GET['status'] === 'pending_payment') {
  // $_SESSION['flash_warning'] = "Payment cancelled. You can complete payment here.";
  $toastMsg = "Payment cancelled. You can complete payment here.";
  $toastType = "warning";
}

include '../header.php';

$orderController = new orderController();
$orders = $orderController->getOrderItems();

$groupedOrders = [];

foreach ($orders as $row) {
  $order_id = $row['order_id'];
  $isReviewed = $orderController->getReviewOrders($order_id);

  if (!isset($groupedOrders[$order_id])) {
    $groupedOrders[$order_id] = [
      "order_status" => $row['order_status'],
      "total_amount" => $row['total_amount'],
      "isReviewed" => $isReviewed,
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

function displayValue($value)
{
  return empty($value) && $value !== "0" ? "-" : $value;
}

?>

<div class="orderHistory">
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
  <div class="dataDetailsBox">

    <?php if (!empty($toastMsg)): ?>
      <div id="toast-notification" class="toast-notification toast-<?= $toastType ?>" style="display: flex;">
        <div class="toast-content">
          <i class="fas fa-exclamation-circle toast-icon"></i>
          <span class="toast-message"><?= htmlspecialchars($toastMsg) ?></span>
        </div>
        <div class="toast-progress"></div>
      </div>
      <script>
        setTimeout(() => {
          const toast = document.getElementById("toast-notification");
          if (toast) {
            toast.style.transition = "opacity 0.5s ease";
            toast.style.opacity = "0";
            toast.style.transform = "translateY(-20px)";
            setTimeout(() => {
              toast.style.display = "none";
            }, 500);
          }
        }, 3000);
      </script>
    <?php endif; ?>

    <p>Order History</p>

    <input type="text" id="orderSearch" placeholder="Search by order ID, product name..." />

    <div class="statusTabs">
      <button class="tab active" data-status="">All</button>
      <button class="tab" data-status="Pending">To Pay</button>
      <button class="tab" data-status="Paid Packing Out for Delivery Delivered">To Receive</button>
      <button
        class="tab"
        data-status="Completed">
        To Review
      </button>
      <button class="tab" data-status="Cancel Requested Cancelled Refunded">Refund & Cancellations</button>
    </div>

    <div class="orderList">

      <?php foreach ($orders as $order_id => $orderData): ?>
        <?php
        $status = strtolower(trim($orderData['order_status']));
        $statusClass = '';

        switch ($status) {
          case 'pending':
          case 'cancel requested':
            $statusClass = 'status-alert';
            break;

          case 'paid':
          case 'packing':
          case 'out for delivery':
          case 'delivered':
            $statusClass = 'status-progress';
            break;

          case 'completed':
            $statusClass = 'status-success';
            break;

          case 'cancelled':
          case 'refunded':
          case 'reviewed':
            $statusClass = 'status-final-fail';
            break;

          default:
            $statusClass = 'status-default';
        }
        ?>
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

          <p class="order_total_amount">Total: RM <?= number_format($orderData['total_amount'], 2) ?></p>

          <div class="btn-container">
            <?php if ($orderData['order_status'] == "Pending"): ?>
              <button type="button"
                class="order-action-btn pay-now"
                onclick="window.location.href='/app/views/shoppingCart/payment.php?order_id=<?= $order_id ?>'">
                Pay Now
              </button>
              <button type="button"
                class="order-action-btn view-order"
                onclick="window.location.href='customerOrderDetails.php?id=<?= $order_id ?>'">
                View Order
              </button>
            <?php elseif ($orderData['order_status'] == "Paid" || $orderData['order_status'] == "Packing"): ?>
              <button type="button"
                class="order-action-btn cancel-order"
                onclick="window.location.href='cancelOrder.php?id=<?= $order_id ?>'">
                Cancel
              </button>
              <button type="button"
                class="order-action-btn view-order"
                onclick="window.location.href='customerOrderDetails.php?id=<?= $order_id ?>'">
                View Order
              </button>
            <?php elseif ($orderData['order_status'] == "Out for Delivery" || $orderData['order_status'] == "Cancel Requested" || $orderData['order_status'] == "Cancelled" || $orderData['order_status'] == "Refunded"): ?>
              <button type="button"
                class="order-action-btn view-order"
                onclick="window.location.href='customerOrderDetails.php?id=<?= $order_id ?>'">
                View Order
              </button>
            <?php elseif ($orderData['order_status'] == "Delivered"): ?>
              <button type="button"
                class="order-action-btn view-order"
                onclick="window.location.href='customerOrderDetails.php?id=<?= $order_id ?>'">
                View Order
              </button>
              <button
                class="order-action-btn received"
                onclick="window.location.href='customerOrderDetails.php?id=<?= $order_id ?>'">
                Received
              </button>
            <?php elseif ($orderData['order_status'] == "Completed" && $orderData['isReviewed'] != null): ?>
              <button type="button"
                class="order-action-btn view-order"
                onclick="window.location.href='customerOrderDetails.php?id=<?= $order_id ?>'">
                View Order
              </button>
              <button type="button"
                class="order-action-btn review"
                onclick="window.location.href='orderRating.php?id=<?= $order_id ?>'">
                Review Order </button>
            <?php elseif ($orderData['order_status'] == "Completed"): ?>
              <button type="button"
                class="order-action-btn view-order"
                onclick="window.location.href='customerOrderDetails.php?id=<?= $order_id ?>'">
                View Order
              </button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </div>

</div>

<script src="../../../public/js/orderHistoryAction.js"></script>

<?php include '../footer.php' ?>