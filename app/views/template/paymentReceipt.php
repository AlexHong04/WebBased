<?php
/**
 * @var array $calculations
 * @var array $order
 * @var array $items
 */
//
extract($calculations);

?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background-color: #fc84a3;
            padding: 25px;
            text-align: center;
            color: white;
        }

        .content {
            padding: 30px;
            background-color: #ffffff;
        }

        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 14px;
            border: 1px solid #eee;
        }

        .total-row td {
            padding: 5px 0;
            text-align: right;
        }

        .grand-total {
            font-size: 18px;
            font-weight: bold;
            color: #fc84a3;
            border-top: 2px solid #eee;
            padding-top: 10px !important;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>

<body style='margin:0; padding:0; background-color:#f4f4f4;'>
    <br>
    <div class='container'>
        <div class='header'>
            <h1 style='margin:0; font-size: 24px;'>Payment Receipt</h1>
            <p style='margin:5px 0 0 0; opacity: 0.9;'>Thank you for your order!</p>
        </div>

        <div class='content'>
            <p>Hi <strong><?= htmlspecialchars($order['recipient_name']) ?></strong>,</p>
            <p>We've received your payment. Your order is now being processed.</p>

            <table width='100%' cellpadding='0' cellspacing='0' style='margin-bottom: 20px;'>
                <tr>
                    <td width='50%' valign='top'>
                        <div class='info-box' style='margin-right: 5px;'>
                            <div style='color:#999; font-size:12px; text-transform:uppercase;'>Order Info</div>
                            <strong>#<?= $order['order_id'] ?></strong><br>
                            <?= date('d M Y, h:i A', strtotime($order['updated_datetime'] ?? $order['created_datetime'])) ?><br>
                            <span style='color:#28a745;'>Paid via <?= $order['payment_method'] ?></span>
                        </div>
                    </td>
                    <td width='50%' valign='top'>
                        <div class='info-box' style='margin-left: 5px;'>
                            <div style='color:#999; font-size:12px; text-transform:uppercase;'>Shipping Address</div>
                            <?= htmlspecialchars($order['recipient_name']) ?><br>
                            <?= htmlspecialchars($order['recipient_phone']) ?><br>
                            <?= htmlspecialchars($order['street_line']) ?><br>
                            <?= htmlspecialchars($order['postcode'] . " " . $order['city']) ?>, <?= htmlspecialchars($order['state']) ?>
                        </div>
                    </td>
                </tr>
            </table>

            <div style='margin-bottom: 10px; font-weight: bold; border-bottom: 2px solid #fc84a3; padding-bottom: 5px;'>Order Details</div>
            <table width='100%' cellpadding='0' cellspacing='0' style='font-size: 14px; width: 100%;'>
                <thead>
                    <tr>
                        <th align='left' style='padding-bottom: 10px; color:#777;'>Item</th>
                        <th align='center' style='padding-bottom: 10px; color:#777;'>Qty</th>
                        <th align='right' style='padding-bottom: 10px; color:#777;'>Price</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee;'>
                                <div style='font-weight: 600; color: #333;'><?= htmlspecialchars($item['product_name']) ?></div>
                                <div style='font-size: 12px; color: #888;'><?= htmlspecialchars($item['variant_name']) ?></div>
                            </td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee; text-align: center;'>x<?= $item['order_qty'] ?></td>
                            <td style='padding: 12px 0; border-bottom: 1px solid #eee; text-align: right;'>RM <?= number_format($item['price'] * $item['order_qty'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <table width='100%' cellpadding='0' cellspacing='0' style='font-size: 14px; margin-top: 20px;'>
                <tr class='total-row'>
                    <td colspan='2'>Subtotal:</td>
                    <td width='25%'>RM <?= number_format($subtotal, 2) ?></td>
                </tr>
                <tr class='total-row'>
                    <td colspan='2'>Shipping Fee:</td>
                    <td>RM <?= number_format($shippingFee, 2) ?></td>
                </tr>
                <tr class='total-row'>
                    <td colspan='2'>Tax (6%):</td>
                    <td>RM <?= number_format($taxFee, 2) ?></td>
                </tr>
                <?php if ($discountAmount > 0): ?>
                    <tr>
                        <td colspan='2' style='padding: 5px 0; text-align: right; color: #fc84a3;'>Points Redeemed (<?= $order['reward'] ?>):</td>
                        <td style='padding: 5px 0; text-align: right; color: #fc84a3;'>- RM <?= number_format($discountAmount, 2) ?></td>
                    </tr>
                <?php endif; ?>
                <tr class='total-row'>
                    <td colspan='2' class='grand-total'>Total Paid:</td>
                    <td class='grand-total'>RM <?= number_format($totalPaid, 2) ?></td>
                </tr>
            </table>
        </div>

        <div class='footer'>
            <p style='margin:0;'>Need help? Contact us at support@lovine.com</p>
            <p style='margin:5px 0 0 0;'>&copy; <?= date('Y') ?> Lovine. All rights reserved.</p>
        </div>
    </div>
    <br>
</body>

</html>