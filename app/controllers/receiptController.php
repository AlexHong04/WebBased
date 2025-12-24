<?php
require_once __DIR__ . '../../helpers/request.php';
require_once __DIR__ . '../../helpers/html.php';
require_once __DIR__ . '/../models/orderModel.php';

class receiptController
{
    private $orderModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->orderModel = new orderModel();
    }

    public function index($returnOnly = false)
    {
        if (!isset($_SESSION['customerId'])) {
            redirect('/app/views/security/signIn.php');
            return;
        }

        $orderId = get('order_id');

        if (!$orderId) {
            redirect('/app/views/home.php');
            return;
        }

        $order = $this->orderModel->getOrderWithDetails($orderId);

        if (!$order) {
            die("Order not found.");
        }

        if ($order['customer_id'] !== $_SESSION['customerId']) {            
            temp('flash_error', 'Unauthorized access to this receipt.');
            redirect('/app/views/home.php');
            return;
        }

        if ($order['payment_status'] !== 'Paid') {
            temp('flash_warning', 'Payment not completed. Please make payment to view receipt.');
            redirect('/app/views/shoppingCart/payment.php?payment_id=' . $order['payment_id']);
            return; 
        }

        $orderItems = $this->orderModel->getOrderItems($orderId);

        date_default_timezone_set('Asia/Kuala_Lumpur');

        $rawDate = !empty($order['payment_updated_at'])
            ? $order['payment_updated_at']
            : $order['payment_created_at'];
        $orderDate = date('d M Y, h:i A', strtotime($rawDate));
        $amountPaid = number_format($order['total_amount'], 2);
        $subTotal = 0;

        foreach ($orderItems as $item) {
            $subTotal += $item['price'] * $item['order_qty'];
        }

        $data = [
            'order' => $order,
            'orderItems' => $orderItems,
            'orderDate' => $orderDate,
            'amountPaid' => $amountPaid,
            'subTotal' => $subTotal,
            'deliveryFee' => 5.00,
        ];

        if ($returnOnly) {
            return $data;
        }

        extract($data);
        require __DIR__ . '/../views/shoppingCart/receipt.php';
    }
}
