<?php
require_once __DIR__ . '/../../controllers/receiptController.php';
require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../helpers/request.php';

$controller = new ReceiptController();
$data = $controller->index(true);

if (!is_array($data)) {
    exit;
}
extract($data);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/public/css/receipt.css">
</head>

<body>
    <div class="success-card">

        <div class="brand-logo">Lovine</div>

        <div class="icon-wrapper">
            <i class="fas fa-check-circle success-icon"></i>
        </div>

        <h1>Payment Successful!</h1>
        <p class="subtitle">
            Your order has been placed. We have sent a confirmation email to you.
        </p>

        <div class="order-meta">
            <div class="meta-group">
                <span class="meta-label">Order ID</span>
                <span class="meta-value"><?= encode($order['order_id']) ?></span>
            </div>
            <div class="meta-group">
                <span class="meta-label">Payment Date</span>
                <span class="meta-value"><?= encode($orderDate) ?></span>
            </div>
        </div>

        <div class="receipt-table-wrapper">
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th style="width: 60%;">Item</th>
                        <th style="width: 15%; text-align: center;">Qty</th>
                        <th style="width: 25%; text-align: right;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderItems as $item): 
                        $itemTotal = $item['price'] * $item['order_qty'];
                    ?>
                        <tr>
                            <td>
                                <span class="item-name"><?= encode($item['product_name']) ?></span>
                                <span class="item-variant"><?= encode($item['variant_name']) ?></span>
                            </td>
                            <td style="text-align: center;">x<?= encode($item['order_qty']) ?></td>
                            <td style="text-align: right;">RM <?= number_format($itemTotal, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div style="margin-bottom: 25px;">
            <div class="receipt-total-row">
                <span style="color:#777;">Subtotal</span>
                <span>RM <?= number_format($subTotal, 2) ?></span>
            </div>

            <div class="receipt-total-row">
                <span style="color:#777;">Shipping Fee</span>
                <span>+ RM <?= number_format($deliveryFee, 2) ?></span>
            </div>

            <?php if ($order['tax_fee'] > 0): ?>
                <div class="receipt-total-row">
                    <span style="color:#777;">Tax</span>
                    <span>+ RM <?= number_format($order['tax_fee'], 2) ?></span>
                </div>
            <?php endif; ?>

            <?php 
            $discount = ($order['redeemed_point'] ?? 0) / 100;
            if ($discount > 0): 
            ?>
                <div class="receipt-total-row">
                    <span style="color:#fc84a3;">Points Redeemed</span>
                    <span style="color:#fc84a3;">- RM <?= number_format($discount, 2) ?></span>
                </div>
            <?php endif; ?>

            <div class="receipt-grand-total">
                <span>Total Paid</span>
                <span>RM <?= $amountPaid ?></span>
            </div>
        </div>

        <div class="btn-group">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fas fa-print"></i> Print Receipt
            </button>

            <a href="/app/views/home.php" class="btn btn-primary">
                Back to Home
            </a>
        </div>

    </div>
</body>
</html>