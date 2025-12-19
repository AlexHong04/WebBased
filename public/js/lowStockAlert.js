document.addEventListener("DOMContentLoaded", function () {
    // --- 1. Table Sorting Logic (This was in the outer scope and works) ---
    const sortableHeaders = document.querySelectorAll(".sortable");
    const urlParams = new URLSearchParams(window.location.search);
    let currentSort = urlParams.get("sort") || "product_variant_id";
    let currentOrder = urlParams.get("order") || "asc";

    sortableHeaders.forEach((header) => {
        header.addEventListener("click", function () {
            const sortColumn = this.getAttribute("data-sort-column");
            let newOrder = "asc";

            if (sortColumn === currentSort) {
                newOrder = currentOrder === "asc" ? "desc" : "asc";
            }

            window.location.href = `?sort=${sortColumn}&order=${newOrder}`;
        });
    });

    // --- 2. Filtering and Pagination Logic (This section was nested and is now moved out) ---

    // --- Initial Variable Setup ---
    const searchInput = document.getElementById("searchInput");
    // Get all rows from the table body
    const tableRows = Array.from(document.querySelectorAll("tbody tr"));
    const rowPerPageSelect = document.getElementById("rowPerPage");
    const paginationControls = document.getElementById("paginationControls");

    let filteredRows = tableRows;
    let currentPage = 1;

    // --- Core Filtering Logic ---
    function filterTable() {
        const searchValue = searchInput.value.toLowerCase().trim();

        // Check if there are actual rows to filter (handles "No alert" message row)
        if (tableRows.length > 0 && tableRows[0].classList.contains('no-alert')) {
             filteredRows = []; // No rows to display if only the 'no alert' row exists
        } else {
            filteredRows = tableRows.filter((row) => {
                // Data is extracted from the table cells (tds)
                const variantID = row.children[0].innerText.toLowerCase();
                const productName = row.children[1].innerText.toLowerCase();
                const variantName = row.children[2].innerText.toLowerCase();
                
                const matchSearch =
                    variantID.includes(searchValue) ||
                    productName.includes(searchValue) ||
                    variantName.includes(searchValue);

                return matchSearch;
            });
        }
        
        currentPage = 1; // Reset to first page after a filter change
        updatePagination();
    }

    // --- Pagination Logic ---
    function updatePagination() {
        const rowsPerPage = parseInt(rowPerPageSelect.value);
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);

        // Hide all original rows
        tableRows.forEach((row) => (row.style.display = "none"));
        
        // If there are no rows after filtering, skip rendering
        if (totalRows === 0) {
            paginationControls.innerHTML = "";
            return;
        }

        // Calculate start and end index for the current page
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        // Show only the rows for the current page
        filteredRows.slice(start, end).forEach((row) => {
            row.style.display = "";
        });

        renderPaginationControls(totalPages);
    }

    // --- Pagination Control Rendering ---
    function renderPaginationControls(totalPages) {
        paginationControls.innerHTML = "";

        if (totalPages <= 1) return;

        // PREVIOUS BUTTON
        const prevBtn = document.createElement("button");
        prevBtn.innerText = "Previous";
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => {
            currentPage--;
            updatePagination();
        };
        paginationControls.appendChild(prevBtn);

        // PAGE NUMBERS
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

        // NEXT BUTTON
        const nextBtn = document.createElement("button");
        nextBtn.innerText = "Next";
        nextBtn.disabled = currentPage === totalPages;

        nextBtn.onclick = () => {
            currentPage++;
            updatePagination();
        };
        paginationControls.appendChild(nextBtn);
    }

    // --- Attach Event Listeners ---
    searchInput.addEventListener("keyup", filterTable);

    // When rows per page changes, reset to page 1 and update
    rowPerPageSelect.addEventListener("change", () => {
        currentPage = 1;
        updatePagination();
    });

    // --- Initial Render ---
    // Start the process by running the filter and pagination for the first time
    filterTable(); 
});