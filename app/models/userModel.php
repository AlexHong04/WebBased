<?php
include __DIR__ . '/../config/database.php';
class userModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }


    public function getUser($email, $password)
    {
        $this->db->query("SELECT customer_id,email,password FROM customer WHERE email = :email");
        $this->db->bind(':email', $email);
        // $this->db->bind(':password', $password);
        return $this->db->result();
    }
}
