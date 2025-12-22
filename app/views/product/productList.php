<?php
$title = "Product Maintenance";
$pageCSS = "productList.css";

include '../../controllers/productsController.php';

$controller = new ProductController();

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
            $_SESSION['success_message'] = "Selected products restocked successfully!";
        } else {
            $_SESSION['error_message'] = "No quantities entered for restocking.";
        }

        header("Location: productList.php");
        exit;
    }

    if (isset($_POST['delete_selected'])) {
        $productIds = $_POST['product_ids'] ?? [];

        if (!empty($productIds)) {
            foreach ($productIds as $id) {
                $controller->handleDeleteProductById($id);
            }
            $_SESSION['success_message'] = count($productIds) . " product(s) deleted successfully!";
        } else {
            $_SESSION['error_message'] = "No products selected for deletion.";
        }

        header("Location: productList.php");
        exit;
    }
}
include '../header.php';

$current_sort = $_GET['sort'] ?? 'product_id';
$current_order = $_GET['order'] ?? 'asc';

$products = $controller->getAllProducts($current_sort, $current_order, "all");
$productsCount = count($products);
$categories = $controller->getAllCategories();
$totalVariants = $controller->getAllProductVariant($current_sort, $current_order, "countAll");

?>

<body>
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
                        <button type="button" id="bulkRestockBtn" class="btn btn-restock">
                            📦 Bulk Restock Selected
                        </button>

                        <button type="submit" name="delete_selected" class="btn btn-delete">
                            🗑 Delete Selected
                        </button>
                    </div>
                </div>

                <div class="table_overview">
                    <table>
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
                                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                    </a>
                                     <form method="POST" action="productList.php" class="delete-single-form" style="display:inline;">
                                        <input type="hidden" name="delete_selected" value="1">
                                        <input type="hidden" name="product_ids[]" value="' . $product['product_id'] . '">
                                        <button type="submit" class="btn btn-delete-single" onclick="return confirm(\'Delete this product?\');">
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

<?php include '../footer.php' ?>