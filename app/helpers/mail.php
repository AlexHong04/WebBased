<?php
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/html.php';
require_once __DIR__ . '/../models/OrderModel.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function _sendEmail($toEmail, $toName, $subject, $body, $altBody = '') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';                     
        $mail->SMTPAuth   = true;                             
        $mail->Username   = 'unknowsuser050@gmail.com';          
        $mail->Password   = 'dgsj nahj zsld nekl';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587;

        $mail->setFrom('no-reply@lovine.com', 'Lovine'); 
        $mail->addAddress($toEmail, $toName);           

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = !empty($altBody) ? $altBody : strip_tags($body);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: {$mail->ErrorInfo}");
        return false;
    }
}

function sendOrderReceipt($orderId, $customerEmail, $customerName) {
    $htmlContent = prepareReceiptContent($orderId);
    if (!$htmlContent) return false;

    $subject = 'Payment Receipt for Order #' . $orderId;
    $plainText = "Your order #{$orderId} has been confirmed. Thank you for shopping with Lovine.";
    return _sendEmail($customerEmail, $customerName, $subject, $htmlContent, $plainText);
}

function prepareReceiptContent($orderId) {
    $orderModel = new OrderModel();

    //get data from ordermodel
    $order = $orderModel->getOrderWithDetails($orderId);
    if (!$order) return false;

    $items = $orderModel->getOrderItems($orderId) ?: [];

    $shippingFee = 5.00;
    $taxFee = $order['tax_fee'];
    $totalPaid = $order['total_amount'];
    $points = $order['reward'] ?? 0;
    $discountAmount = $points / 100;
    $subtotal = 0;
    foreach ($items as $itm) {
        $subtotal += $itm['price'] * $itm['order_qty'];
    }

    date_default_timezone_set('Asia/Kuala_Lumpur');
    $rawDate = !empty($order['payment_updated_at']) ? $order['payment_updated_at'] : $order['payment_created_at'];
    $formattedDate = date('d M Y, h:i A', strtotime($rawDate));

    $viewData = [
        'order' => $order,
        'items' => $items,
        'shippingFee' => $shippingFee,
        'taxFee' => $taxFee,
        'totalPaid' => $totalPaid,
        'discountAmount' => $discountAmount,
        'subtotal' => $subtotal,
        'formattedDate' => $formattedDate 
    ];
    extract($viewData);

    //clean output buffer
    ob_start();
    //load view
    include __DIR__ . '/../views/template/paymentReceipt.php';
    //get the content and clean
    return ob_get_clean();
}
?>