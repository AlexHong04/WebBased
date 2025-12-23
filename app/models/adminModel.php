<?php
require_once __DIR__ . '/../config/database.php'; // Assuming you have a Database wrapper

class adminModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    // 1. Get Admin by Email (For Login)
    // Matches column: email
    public function getAdminByEmail($email)
    {
        $this->db->query("SELECT * FROM admin WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->single();
    }

    // 2. Get Admin by ID (For Profile)
    // Matches column: admin_id
    public function getAdminById($adminId)
    {
        $this->db->query("SELECT admin_id,email,firstName,lastName,phone,address,position FROM admin WHERE admin_id = :id");
        $this->db->bind(':id', $adminId);
        return $this->db->result();
    }

    // 3. Create New Admin (Registration)
    // Uses all columns from the image
    public function createAdmin($data)
    {
        // Generate new Custom ID (e.g., AD001)
        $newId = $this->generateAdminId();

        $this->db->query("INSERT INTO admin (admin_id, email, firstName, lastName, password, phone, address, position) 
                          VALUES (:id, :email, :fname, :lname, :pass, :phone, :addr, :pos)");

        $this->db->bind(':id', $newId);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':fname', $data['firstName']);
        $this->db->bind(':lname', $data['lastName']);
        $this->db->bind(':pass', $data['password']); // Assumed already hashed in controller
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':addr', $data['address']); // Nullable in DB
        $this->db->bind(':pos', $data['position']);

        if ($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }

    // 4. Update Admin Profile
    public function updateAdmin($data)
    {
        $this->db->query("UPDATE admin SET 
                            firstName = :fname, 
                            lastName = :lname, 
                            phone = :phone, 
                            address = :addr, 
                            email = :email
                          WHERE admin_id = :id");

        $this->db->bind(':fname', $data['firstName']);
        $this->db->bind(':lname', $data['lastName']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':addr', $data['address']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':id', $data['admin_id']);

        return $this->db->execute();
    }

    // 5. Helper: Generate Custom Admin ID (AD001, AD002, etc.)
    // Because admin_id is varchar(6)
    private function generateAdminId()
    {
        $this->db->query("SELECT admin_id FROM admin ORDER BY admin_id DESC LIMIT 1");
        $row = $this->db->single();

        if ($row) {
            // 修复错误: 判断 $row 是字符串、对象还是数组
            if (is_string($row)) {
                $lastId = $row; // 如果直接返回了字符串 (如 "AD005")
            } elseif (is_object($row)) {
                $lastId = $row->admin_id; // 如果返回了对象
            } elseif (is_array($row)) {
                $lastId = $row['admin_id']; // 如果返回了数组
            } else {
                $lastId = 'AD000'; // 默认回退
            }

            // Extract number from last ID (e.g., AD005 -> 5)
            $num = (int)substr($lastId, 2);
            $newNum = $num + 1;
        } else {
            $newNum = 1;
        }

        // Pad with zeros (e.g., AD006)
        return 'AD' . str_pad($newNum, 3, '0', STR_PAD_LEFT);
    }

    // 6. Check if Email Exists (Validation)
    public function isEmailExists($email)
    {
        $this->db->query("SELECT email FROM admin WHERE email = :email");
        $this->db->bind(':email', $email);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    // 7. Check if Phone Exists (Validation)
    public function isPhoneExists($phone)
    {
        $this->db->query("SELECT phone FROM admin WHERE phone = :phone");
        $this->db->bind(':phone', $phone);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    // 8. Get All Admins (For List View)
    public function getAllAdmins()
    {
        $this->db->query("SELECT * FROM admin ORDER BY created_at DESC");
        return $this->db->resultAll();
    }

    public function updateStaffFull($data)
    {
        $sql = "UPDATE admin SET 
                firstName = :fname, 
                lastName = :lname, 
                phone = :phone, 
                address = :addr, 
                position = :pos,
                email = :email";

        if (!empty($data['password'])) {
            $sql .= ", password = :pass";
        }

        $sql .= " WHERE admin_id = :id";

        $this->db->query($sql);

        $this->db->bind(':fname', $data['firstName']);
        $this->db->bind(':lname', $data['lastName']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':addr', $data['address']);
        $this->db->bind(':pos', $data['position']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':id', $data['admin_id']);

        if (!empty($data['password'])) {
            $this->db->bind(':pass', $data['password']);
        }

        return $this->db->execute();
    }

    // [NEW] Delete Admin
    public function deleteAdmin($id)
    {
        $this->db->query("DELETE FROM admin WHERE admin_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    // [NEW] Check email exists excluding specific ID (for edit validation)
    public function isEmailExistsForOthers($email, $excludeId)
    {
        $this->db->query("SELECT email FROM admin WHERE email = :email AND admin_id != :id");
        $this->db->bind(':email', $email);
        $this->db->bind(':id', $excludeId);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }
}
