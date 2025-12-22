document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const rowPerPageSelect = document.getElementById("rowPerPage");
    const paginationControls = document.getElementById("paginationControls");
    const tableRows = Array.from(document.querySelectorAll("tbody tr"));
    let filteredRows = [];
    let currentPage = 1;

    const WA_STORAGE_KEY = "wa_sent_log";
    const CALL_STORAGE_KEY = "call_log";

    // ---------------- PAGINATION & SEARCH ----------------
    function getCustomerGroups() {
        const groups = {};
        tableRows.forEach(row => {
            const customerId = row.dataset.customerId;
            if (!groups[customerId]) groups[customerId] = [];
            groups[customerId].push(row);
        });
        return Object.values(groups);
    }

    const customerGroups = getCustomerGroups();

    function filterTable() {
        const searchValue = searchInput.value.toLowerCase().trim();
        filteredRows = customerGroups.filter(group =>
            group.some(row => {
                const cells = row.children;
                const customerId = cells[0]?.innerText.toLowerCase() || "";
                const customerName = cells[1]?.innerText.toLowerCase() || "";
                const phone = cells[2]?.innerText.toLowerCase() || "";
                const productName = cells[3]?.innerText.toLowerCase() || "";
                return customerId.includes(searchValue) || customerName.includes(searchValue) || phone.includes(searchValue) || productName.includes(searchValue);
            })
        );
        currentPage = 1;
        updatePagination();
    }

    function updatePagination() {
        const rowsPerPage = parseInt(rowPerPageSelect.value);
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);

        tableRows.forEach(row => row.style.display = "none");
        if (!totalRows) { paginationControls.innerHTML = ""; return; }

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        filteredRows.slice(start, end).forEach(group => group.forEach(row => row.style.display = ""));
        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        paginationControls.innerHTML = "";
        if (totalPages <= 1) return;

        const prevBtn = document.createElement("button");
        prevBtn.textContent = "Previous";
        prevBtn.disabled = currentPage === 1;
        prevBtn.onclick = () => { currentPage--; updatePagination(); };
        paginationControls.appendChild(prevBtn);

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement("button");
            btn.textContent = i;
            if (i === currentPage) btn.classList.add("active");
            btn.onclick = () => { currentPage = i; updatePagination(); };
            paginationControls.appendChild(btn);
        }

        const nextBtn = document.createElement("button");
        nextBtn.textContent = "Next";
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.onclick = () => { currentPage++; updatePagination(); };
        paginationControls.appendChild(nextBtn);
    }

    searchInput.addEventListener("keyup", filterTable);
    rowPerPageSelect.addEventListener("change", () => { currentPage = 1; updatePagination(); });
    filterTable();

    // ---------------- LOAD LOGS ----------------
    function loadLogs() {
        const waLog = JSON.parse(localStorage.getItem(WA_STORAGE_KEY)) || {};
        const callLog = JSON.parse(localStorage.getItem(CALL_STORAGE_KEY)) || {};

        // WhatsApp logs
        document.querySelectorAll(".js-send-btn").forEach(btn => {
            const id = btn.getAttribute("data-customer-id");
            if (waLog[id]) {
                const statusLabel = document.getElementById("status-" + id);
                statusLabel.innerHTML = `✅ Sent: ${waLog[id]}`;
                btn.style.opacity = "0.5";
                btn.innerText = "Send Again";
            }
        });

        // Call logs
        Object.keys(callLog).forEach(phone => {
            const container = document.querySelector('#callInfoContainer-' + phone);
            if (container && callLog[phone]) {
                container.innerHTML = `
                    <div class="call-info" style="background:#DCFCE7; padding:4px 6px; border-radius:4px; font-size:12px; margin-top:3px; display:inline-block;">
                        📞 Call: ${callLog[phone]}
                    </div>
                `;
            }
        });
    }

    // ---------------- WHATSAPP BUTTON ----------------
    document.querySelectorAll(".js-send-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.getAttribute("data-customer-id");
            const now = new Date().toLocaleString([], { month: "short", day: "numeric", hour: "2-digit", minute: "2-digit" });
            const waLog = JSON.parse(localStorage.getItem(WA_STORAGE_KEY)) || {};
            waLog[id] = now;
            localStorage.setItem(WA_STORAGE_KEY, JSON.stringify(waLog));

            const statusLabel = document.getElementById("status-" + id);
            statusLabel.innerHTML = `✅ Sent: ${now}`;
            this.style.opacity = "0.5";
        });
    });

    // ---------------- CAPTURE CALL ----------------
    const urlParams = new URLSearchParams(window.location.search);
    const calledNum = urlParams.get('called_num');

    if (calledNum) {
        const phoneKey = calledNum.replace(/\D/g,'');
        const callLog = JSON.parse(localStorage.getItem(CALL_STORAGE_KEY)) || {};
        const now = new Date().toLocaleString();

        // Save call datetime only when a call happens
        callLog[phoneKey] = now;
        localStorage.setItem(CALL_STORAGE_KEY, JSON.stringify(callLog));

        // Display call log just like WhatsApp
        const container = document.querySelector('#callInfoContainer-' + phoneKey);
        if (container) {
            container.innerHTML = `
                <div class="call-info" style="background:#DCFCE7; padding:4px 6px; border-radius:4px; font-size:12px; margin-top:3px; display:inline-block;">
                    📞 Call: ${now}
                </div>
            `;
        }

        // Remove query param from URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    loadLogs();

    // ---------------- RESET BUTTON ----------------
    const resetBtn = document.querySelector(".btn-clear");
    if (resetBtn) {
        resetBtn.addEventListener("click", () => {
            localStorage.removeItem(WA_STORAGE_KEY);
            localStorage.removeItem(CALL_STORAGE_KEY);
            window.location.reload();
        });
    }
});
