<?php
$title = "Order Rating & Review";
$pageCSS = "orderrating.css";

include '../header.php';
include '../../controllers/orderController.php';
include '../../controllers/reviewController.php';

$orderController = new OrderController();
$reviewController = new ReviewController();

if (!isset($_GET['id'])) {
  die("No order ID provided.");
}

$orderId = $_GET['id'];

$orderItems = $orderController->getReviewOrders($orderId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $reviewController->reviewOrder();

  header("Location: " . $_SERVER['REQUEST_URI']);
  exit;
}
?>

<section class="order-rating-section">
  <div class="order-rating-header">
    <a href="#" class="back-link" onclick="history.back(); return false;">&#x293A;</a>
    <h1>Order Rating & Review</h1>
  </div>

  <?php if (!empty($orderItems)): ?>
    <?php foreach ($orderItems as $order): ?>
      <div class="order-card">
        <div class="orderCard">
          <div class="col image-col">
            <img src="/public/images/<?= $order['category_name'] ?>/<?= $order['img_url'] ?>">
          </div>

          <div class="col info-col">
            <p class="name"><?= $order['product_name'] ?></p>
            <p class="description"><?= $order['description'] ?></p>
            <p class="variant">Variant: <?= $order['variant_name'] ?></p>
            <p class="payment">Ordered On: <?= $order['payment_time'] ?></p>
          </div>

          <div class="col qty-col">x<?= $order['order_qty'] ?></div>

          <div class="col subtotal-col">
            RM <?= number_format($order['price'] * $order['order_qty'], 2) ?>
          </div>

          <button
            class="review-btn"
            onclick="window.location.href='orderRatingDetails.php?order_id=<?= $order['order_id'] ?>&variant_id=<?= $order['product_variant_id'] ?>'">
            To Review
          </button>

        </div>
        <p>Review to get 10 points.</p>
        <img src="" />
      </div>
    <?php endforeach; ?>

  <?php else: ?>
    <p>No completed orders found.</p>
  <?php endif; ?>
</section>

<?php include '../footer.php' ?>