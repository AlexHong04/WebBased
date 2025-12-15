<?php
require_once __DIR__ . '/../config/database.php';

class OrderModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function createOrder($customerId, $addressId, $items, $totalAmount, $shippingFee, $taxFee, $totalOrderQty, $pointsRedeemed, $paymentMethod) {
        
        $this->db->query("START TRANSACTION"); 
        $this->db->execute();

        try {
            $orderId = $this->db->generateId('`order`', 'order_id', 'O'); 

            $sqlOrder = "INSERT INTO `order` (order_id, customer_id, address_id, total_amount, tax_fee, total_order_qty, reward) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";
            
            $this->db->query($sqlOrder);
            $this->db->bind(1, $orderId);
            $this->db->bind(2, $customerId);
            $this->db->bind(3, $addressId);
            $this->db->bind(4, $totalAmount);
            $this->db->bind(5, $taxFee);
            $this->db->bind(6, $totalOrderQty);
            $this->db->bind(7, $pointsRedeemed);
            $this->db->execute();

            $orderStatusId = $this->db->generateId('orderStatus', 'order_status_id', 'OS'); 

            $sqlStatus = "INSERT INTO orderStatus (order_status_id, order_status, created_datetime, order_id) 
                          VALUES (?, 'Pending', NOW(), ?)";

            $this->db->query($sqlStatus);
            $this->db->bind(1, $orderStatusId);
            $this->db->bind(2, $orderId);
            $this->db->execute();

            foreach ($items as $item) {
                $sqlItem = "INSERT INTO order_items (order_id, product_variant_id, price, order_qty) 
                            VALUES (?, ?, ?, ?)";
                $this->db->query($sqlItem);
                $this->db->bind(1, $orderId);
                $this->db->bind(2, $item['variant_id']);
                $this->db->bind(3, $item['unit_price']);
                $this->db->bind(4, $item['quantity']);
                $this->db->execute();

                $sqlStock = "UPDATE product_variant SET stock_qty = stock_qty - ? WHERE product_variant_id = ?";
                $this->db->query($sqlStock);
                $this->db->bind(1, $item['quantity']);
                $this->db->bind(2, $item['variant_id']);
                $this->db->execute();
            }

            $paymentId = $this->db->generateId('payment', 'payment_id', 'PM');

            $sqlPayment = "INSERT INTO payment (payment_id, order_id, amount, payment_method, payment_status, created_datetime) 
                           VALUES (?, ?, ?, ?, 'Unpaid', NOW())";
            
            $this->db->query($sqlPayment);
            $this->db->bind(1, $paymentId);
            $this->db->bind(2, $orderId);
            $this->db->bind(3, $totalAmount);
            $this->db->bind(4, $paymentMethod);
            $this->db->execute();

            $this->db->query("SELECT cart_id FROM cart WHERE customer_id = ?");
            $this->db->bind(1, $customerId);
            $cartRow = $this->db->result();
            
            if ($cartRow) {
                $cartId = $cartRow['cart_id'];
                foreach ($items as $item) {
                    $this->db->query("DELETE FROM cart_items WHERE cart_id = ? AND product_variant_id = ? AND cart_status = 0");
                    $this->db->bind(1, $cartId);
                    $this->db->bind(2, $item['variant_id']);
                    $this->db->execute();
                }
            }

            if ($pointsRedeemed > 0) {
                $this->db->query("SELECT rewardPoint FROM customer WHERE customer_id = ?");
                $this->db->bind(1, $customerId);
                $result = $this->db->result(); 
                $userPoints = $result['rewardPoint'] ?? 0;
                
                if ($userPoints < $pointsRedeemed) {
                    throw new Exception("Insufficient points.");
                }

                $this->db->query("UPDATE customer SET rewardPoint = rewardPoint - ? WHERE customer_id = ?");
                $this->db->bind(1, $pointsRedeemed);
                $this->db->bind(2, $customerId);
                $this->db->execute();
            }

            $this->db->query("COMMIT");
            $this->db->execute();

            return $paymentId;

        } catch (Exception $e) {
            $this->db->query("ROLLBACK");
            $this->db->execute();
            return false;
        }
    }

    public function getSelectedCartItems($customerId, $selectedVariantIds) {
        if (empty($selectedVariantIds)) {
            return [];
        }

        $results = [];
        foreach ($selectedVariantIds as $vid) {
            $sql = "SELECT 
                        ci.quantity,
                        pv.product_variant_id, 
                        v.variant_name,
                        p.product_name, 
                        p.sale_price, 
                        pv.img_url as variant_img, 
                        p.img_url as main_img
                    FROM cart c
                    JOIN cart_items ci ON c.cart_id = ci.cart_id
                    JOIN product_variant pv ON ci.product_variant_id = pv.product_variant_id
                    JOIN product p ON pv.product_id = p.product_id
                    LEFT JOIN variant v ON pv.variant_id = v.variant_id
                    WHERE c.customer_id = ?
                    AND ci.product_variant_id = ?
                    AND ci.cart_status = 0";
            
            $this->db->query($sql);
            $this->db->bind(1, $customerId);
            $this->db->bind(2, $vid);
            
            $row = $this->db->result();
            if ($row) {
                $results[] = $row;
            }
        }
        return $results;
    }

    public function getOrderWithDetails($orderId) {

        $sql = "SELECT o.*, 
                       a.recipient_name , a.recipient_phone, a.street_line, a.city, a.state, a.postcode,
                       p.payment_method, 
                       p.payment_status,
                       p.payment_id,
                       p.updated_datetime as payment_updated_at,
                       p.created_datetime as payment_created_at
                FROM `order` o
                JOIN address a ON o.address_id = a.address_id
                JOIN payment p ON o.order_id = p.order_id
                WHERE o.order_id = ? LIMIT 1";
        
        $this->db->query($sql);
        $this->db->bind(1, $orderId);
        return $this->db->result();
    }

    public function getOrderItems($orderId) {
        $sql = "SELECT oi.*, p.product_name, v.variant_name 
                FROM order_items oi
                JOIN product_variant pv ON oi.product_variant_id = pv.product_variant_id
                JOIN variant v ON pv.variant_id = v.variant_id
                JOIN product p ON pv.product_id = p.product_id
                WHERE oi.order_id = ?";
        
        $this->db->query($sql);
        $this->db->bind(1, $orderId);
        return $this->db->resultAll();
    }
    // Get Order by ID
    public function getOrderById($orderId) {
        $sql = "SELECT * FROM `order` WHERE order_id = ? LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(1, $orderId);
        return $this->db->result();
    }

    // Update Order Status
    public function updateOrderStatus($orderId, $status) {
        $statusId = $this->db->generateId('orderStatus', 'order_status_id', 'OS');
        $sql = "INSERT INTO orderStatus (order_status_id, order_status, created_datetime, order_id) 
                VALUES (?, ?, NOW(), ?)";
        $this->db->query($sql);
        $this->db->bind(1, $statusId);
        $this->db->bind(2, $status);
        $this->db->bind(3, $orderId);
        return $this->db->execute();
    }

    // Get Customer Info needed for Email Receipt
    public function getCustomerInfoByOrder($orderId) {
        $sql = "SELECT c.email, c.firstname, c.lastname 
                FROM `order` o 
                JOIN customer c ON o.customer_id = c.customer_id 
                WHERE o.order_id = ?";
        $this->db->query($sql);
        $this->db->bind(1, $orderId);
        return $this->db->result();
    }
}
?>