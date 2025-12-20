<?php
include '../../controllers/orderController.php';

$title = "Order History Page";
$pageCSS = "orderhistory.css";

include '../header.php';

$orderController = new OrderController();
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

?>

<div class="orderHistory">
  <div class="dataDetailsBox">
    <p>Order History</p>

    <input type="text" id="orderSearch" placeholder="Search by order ID, product name..." />

    <div class="statusTabs">
      <button class="tab active" data-status="">All</button>
      <button class="tab" data-status="Pending">To Pay</button>
      <button class="tab" data-status="Paid Packing Out_for_delivery Delivered">To Receive</button>
      <button
        class="tab"
        data-status="<?= (
                        $orderData['order_status'] === 'Completed' &&
                        empty($isReviewed)
                      ) ?>">
        To Review
      </button>
      <button class="tab" data-status="Cancel_requested Cancelled Refunded">Refund & Cancellations</button>
    </div>

    <div class="orderList">

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

          <p class="order_total_amount">Total: RM <?= number_format($orderData['total_amount'], 2) ?></p>

          <div class="btn-container">
            <?php if ($orderData['order_status'] == "Pending"): ?>
              <button type="button"
                class="order-action-btn pay-now">
                <!-- onclick="window.location.href='/payment.php?id=<?= $order_id ?>'"> -->
                Pay Now
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
            <?php elseif ($orderData['order_status'] == "Out For Delivery" || $orderData['order_status'] == "Cancel Requested" || $orderData['order_status'] == "Cancelled" || $orderData['order_status'] == "Refunded"): ?>
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