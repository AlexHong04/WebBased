<?php
session_start();

$title = "Order Cancellation";
$pageCSS = "ordercancellation.css";

include '../../controllers/orderController.php';
include '../header.php';
require_once __DIR__ . '/../../helpers/auth.php';

authenticate();
$orderId = $_GET['id'];
$custId = $_SESSION['customerId'];
$orderController = new OrderController();
$orders = $orderController->getOrderDetails();
// $custId = $orders[0]['customer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $orderId = $_POST['order_id'] ?? $_GET['id'] ?? null;
  $reason = $_POST['reason'] ?? '';

  if ($orderId && $reason) {
    $orderController->cancelOrder($orderId, $reason);
    $orderController->sendCancelRequestEmail($orderId);
    $_SESSION['success_message'] = "Order cancellation request submitted successfully!";
    echo "success";
    exit;
  }
}
?>

<div class="order-cancellation-section">
  <div class="order-cancel-header">
    <a href="#" class="back-link" onclick="history.back(); return false;">&#x293A;</a>
    <h1>Order Cancellation</h1>
  </div>

  <div id="errorPopup" class="customPopup"></div>

  <?php if (!empty($orders)): ?>
    <div class="order-card-wrapper">
      <?php foreach ($orders as $order): ?>
        <div class="order-card">
          <div class="col image-col">
            <img src="/public/images/<?= $order['category_name'] ?>/<?= $order['img_url'] ?>"
              alt="<?= $order['product_name'] ?>">
          </div>
          <div class="col info-col">
            <p class="name"><?= $order['product_name'] ?></p>
            <p class="description"><?= $order['description'] ?></p>
            <p class="variant">Variant: <?= $order['variant_name'] ?></p>
          </div>
          <div class="col qty-col">x<?= $order['order_qty'] ?></div>
          <div class="col subtotal-col">
            RM <?= number_format($order['price'] * $order['order_qty'], 2) ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="cancel-container">
      <label>Choose Reason:</label>
      <div class="cancel-select" id="cancelSelect">
        <input type="hidden" name="reason" id="reason" value="">
        <div class="selected">Select Reason</div>
        <ul class="options">
          <li data-value="Ordered by mistake">Ordered by mistake</li>
          <li data-value="Found a better price elsewhere">Found a better price elsewhere</li>
          <li data-value="Delivery takes too long">Delivery takes too long</li>
          <li data-value="Changed mind">Changed mind</li>
          <li data-value="Product no longer needed">Product no longer needed</li>
          <li data-value="Other">Other</li>
        </ul>
      </div>
    </div>

    <div class="submit-btn">
      <button type="button" id="cancelBtn" data-order-id="<?= $orderId ?>">Cancel Order</button>
    </div>
  <?php else: ?>
    <p class="no-order">No orders available for cancellation.</p>
  <?php endif; ?>

  <!-- Confirmation Modal -->
  <div id="cancelModal" class="modal-overlay hidden">
    <div class="modal-box">
      <h3>Confirm Order Cancellation</h3>
      <p>Are you sure you want to cancel this order?</p>
      <div class="modal-actions">
        <button id="cancelRequest" class="cancel-btn">Cancel</button>
        <button id="confirmRequest" class="confirm-btn">Confirm</button>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const cancelSelect = document.getElementById("cancelSelect");
    const selected = cancelSelect.querySelector(".selected");
    const options = cancelSelect.querySelectorAll("ul.options li");
    const hiddenInput = document.getElementById("reason");
    const cancelBtn = document.getElementById("cancelBtn");
    const cancelModal = document.getElementById("cancelModal");
    const errorPopup = document.getElementById("errorPopup");
    const orderId = cancelBtn.dataset.orderId;

    setTimeout(() => {
      if (document.getElementById("successPopup")) document.getElementById("successPopup").classList.remove("show");
    }, 3000);

    // Dropdown toggle
    selected.addEventListener("click", e => {
      e.stopPropagation();
      cancelSelect.classList.toggle("open");
    });

    // Option selection
    options.forEach(option => {
      option.addEventListener("click", () => {
        hiddenInput.value = option.dataset.value;
        selected.textContent = option.textContent;
        cancelSelect.classList.remove("open");
      });
    });

    document.addEventListener("click", () => cancelSelect.classList.remove("open"));

    // Cancel button click
    cancelBtn.addEventListener("click", () => {
      if (!hiddenInput.value) {
        showError("Please select a cancellation reason.");
        return;
      }
      cancelModal.classList.remove("hidden");
    });

    // Modal cancel button
    document.getElementById("cancelRequest").addEventListener("click", () => {
      cancelModal.classList.add("hidden");
    });

    // Modal confirm button
    document.getElementById("confirmRequest").addEventListener("click", () => {
      fetch("", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: `order_id=${orderId}&reason=${encodeURIComponent(hiddenInput.value)}`
        })
        .then((response) => {
          if (response.ok) {
            window.location.href = "customerOrderhistory.php?id=<?= $custId ?>";
          }
        });
    });

    function showError(msg) {
      if (!errorPopup) return;
      errorPopup.textContent = msg;
      errorPopup.classList.add("show");

      setTimeout(() => {
        errorPopup.classList.remove("show");
      }, 3000);
    }
  });
</script>