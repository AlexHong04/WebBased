document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("orderSearch");
  const tabs = document.querySelectorAll(".statusTabs .tab");
  const orderCards = document.querySelectorAll(".order-card-wrapper");

  let activeStatus = "";

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
      tabs.forEach((t) => t.classList.remove("active"));
      tab.classList.add("active");

      activeStatus = tab.dataset.status;
      filterOrders();
    });
  });
});
