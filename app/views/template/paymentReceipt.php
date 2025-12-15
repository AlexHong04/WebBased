<?php 
require_once __DIR__ . '/../../helpers/html.php'; 
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Receipt</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Container */
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f4f4;
            padding-bottom: 40px;
        }
        
        .main-table {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #e0e0e0;
            font-family: sans-serif;
        }

        /* Header (Pink Section) */
        .header-cell {
            background-color: #fc84a3; /* Primary Pink */
            padding: 30px 20px;
            text-align: center;
            color: #ffffff;
        }
        
        .brand-logo {
            font-family: 'Brush Script MT', 'Comic Sans MS', cursive;
            font-size: 32px;
            margin: 0 0 10px 0;
            font-style: italic;
            line-height: 1.2;
        }
        
        .header-title {
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }
        
        .header-subtitle {
            font-size: 14px;
            margin: 0;
            opacity: 0.9;
        }

        /* Content Body */
        .content-cell {
            padding: 30px;
            background-color: #ffffff;
        }

        /* Greeting */
        .greeting {
            font-size: 14px;
            margin-bottom: 20px;
            color: #333;
        }

        /* Info Boxes (Gray Backgrounds) */
        .info-box {
            background-color: #f9f9f9;
            border: 1px solid #eeeeee;
            border-radius: 4px;
            padding: 15px;
            font-size: 13px;
            line-height: 1.5;
            height: 100%; /* Ensure equal height visually if possible */
        }

        .info-label {
            font-size: 10px;
            color: #999999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .info-value {
            color: #333333;
            margin: 0;
        }

        .status-paid {
            color: #28a745; /* Green */
            font-weight: bold;
            font-size: 12px;
            margin-top: 4px;
            display: block;
        }

        /* Order Details Table */
        .details-heading {
            font-size: 13px;
            font-weight: bold;
            color: #333;
            margin-top: 25px;
            margin-bottom: 5px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .items-table th {
            text-align: left;
            padding-bottom: 8px;
            color: #777;
            font-weight: normal;
            border-bottom: 2px solid #fc84a3; /* Pink Border */
        }

        .items-table td {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }

        .item-name {
            font-weight: bold;
            color: #333;
            display: block;
        }

        .item-variant {
            font-size: 12px;
            color: #888;
            margin-top: 2px;
            display: block;
        }

        /* Totals Section */
        .totals-table {
            width: 100%;
            margin-top: 15px;
            font-size: 13px;
        }

        .totals-table td {
            padding: 3px 0;
            text-align: right;
        }

        .total-label {
            color: #666;
            padding-right: 15px;
        }

        .total-value {
            color: #333;
            font-weight: 500;
            width: 100px;
        }

        .discount-text {
            color: #fc84a3;
        }

        .grand-total-row td {
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .grand-total-value {
            font-size: 18px;
            font-weight: bold;
            color: #fc84a3; /* Pink Total */
        }

        /* Footer */
        .footer-cell {
            background-color: #f8f9fa;
            text-align: center;
            padding: 20px;
            font-size: 11px;
            color: #999;
            border-top: 1px solid #eee;
        }
        
        .footer-link {
            color: #007bff;
            text-decoration: none;
        }
    </style>
</head>
<body>

    <div class="wrapper">
        <br>
        <table class="main-table">
            
            <tr>
                <td class="header-cell">
                    <div class="brand-logo">Lovine</div>
                    <div class="header-title">Payment Receipt</div>
                    <div class="header-subtitle">Thank you for your order!</div>
                </td>
            </tr>

            <tr>
                <td class="content-cell">
                    
                    <div class="greeting">
                        Hi <strong><?= encode($order['recipient_name'] ?? 'Customer') ?></strong>,
                        <br><br>
                        We've received your payment. Your order is now being processed.
                    </div>

                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td width="48%" valign="top">
                                <div class="info-box">
                                    <div class="info-label">ORDER INFO</div>
                                    <div class="info-value">
                                        <strong>#<?= encode($order['order_id']) ?></strong><br>
                                        <?= encode($formattedDate) ?><br>
                                        <span class="status-paid">Paid via <?= encode($order['payment_method']) ?></span>
                                    </div>
                                </div>
                            </td>
                            <td width="4%"></td>
                            <td width="48%" valign="top">
                                <div class="info-box">
                                    <div class="info-label">SHIPPING ADDRESS</div>
                                    <div class="info-value">
                                        <?= encode($order['recipient_name']) ?><br>
                                        <?= encode($order['recipient_phone']) ?><br>
                                        <?= encode($order['street_line']) ?><br>
                                        <?= encode($order['postcode'] . " " . $order['city']) ?>, <br>
                                        <?= encode($order['state']) ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="details-heading">Order Details</div>
                    <table class="items-table">
                        <thead>
                            <tr>
                                <th width="60%">Item</th>
                                <th width="15%" style="text-align:center;">Qty</th>
                                <th width="25%" style="text-align:right;">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): 
                                $itemTotal = $item['price'] * $item['order_qty'];
                            ?>
                            <tr>
                                <td>
                                    <span class="item-name"><?= encode($item['product_name']) ?></span>
                                    <span class="item-variant"><?= encode($item['variant_name']) ?></span>
                                </td>
                                <td style="text-align:center;">x<?= encode($item['order_qty']) ?></td>
                                <td style="text-align:right;">RM <?= number_format($item['price'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <table class="totals-table">
                        <tr>
                            <td class="total-label">Subtotal:</td>
                            <td class="total-value">RM <?= number_format($subtotal, 2) ?></td>
                        </tr>
                        <tr>
                            <td class="total-label">Shipping Fee:</td>
                            <td class="total-value">RM <?= number_format($shippingFee, 2) ?></td>
                        </tr>
                        <tr>
                            <td class="total-label">Tax (6%):</td>
                            <td class="total-value">RM <?= number_format($taxFee, 2) ?></td>
                        </tr>
                        
                        <?php if ($discountAmount > 0): ?>
                        <tr>
                            <td class="total-label discount-text">Points Redeemed (<?= encode($order['reward']) ?>):</td>
                            <td class="total-value discount-text">- RM <?= number_format($discountAmount, 2) ?></td>
                        </tr>
                        <?php endif; ?>

                        <tr class="grand-total-row">
                            <td class="total-label" style="padding-top:10px; font-weight:bold; color:#fc84a3;">Total Paid:</td>
                            <td class="total-value grand-total-value">RM <?= number_format($totalPaid, 2) ?></td>
                        </tr>
                    </table>

                </td>
            </tr>

            <tr>
                <td class="footer-cell">
                    Need help? Contact us at <a href="mailto:support@lovine.com" class="footer-link">support@lovine.com</a><br>
                    &copy; <?= date('Y') ?> Lovine. All rights reserved.
                </td>
            </tr>
        </table>
        <br>
    </div>

</body>
</html>