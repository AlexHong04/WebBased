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

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('callModal');
    const popup = document.getElementById('draggablePopup');
    const handle = document.getElementById('dragHandle');
    const endBtn = document.getElementById('endCallBtn');
    
    // 1. GET DATA FROM URL
    const urlParams = new URLSearchParams(window.location.search);
    const isCallActive = urlParams.get('call_active') === 'true';
    const calledNum = urlParams.get('called_num');
    const calledName = urlParams.get('cname');

    // 2. ONLY RUN TIMER IF CALL IS ACTIVE
    if (isCallActive) {
        console.log("Call detected for:", calledName);
        modal.style.display = 'block';
        
        // Populate UI
        if(calledNum) document.getElementById('callingNumber').innerText = calledNum;
        if(calledName) document.getElementById('callingName').innerText = calledName;

        // --- TIMER LOGIC ---
        let totalSeconds = 0;
        const minEl = document.getElementById('minutes');
        const secEl = document.getElementById('seconds');
        const startTimeStr = new Date().toLocaleTimeString();

        const timerInterval = setInterval(() => {
            totalSeconds++;
            minEl.innerText = Math.floor(totalSeconds / 60).toString().padStart(2, '0');
            secEl.innerText = (totalSeconds % 60).toString().padStart(2, '0');
        }, 1000);

        // --- FINISH BUTTON LOGIC ---
        endBtn.onclick = function() {
            console.log("Finish clicked. Saving log...");
            clearInterval(timerInterval);
            
            const endTimeStr = new Date().toLocaleTimeString();
            const duration = minEl.innerText + ":" + secEl.innerText;

            // Save to LocalStorage using phone as key (digits only)
            const cleanPhone = calledNum.replace(/\D/g,'');
            const logData = { start: startTimeStr, end: endTimeStr, dur: duration };
            localStorage.setItem('log_' + cleanPhone, JSON.stringify(logData));

            // Hide modal and Clean URL
            modal.style.display = 'none';
            window.history.replaceState({}, document.title, window.location.pathname);
            
            // Immediately update the table display
            displayLogsInTable();
        };
    }

    // 3. DRAG LOGIC (Remains the same)
    let isDragging = false;
    let offsetX, offsetY;

    handle.onmousedown = (e) => {
        isDragging = true;
        offsetX = e.clientX - popup.offsetLeft;
        offsetY = e.clientY - popup.offsetTop;
    };

    document.onmousemove = (e) => {
        if (!isDragging) return;
        popup.style.left = (e.clientX - offsetX) + 'px';
        popup.style.top = (e.clientY - offsetY) + 'px';
        popup.style.right = 'auto';
    };

    document.onmouseup = () => { isDragging = false; };

    // 4. FUNCTION TO SHOW LOGS UNDER PHONE NUMBERS
    function displayLogsInTable() {
        document.querySelectorAll('.phone-number-display').forEach(span => {
            const phone = span.innerText.trim().replace(/\D/g,'');
            const savedLog = JSON.parse(localStorage.getItem('log_' + phone));
            const container = document.querySelector('.log-container-' + phone);

            if (savedLog && container) {
                container.innerHTML = `
                    <div style="font-size: 10px; color: #15803d; background: #f0fdf4; padding: 4px; border-radius: 4px; margin-top: 5px; border: 1px solid #bbf7d0; display: inline-block;">
                        ⏱️ <b>${savedLog.dur}</b> (${savedLog.start} - ${savedLog.end})
                    </div>
                `;
            }
        });
    }

    displayLogsInTable(); // Run this regardless of whether a call is active
});