let isGridView = false;

document.addEventListener("DOMContentLoaded", () => {

  const selectAllCheckbox = document.getElementById("selectAll");
  const rowCheckboxes = document.querySelectorAll(".row-checkbox");
  const deleteBtn = document.querySelector(".btn-delete");
  const bulkRestockBtn = document.getElementById("bulkRestockBtn");
  const toggleViewBtn = document.getElementById("toggleViewBtn");

  const productTable = document.getElementById("productTable");
  const productGrid = document.getElementById("productGrid");

  const searchInput = document.getElementById("searchInput");
  const categoryFilters = document.querySelectorAll(".filter-category");
  const priceMinInput = document.getElementById("priceMin");
  const minText = document.getElementById("minText");
  const maxText = document.getElementById("maxText");

  const rowPerPageSelect = document.getElementById("rowPerPage");
  const paginationControls = document.getElementById("paginationControls");

  const tableRows = Array.from(document.querySelectorAll("tbody tr"));
  const deleteForm = document.getElementById("deleteForm");

  let filteredRows = [...tableRows];
  let currentPage = 1;

  function toggleActionButtons() {
    const hasChecked = document.querySelector(".row-checkbox:checked");
    deleteBtn.disabled = !hasChecked;
    bulkRestockBtn.disabled = !hasChecked;
  }

  selectAllCheckbox.addEventListener("change", () => {
    rowCheckboxes.forEach((cb) => (cb.checked = selectAllCheckbox.checked));
    toggleActionButtons();
  });

  rowCheckboxes.forEach((cb) =>
    cb.addEventListener("change", toggleActionButtons)
  );

  toggleActionButtons();

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
      const price = parseFloat(row.children[6].innerText);
      const category = row.children[7].innerText.toLowerCase();

      const matchSearch =
        productID.includes(searchValue) ||
        productName.includes(searchValue) ||
        category.includes(searchValue);

      const matchCategory =
        selectedCategories.length === 0 ||
        selectedCategories.includes(category);

      const matchPrice = price <= maxPrice;

      return matchSearch && matchCategory && matchPrice;
    });

    currentPage = 1;
    updatePagination();
  }

   function updatePagination() {
    const rowsPerPage = parseInt(rowPerPageSelect.value);
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

    tableRows.forEach((row) => (row.style.display = "none"));

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    filteredRows.slice(start, end).forEach((row) => {
      row.style.display = "";
    });

    renderPaginationControls(totalPages);

    if (isGridView) renderGridFromVisibleRows();
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

  function renderGridFromVisibleRows() {
    productGrid.innerHTML = "";

    const visibleRows = tableRows.filter((row) => row.style.display !== "none");

    visibleRows.forEach((row) => {
      const checkbox = row.querySelector(".row-checkbox");
      const img = row.querySelector("img").src;
      const productId = row.children[2].innerText.trim();
      const name = row.children[3].innerText;
      const price = row.children[6].innerText;
      const category = row.children[7].innerText;

      const card = document.createElement("div");
      card.className = "product-card";
      card.dataset.productId = productId;

      if (checkbox.checked) {
        card.classList.add("selected");
      }

      card.innerHTML = `
      <div class="grid-select">
        <input type="checkbox" ${checkbox.checked ? "checked" : ""}>
      </div>

      <img src="${img}">
      <h4>${name}</h4>
      <small>ID: ${productId}</small><br>
      <small>${category}</small><br>
      <strong>RM ${price}</strong>

<button type="button" class="grid-edit-btn">✏️</button>
    `;

      const gridCheckbox = card.querySelector(".grid-select input");

      gridCheckbox.addEventListener("change", () => {
        checkbox.checked = gridCheckbox.checked;
        card.classList.toggle("selected", gridCheckbox.checked);
        toggleActionButtons();
      });

      card.addEventListener("click", (e) => {
        if (e.target.closest("button, input")) return;
        gridCheckbox.checked = !gridCheckbox.checked;
        checkbox.checked = gridCheckbox.checked;
        card.classList.toggle("selected", gridCheckbox.checked);
        toggleActionButtons();
      });

      card.querySelector(".grid-edit-btn").addEventListener("click", (e) => {
        e.stopPropagation();
        window.location.href = `addSingleProduct.php?product_id=${productId}`;
      });

      productGrid.appendChild(card);
    });
  }

  toggleViewBtn.addEventListener("click", () => {
    isGridView = !isGridView;

    localStorage.setItem("productViewMode", isGridView ? "grid" : "table");

    applyViewMode();
  });

  function applyViewMode() {
    if (isGridView) {
      productTable.style.display = "none";
      productGrid.style.display = "grid";
      toggleViewBtn.innerText = "📋 Table View";
      renderGridFromVisibleRows();
    } else {
      productTable.style.display = "";
      productGrid.style.display = "none";
      toggleViewBtn.innerText = "🖼️ Grid View";
    }
  }

  searchInput.addEventListener("keyup", filterTable);
  categoryFilters.forEach((cb) => cb.addEventListener("change", filterTable));

  priceMinInput.addEventListener("input", () => {
    minText.innerText = "0";
    maxText.innerText = priceMinInput.value;
    filterTable();
  });

  rowPerPageSelect.addEventListener("change", () => {
    currentPage = 1;
    updatePagination();
  });

  const savedView = localStorage.getItem("productViewMode");
  isGridView = savedView === "grid";

  applyViewMode();
  filterTable();
});

const bulkRestockBtn = document.getElementById("bulkRestockBtn");
const modal = document.getElementById("bulkRestockModal");
const container = document.getElementById("modalProductsContainer");
const closeModal = modal.querySelector(".close");

bulkRestockBtn.addEventListener("click", () => {
  container.innerHTML = "";
  const selectedRows = document.querySelectorAll(".row-checkbox:checked");

  if (selectedRows.length === 0) {
    alert("Please select at least one product to restock.");
    return;
  }

  selectedRows.forEach((row) => {
    const productId = row.value;
    const tr = row.closest("tr");
    const productName = tr.querySelector("td:nth-child(4)").innerText;
    const categoryName = tr.querySelector("td:nth-child(8)").innerText.trim();
    const variants = JSON.parse(row.dataset.variants || "[]");

    let html = `
            <div class="modal-product-item" id="product-group-${productId}">
                <div class="product-modal-header">
                    <h4>${productName}</h4>
                    <button type="button" class="btn-remove-product" onclick="removeProductFromModal('${productId}')">
                        <i class="fa-solid fa-trash"></i> Remove
                    </button>
                </div>`;

    variants.forEach((v) => {
      const imgFile = v.img_url
        ? v.img_url.split(",")[0].trim()
        : "default-product.png";
      const imgPath = `../../../public/images/${categoryName}/${imgFile}`;

      html += `
                <div class="variant-item">
                    <input type="checkbox" class="variant-checkbox" 
                           name="product_variant_id[${v.product_variant_id}]" 
                           value="${v.product_variant_id}">
                    
                    <img src="${imgPath}" class="variant-img" alt="${v.variant_name}" 
                         onerror="this.src='../../../public/images/default-product.png'">
                    
                    <div class="variant-info">
                        <span class="variant-name"><strong>${v.variant_name}</strong></span>
                        <small style="color:#666">ID: ${v.product_variant_id}</small>
                        <div class="error-msg" style="color: red; font-size: 0.8em; display: none;">Please enter quantity</div>
                    </div>

                    <input type="hidden" name="product_id[${v.product_variant_id}]" value="${productId}">
                    <input type="number" name="restock_qty[${v.product_variant_id}]" 
                           min="0" class="variant-qty" style="visibility:hidden; width: 60px;">
                </div>`;
    });
    html += `</div>`;
    container.innerHTML += html;
  });

  modal.style.display = "flex";
  toggleSubmitButton();

  container.querySelectorAll(".variant-checkbox").forEach((cb) => {
    cb.addEventListener("change", (e) => {
      const row = e.target.closest(".variant-item");
      const qtyInput = row.querySelector(".variant-qty");
      const errorMsg = row.querySelector(".error-msg");

      if (e.target.checked) {
        qtyInput.style.visibility = "visible";
        qtyInput.focus();
      } else {
        qtyInput.style.visibility = "hidden";
        qtyInput.value = "";
        qtyInput.style.borderColor = "#ccc";
        errorMsg.style.display = "none";
      }

      toggleSubmitButton();
    });
  });
});

function toggleSubmitButton() {
  const submitBtn = document.querySelector(".btn-restock-submit");
  const checkedCount = container.querySelectorAll(
    ".variant-checkbox:checked"
  ).length;
  submitBtn.disabled = checkedCount === 0;
}

function removeProductFromModal(productId) {
  const item = document.getElementById(`product-group-${productId}`);
  if (item) item.remove();
  toggleSubmitButton();
  if (container.querySelectorAll(".modal-product-item").length === 0) {
    modal.style.display = "none";
  }
}

// Validation on Submit
document.getElementById("bulkRestockForm").addEventListener("submit", (e) => {
  let isValid = true;
  const checkedVariants = container.querySelectorAll(
    ".variant-checkbox:checked"
  );

  if (checkedVariants.length === 0) {
    alert("Please select at least one variant to restock.");
    e.preventDefault();
    return;
  }

  checkedVariants.forEach((cb) => {
    const row = cb.closest(".variant-item");
    const qtyInput = row.querySelector(".variant-qty");
    const errorMsg = row.querySelector(".error-msg");

    if (!qtyInput.value || parseInt(qtyInput.value) <= 0) {
      isValid = false;
      qtyInput.style.borderColor = "red";
      errorMsg.style.display = "block";
    } else {
      qtyInput.style.borderColor = "#ccc";
      errorMsg.style.display = "none";
    }
  });

  if (!isValid) {
    e.preventDefault();
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

closeModal.addEventListener("click", () => (modal.style.display = "none"));
window.addEventListener("click", (e) => {
  if (e.target === modal) modal.style.display = "none";
});
