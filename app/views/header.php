<?php
require_once __DIR__ . '/../controllers/userController.php';
require_once __DIR__ . '/../models/CartModel.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../controllers/productsController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


$cartCount = 0;
$cartModel = new CartModel();
$controller = new ProductsController();

$categories = $controller->fetchAllCategories();

if (isset($_SESSION['customerId'])) {
    $cid = $_SESSION['customerId'];
    $cartCount = $cartModel->getCartCount($cid);
} elseif (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $controller = new userController();
    $controller->signOut();
    exit;
}

$cronPath = __DIR__ . '/../controllers/cronController.php';
if (file_exists($cronPath)) {
    require_once $cronPath;
    $cron = new cronController();
    $cron->runOrderCleanup();
}

if (isset($_GET['ajax_action']) && $_GET['ajax_action'] === 'search_suggestions') {
    if (ob_get_length()) ob_clean();
    $controller->getSearchSuggestions();
    exit;
}

$query = $_GET['q'] ?? '';
$products = [];

if (!empty($query)) {
    $products = $controller->search($query);
}
$currentId = $_SESSION['customerId'] ?? $_SESSION['adminId'] ?? null;
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="/public/images/lovine_logo_circle.png" />
    <title><?php echo $title ?? "My site"; ?></title>

    <link rel="stylesheet" href="/public/css/pc_reset.css" />
    <link rel="stylesheet" href="/public/css/header_footer.css" />
    <link rel="stylesheet" href="/public/css/darklightMode.css" />


    <!-- <link rel="stylesheet" href="/public/css/animation.css" /> -->
    <?php if (!empty($pageCSS)): ?>
        <link rel="stylesheet" href="/public/css/<?php echo $pageCSS; ?>" />
        <!-- <link rel="stylesheet" href="/WebBased/public/css/<?php echo $pageCSS; ?>" /> -->
    <?php endif; ?>
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>

<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <a href="/app/views/home.php">
                    <img src="/public/images/lovine_logo_circle.png" alt="Lovine Logo" class="logo-image" />
                </a>
            </div>

            <nav class="main-nav">
                <ul>
                    <li><a href="/app/views/home.php">Home</a></li>
                    <li class="dropdown">
                        <a href="#">Category</a>
                        <ul class="dropdown-menu">
                            <li><a href="/app/views/category/categoryHomePage.php">All</a></li>
                            <?php if (!empty($categories)): ?>
                                <?php foreach ($categories as $cat): ?>
                                    <li>
                                        <a href="/app/views/category/specificCategoryPage.php?id=<?php echo $cat['category_id']; ?>">
                                            <?php echo $cat['category_name']; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li><a href="/app/views/order/customerOrderHistory.php?id=<?= $cid ?>">Order</a></li>
                </ul>
            </nav>

            <nav class="right-nav">
                <ul>
                    <form class="header-search" action="/app/views/product/product_details.php" method="get" role="search" aria-label="Site search" style="position: relative;"> <input type="search" id="searchInput" name="q" placeholder="Search" aria-label="Search" autocomplete="off" />
                        <button type="submit" aria-label="Search">
                            <div class="cart-link">
                                <svg class="cart-icon" viewBox="0 0 640 640" width="24" height="24">
                                    <path fill="currentColor" d="M480 272C480 317.9 465.1 360.3 440 394.7L566.6 521.4C579.1 533.9 579.1 554.2 566.6 566.7C554.1 579.2 533.8 579.2 521.3 566.7L394.7 440C360.3 465.1 317.9 480 272 480C157.1 480 64 386.9 64 272C64 157.1 157.1 64 272 64C386.9 64 480 157.1 480 272zM272 416C351.5 416 416 351.5 416 272C416 192.5 351.5 128 272 128C192.5 128 128 192.5 128 272C128 351.5 192.5 416 272 416z" />
                                </svg>
                            </div>
                        </button>

                        <div id="searchSuggestions" class="search-dropdown"></div>
                    </form>

                    <li>
                        <a href="#" id="scanQrBtn" class="cart-link" title="Scan QR Code">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="cart-icon">
                                <path d="M3 7V5a2 2 0 0 1 2-2h2"></path>
                                <path d="M17 3h2a2 2 0 0 1 2 2v2"></path>
                                <path d="M21 17v2a2 2 0 0 1-2 2h-2"></path>
                                <path d="M7 21H5a2 2 0 0 1-2-2v-2"></path>
                                <path d="M12 7v10"></path>
                            </svg>
                        </a>
                    </li>

                    <li>
                        <a href="/app/views/shoppingCart/cart.php" class="cart-link">
                            <span class="cart-count" id="globalCartCount"><?php echo $cartCount; ?></span>
                            <svg class="cart-icon" viewBox="0 0 24 24" width="24" height="24">
                                <path fill="currentColor" d="M17,18C15.89,18 15,18.89 15,20A2,2 0 0,0 17,22A2,2 0 0,0 19,20C19,18.89 18.1,18 17,18M1,2V4H3L6.6,11.59L5.24,14.04C5.09,14.32 5,14.65 5,15A2,2 0 0,0 7,17H19V15H7.42A0.25,0.25 0 0,1 7.17,14.75C7.17,14.7 7.18,14.66 7.2,14.63L8.1,13H15.55C16.3,13 16.96,12.58 17.3,11.97L20.88,5.5C20.95,5.34 21,5.17 21,5A1,1 0 0,0 20,4H5.21L4.27,2M7,18C5.89,18 5,18.89 5,20A2,2 0 0,0 7,22A2,2 0 0,0 9,20C9,18.89 8.1,18 7,18Z" />
                            </svg>
                        </a>
                    </li>

                    <li>
                        <button id="themeToggle"></button>
                    </li>

                    <?php if (isset($_SESSION['customerId']) || isset($_SESSION['adminId'])): ?>
                        <li class="dropdown">
                            <a href="#" class="cart-link" title="My Account">
                                <svg class="cart-icon" viewBox="0 0 640 640" width="24" height="24">
                                    <path fill="currentColor" d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112 447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576 528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z" />
                                </svg>
                            </a>

                            <ul class="dropdown-menu" style="right: 0; left: auto; min-width: 150px;">
                                <li>
                                    <!-- <a href="/app/views/userProfile/profile.php" style="display: flex; align-items: center; gap: 10px;"> -->
                                    <a href="/app/views/userProfile/profile.php?id=<?= $currentId ?>" style="display: flex; align-items: center; gap: 10px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" fill="currentColor">
                                            <path d="M384 112L384 128C384 145.7 369.7 160 352 160L288 160C270.3 160 256 145.7 256 128L256 112L192 112C183.2 112 176 119.2 176 128L176 512C176 520.8 183.2 528 192 528L448 528C456.8 528 464 520.8 464 512L464 128C464 119.2 456.8 112 448 112L384 112zM128 128C128 92.7 156.7 64 192 64L448 64C483.3 64 512 92.7 512 128L512 512C512 547.3 483.3 576 448 576L192 576C156.7 576 128 547.3 128 512L128 128zM288 384L352 384C396.2 384 432 419.8 432 464C432 472.8 424.8 480 416 480L224 480C215.2 480 208 472.8 208 464C208 419.8 243.8 384 288 384zM264 288C264 257.1 289.1 232 320 232C350.9 232 376 257.1 376 288C376 318.9 350.9 344 320 344C289.1 344 264 318.9 264 288z" />
                                        </svg>
                                        <span>My Profile</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="/app/views/home.php?action=logout" style="display: flex; align-items: center; gap: 10px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" fill="currentColor">
                                            <path d="M224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L160 96C107 96 64 139 64 192L64 448C64 501 107 544 160 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480C142.3 480 128 465.7 128 448L128 192C128 174.3 142.3 160 160 160L224 160zM566.6 342.6C579.1 330.1 579.1 309.8 566.6 297.3L438.6 169.3C426.1 156.8 405.8 156.8 393.3 169.3C380.8 181.8 380.8 202.1 393.3 214.6L466.7 288L256 288C238.3 288 224 302.3 224 320C224 337.7 238.3 352 256 352L466.7 352L393.3 425.4C380.8 437.9 380.8 458.2 393.3 470.7C405.8 483.2 426.1 483.2 438.6 470.7L566.6 342.7z" />
                                        </svg>
                                        <span>Logout</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                    <?php else: ?>

                        <li><a href=" /app/views/security/signIn.php">Sign In/Sign Up</a>
                        </li>

                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div id="qrPopup" class="popup-overlay">
        <div class="popup-box">
            <h3>Scan QR Code</h3>
            <p>Point your camera at an order QR code</p>

            <div id="qr-reader"></div>

            <div class="popup-actions">
                <input type="file" id="qr-input-file" accept="image/*" style="display:none">

                <button id="uploadQrBtn">
                    <i class="fa-solid fa-image"></i> Upload
                </button>

                <button id="qrCancel">Cancel</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script src="/public/js/header.js"></script>
    <script>
        // const scanBtn = document.getElementById("scanQrBtn");
        // const qrPopup = document.getElementById("qrPopup");
        // const qrCancel = document.getElementById("qrCancel");

        // let qrScanner;

        // scanBtn.addEventListener("click", (e) => {
        //     e.preventDefault();

        //     // Add 'active' class for the fade-in animation
        //     qrPopup.classList.add("active");

        //     // Initialize scanner
        //     qrScanner = new Html5Qrcode("qr-reader");

        //     const config = {
        //         fps: 10,
        //         qrbox: {
        //             width: 250,
        //             height: 250
        //         },
        //         aspectRatio: 1.0
        //     };

        //     qrScanner.start({
        //             facingMode: "environment"
        //         }, config,
        //         (decodedText) => {
        //             // SUCCESS
        //             console.log("QR Code:", decodedText);

        //             qrScanner.stop().then(() => {
        //                 qrPopup.classList.remove("active");

        //                 if (decodedText.includes("http")) {
        //                     window.location.href = decodedText;
        //                 } else {
        //                     window.location.href = `adminOrderDetails.php?id=${decodedText}`;
        //                 }
        //             });
        //         },
        //         (error) => {
        //             // Ignore failures
        //         }
        //     );
        // });

        // qrCancel.addEventListener("click", () => {
        //     if (qrScanner) {
        //         qrScanner.stop().then(() => {
        //             qrScanner.clear();
        //         }).catch(err => console.log(err));
        //     }
        //     // Remove active class for fade-out
        //     qrPopup.classList.remove("active");
        // });


        // const toggleBtn = document.getElementById("themeToggle");
        // const root = document.documentElement;
        // const sunIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>`;

        // const moonIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>`;

        // function updateButtonText(theme) {
        //     if (toggleBtn) {
        //         if (theme === "dark") {
        //             // Show Sun Icon + Text "Light Mode"
        //             toggleBtn.innerHTML = `${sunIcon}`;
        //             toggleBtn.style.color = "#ffffff"; // Ensure text is white in dark mode
        //         } else {
        //             // Show Moon Icon + Text "Dark Mode"
        //             toggleBtn.innerHTML = `${moonIcon}`;
        //             toggleBtn.style.color = "inherit"; // Use default color in light mode
        //         }
        //     }
        // }

        // const savedTheme = localStorage.getItem("theme");
        // const systemPrefersDark = window.matchMedia("(prefers-color-scheme: dark)").matches;

        // let initialTheme = "light";

        // if (savedTheme) {
        //     initialTheme = savedTheme;
        // } else if (systemPrefersDark) {
        //     initialTheme = "dark";
        // }

        // root.setAttribute("data-theme", initialTheme);
        // updateButtonText(initialTheme);

        // if (toggleBtn) {
        //     toggleBtn.addEventListener("click", () => {
        //         const currentTheme = root.getAttribute("data-theme");
        //         const newTheme = currentTheme === "dark" ? "light" : "dark";

        //         root.setAttribute("data-theme", newTheme);
        //         localStorage.setItem("theme", newTheme);
        //         updateButtonText(newTheme);
        //     });
        // }

        // document.addEventListener('DOMContentLoaded', function() {
        //     const input = document.getElementById('searchInput');
        //     const suggestionsBox = document.getElementById('searchSuggestions');
        //     let debounceTimer;

        //     input.addEventListener('input', function() {
        //         const query = this.value.trim();
        //         clearTimeout(debounceTimer);

        //         if (query.length < 2) {
        //             suggestionsBox.style.display = 'none';
        //             return;
        //         }

        //         debounceTimer = setTimeout(() => {
        //             fetch(`/app/api/search_suggestions.php?q=${encodeURIComponent(query)}`)
        //                 .then(response => response.json())
        //                 .then(data => {
        //                     if (data.length > 0) {
        //                         renderSuggestions(data);
        //                     } else {
        //                         suggestionsBox.style.display = 'none';
        //                     }
        //                 })
        //                 .catch(err => console.error('Search error:', err));
        //         }, 300);
        //     });

        //     function renderSuggestions(products) {
        //         let html = '';
        //         products.forEach(p => {
        //             const link = `/app/views/product/productDetails.php?id=${p.product_id}`;
        //             html += `
        //         <a href="${link}" class="search-item">
        //             <div class="search-item-info">
        //                 <span class="search-item-name">${highlightMatch(p.product_name, input.value)}</span>
        //                 <span class="search-item-price">RM ${p.sale_price}</span>
        //             </div>
        //         </a>
        //     `;
        //         });
        //         suggestionsBox.innerHTML = html;
        //         suggestionsBox.style.display = 'block';
        //     }

        //     function highlightMatch(text, query) {
        //         const regex = new RegExp(`(${query})`, 'gi');
        //         return text.replace(regex, '<span style="color:#f1a2b8; font-weight:bold;">$1</span>');
        //     }

        //     input.addEventListener('focus', function() {
        //         if (this.value.trim() === '') {
        //             showHistory();
        //         }
        //     });

        //     function showHistory() {
        //         const history = JSON.parse(localStorage.getItem('searchHistory')) || [];
        //         if (history.length === 0) return;

        //         let html = `
        //     <div class="history-header">
        //         <span>Recent Searches</span>
        //         <span class="clear-history" onclick="clearSearchHistory()">Clear</span>
        //     </div>
        // `;

        //         history.forEach(term => {
        //             html += `
        //         <div class="search-item" onclick="selectHistory('${term}')">
        //             <i class="fa fa-history" style="color:#ccc; margin-right:5px;"></i>
        //             <span>${term}</span>
        //         </div>
        //     `;
        //         });

        //         suggestionsBox.innerHTML = html;
        //         suggestionsBox.style.display = 'block';

        //         document.querySelector('.clear-history').addEventListener('click', function(e) {
        //             e.stopPropagation(); 
        //             localStorage.removeItem('searchHistory');
        //             suggestionsBox.style.display = 'none';
        //         });
        //     }

        //     window.selectHistory = function(term) {
        //         input.value = term;
        //         input.closest('form').submit();
        //     };

        //     window.clearSearchHistory = function() {
        //         localStorage.removeItem('searchHistory');
        //         suggestionsBox.style.display = 'none';
        //     };

        //     input.closest('form').addEventListener('submit', function() {
        //         const val = input.value.trim();
        //         if (val) {
        //             let history = JSON.parse(localStorage.getItem('searchHistory')) || [];
        //             history = history.filter(item => item !== val);
        //             history.unshift(val);
        //             if (history.length > 5) history.pop();
        //             localStorage.setItem('searchHistory', JSON.stringify(history));
        //         }
        //     });

        //     document.addEventListener('click', function(e) {
        //         if (!input.contains(e.target) && !suggestionsBox.contains(e.target)) {
        //             suggestionsBox.style.display = 'none';
        //         }
        //     });
        // });
    </script>
</body>

</html>