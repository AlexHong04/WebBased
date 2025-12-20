document.addEventListener("DOMContentLoaded", function () {
  const checkboxes = document.querySelectorAll(".dataCheckbox");
  const viewBtn = document.getElementById("viewBtn");
  const updateBtn = document.getElementById("updateBtn");

  const searchInput = document.getElementById("dataSearch");
  const tableRows = document.querySelectorAll(".data tbody tr");
  const activeFilter = document.getElementById("activeFilter");
  const blockedFilter = document.getElementById("blockedFilter");

  // Update buttons based on checked boxes
  function updateButtons() {
    const checked = document.querySelectorAll(".dataCheckbox:checked").length;
    if (checked === 1) {
      viewBtn.style.display = "inline-block";
      updateBtn.style.display = "inline-block";
    } else if (checked > 1) {
      viewBtn.style.display = "none";
      updateBtn.style.display = "inline-block";
    } else {
      viewBtn.style.display = "none";
      updateBtn.style.display = "none";
    }
  }

  // Filter table rows
  function filterTable() {
    const searchValue = searchInput.value.toLowerCase();
    const activeValue = activeFilter.value;
    const blockedValue = blockedFilter.value;

    tableRows.forEach((row) => {
      const memberId = row.cells[1].textContent.toLowerCase();
      const firstName = row.cells[3].textContent.toLowerCase();
      const lastName = row.cells[4].textContent.toLowerCase();
      const activeStatus = row.cells[9].textContent; // Active column
      const blockedStatus = row.cells[10].textContent; // Blocked column

      const matchesSearch =
        memberId.includes(searchValue) ||
        firstName.includes(searchValue) ||
        lastName.includes(searchValue);
      const matchesActive = activeValue === "" || activeStatus === activeValue;
      const matchesBlocked =
        blockedValue === "" || blockedStatus === blockedValue;

      row.style.display =
        matchesSearch && matchesActive && matchesBlocked ? "" : "none";
    });

    updateButtons();
  }

  // Custom select handling
  document.querySelectorAll(".custom-select").forEach((select) => {
    const selected = select.querySelector(".selected");
    const options = select.querySelector(".options");

    selected.addEventListener("click", () => select.classList.toggle("open"));

    options.querySelectorAll("li").forEach((option) => {
      option.addEventListener("click", () => {
        selected.textContent = option.textContent;
        select.querySelector("input").value = option.dataset.value;
        select.classList.remove("open");
        filterTable();
      });
    });

    document.addEventListener("click", (e) => {
      if (!select.contains(e.target)) select.classList.remove("open");
    });
  });

  document.getElementById("viewBtn").addEventListener("click", function () {
    const checkboxes = document.querySelectorAll(".dataCheckbox:checked");

    const customerId = checkboxes[0].value;
    window.location.href = `adminMemberDetails.php?id=${customerId}`;
  });

  document.getElementById("updateBtn").addEventListener("click", function () {
    const selected = [
      ...document.querySelectorAll(".dataCheckbox:checked"),
    ].map((cb) => cb.value);

    if (selected.length === 0) {
      showToast("Please select at least one member.", "error");
      return;
    }

    fetch("memberListing.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ customer_ids: selected }),
    })
      .then((res) => res.text())
      .then((text) => {
        if (text.includes("SUCCESS")) {
          showToast("Status updated successfully!", "success");
          setTimeout(() => location.reload(), 3000);
        } else {
          showToast("Failed to update: " + text, "error");
        }
      });
  });

  function showToast(message, type = "success", duration = 3000) {
    const container = document.getElementById("toast-container");
    const toast = document.createElement("div");
    toast.className = `toast ${type}`;
    toast.innerText = message;
    container.appendChild(toast);

    // Show toast
    setTimeout(() => toast.classList.add("show"), 100);

    // Hide and remove toast
    setTimeout(() => {
      toast.classList.remove("show");
      setTimeout(() => container.removeChild(toast), 500);
    }, duration);
  }

  // Checkbox change listener
  checkboxes.forEach((cb) => cb.addEventListener("change", updateButtons));

  // Search input listener
  searchInput.addEventListener("input", filterTable);

  // Initial button state
  updateButtons();
});
