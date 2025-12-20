<?php
require_once __DIR__ . '/../../app/helpers/request.php';
require_once __DIR__ . '/../../app/helpers/mail.php';
require_once __DIR__ . '/../models/paymentModel.php';
require_once __DIR__ . '/../models/orderModel.php';
require_once __DIR__ . '/../../stripe-php/init.php';

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
            redirect('/app/views/home.php');
            return;
        }

        $totalAmount = $payment['amount'];
        $paymentMethod = $payment['payment_method'];
        $formattedAmount = number_format($totalAmount, 2);

        // $shippingFee = 5.00;
        // $taxFee = $order['tax_fee'];
        // $pointsRedeemed = $order['reward'] ?? 0;
        // $discountAmount = $pointsRedeemed / 100;

        // $subtotal = $totalAmount + $discountAmount - $taxFee - $shippingFee;

        $clientSecret = null;

        if ($paymentMethod === 'Credit Card') {
            \Stripe\Stripe::setApiKey('sk_test_51Sg74oBZXdC5koAQmjvX3uOmBQ2edvJPxoEi84FRlcczGFNJBcfdkz998tr4ERgH35BF8dd5tPLRsEAnSq6QFZD800ng3234rI');

            try {
                $amountInCents = intval($totalAmount * 100);

                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $amountInCents,
                    'currency' => 'myr',
                    'payment_method_types' => ['card'],
                ]);

                $clientSecret = $paymentIntent->client_secret;
            } catch (\Exception $e) {
                error_log("Stripe Error: " . $e->getMessage());
            }
        }

        // Prepare View Data
        $data = [
            'paymentId' => $paymentId,
            'orderId' => $orderId,
            'totalAmount' => $totalAmount,
            'paymentMethod' => $paymentMethod,
            'formattedAmount' => $formattedAmount,
            'clientSecret' => $clientSecret
        ];

        if ($returnOnly) {
            return $data;
        }

        extract($data);
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
            redirect('/app/views/home.php');
            return;
        }


        $this->paymentModel->updatePaymentStatus($paymentId, 'Paid');
        $this->orderModel->updateOrderStatus($orderId, 'Paid');

        $cust = $this->orderModel->getCustomerInfoByOrder($orderId);

        // Send Email
        if ($cust && !empty($cust['email'])) {
            $fullName = $cust['firstname'] . ' ' . $cust['lastname'];
            if (function_exists('sendOrderReceipt')) {
                sendOrderReceipt($orderId, $cust['email'], $fullName);
            }
        }

        redirect('/app/views/shoppingCart/receipt.php?order_id=' . $orderId);
    }
}
