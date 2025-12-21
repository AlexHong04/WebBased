<?php
require_once __DIR__ . '/../../app/config/database.php';

class PaymentModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    // Get Payment Details by ID
    public function getPaymentById($paymentId) {
        $sql = "SELECT * FROM payment WHERE payment_id = ? LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(1, $paymentId);
        return $this->db->result();
    }

    // Get Payment Details by Order ID
    public function getPaymentByOrderId($orderId) {
        $sql = "SELECT * FROM payment WHERE order_id = ? LIMIT 1";
        $this->db->query($sql);
        $this->db->bind(1, $orderId);
        return $this->db->result();
    }

    // Update Payment Status to 'Paid'
    public function updatePaymentStatus($paymentId, $status = 'Paid') {
        $sql = "UPDATE payment SET payment_status = ?, updated_datetime = NOW() WHERE payment_id = ?";
        $this->db->query($sql);
        $this->db->bind(1, $status);
        $this->db->bind(2, $paymentId);
        return $this->db->execute();
    }
}
?>