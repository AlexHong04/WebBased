<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['undo_products'])) {
    // Check if it's older than 10 seconds (should have been auto-deleted)
    if (isset($_SESSION['undo_time']) && (time() - $_SESSION['undo_time'] > 10)) {
        unset($_SESSION['undo_products']);
        unset($_SESSION['undo_count']);
        unset($_SESSION['undo_time']);
    }
}

if (!isset($_SESSION['undo_id'])) {
    $_SESSION['undo_id'] = uniqid();
}

$title = "Product Maintenance";
$pageCSS = "productList.css";

include '../../controllers/productsController.php';
include_once '../../helpers/html.php';
require_once __DIR__ . '/../../helpers/auth.php';

authenticate();
$controller = new ProductsController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['restock_selected'])) {
        $restockQuantities = $_POST['restock_qty'] ?? [];
        $variantProductMap = $_POST['product_id'] ?? [];

        if (!empty($restockQuantities)) {
            foreach ($restockQuantities as $variantId => $qty) {
                $qty = intval($qty);
                $productId = $variantProductMap[$variantId] ?? null;

                if ($qty > 0 && $productId) {
                    $controller->handleRestockProductById($variantId, $qty, $productId);
                }
            }
            $_SESSION['flash_success'] = "Bulk restock completed successfully!";
        } else {
            $_SESSION['flash_error'] = "Failed to restock products. Please try again.";
        }

        header("Location: productList.php");
        exit;
    }

    if (isset($_POST['delete_selected'])) {
        $productIds = $_POST['product_ids'] ?? [];
        $productIds = array_unique(array_filter($productIds));

        if (!empty($productIds)) {
            $controller->handleSoftDelete($productIds);

            $actualCount = count($productIds);
            $_SESSION['undo_products'] = $productIds;
            $_SESSION['undo_count'] = $actualCount;
            $_SESSION['undo_time'] = time();
            $_SESSION['undo_batch_id'] = uniqid(); // Unique ID for this batch

            header("Location: productList.php?undo=1&batch=" . $_SESSION['undo_batch_id'] . "&t=" . time());
            exit;
        } else {
            $_SESSION['flash_error'] = "No products selected for deletion.";
            header("Location: productList.php");
            exit;
        }
    }

    if (isset($_POST['delete_single'])) {
        $productId = $_POST['product_id'] ?? null;

        if ($productId) {
            $controller->handleSoftDelete([$productId]);

            $_SESSION['undo_products'] = [$productId];
            $_SESSION['undo_count'] = 1;
            $_SESSION['undo_time'] = time();
            $_SESSION['undo_batch_id'] = uniqid();

            header("Location: productList.php?undo=1&batch=" . $_SESSION['undo_batch_id'] . "&t=" . time());
            exit;
        }
    }

    if (isset($_POST['undo_action']) && $_POST['undo_action'] === 'restore') {
        if (isset($_SESSION['undo_products'])) {
            $controller->handleRestore($_SESSION['undo_products']);
            $count = count($_SESSION['undo_products']);
            unset($_SESSION['undo_products']);
            unset($_SESSION['undo_count']);
            unset($_SESSION['undo_time']);
            unset($_SESSION['undo_batch_id']);

            $_SESSION['flash_success'] = "$count product(s) restored successfully!";
            header("Location: productList.php");
            exit;
        }
    }

    if (isset($_POST['undo_action']) && $_POST['undo_action'] === 'permanent') {
        if (isset($_SESSION['undo_products'])) {
            $controller->handlePermanentDelete($_SESSION['undo_products']);
            unset($_SESSION['undo_products']);
            unset($_SESSION['undo_count']);
            unset($_SESSION['undo_time']);
            unset($_SESSION['undo_batch_id']);
            header("Location: productList.php");
            exit;
        }
    }
}

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
) {

    $input = json_decode(file_get_contents('php://input'), true);

    if (isset($input['action']) && $input['action'] === 'restore' && isset($_SESSION['undo_products'])) {
        $controller->handleRestore($_SESSION['undo_products']);
        $count = count($_SESSION['undo_products']);
        unset($_SESSION['undo_products']);
        unset($_SESSION['undo_count']);
        unset($_SESSION['undo_time']);
        echo json_encode(['success' => true, 'message' => "$count product(s) restored"]);
        exit;
    }

    if (isset($input['action']) && $input['action'] === 'permanent' && isset($_SESSION['undo_products'])) {
        $controller->handlePermanentDelete($_SESSION['undo_products']);
        $count = count($_SESSION['undo_products']);
        unset($_SESSION['undo_products']);
        unset($_SESSION['undo_count']);
        unset($_SESSION['undo_time']); 
        echo json_encode(['success' => true, 'message' => "$count product(s) permanently deleted"]);
        exit;
    }
}

include '../adminHeader.php';

$current_sort = $_GET['sort'] ?? 'product_id';
$current_order = $_GET['order'] ?? 'asc';

$products = $controller->getAllProducts($current_sort, $current_order, "all");
$productsCount = count($products);
$categories = $controller->getAllCategories();
$totalVariants = $controller->getAllProductVariant($current_sort, $current_order, "countAll");
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<body>
    <?php
    showToast();
    ?>
    <?php if (isset($_SESSION['undo_products']) && !empty($_SESSION['undo_products'])): ?>
        <div id="undoToast" class="undo-toast" data-count="<?= $_SESSION['undo_count'] ?>">
            <div class="undo-toast-content">
                <div class="undo-message">
                    <span>Product(s) moved to trash</span>
                    <span class="undo-timer" id="undoTimer">10</span>
                </div>
                <div class="undo-actions">
                    <button type="button" id="undoBtn" class="undo-btn">
                        Restore
                    </button>
                    <button type="button" id="dismissBtn" class="dismiss-btn" title="Delete permanently">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="undo-progress" id="undoProgress"></div>
        </div>
    <?php endif; ?>

    <div class="main-content-wrapper">

        <div class="filter-sidebar">

            <div class="filter-block">
                <h4>Category</h4>
                <?php
                if (is_array($categories) && !empty($categories)):
                    foreach ($categories as $cat):
                ?>
                        <label>
                            <input type="checkbox" class="filter-category" value="<?= htmlspecialchars($cat['category_name'] ?? '') ?>">
                            <?= htmlspecialchars($cat['category_name'] ?? 'Unknown Category') ?>
                        </label>
                <?php
                    endforeach;
                endif;
                ?>
            </div>

            <div class="filter-block">
                <h4>Price Range</h4>
                <input type="range" class="rangeInput" id="priceMin" min="0" max="200" step="1">
                <p>Price: RM<span id="minText">0</span> – RM<span id="maxText">200</span></p>
            </div>

        </div>
        <div class="product-list-container">
            <form method="POST" id="deleteForm" action="productList.php">

                <div class="first_row">
                    <div class="search_sort">
                        <input type="text" class="form-control" id="searchInput" placeholder="Search Product by Id, Name, Category">
                    </div>

                    <div class="pagination">
                        <label>Rows:</label>
                        <select name="rowPerPage" id="rowPerPage">
                            <option value="5" selected>5</option>

                            <?php
                            $options = [10, 20, 30, 40, 50];

                            foreach ($options as $opt) {
                                if ($opt < $productsCount) {
                                    echo "<option value='$opt'>$opt</option>";
                                }
                            }

                            echo "<option value='$productsCount'>All ($productsCount)</option>";
                            ?>
                        </select>

                    </div>
                </div>
                <div class="table-header">
                    <div class="table-header-left">
                        <div class="product-count">
                            <p>Total Products: <?php echo $productsCount; ?></p>
                        </div>
                    </div>

                    <div class="table-header-right">

                        <button type="button"
                            id="addProductBtn"
                            class="btn-add-product"
                            onclick="window.location.href='/app/views/product/addSingleProduct.php'">
                            ➕ Add Product
                        </button>


                        <button type="button" id="bulkRestockBtn" class="btn btn-restock">
                            📦 Bulk Restock Selected
                        </button>

                        <button type="submit" name="delete_selected" class="btn btn-delete">
                            🗑 Delete Selected
                        </button>

                        <button type="button" id="toggleViewBtn" class="btn btn-view">
                            🖼️ Grid View
                        </button>
                    </div>
                </div>

                <div class="table_overview">
                    <table id="productTable" class="product-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Image</th>

                                <?php $link = $controller->get_sort_link('product_id', $current_sort, $current_order); ?>
                                <th class="sortable-header"><a href="<?= $link['url'] ?>">Product ID<?= $link['icon'] ?></a></th>

                                <?php $link = $controller->get_sort_link('product_name', $current_sort, $current_order); ?>
                                <th class="sortable-header"><a href="<?= $link['url'] ?>">Product Name<?= $link['icon'] ?></a></th>

                                <?php $link = $controller->get_sort_link('created_at', $current_sort, $current_order); ?>
                                <th class="sortable-header"><a href="<?= $link['url'] ?>">Created At<?= $link['icon'] ?></a></th>

                                <?php $link = $controller->get_sort_link('updated_at', $current_sort, $current_order); ?>
                                <th class="sortable-header"><a href="<?= $link['url'] ?>">Updated At<?= $link['icon'] ?></a></th>

                                <th>Cost Price(RM)</th>

                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $col_span = 9;

                            if (is_array($products) && !empty($products)) {
                                foreach ($products as $product) {
                                    $img_url = $product['img_url'] ?? '/img/default-product.png';
                                    $updated_at = $product['updated_at'] ?? 'N/A';
                                    $category_name = $product['category_name'] ?? 'N/A';
                                    $images = explode(',', $img_url);
                                    if (count($images) === 1) {
                                        $images = explode('，', $img_url);
                                    }

                                    $display_img = trim($images[0]);
                                    echo "<tr>";
                                    $variants = $controller->getProductVariantsByProductId($product['product_id']);
                                    echo "<td>
                                    <input type='checkbox' class='row-checkbox' 
                                        name='product_ids[]' 
                                        value='" . $product['product_id'] . "' 
                                        data-variants='" . htmlspecialchars(json_encode($variants), ENT_QUOTES, 'UTF-8') . "'>
                                    </td>";

                                    echo "<td><img src='../../../public/images/$category_name/{$display_img}' alt='{$product['product_name']} Image' width='50' height='50'></td>";
                                    echo "<td>{$product['product_id']}</td>";
                                    echo "<td>{$product['product_name']}</td>";
                                    echo "<td>{$product['created_at']}</td>";
                                    echo "<td>{$updated_at}</td>";
                                    echo "<td>{$product['cost_price']}</td>";
                                    echo "<td>{$category_name}</td>";

                                    echo '<td>
                                <div class="actions">
                                    <a href="addSingleProduct.php?product_id=' . $product['product_id'] . '">
                                    <svg xmlns="http://www.w3.org/2000/svg" 
                                        width="16" height="16" 
                                        viewBox="0 0 512 512" 
                                        fill="currentColor">
                                    <path d="M448 0H320c-17.7 0-32 14.3-32 32s14.3 32 32 32h75.3L201 258.3c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L440 109.3V184c0 17.7 14.3 32 32 32s32-14.3 32-32V32c0-17.7-14.3-32-32-32z"/>
                                    <path d="M64 64v384c0 35.3 28.7 64 64 64h320c35.3 0 64-28.7 64-64V256c0-17.7-14.3-32-32-32s-32 14.3-32 32v192c0 17.7-14.3 32-32 32H128c-17.7 0-32-14.3-32-32V64c0-17.7-14.3-32-32-32S64 46.3 64 64z"/>
                                    </svg>
                                    </a>
                                     <form method="POST" action="productList.php" class="delete-single-form" style="display:inline;">
                                        <input type="hidden" name="delete_selected" value="1">
                                        <input type="hidden" name="product_ids[]" value="' . $product['product_id'] . '">
                                        <button type="submit" class="btn btn-delete-single" data-single-delete="1" onclick="return confirm(\'Delete this product?\');">
                                            🗑
                                        </button>
                                    </form>
                                </div>

                            </td>';
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='{$col_span}' style='text-align: center;'>No products found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                    <div id="productGrid" class="product-grid" style="display: none;">
                    </div>
                </div>
                <div id="paginationControls" class="pagination-controls"></div>
            </form>
        </div>
    </div>
    <!-- Bulk Restock Modal -->
    <div id="bulkRestockModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Bulk Restock Products</h3>
            <form id="bulkRestockForm" method="POST" action="productList.php">
                <div id="modalProductsContainer" class="modal-products-container">
                    <!-- Dynamic content will be inserted here -->
                </div>
                <input type="hidden" name="restock_selected" value="1">
                <button type="submit" class="btn-restock-submit" disabled>Update Stock</button>
            </form>
        </div>
    </div>

    <script src="/public/js/productList.js"></script>
</body>