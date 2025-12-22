<?php
require_once __DIR__ . '/../models/OrderModel.php';
require_once __DIR__ . '/../models/productsModel.php';
require_once __DIR__ . '/../models/paymentModel.php';


class cronController {
    private $orderModel;
    private $productModel;
    private $paymentModel;

    public function __construct() {
        $this->orderModel = new OrderModel();
        $this->productModel = new ProductModel();
        $this->paymentModel = new PaymentModel();
    }

    public function runOrderCleanup() {
        $expiredOrders = $this->orderModel->getExpiredUnpaidOrders(4);

        if (empty($expiredOrders)) {
            return; // No expired orders to process
        }

        foreach ($expiredOrders as $order) {
            $orderId = $order['order_id'];
            $paymentId = $order['payment_id'];
            
            //restock the products associated with the order
            $items = $this->orderModel->getOrderItems($orderId);

            if ($items) {
                foreach ($items as $item) {
                    $this->productModel->restoreStock($item['product_variant_id'], $item['order_qty']);
                }
            }

            $this->orderModel->updateOrderStatus($orderId, 'Cancelled');

            $this->paymentModel->updatePaymentStatus($paymentId, 'Cancelled');
            
        }
    }
}
?>