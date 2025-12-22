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

    // Get the status of selected orders
    const statuses = Array.from(checkedBoxes).map((cb) =>
      cb.closest("tr").querySelector(".order-status").textContent.trim()
    );

    const allSameStatus = statuses.every((status) => status === statuses[0]);

    // Determine if any selected status is final (cannot update)
    const cannotUpdateStatuses = [
      "Delivered",
      "Completed",
      "Cancelled",
      "Refunded",
    ];
    const anyFinalStatus = statuses.some((status) =>
      cannotUpdateStatuses.includes(status)
    );

    // Show/Hide buttons
    viewBtn.style.display = checkedCount >= 1 ? "inline-block" : "none";

    if (checkedCount === 1) {
      updateBtn.style.display = anyFinalStatus ? "none" : "inline-block";
    } else if (checkedCount > 1 && allSameStatus) {
      updateBtn.style.display = anyFinalStatus ? "none" : "inline-block";
    } else {
      updateBtn.style.display = "none";
    }
  }

  // function updateButtons() {
  //   const checkedBoxes = document.querySelectorAll(".dataCheckbox:checked");
  //   const checkedCount = checkedBoxes.length;

  //   if (checkedCount === 0) {
  //     viewBtn.style.display = "none";
  //     updateBtn.style.display = "none";
  //     return;
  //   }

  //   const statuses = Array.from(checkedBoxes).map((cb) =>
  //     cb.closest("tr").querySelector(".order-status").textContent.trim()
  //   );
  //   const allSameStatus = statuses.every((status) => status === statuses[0]);

  //   if (checkedCount === 1) {
  //     viewBtn.style.display = "inline-block";
  //     updateBtn.style.display = "inline-block";
  //   } else if (checkedCount > 1 && allSameStatus) {
  //     viewBtn.style.display = "none";
  //     updateBtn.style.display = "inline-block";
  //   } else {
  //     viewBtn.style.display = "none";
  //     updateBtn.style.display = "none";
  //   }
  // }

  // Show popup when clicking Update
  updateBtn.addEventListener("click", () => {
    const checkedBoxes = document.querySelectorAll(".dataCheckbox:checked");
    if (checkedBoxes.length === 0) {
      console.log("No checkboxes found"); // Debugging
      return;
    }

    const currentStatus = checkedBoxes[0]
      .closest("tr")
      .querySelector(".order-status")
      .textContent.trim();
    console.log("Current Status identified as:", currentStatus);
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

    // popup.style.display = "flex";
    Object.assign(popup.style, {
      display: "flex",
      visibility: "visible",
      opacity: "1",
      zIndex: "9999",
    });
  });

  // Cancel popup
  popupCancel.addEventListener("click", () => (popup.style.display = "none"));

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
            const orderId = row.querySelector(".dataCheckbox").value;
            if (selectedOrders.includes(orderId)) {
              row.querySelector(".order-status").textContent = newStatus;
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

  function filterTable() {
    const searchValue = searchInput.value.toLowerCase().trim();
    const statusValue = statusFilter.value;

    tableRows.forEach((row) => {
      const orderId = row.querySelector(".dataCheckbox").value.toLowerCase();
      const customerId = row.cells[2].textContent.toLowerCase();
      const status = row.querySelector(".order-status").textContent.trim();

      const matchesSearch =
        orderId.includes(searchValue) || customerId.includes(searchValue);
      const matchesStatus = statusValue === "" || status === statusValue;

      row.style.display = matchesSearch && matchesStatus ? "" : "none";
    });

    updateButtons();
  }

  // Search/filter
  // function filterTable() {
  //   const searchValue = searchInput.value.toLowerCase();
  //   const statusValue = statusFilter.value;

  //   tableRows.forEach((row) => {
  //     const orderId = row.querySelector(".dataCheckbox").value;
  //     const customerId = row.cells[2].textContent.toLowerCase();
  //     const status = row.querySelector(".order-status").textContent.trim();

  //     const matchesSearch =
  //       orderId.includes(searchValue) || customerId.includes(searchValue);
  //     const matchesStatus = statusValue === "" || status === statusValue;

  //     row.style.display = matchesSearch && matchesStatus ? "" : "none";
  //   });

  //   updateButtons();
  // }

  // const urlParams = new URLSearchParams(window.location.search);
  // const currentStatus = urlParams.get("status") || "";

  // document.querySelectorAll(".custom-select").forEach((select) => {
  //   const selected = select.querySelector(".selected");
  //   const options = select.querySelector(".options");

  //   selected.addEventListener("click", () => select.classList.toggle("open"));
  //   options.querySelectorAll("li").forEach((option) => {
  //     option.addEventListener("click", () => {
  //       selected.textContent = option.textContent;
  //       document.getElementById("statusFilter").value = option.dataset.value;
  //       select.classList.remove("open");
  //       const params = new URLSearchParams(window.location.search);
  //       params.set("status", option.dataset.value); // use 'status'
  //       params.set("page", 1);
  //       window.location.search = params.toString();
  //       filterTable();
  //     });
  //   });

  //   document.addEventListener("click", (e) => {
  //     if (!select.contains(e.target)) select.classList.remove("open");
  //   });
  // });

  const urlParams = new URLSearchParams(window.location.search);
  const currentStatus = urlParams.get("status") || ""; // get current status from URL

  document.querySelectorAll(".custom-select").forEach((select) => {
    const selected = select.querySelector(".selected");
    const options = select.querySelector(".options");
    const hiddenInput = select.querySelector("input[type=hidden]");

    // Set initial value based on URL
    hiddenInput.value = currentStatus;
    if (currentStatus === "") {
      selected.textContent = "All Status";
    } else {
      const option = select.querySelector(
        `.options li[data-value="${currentStatus}"]`
      );
      if (option) selected.textContent = option.textContent;
    }

    // Handle dropdown click
    selected.addEventListener("click", () => select.classList.toggle("open"));

    // Handle selecting an option
    options.querySelectorAll("li").forEach((option) => {
      option.addEventListener("click", () => {
        selected.textContent = option.textContent;
        hiddenInput.value = option.dataset.value;
        select.classList.remove("open");

        // Update URL with new filter and reset page to 1
        const params = new URLSearchParams(window.location.search);
        params.set("status", option.dataset.value);
        params.set("page", 1);
        window.location.search = params.toString();

        filterTable(); // optional, if you are using client-side filtering
      });
    });

    // Close dropdown if clicked outside
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

    const orderId = checkedBoxes[0].value;
    window.location.href = `adminOrderDetails.php?id=${orderId}`;
  });

  searchInput.addEventListener("input", filterTable);
  statusFilter.addEventListener("change", filterTable);

  updateButtons(); // Initialize
});
