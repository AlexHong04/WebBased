<?php
require_once '../../controllers/productsController.php';
$controller = new ProductController();
$current_sort = $_GET['sort'] ?? 'product_variant_id';
$current_order = $_GET['order'] ?? 'asc';
$products = $controller->getAllProductVariant($current_sort, $current_order, "lowStock");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Variant Stock Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #333;
            padding: 8px 12px;
            text-align: left;
        }

        th {
            background-color: #4CAF50;
            color: white;
        }

        td.center {
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .suggested {
            font-weight: bold;
            color: #d9534f;
        }
    </style>
</head>
<body>

<h2>Product Variant Stock Report</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Product</th>
            <th>Variant</th>
            <th>Category</th>
            <th>Stock Qty</th>
            <th>Min Stock</th>
            <th>Status</th>
            <th>Suggested Purchase</th>
        </tr>
    </thead>
    <tbody>
        <!-- Example row: replace with PHP loop -->
        <?php foreach($products as $p): 
            $suggestedQty = (int)$p['stock_qty'] + 20;
        ?>
        <tr>
            <td><?php echo $p['product_variant_id']; ?></td>
            <td><?php echo $p['product_name']; ?></td>
            <td><?php echo $p['variant_name']; ?></td>
            <td><?php echo $p['category_name']; ?></td>
            <td class="center"><?php echo $p['stock_qty']; ?></td>
            <td class="center"><?php echo $p['min_stock_level']; ?></td>
            <td><?php echo $p['stock_status']; ?></td>
            <td class="center suggested"><?php echo $suggestedQty; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>


