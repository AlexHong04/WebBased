document.addEventListener("DOMContentLoaded", () => {
  const uploadBox = document.querySelector(".upload-box");
  const fileInput = document.getElementById("csvfile");
  const fileName = document.getElementById("fileName");
  const previewContainer = document.getElementById("previewContainer");

  if (!uploadBox || !fileInput || !fileName) return;

  removeFileBtn.style.display = "none";
  uploadBox.addEventListener("click", () => fileInput.click());

  fileInput.addEventListener("change", function () {
    if (this.files.length > 0) {
      fileName.textContent = "Selected File: " + this.files[0].name;
      removeFileBtn.style.display = "inline-block";
      readAndPreviewFile(this.files[0]);
    } else {
      clearSelectedFile();
    }
  });

  removeFileBtn.addEventListener("click", clearSelectedFile);

  function clearSelectedFile() {
    fileInput.value = "";
    fileName.textContent = "No file chosen";
    previewContainer.innerHTML = "";
    previewContainer.classList.remove("show");
    tableActions.classList.remove("show");
    removeFileBtn.style.display = "none";
  }

  uploadBox.addEventListener("dragover", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.add("drag");
  });

  uploadBox.addEventListener("dragleave", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove("drag");
  });

  uploadBox.addEventListener("drop", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove("drag");

    const files = e.dataTransfer.files;
    if (files.length === 0) return;

    fileInput.files = files;
    fileName.textContent = "Selected File: " + files[0].name;
    readAndPreviewFile(files[0]);
  });

  function readAndPreviewFile(file) {
    const reader = new FileReader();

    reader.onload = function (e) {
      let data = e.target.result;
      let workbook;

      try {
        if (file.name.endsWith(".csv")) {
          const csvData = new TextDecoder("utf-8").decode(data);
          const arr = XLSX.read(csvData, { type: "string" });
          workbook = arr;
        } else {
          workbook = XLSX.read(new Uint8Array(data), { type: "array" });
        }
      } catch (err) {
        previewContainer.innerHTML = `<p style="color:red;">Error reading file.</p>`;
        return;
      }

      const firstSheet = workbook.SheetNames[0];
      const worksheet = workbook.Sheets[firstSheet];
      const jsonData = XLSX.utils.sheet_to_json(worksheet, { defval: "" });

      if (!jsonData.length) {
        previewContainer.innerHTML = `<p style="color:red;">File is empty.</p>`;
        return;
      }

      generatePreviewTable(jsonData);
    };

    if (file.name.endsWith(".csv")) {
      reader.readAsArrayBuffer(file);
    } else {
      reader.readAsArrayBuffer(file);
    }
  }

  function generatePreviewTable(data) {
    const addStock = [];
    const addNew = [];

    data.forEach((row) => {
      const errors = [];

      if (!row.product_name) errors.push("Missing product_name");
      if (!row.category_name) errors.push("Missing category_name");
      if (
        !row.cost_price ||
        isNaN(row.cost_price) ||
        Number(row.cost_price) <= 0
      )
        errors.push("Invalid cost_price");
      if (
        !row.sales_price ||
        isNaN(row.sales_price) ||
        Number(row.sales_price) <= 0
      )
        errors.push("Invalid sales_price");
      if (
        row.stock_qty === "" ||
        isNaN(row.stock_qty) ||
        Number(row.stock_qty) < 0
      )
        errors.push("Invalid stock_qty");
      if (
        row.min_stock_level === "" ||
        isNaN(row.min_stock_level) ||
        Number(row.min_stock_level) < 0
      )
        errors.push("Invalid min_stock_level");

      row.validation = errors.join(", ");

      if (row.product_id) addStock.push(row);
      else addNew.push(row);
    });

    let html = "";

    function buildTable(title, rows) {
      if (!rows.length) return "";

      let table = `<h4>${title}</h4>`;
      table += `<table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Product Variant ID</th>
                    <th>Product ID</th>
                    <th>Product Name</th>
                    <th>Description</th>
                    <th>Cost Price</th>
                    <th>Sales Price</th>
                    <th>Category</th>
                    <th>Variant</th>
                    <th>Validation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>`;

      rows.forEach((row, i) => {
        const rowClass = row.validation
          ? "validation-error"
          : "validation-success";
        table += `<tr class="${rowClass}">
                <td>${i + 1}</td>
                <td>${row.product_variant_id}</td>
                <td>${row.product_id}</td>
                <td>${row.product_name}</td>
                <td>${row.description}</td>
                <td>${row.cost_price}</td>
                <td>${row.sales_price}</td>
                <td>${row.category_name}</td>
                <td>${row.variant_name}</td>
                <td>${
                  row.validation
                    ? `<span class="validation-error">${row.validation}</span>`
                    : `<span class="validation-ok">OK</span>`
                }</td>
                <td>
                  <i class="fa-solid fa-pen" onclick="editRow(${i})"></i>
                  <i class="fa-solid fa-trash" onclick="deleteRow(${i})"></i>
                </td>
            </tr>`;
      });

      table += `</tbody></table>`;
      return table;
    }

    html += buildTable("Add Stock", addStock);
    html += buildTable("Add New Product", addNew);

    // Add Submit and Cancel buttons
    html += `
        <div class="table-actions">
            <button class="submit-btn" onclick="submitValidRows()">Submit</button>
            <button class="cancel-btn" onclick="cancelUpload()">Cancel</button>
        </div>
    `;

    previewContainer.innerHTML = html;
    previewContainer.classList.add("show");
    document.getElementById("tableActions").classList.add("show");
  }
  function submitValidRows() {
    alert("Submit clicked! Implement server upload here for valid rows.");
  }

  function cancelUpload() {
    document.getElementById("csvfile").value = "";
    document.getElementById("fileName").textContent = "";
    const previewContainer = document.getElementById("previewContainer");
    previewContainer.innerHTML = "";
    previewContainer.classList.remove("show");

    const tableActions = document.getElementById("tableActions");
    tableActions.classList.remove("show");
  }
});

function deleteRow(index) {
  const tables = previewContainer.querySelectorAll("table tbody");

  tables.forEach((tbody) => {
    if (tbody.rows[index]) {
      tbody.deleteRow(index);
    }
  });
}

function editRow(index) {
    // Collect the row data from preview table
    const tables = previewContainer.querySelectorAll("table tbody");
    let variantData = [];

    tables.forEach((tbody) => {
        const row = tbody.rows[index];
        if (!row) return;

        variantData.push({
            product_variant_id: row.cells[1].textContent,
            product_id: row.cells[2].textContent,
            product_name: row.cells[3].textContent,
            description: row.cells[4].textContent,
            cost_price: row.cells[5].textContent,
            sales_price: row.cells[6].textContent,
            category_name: row.cells[7].textContent,
            variant_name: row.cells[8].textContent,
            stock_qty: row.cells[9].textContent,
            min_stock_level: row.cells[10].textContent,
        });
    });

    // Open modal for this row
    openVariantModal(variantData);
}


// --- Modal Elements ---
const variantModal = document.getElementById("variantModal");
const closeModal = document.getElementById("closeModal");
const variantFormContainer = document.getElementById("variantFormContainer");
const modalCancelBtn = document.getElementById("modalCancelBtn");
const modalSubmitBtn = document.getElementById("modalSubmitBtn");

closeModal.addEventListener("click", () => variantModal.style.display = "none");
modalCancelBtn.addEventListener("click", () => variantModal.style.display = "none");

// Open modal based on variant data
function openVariantModal(variants) {
    variantFormContainer.innerHTML = ""; // clear previous content

    variants.forEach((variant, i) => {
        const row = document.createElement("div");
        row.classList.add("variant-row");

        row.innerHTML = `
            <div>
                <label>Variant: <b>${variant.variant_name || variant.product_name}</b></label>
            </div>
            <div>
                <label>Stock Qty:</label>
                <input type="number" min="0" value="${variant.stock_qty || 0}" data-index="${i}" class="stock-input">
            </div>
            <div>
                <label>Min Stock Level:</label>
                <input type="number" min="0" value="${variant.min_stock_level || 0}" data-index="${i}" class="min-stock-input">
            </div>
            <div>
                <label>Images (max 5):</label>
                <input type="file" accept="image/*" multiple data-index="${i}" class="variant-images">
            </div>
        `;

        variantFormContainer.appendChild(row);
    });

    variantModal.style.display = "block";
}

// Handle modal submit
modalSubmitBtn.addEventListener("click", () => {
    const variantData = [];
    const rows = variantFormContainer.querySelectorAll(".variant-row");

    rows.forEach((row, i) => {
        const stock = row.querySelector(".stock-input").value;
        const minStock = row.querySelector(".min-stock-input").value;
        const images = row.querySelector(".variant-images").files;

        if (images.length > 5) {
            alert(`Variant ${i + 1}: You can upload a maximum of 5 images.`);
            return;
        }

        variantData.push({
            variantIndex: i,
            stock_qty: Number(stock),
            min_stock_level: Number(minStock),
            images: images
        });
    });

    console.log("Variant Data to upload:", variantData);
    variantModal.style.display = "none";

    // TODO: send `variantData` to server
});
