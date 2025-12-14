<?php
require_once __DIR__ . '/../config/database.php';

class CustomerModel {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getCustomerDetails($customerId) {
        $sql = "SELECT customer_id, 
                       CONCAT(firstname, ' ', lastname) AS username, 
                       email, 
                       phone,
                       rewardPoint
                FROM customer 
                WHERE customer_id = ?";
                
        $this->db->query($sql);
        $this->db->bind(1, $customerId);
        
        return $this->db->result();
    }
}
?>