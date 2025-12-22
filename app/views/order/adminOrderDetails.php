<?php
session_start();
$title = "Order Details Page";
$pageCSS = "adminorderdetails.css";

include  '../../controllers/orderController.php';

// Handle AJAX update request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ajaxUpdate"])) {

  $orderController = new OrderController();

  foreach ($_POST["orders"] as $orderId) {
    $updated = $orderController->updateStatus($orderId, $_POST["status"]);
    $_SESSION['success_message'] = "Status updated successfully.";
  }
  echo "success";
  exit;
}

include '../adminheader.php';

$orderController = new OrderController();
$orders = $orderController->getOrderDetails();

if (!empty($orders)) {
  $orderId = $orders[0]["order_id"];
  $statuses = $orderController->getOrderStatus($orderId);
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
    <div class="dataDetailsHeader">
      <a href="#" class="back-link" onclick="history.back(); return false;">&#x293A;</a>
      <h1>Order Details</h1>
    </div>

    <div class="orderList">
      <?php if (!empty($orders)): ?>
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
                <h3 class="currentOrderStatus"><?= $orderData['order_status'] ?></h3>
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

            <!-- <?php if ($orderData['order_status'] != "Pending" && $orderData['order_status'] != "Delivered" && $orderData['order_status'] != "Completed" && $orderData['order_status'] != "Cancelled" && $orderData['order_status'] != "Refunded"): ?> -->
            <div class="btn-container">
              <button type="button" class="updateBtn" data-order-id="<?= $order_id ?>">Update Status</button>
            </div>
            <!-- <?php endif; ?> -->

          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="no-order">No order details found.</p>
      <?php endif; ?>
    </div>
  </div>

</div>

<div id="statusPopup" class="status-popup-overlay">
  <div class="status-popup-box">
    <h3>Update Order Status</h3>

    <p id="popupInfo"></p>

    <p>
      New status:
      <strong id="nextStatusText"></strong>
    </p>

    <div class="popup-actions">
      <button id="popupCancel">Cancel</button>
      <button id="popupConfirm">Confirm</button>
    </div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {

    const popup = document.getElementById("statusPopup");
    const popupInfo = document.getElementById("popupInfo");
    const nextStatusText = document.getElementById("nextStatusText");
    const btnCancel = document.getElementById("popupCancel");
    const btnConfirm = document.getElementById("popupConfirm");

    let selectedOrderId = null;
    let nextStatus = null;

    const nextStatusMap = {
      "Pending": "Paid",
      "Paid": "Packing",
      "Packing": "Out for Delivery",
      "Out for Delivery": "Delivered",
      "Delivered": "Completed",
      "Cancel Requested": "Cancelled"
    };

    document.querySelectorAll(".updateBtn").forEach(btn => {
      btn.addEventListener("click", () => {

        selectedOrderId = btn.dataset.orderId;

        const currentStatus = btn
          .closest(".order-card-wrapper")
          .querySelector(".currentOrderStatus")
          .textContent
          .trim();

        nextStatus = nextStatusMap[currentStatus];

        if (!nextStatus) {
          alert("No next status available.");
          return;
        }

        popupInfo.innerHTML = `
        Order ID: <strong>${selectedOrderId}</strong><br>
        Current status: <strong>${currentStatus}</strong>
      `;

        nextStatusText.textContent = nextStatus;

        popup.classList.add("show");
      });
    });

    btnCancel.addEventListener("click", () => {
      popup.classList.remove("show");
    });

    btnConfirm.addEventListener("click", () => {
      if (!selectedOrderId || !nextStatus) return;

      const formData = new FormData();
      formData.append("ajaxUpdate", "1");
      formData.append("status", nextStatus);
      formData.append("orders[]", selectedOrderId);

      fetch("", {
          method: "POST",
          body: formData
        })
        .then(res => res.text())
        .then(res => {
          console.log("AJAX response:", res);
          if (res.trim() === "success") {
            popup.classList.remove("show");
            location.reload();
          } else {
            alert("Update failed");
          }
        })
        .catch(err => {
          console.error(err);
          alert("Network error");
        });
    });

  });
</script>