document.addEventListener("DOMContentLoaded", () => {
  let selectedOrderId = null;

  document.querySelectorAll(".received").forEach((btn) => {
    btn.addEventListener("click", () => {
      selectedOrderId = btn.dataset.orderId;
      document.getElementById("receivedModal").classList.remove("hidden");
    });
  });

  document.getElementById("cancelReceived").addEventListener("click", () => {
    document.getElementById("receivedModal").classList.add("hidden");
    selectedOrderId = null;
  });

  document.getElementById("confirmReceived").addEventListener("click", () => {
    if (!selectedOrderId) return;

    fetch("customerOrderDetails.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      // body: `order_id=${selectedOrderId}&status=Completed`,
      body: `action=complete_order&order_id=${selectedOrderId}&status=Completed`,
    })
      .then((res) => res.json()) 
      .then((data) => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || "Failed to update order.");
            document.getElementById("receivedModal").classList.add("hidden");
        }
      })
      .catch(err => {
          console.error(err);
          alert("An error occurred.");
      });
  });
});
