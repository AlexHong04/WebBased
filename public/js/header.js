const scanBtn = document.getElementById("scanQrBtn");
const qrPopup = document.getElementById("qrPopup");
const qrCancel = document.getElementById("qrCancel");

let qrScanner;

scanBtn.addEventListener("click", (e) => {
  e.preventDefault();

  // Add 'active' class for the fade-in animation
  qrPopup.classList.add("active");

  // Initialize scanner
  qrScanner = new Html5Qrcode("qr-reader");

  const config = {
    fps: 10,
    qrbox: {
      width: 250,
      height: 250,
    },
    aspectRatio: 1.0,
  };

  qrScanner.start(
    {
      facingMode: "environment",
    },
    config,
    (decodedText) => {
      // SUCCESS
      console.log("QR Code:", decodedText);

      qrScanner.stop().then(() => {
        qrPopup.classList.remove("active");

        if (decodedText.includes("http")) {
          window.location.href = decodedText;
        } else {
          window.location.href = `adminOrderDetails.php?id=${decodedText}`;
        }
      });
    },
    (error) => {
      // Ignore failures
    }
  );
});

qrCancel.addEventListener("click", () => {
  if (qrScanner) {
    qrScanner
      .stop()
      .then(() => {
        qrScanner.clear();
      })
      .catch((err) => console.log(err));
  }
  // Remove active class for fade-out
  qrPopup.classList.remove("active");
});

const toggleBtn = document.getElementById("themeToggle");
const root = document.documentElement;
const sunIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>`;

const moonIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>`;

function updateButtonText(theme) {
  if (toggleBtn) {
    if (theme === "dark") {
      // Show Sun Icon + Text "Light Mode"
      toggleBtn.innerHTML = `${sunIcon}`;
      toggleBtn.style.color = "#ffffff"; // Ensure text is white in dark mode
    } else {
      // Show Moon Icon + Text "Dark Mode"
      toggleBtn.innerHTML = `${moonIcon}`;
      toggleBtn.style.color = "inherit"; // Use default color in light mode
    }
  }
}

const savedTheme = localStorage.getItem("theme");
const systemPrefersDark = window.matchMedia(
  "(prefers-color-scheme: dark)"
).matches;

let initialTheme = "light";

if (savedTheme) {
  initialTheme = savedTheme;
} else if (systemPrefersDark) {
  initialTheme = "dark";
}

root.setAttribute("data-theme", initialTheme);
updateButtonText(initialTheme);

if (toggleBtn) {
  toggleBtn.addEventListener("click", () => {
    const currentTheme = root.getAttribute("data-theme");
    const newTheme = currentTheme === "dark" ? "light" : "dark";

    root.setAttribute("data-theme", newTheme);
    localStorage.setItem("theme", newTheme);
    updateButtonText(newTheme);
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const input = document.getElementById("searchInput");
  const suggestionsBox = document.getElementById("searchSuggestions");
  let debounceTimer;

  input.addEventListener("input", function () {
    const query = this.value.trim();
    clearTimeout(debounceTimer);

    if (query.length < 2) {
      suggestionsBox.style.display = "none";
      return;
    }

    debounceTimer = setTimeout(() => {
      fetch(`/app/api/search_suggestions.php?q=${encodeURIComponent(query)}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.length > 0) {
            renderSuggestions(data);
          } else {
            suggestionsBox.style.display = "none";
          }
        })
        .catch((err) => console.error("Search error:", err));
    }, 300);
  });

  function renderSuggestions(products) {
    let html = "";
    products.forEach((p) => {
      const link = `/app/views/product/productDetails.php?id=${p.product_id}`;
      html += `
                <a href="${link}" class="search-item">
                    <div class="search-item-info">
                        <span class="search-item-name">${highlightMatch(
                          p.product_name,
                          input.value
                        )}</span>
                        <span class="search-item-price">RM ${
                          p.sale_price
                        }</span>
                    </div>
                </a>
            `;
    });
    suggestionsBox.innerHTML = html;
    suggestionsBox.style.display = "block";
  }

  function highlightMatch(text, query) {
    const regex = new RegExp(`(${query})`, "gi");
    return text.replace(
      regex,
      '<span style="color:#f1a2b8; font-weight:bold;">$1</span>'
    );
  }

  input.addEventListener("focus", function () {
    if (this.value.trim() === "") {
      showHistory();
    }
  });

  function showHistory() {
    const history = JSON.parse(localStorage.getItem("searchHistory")) || [];
    if (history.length === 0) return;

    let html = `
            <div class="history-header">
                <span>Recent Searches</span>
                <span class="clear-history" onclick="clearSearchHistory()">Clear</span>
            </div>
        `;

    history.forEach((term) => {
      html += `
                <div class="search-item" onclick="selectHistory('${term}')">
                    <i class="fa fa-history" style="color:#ccc; margin-right:5px;"></i>
                    <span>${term}</span>
                </div>
            `;
    });

    suggestionsBox.innerHTML = html;
    suggestionsBox.style.display = "block";

    document
      .querySelector(".clear-history")
      .addEventListener("click", function (e) {
        e.stopPropagation();
        localStorage.removeItem("searchHistory");
        suggestionsBox.style.display = "none";
      });
  }

  window.selectHistory = function (term) {
    input.value = term;
    input.closest("form").submit();
  };

  window.clearSearchHistory = function () {
    localStorage.removeItem("searchHistory");
    suggestionsBox.style.display = "none";
  };

  input.closest("form").addEventListener("submit", function () {
    const val = input.value.trim();
    if (val) {
      let history = JSON.parse(localStorage.getItem("searchHistory")) || [];
      history = history.filter((item) => item !== val);
      history.unshift(val);
      if (history.length > 5) history.pop();
      localStorage.setItem("searchHistory", JSON.stringify(history));
    }
  });

  document.addEventListener("click", function (e) {
    if (!input.contains(e.target) && !suggestionsBox.contains(e.target)) {
      suggestionsBox.style.display = "none";
    }
  });
});
