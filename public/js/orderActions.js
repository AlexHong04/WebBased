document.addEventListener("DOMContentLoaded", function () {
  const checkboxes = document.querySelectorAll(".dataCheckbox");
  const viewBtn = document.getElementById("viewBtn");
  const updateBtn = document.getElementById("updateBtn");

  const popup = document.getElementById("statusPopup");
  const popupCancel = document.getElementById("popupCancel");
  const popupConfirm = document.getElementById("popupConfirm");
  const statusSelect = document.getElementById("newStatus");
  const selectedCount = document.getElementById("selectedCount");

  const searchInput = document.getElementById("dataSearch");
  const statusFilter = document.getElementById("statusFilter");
  const tableRows = document.querySelectorAll(".data tbody tr");

  const popupSelect = document.getElementById("statusSelectPopup");
  const selected = popupSelect.querySelector(".status-selected");
  const options = popupSelect.querySelectorAll(".options li");
  const hiddenInput = document.getElementById("newStatus");

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

  // Update buttons based on selected checkboxes
  function updateButtons() {
    const checkedBoxes = document.querySelectorAll(".dataCheckbox:checked");
    const checkedCount = checkedBoxes.length;

    if (checkedCount === 0) {
      viewBtn.style.display = "none";
      updateBtn.style.display = "none";
      return;
    }

    const statuses = Array.from(checkedBoxes).map((cb) =>
      cb.closest("tr").querySelector(".order-status").textContent.trim()
    );
    const allSameStatus = statuses.every((status) => status === statuses[0]);

    if (checkedCount === 1) {
      viewBtn.style.display = "inline-block";
      updateBtn.style.display = "inline-block";
    } else if (checkedCount > 1 && allSameStatus) {
      viewBtn.style.display = "none";
      updateBtn.style.display = "inline-block";
    } else {
      viewBtn.style.display = "none";
      updateBtn.style.display = "none";
    }
  }

  // Show popup when clicking Update
  updateBtn.addEventListener("click", () => {
    const checkedBoxes = document.querySelectorAll(".dataCheckbox:checked");
    if (checkedBoxes.length === 0) return;

    const currentStatus = checkedBoxes[0]
      .closest("tr")
      .querySelector(".order-status")
      .textContent.trim();
    selectedCount.innerHTML = `You selected ${checkedBoxes.length} order(s).<br><br>Current status: ${currentStatus}`;

    // Filter popup options: only allow next statuses
    options.forEach((option) => {
      const value = option.dataset.value;
      const currentIndex = statusFlow.indexOf(currentStatus);
      const nextIndex = currentIndex + 1;

      if (statusFlow.indexOf(value) === nextIndex) {
        option.style.display = "block";
      } else {
        option.style.display = "none";
      }
    });

    // Auto-select first allowed status
    const firstAllowed = Array.from(options).find(
      (opt) => opt.style.display !== "none"
    );
    if (firstAllowed) {
      selected.textContent = firstAllowed.textContent;
      hiddenInput.value = firstAllowed.dataset.value;
    } else {
      selected.textContent = "No available status";
      hiddenInput.value = "";
    }

    popup.style.display = "flex";
  });

  // Cancel popup
  popupCancel.addEventListener("click", () => (popup.style.display = "none"));

  // Dropdown toggle
  // selected.addEventListener("click", () =>
  //   popupSelect.classList.toggle("open")
  // );

  // options.forEach((option) => {
  //   option.addEventListener("click", () => {
  //     if (option.style.display === "none") return; // ignore hidden options
  //     selected.textContent = option.textContent;
  //     hiddenInput.value = option.dataset.value;
  //     popupSelect.classList.remove("open");
  //   });
  // });

  // document.addEventListener("click", (e) => {
  //   if (!popupSelect.contains(e.target)) {
  //     popupSelect.classList.remove("open");
  //   }
  // });

  // Confirm update
  popupConfirm.addEventListener("click", () => {
    const newStatus = statusSelect.value;
    if (!newStatus) return;

    const selectedOrders = [
      ...document.querySelectorAll(".dataCheckbox:checked"),
    ].map((cb) => cb.value);

    const formData = new FormData();
    formData.append("ajaxUpdate", "1");
    formData.append("status", newStatus);
    selectedOrders.forEach((id) => formData.append("orders[]", id));

    fetch("", { method: "POST", body: formData })
      .then((res) => res.text())
      .then((result) => {
        let cleanResult = result.replace(/<script.*<\/script>/, "").trim();
        if (cleanResult === "success") {
          tableRows.forEach((row) => {
            const orderId = row.cells[1].textContent;
            if (selectedOrders.includes(orderId)) {
              row.cells[7].textContent = newStatus;
              row.querySelector(".dataCheckbox").checked = false;
            }
          });
          popup.style.display = "none";
          showSuccessToast("Order status updated!");
          updateButtons();
        } else {
          alert("Failed to update order status.");
        }
      })
      .catch((err) => console.error("Fetch error:", err));
  });

  // Toast
  function showSuccessToast(message) {
    const popup = document.getElementById("successPopup");
    popup.innerText = message;
    popup.classList.add("show");
    setTimeout(() => popup.classList.remove("show"), 3000);
  }

  // Checkbox change listener
  checkboxes.forEach((cb) => cb.addEventListener("change", updateButtons));

  // Search/filter
  function filterTable() {
    const searchValue = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value;

    tableRows.forEach((row) => {
      const orderId = row.cells[1].textContent.toLowerCase();
      const customerId = row.cells[2].textContent.toLowerCase();
      const status = row.cells[7].textContent.trim();

      const matchesSearch =
        orderId.includes(searchValue) || customerId.includes(searchValue);
      const matchesStatus = statusValue === "" || status === statusValue;

      row.style.display = matchesSearch && matchesStatus ? "" : "none";
    });

    updateButtons();
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

  document.getElementById("viewBtn").addEventListener("click", function () {
    const checkedBoxes = document.querySelectorAll(".dataCheckbox:checked");
    if (checkedBoxes.length === 0) {
      alert("Please select an order to view.");
      return;
    }
    if (checkedBoxes.length > 1) {
      alert("Please select only one order to view.");
      return;
    }

    const orderID = checkedBoxes[0].value;
    window.location.href = `adminOrderDetails.php?id=${orderID}`;
  });

  searchInput.addEventListener("input", filterTable);
  statusFilter.addEventListener("change", filterTable);

  updateButtons(); // Initialize
});
