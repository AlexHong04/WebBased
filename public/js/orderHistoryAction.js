document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("orderSearch");
  const tabs = document.querySelectorAll(".statusTabs .tab");
  const orderCards = document.querySelectorAll(".order-card-wrapper");

  let activeStatus = ""; // Current status filter

  // Filter function
  function filterOrders() {
    const searchValue = searchInput.value.toLowerCase().trim();

    orderCards.forEach((card) => {
      const orderID = card
        .querySelector(".orderHeader h2")
        .textContent.toLowerCase();
      const productName = card
        .querySelector(".orderCard .name")
        .textContent.toLowerCase();
      const status = card
        .querySelector(".orderStatus h3")
        .textContent.toLowerCase();

      // Check search and status
      const matchesSearch =
        orderID.includes(searchValue) || productName.includes(searchValue);
      const matchesStatus =
        activeStatus === "" ||
        activeStatus.split(" ").some((s) => status.includes(s.toLowerCase()));

      if (matchesSearch && matchesStatus) {
        card.style.display = "block";
      } else {
        card.style.display = "none";
      }
    });
  }

  // Search input event
  searchInput.addEventListener("input", filterOrders);

  // Tab click event
  tabs.forEach((tab) => {
    tab.addEventListener("click", () => {
      // Remove active class from all tabs
      tabs.forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");

      // Update active status
      activeStatus = tab.dataset.status;
      filterOrders();
    });
  });

  // let selectedOrderId = null;

  // document.querySelectorAll(".received").forEach((btn) => {
  //   btn.addEventListener("click", () => {
  //     selectedOrderId = btn.dataset.orderId;
  //     document.getElementById("receivedModal").classList.remove("hidden");
  //   });
  // });

  // document.getElementById("cancelReceived").addEventListener("click", () => {
  //   document.getElementById("receivedModal").classList.add("hidden");
  //   selectedOrderId = null;
  // });

  // document.getElementById("confirmReceived").addEventListener("click", () => {
  //   if (!selectedOrderId) return;

  // fetch("customerOrderHistory.php", {
  //   method: "POST",
  //   headers: {
  //     "Content-Type": "application/x-www-form-urlencoded",
  //   },
  //   body: `order_id=${selectedOrderId}&status=Completed`,
  // })
  //   .then((res) => res.text())
  //   .then(() => {
  //     location.reload();
  //   });
});
