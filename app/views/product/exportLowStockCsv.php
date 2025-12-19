<?php
include '../../controllers/productsController.php';

$controller = new ProductController();
$products = $controller->getAllProductVariant('', '', 'lowStock');

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="low_stock_report.csv"');
header('Pragma: no-cache');
header('Expires: 0');

$output = fopen('php://output', 'w');

fputcsv($output, ['Product Variant ID','Product Name','Variant Name','Category','Stock Qty','Minimum Stock','Status','Recommendation','Purchase Suggestion']);

foreach ($products as $product) {
    $status = ($product['stock_qty'] <= 0) ? 'OUT OF STOCK' : 'LOW STOCK';
    fputcsv($output, [
        $product['product_variant_id'],
        $product['product_name'],
        $product['variant_name'],
        $product['category_name'],
        $product['stock_qty'],
        $product['min_stock_level'],
        $status,
        $product['stock_qty'] <= 0 ? 'Reorder Immediately' : 'Consider Reordering Soon',
        $product['stock_qty'] + 20
    ]);
}

fclose($output);
exit;
?>
