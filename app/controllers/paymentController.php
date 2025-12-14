<?php
require_once __DIR__ . '/../../app/helpers/request.php';
require_once __DIR__ . '/../../app/helpers/mail.php';
require_once __DIR__ . '/../models/paymentModel.php';
require_once __DIR__ . '/../models/orderModel.php';

class PaymentController
{
    private $paymentModel;
    private $orderModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->paymentModel = new PaymentModel();
        $this->orderModel = new OrderModel();
    }

    public function index($returnOnly = false)
    {
        if (!isset($_SESSION['customerId'])) {
            redirect('/app/views/security/signIn.php');
            return;
        }

        $paymentId = get('payment_id');

        if (!$paymentId) {
            redirect('/app/views/shoppingCart/cart.php');
            return;
        }

        $payment = $this->paymentModel->getPaymentById($paymentId);

        if (!$payment) {
            die("Invalid Payment ID");
        }

        if ($payment['payment_status'] === 'Paid') {
            temp('flash_success', 'This order has already been paid.');

            redirect('/app/views/shoppingCart/receipt.php?order_id=' . $payment['order_id']);
            return;
        }
        $orderId = $payment['order_id'];
        $order = $this->orderModel->getOrderById($orderId);
        

        if (!$order) {
            die("Order not found");
        }
        if ($order['customer_id'] !== $_SESSION['customerId']) {
            temp('flash_error', 'Unauthorized access to this payment page.');
            redirect('/app/views/index.php');
            return;
        }

        $totalAmount = $payment['amount'];
        $paymentMethod = $payment['payment_method'];
        $formattedAmount = number_format($totalAmount, 2);

        $shippingFee = 5.00;
        $taxFee = $order['tax_fee'];
        $pointsRedeemed = $order['reward'] ?? 0;
        $discountAmount = $pointsRedeemed / 100;

        $subtotal = $totalAmount + $discountAmount - $taxFee - $shippingFee;

        // Prepare View Data
        $data = [
            'paymentId' => $paymentId,
            'orderId' => $orderId,
            'totalAmount' => $totalAmount,
            'paymentMethod' => $paymentMethod,
            'formattedAmount' => $formattedAmount
        ];

        if ($returnOnly) {
            return $data;
        }

        extract(array: $data);
        require __DIR__ . '/../views/shoppingCart/payment.php';
    }

    public function process()
    {
        if (!isset($_SESSION['customerId'])) {
            redirect('/app/views/security/signIn.php');
            return;
        }

        $paymentId = post('payment_id');

        if (!$paymentId) {
            redirect('/app/views/shoppingCart/cart.php');
            return;
        }

        $payment = $this->paymentModel->getPaymentById($paymentId);
        if (!$payment) die("Payment not found");

        $orderId = $payment['order_id'];
        $order = $this->orderModel->getOrderById($orderId);
        if ($order['customer_id'] !== $_SESSION['customerId']) {
            temp('flash_error', 'Unauthorized action.');
            redirect('/app/views/index.php');
            return;
        }


        $this->paymentModel->updatePaymentStatus($paymentId, 'Paid');
        $this->orderModel->updateOrderStatus($orderId, 'Paid');

        $cust = $this->orderModel->getCustomerInfoByOrder($orderId);

        // Send Email
        if ($cust && !empty($cust['email'])) {
            $fullName = $cust['firstname'] . ' ' . $cust['lastname'];
            sendOrderReceipt($orderId, $cust['email'], $fullName);
        }

        redirect('/app/views/shoppingCart/receipt.php?order_id=' . $orderId);
    }
}
