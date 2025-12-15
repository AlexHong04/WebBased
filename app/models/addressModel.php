<?php
require_once __DIR__ . '/../../app/config/database.php';

class addressModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    private function formatAddressRow($row)
    {
        if (!$row) return null;
        $row['address'] = $row['street_line'] . "\n" . $row['postcode'] . " " . $row['city'] . "\n" . $row['state'];
        $row['name'] = $row['recipient_name'];
        $row['phone'] = $row['recipient_phone'];
        $row['id'] = $row['address_id'];
        return $row;
    }

    public function getAddressById($customerId, $id)
    {
        $sql = "SELECT * FROM address 
                WHERE address_id = ? AND customer_id = ? AND is_deleted = 0";

        $this->db->query($sql);
        $this->db->bind(1, $id);
        $this->db->bind(2, $customerId);
        return $this->formatAddressRow($this->db->result());
    }

    public function getCustomerAddresses($customerId)
    {
        $sql = "SELECT address_id, recipient_name, recipient_phone, street_line, city, state, postcode, is_default 
                FROM address 
                WHERE customer_id = ? AND is_deleted = 0
                ORDER BY is_default DESC, address_id DESC";
        $this->db->query($sql);
        $this->db->bind(1, $customerId);
        $results = $this->db->resultAll();
        return array_map([$this, 'formatAddressRow'], $results);
    }

    public function getDefaultAddress($customerId) {
        $sql = "SELECT * FROM address 
                WHERE customer_id = ? AND is_default = 1 AND is_deleted = 0 
                LIMIT 1";
        
        $this->db->query($sql);
        $this->db->bind(1, $customerId);
        $row = $this->db->result();
        
        if ($row) {
            $row = $this->formatAddressRow($row);
            $row['username'] = $row['name'];
        }
        return $row;
    }

    public function saveAddress($customerId, $id, $name, $phone, $street, $city, $state, $postcode)
    {
        if ($id) {
            // Update
            $this->db->query("UPDATE address 
                              SET recipient_name = ?, recipient_phone = ?, street_line = ?, city = ?, state = ?, postcode = ?
                              WHERE address_id = ? AND customer_id = ?");
            $this->db->bind(1, $name);
            $this->db->bind(2, $phone);
            $this->db->bind(3, $street);
            $this->db->bind(4, $city);
            $this->db->bind(5, $state);
            $this->db->bind(6, $postcode);
            $this->db->bind(7, $id);
            $this->db->bind(8, $customerId);
            $newId = $id;
        } else {
            // Insert
            $newId = $this->db->generateId('address', 'address_id', 'AD');
            $isDefault = $this->countAddresses($customerId) == 0 ? 1 : 0;

            $sql = "INSERT INTO address (address_id, customer_id, recipient_name, recipient_phone, street_line, city, state, postcode, is_default) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $this->db->query($sql);
            $this->db->bind(1, $newId);
            $this->db->bind(2, $customerId);
            $this->db->bind(3, $name);
            $this->db->bind(4, $phone);
            $this->db->bind(5, $street);
            $this->db->bind(6, $city);
            $this->db->bind(7, $state);
            $this->db->bind(8, $postcode);
            $this->db->bind(9, $isDefault);
        }

        if ($this->db->execute()) {
            return $newId;
        }
        return false;
    }

    public function deleteAddress($customerId, $id)
    {
        $sql = "UPDATE address SET is_deleted = TRUE WHERE address_id = ? AND customer_id = ?";
        $this->db->query($sql);
        $this->db->bind(1, $id);
        $this->db->bind(2, $customerId);
        return $this->db->execute();
    }

    public function countAddresses($customerId)
    {
        $this->db->query("SELECT COUNT(*) FROM address WHERE customer_id = ? AND is_deleted = 0");
        $this->db->bind(1, $customerId);
        return (int)$this->db->single();
    }
}
