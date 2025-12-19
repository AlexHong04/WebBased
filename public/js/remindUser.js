document.addEventListener("DOMContentLoaded", function () {
  const searchInput = document.getElementById("searchInput");
  const rowPerPageSelect = document.getElementById("rowPerPage");
  const paginationControls = document.getElementById("paginationControls");

  // ALL table rows
  const tableRows = Array.from(document.querySelectorAll("tbody tr"));

  let filteredRows = [];
  let currentPage = 1;

  function getCustomerGroups() {
    const groups = {};
    tableRows.forEach((row) => {
      const customerId = row.dataset.customerId;
      if (!groups[customerId]) {
        groups[customerId] = [];
      }
      groups[customerId].push(row);
    });
    return Object.values(groups);
  }

  const customerGroups = getCustomerGroups();

  function filterTable() {
    const searchValue = searchInput.value.toLowerCase().trim();

    filteredRows = customerGroups.filter((group) => {
      return group.some((row) => {
        const cells = row.children;

        const customerId = cells[0]?.innerText.toLowerCase() || "";
        const customerName = cells[1]?.innerText.toLowerCase() || "";
        const phone = cells[2]?.innerText.toLowerCase() || "";
        const productName = cells[5]?.innerText.toLowerCase() || "";
        const variantId = cells[6]?.innerText.toLowerCase() || "";

        return (
          customerId.includes(searchValue) ||
          customerName.includes(searchValue) ||
          phone.includes(searchValue) ||
          productName.includes(searchValue) ||
          variantId.includes(searchValue)
        );
      });
    });

    currentPage = 1;
    updatePagination();
  }

  // ---------------- PAGINATION ----------------
  function updatePagination() {
    const rowsPerPage = parseInt(rowPerPageSelect.value);
    const totalRows = filteredRows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);

    // Hide all rows first
    tableRows.forEach((row) => (row.style.display = "none"));

    if (totalRows === 0) {
      paginationControls.innerHTML = "";
      return;
    }

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    filteredRows.slice(start, end).forEach((group) => {
      group.forEach((row) => (row.style.display = ""));
    });

    renderPaginationControls(totalPages);
  }

  function renderPaginationControls(totalPages) {
    paginationControls.innerHTML = "";

    const rowsPerPage = parseInt(rowPerPageSelect.value);
    const totalRows = filteredRows.length;

    if (totalPages <= 1 && rowsPerPage >= totalRows) return;

    const prevBtn = document.createElement("button");
    prevBtn.textContent = "Previous";
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => {
      currentPage--;
      updatePagination();
    };
    paginationControls.appendChild(prevBtn);

    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement("button");
      btn.textContent = i;
      if (i === currentPage) btn.classList.add("active");

      btn.onclick = () => {
        currentPage = i;
        updatePagination();
      };

      paginationControls.appendChild(btn);
    }

    const nextBtn = document.createElement("button");
    nextBtn.textContent = "Next";
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => {
      currentPage++;
      updatePagination();
    };
    paginationControls.appendChild(nextBtn);
  }

  searchInput.addEventListener("keyup", filterTable);

  rowPerPageSelect.addEventListener("change", () => {
    currentPage = 1;
    updatePagination();
  });

  filterTable();
});

document.addEventListener("DOMContentLoaded", function () {
  const STORAGE_KEY = "wa_sent_log";

  // 1. Load existing statuses from Local Storage
  function loadSentStatuses() {
    const sentLog = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};

    document.querySelectorAll(".js-send-btn").forEach((btn) => {
      const id = btn.getAttribute("data-customer-id");
      if (sentLog[id]) {
        const statusLabel = document.getElementById("status-" + id);
        statusLabel.innerHTML = `✅ Sent: ${sentLog[id]}`;
        btn.style.opacity = "0.5";
        btn.innerText = "Send Again";
      }
    });
  }

  // 2. Handle the click event
  document.querySelectorAll(".js-send-btn").forEach((btn) => {
    btn.addEventListener("click", function () {
      const id = this.getAttribute("data-customer-id");
      const now = new Date().toLocaleString([], {
        month: "short",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });

      // Save to Local Storage
      const sentLog = JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
      sentLog[id] = now;
      localStorage.setItem(STORAGE_KEY, JSON.stringify(sentLog));

      // Update UI
      const statusLabel = document.getElementById("status-" + id);
      statusLabel.innerHTML = `✅ Sent: ${now}`;
      this.style.opacity = "0.5";
    });
  });

  loadSentStatuses();
});
