<?php
$title = "Low Stock Alert";
$pageCSS = "lowStockAlert.css";

require_once __DIR__ . '/../header.php';
include '../../controllers/productsController.php';

$controller = new ProductController();

$current_sort = $_GET['sort'] ?? 'product_variant_id';
$current_order = $_GET['order'] ?? 'asc';

$lowStockProducts = $controller->getAllProductVariant($current_sort, $current_order, "lowStock");
$lowStockCount = count($lowStockProducts);
$outOfStockCount = $controller->getAllProductVariant($current_sort, $current_order, "countOutOfStock");
$totalVariants = $controller->getAllProductVariant($current_sort, $current_order, "countAll");
$criticalCount = $lowStockCount + $outOfStockCount;
$criticalPercentage = ($totalVariants > 0)
    ? number_format(($criticalCount / $totalVariants) * 100, 2)
    : 0.00;

if (isset($_GET['action']) && $_GET['action'] === 'sendPdf') {
    $controller->sendLowStockPdf();
}
?>

<body>
    <div class="container">
        <h1>🚨 Low Stock Alert Dashboard</h1>

        <div class="insights-panel">

            <div class="insight-card low-stock-count-card">
                <h3>⚠️ Low Stock Variants</h3>
                <p id="lowStockCount"><?php echo $lowStockCount; ?></p>
                <small>Need immediate review.</small>
            </div>

            <div class="insight-card out-of-stock-card">
                <h3>🚫 Out of Stock Variants</h3>
                <p id="outOfStockCount"><?php echo $outOfStockCount; ?></p>
                <small>High risk of lost sales.</small>
            </div>

            <div class="insight-card percentage-card">
                <h3>📈 Critical Inventory Percentage</h3>
                <p id="criticalPercentage"><?php echo $criticalPercentage; ?>%</p>
                <small><?php echo $criticalCount; ?> total critical items out of <?php echo $totalVariants; ?>.</small>
            </div>

        </div>

        <div class="data-table-container">
            <div class="first_row">
                <div class="search_sort">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search Product by ID, Name, Variant">
                </div>

                <a href="?action=sendPdf" style="color:#fc84a3;">Send To Email</a>
                <a href="exportLowStockCsv.php" style="color:#4caf50;">Export CSV</a>

                <div class="pagination">
                    <label>Rows:</label>
                    <select name="rowPerPage" id="rowPerPage">
                        <option value="10" <?php echo ($lowStockCount > 10 && $lowStockCount <= 20) ? '' : 'selected'; ?>>10</option>

                        <?php
                        $standardOptions = [20, 30, 40, 50];

                        foreach ($standardOptions as $value) {
                            if ($value < $lowStockCount || $value === 10) {
                                $selected = ($value == 20 && $lowStockCount > 20 && $lowStockCount <= 30) ? 'selected' : '';
                                echo '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
                            }
                        }

                        if ($lowStockCount > 50) {
                            $nextOption = ceil($lowStockCount / 10) * 10;
                            if ($nextOption > 70) {
                                echo '<option value="' . $nextOption . '">' . $nextOption . '</option>';
                            }
                        }

                        if ($lowStockCount > 10) {
                            $selected = ($lowStockCount > 50) ? 'selected' : '';
                            echo '<option value="' . $lowStockCount . '" ' . $selected . '>All (' . $lowStockCount . ')</option>';
                        }
                        ?>

                    </select>
                </div>
            </div>
            <table class="low-stock-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th class="sortable" data-sort-column="product_variant_id">
                            ID
                            <?php echo ($current_sort === 'product_variant_id') ? ($current_order === 'asc' ? '▲' : '▼') : ''; ?>
                        </th>

                        <th class="sortable" data-sort-column="product_name">
                            Product
                            <?php echo ($current_sort === 'product_name') ? ($current_order === 'asc' ? '▲' : '▼') : ''; ?>
                        </th>

                        <th class="sortable" data-sort-column="variant_name">
                            Variant
                            <?php echo ($current_sort === 'variant_name') ? ($current_order === 'asc' ? '▲' : '▼') : ''; ?>
                        </th>

                        <th class="sortable" data-sort-column="min_stock_level">
                            Threshold
                            <?php echo ($current_sort === 'min_stock_level') ? ($current_order === 'asc' ? '▲' : '▼') : ''; ?>
                        </th>

                        <th class="sortable" data-sort-column="stock_qty">
                            Quantity
                            <?php echo ($current_sort === 'stock_qty') ? ($current_order === 'asc' ? '▲' : '▼') : ''; ?>
                        </th>

                        <th>Stock Status</th>

                        <th class="sortable" data-sort-column="suggested_purchase_quantity">
                            Suggest Qty
                            <?php echo ($current_sort === 'suggested_purchase_quantity') ? ($current_order === 'asc' ? '▲' : '▼') : ''; ?>
                        </th>
                        <th>Action</th>

                    </tr>
                </thead>
                <tbody>
                    <?php if ($lowStockCount > 0): ?>
                        <?php foreach ($lowStockProducts as $product): ?>
                            <?php
                            $stock = $product['stock_qty'] ?? 0;
                            $img_url = $product['img_url'] ?? '';
                            $display_img = "../../../public/images/{$product['category_name']}/{$img_url}";
                            if ($stock <= 0) {
                                $status_class = 'out-of-stock';
                                $status_text = 'OUT OF STOCK';
                            } else {
                                $status_class = 'low-stock';
                                $status_text = 'LOW STOCK';
                            }
                            ?>
                            <tr class="alert-row">
                                <td><img src="<?php echo $display_img; ?>" alt="Product Image" class="product-image"></td>
                                <td><?php echo htmlspecialchars($product['product_variant_id'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['product_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['variant_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($product['min_stock_level'] ?? 'N/A'); ?></td>
                                <td class="stock-qty-cell"><?php echo htmlspecialchars($product['stock_qty'] ?? 0); ?></td>
                                <td><span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                                <td class="suggestQty"><?php echo htmlspecialchars($product['stock_qty'] + 20 ?? 0); ?></td>
                                <td>
                                    <div class="actions">
                                        <a href="addSingleProduct.php?product_id=<?php echo $product['product_id'] ?? ''; ?>">
                                            <i class="fa-solid fa-arrow-up-right-from-square" style="color:black"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-alert">All product variants are currently above the minimum stock level!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div id="paginationControls" class="pagination-controls-bar"></div>
        </div>
    </div>

    <script src="/public/js/lowStockAlert.js"></script>
</body>

<?php include '../footer.php' ?>