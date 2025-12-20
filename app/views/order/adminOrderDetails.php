<?php
$title = "Order Details Page";
$pageCSS = "orderhistorydetails.css";

include  '../../controllers/orderController.php';

// Handle AJAX update request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ajaxUpdate"])) {

  $orderController = new OrderController();

  foreach ($_POST["orders"] as $orderId) {
    $updated = $orderController->updateStatus($orderId, $_POST["status"]);
    if (!$updated) {
      echo "Failed to update order $orderId";
      exit;
    }
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
              <div class="orderStatus">
                <h3 id="currentOrderStatus"><?= $orderData['order_status'] ?></h3>
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

            <?php if ($orderData['order_status'] != "Pending" && $orderData['order_status'] != "Delivered" && $orderData['order_status'] != "Completed" && $orderData['order_status'] != "Cancelled" && $orderData['order_status'] != "Refunded"): ?>
              <div class="btn-container">
                <button class="updateBtn" data-order-id="<?= $order_id ?>">Update Status</button>
              </div>
            <?php endif; ?>

          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="no-order">No order details found.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Status Update Popup -->
  <div id="statusPopup" class="popup-overlay">
    <div class="popup-box">
      <h3>Update Order Status</h3>

      <p id="selectedCount"></p>

      <label>New Status:</label>
      <div class="status-custom-select-wrapper">
        <div class="status-custom-select" id="statusSelectPopup">
          <input type="hidden" id="newStatus" value="Pending">
          <div class="status-selected"></div>
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
  <div id="errorPopup" class="customPopup"></div>

</div>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const popup = document.getElementById("statusPopup");
    const popupCancel = document.getElementById("popupCancel");
    const popupConfirm = document.getElementById("popupConfirm");
    const statusSelect = document.getElementById("newStatus");
    const selectedCount = document.getElementById("selectedCount");
    const popupSelect = document.getElementById("statusSelectPopup");
    const selected = popupSelect.querySelector(".status-selected");
    const options = popupSelect.querySelectorAll(".options li");
    const hiddenInput = document.getElementById("newStatus");
    const errorPopup = document.getElementById("errorPopup");
    const updateButtons = document.querySelectorAll(".updateBtn");
    const orderCards = document.querySelectorAll(".order-card-wrapper");

    // Define the status flow
    const statusFlow = [
      "Pending",
      "Paid",
      "Packing",
      "Out for Delivery",
      "Delivered",
      "Completed",
      "Cancel Requested",
      "Cancelled",
      "Refunded",
    ];

    updateButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        const orderId = btn.dataset.orderId;
        hiddenInput.dataset.orderId = orderId;

        const currentStatusEl = btn.closest(".order-card-wrapper").querySelector("#currentOrderStatus");
        const currentStatus = currentStatusEl.textContent.trim();

        selectedCount.innerHTML = `Order ID: ${orderId}<br>Current status: ${currentStatus}`;

        // Filter popup options: only allow next statuses
        options.forEach(option => {
          const value = option.dataset.value;
          const currentIndex = statusFlow.indexOf(currentStatus);
          option.style.display = (statusFlow.indexOf(value) === currentIndex + 1) ? "block" : "none";
        });

        const firstAllowed = Array.from(options).find(opt => opt.style.display !== "none");
        if (firstAllowed) {
          hiddenInput.value = firstAllowed.dataset.value;
        } else {
          selected.textContent = "No available status";
          hiddenInput.value = "";
        }

        popup.style.display = "flex";
      });
    });

    // Cancel popup
    popupCancel.addEventListener("click", () => (popup.style.display = "none"));

    popupConfirm.addEventListener("click", () => {
      const newStatus = hiddenInput.value;
      if (!newStatus) return;

      const orderId = hiddenInput.dataset.orderId;
      const formData = new FormData();
      formData.append("ajaxUpdate", "1");
      formData.append("status", newStatus);
      formData.append("orders[]", hiddenInput.dataset.orderId);

      fetch("", {
          method: "POST",
          body: formData
        })
        .then(res => res.text())
        .then(result => {
          if (result.trim() === "success") {
            const card = document.querySelector(`.order-card-wrapper [data-order-id="${hiddenInput.dataset.orderId}"]`).closest(".order-card-wrapper");
            card.querySelector("#currentOrderStatus").textContent = newStatus;
            popup.style.display = "none";
            showSuccessToast("Order status updated!");
            setTimeout(() => {
              location.reload();
            }, 1500); // 1.5 seconds delay
          } else {
            console.error("Server returned an error:", result.trim());
            showError("Failed to update order status.");
          }
        })
        .catch(err => {
          console.error("Fetch error:", err); // log fetch/network errors
          showError("Network error: " + err);
        });
    });

    // Toast
    function showSuccessToast(message) {
      const popup = document.getElementById("successPopup");
      popup.innerText = message;
      popup.classList.add("show");
      setTimeout(() => popup.classList.remove("show"), 3000);
    }

    function showError(msg) {
      if (!errorPopup) return;
      errorPopup.textContent = msg;
      errorPopup.classList.add("show");

      // Auto-hide after 3 seconds
      setTimeout(() => {
        errorPopup.classList.remove("show");
      }, 3000);
    }

    document.querySelectorAll(".custom-select").forEach((select) => {
      const selected = select.querySelector(".selected");
      const options = select.querySelector(".options");

      selected.addEventListener("click", () => select.classList.toggle("open"));
      options.querySelectorAll("li").forEach((option) => {
        option.addEventListener("click", () => {
          selected.textContent = option.textContent;
          document.getElementById("statusFilter").value = option.dataset.value;
          select.classList.remove("open");
          filterTable();
        });
      });

      document.addEventListener("click", (e) => {
        if (!select.contains(e.target)) select.classList.remove("open");
      });
    });
  });
</script>