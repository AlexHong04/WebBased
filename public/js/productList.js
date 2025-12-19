let selectedProduct = null;

// Open deletion pop-up modal for single product
document.addEventListener("DOMContentLoaded", () => {
  setupConfirmationModal({
    triggerSelector: ".delete-btn",
    modalId: "deleteModal",
    messageId: "deleteMessage",
    confirmBtnId: "confirmDelete",
    cancelBtnId: "cancelDelete",
    getMessage: (btn) =>
      `Are you sure you want to delete "${btn.dataset.productName}"?`,
    onConfirm: (btn) => {
      alert(`Deleted: ${btn.dataset.productName}`);
      // Later: send AJAX request to delete item
    },
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const selectAllCheckbox = document.getElementById("selectAll");
  const rowCheckboxes = document.querySelectorAll(".row-checkbox");
  const deleteBtn = document.querySelector(".btn-delete");

  function toggleDeleteButton() {
    deleteBtn.disabled = !document.querySelector(".row-checkbox:checked");
  }

  selectAllCheckbox.addEventListener("change", () => {
    rowCheckboxes.forEach((checkbox) => {
      checkbox.checked = selectAllCheckbox.checked;
    });
    toggleDeleteButton();
  });

  rowCheckboxes.forEach((cb) =>
    cb.addEventListener("change", toggleDeleteButton)
  );

  toggleDeleteButton();
});

document.addEventListener("DOMContentLoaded", () => {
  const deleteForm = document.getElementById("deleteForm");

  deleteForm.addEventListener("submit", (e) => {
    const selected = document.querySelectorAll(".row-checkbox:checked");

    if (selected.length === 0) {
      e.preventDefault();
      alert("Please select at least one product.");
      return;
    }

    if (!confirm(`Delete ${selected.length} product(s)?`)) {
      e.preventDefault();
    }
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const deleteBtn = document.querySelector(".btn-delete");
  const checkboxes = document.querySelectorAll(".row-checkbox");

  function toggleDeleteButton() {
    deleteBtn.disabled = !document.querySelector(".row-checkbox:checked");
  }

  checkboxes.forEach((cb) => cb.addEventListener("change", toggleDeleteButton));
  toggleDeleteButton();
});

document.addEventListener("DOMContentLoaded", () => {
  const searchInput = document.getElementById("searchInput");
  const tableRows = Array.from(document.querySelectorAll("tbody tr"));
  const categoryFilters = document.querySelectorAll(".filter-category");
  const priceMinInput = document.getElementById("priceMin");
  const minText = document.getElementById("minText");
  const maxText = document.getElementById("maxText");
  const rowPerPageSelect = document.getElementById("rowPerPage");
  const paginationControls = document.getElementById("paginationControls");

  let filteredRows = tableRows;
  let currentPage = 1;

  // Default price value
  priceMinInput.value = priceMinInput.max;
  maxText.innerText = priceMinInput.max;

  function filterTable() {
    const searchValue = searchInput.value.toLowerCase().trim();
    const selectedCategories = Array.from(categoryFilters)
      .filter((cb) => cb.checked)
      .map((cb) => cb.value.toLowerCase());

    const maxPrice = parseFloat(priceMinInput.value);

    filteredRows = tableRows.filter((row) => {
      const productID = row.children[2].innerText.toLowerCase();
      const productName = row.children[3].innerText.toLowerCase();
      const categoryName = row.children[7].innerText.toLowerCase();
      const price = parseFloat(row.children[6].innerText);

      const matchSearch =
        productID.includes(searchValue) ||
        productName.includes(searchValue) ||
        categoryName.includes(searchValue);

      const matchCategory =
        selectedCategories.length === 0 ||
        selectedCategories.includes(categoryName);

      const matchPrice = price <= maxPrice;

      return matchSearch && matchCategory && matchPrice;
    });

    currentPage = 1;
    updatePagination();
  }

  function updatePagination() {
    const rowsPerPage = parseInt(rowPerPageSelect.value);
    const totalRows = filteredRows.length;
    const totalPages = Math.ceil(totalRows / rowsPerPage);

    tableRows.forEach((row) => (row.style.display = "none"));

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    filteredRows.slice(start, end).forEach((row) => {
      row.style.display = "";
    });

    renderPaginationControls(totalPages);
  }

  function renderPaginationControls(totalPages) {
    paginationControls.innerHTML = "";
    if (totalPages <= 1) return;

    const prevBtn = document.createElement("button");
    prevBtn.innerText = "Previous";
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => {
      currentPage--;
      updatePagination();
    };
    paginationControls.appendChild(prevBtn);

    for (let i = 1; i <= totalPages; i++) {
      const btn = document.createElement("button");
      btn.innerText = i;
      if (i === currentPage) btn.classList.add("active");
      btn.onclick = () => {
        currentPage = i;
        updatePagination();
      };
      paginationControls.appendChild(btn);
    }

    const nextBtn = document.createElement("button");
    nextBtn.innerText = "Next";
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => {
      currentPage++;
      updatePagination();
    };
    paginationControls.appendChild(nextBtn);
  }

  // Event listeners
  searchInput.addEventListener("keyup", filterTable);
  rowPerPageSelect.addEventListener("change", () => {
    currentPage = 1;
    updatePagination();
  });
  categoryFilters.forEach((cb) => cb.addEventListener("change", filterTable));
  priceMinInput.addEventListener("input", () => {
    minText.innerText = "0";
    maxText.innerText = priceMinInput.value;
    filterTable();
  });

  filterTable();
});

const bulkRestockBtn = document.getElementById("bulkRestockBtn");
const modal = document.getElementById("bulkRestockModal");
const container = document.getElementById("modalProductsContainer");
const closeModal = modal.querySelector(".close");

bulkRestockBtn.addEventListener("click", () => {
  container.innerHTML = ""; // Clear modal

  document.querySelectorAll(".row-checkbox:checked").forEach((row) => {
    const productId = row.value;
    const productName = row
      .closest("tr")
      .querySelector("td:nth-child(4)").innerText;
    const variants = JSON.parse(row.dataset.variants || "[]");

    let html = `<div class="modal-product-item">
        <h4>${productName}</h4>`;

    variants.forEach((v) => {
      html += `
    <div class="variant-item">
      <label>
        <input type="checkbox" class="variant-checkbox" name="product_variant_id[${
          v.product_variant_id
        }]" value="${v.product_variant_id}">
        ${v.variant_name.trim()}
      </label>
      <input type="hidden" name="product_id[${
        v.product_variant_id
      }]" value="${productId}">
      <input type="number" name="restock_qty[${
        v.product_variant_id
      }]" min="1" value="1" class="variant-qty" style="display:none;">
    </div>
  `;
    });

    html += `</div>`;
    container.innerHTML += html;
  });

  // Show modal if there’s content
  if (container.innerHTML !== "") {
    modal.style.display = "flex";

    // Add checkbox toggle functionality
    container.querySelectorAll(".variant-checkbox").forEach((cb) => {
      cb.addEventListener("change", (e) => {
        const qtyInput = e.target
          .closest(".variant-item")
          .querySelector(".variant-qty");
        if (e.target.checked) {
          qtyInput.style.display = "inline-block";
        } else {
          qtyInput.style.display = "none";
          qtyInput.value = 1; // reset to default
        }
      });
    });
  } else {
    alert("Please select at least one product to restock.");
  }
});

// Close modal
closeModal.addEventListener("click", () => {
  modal.style.display = "none";
});
window.addEventListener("click", (e) => {
  if (e.target === modal) modal.style.display = "none";
});

document.addEventListener("DOMContentLoaded", () => {
  modal.style.display = "none";
  container.innerHTML = "";
});

