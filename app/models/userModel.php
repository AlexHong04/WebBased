<?php
require_once __DIR__ . '/../config/database.php';
class userModel
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function getCustomerDetails($customerId)
    {
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

    // get all user
    public function getAllUsers()
    {
        $this->db->query("SELECT * FROM customer");
        return $this->db->resultAll();
    }

    // get user by id
    public function getUserById($id)
    {
        $this->db->query("SELECT cu.firstName,cu.lastName,cu.email,cu.gender,cu.phone, ad.street_line,ad.city,ad.state, ad.postcode FROM customer cu LEFT JOIN address ad ON cu.customer_id = ad.customer_id WHERE cu.customer_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->result();
    }

    // create new user
    public function createUser($firstName, $lastName, $email, $gender, $password)
    {
        $customerId = $this->db->generateId('customer', 'customer_id', 'CU');

        $this->db->query("INSERT INTO customer (customer_id,firstName,lastName,email,gender,password,created_at,isActive) VALUES (:customer_id, :firstName, :lastName, :email, :gender, :password, NOW(), 0)");
        $this->db->bind(':customer_id', $customerId);
        $this->db->bind(':firstName', $firstName);
        $this->db->bind(':lastName', $lastName);
        $this->db->bind(':email', $email);
        $this->db->bind(':gender', $gender);
        $this->db->bind(':password', $password);
        return $this->db->execute();
    }

    public function createStaff($firstName, $lastName, $email, $position, $password, $phone)
    {
        $adminId = $this->db->generateId('staff', 'admin_id', 'AD');
        $this->db->query("INSERT INTO admin (admin_id,firstName,lastName,email,position,password, phone ,created_at) VALUES (:admin_id, :firstName, :lastName, :email, :position, :password, :phone, NOW())");
        $this->db->bind(':admin_id', $adminId);
        $this->db->bind(':firstName', $firstName);
        $this->db->bind(':lastName', $lastName);
        $this->db->bind(':email', $email);
        $this->db->bind(':phone', $phone);
        $this->db->bind(':position', $position);
        $this->db->bind(':password', $password);
        return $this->db->execute();
    }

    public function getUser($email)
    {
        $this->db->query("SELECT customer_id,firstName,lastName,email,password,isActive,isBlocked FROM customer WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->result();
    }

    public function getStaff($email)
    {
        $this->db->query("SELECT admin_id,firstName,lastName,email,password,position FROM admin WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->result();
    }

    public function updateUser($firstName, $lastName, $phone, $email, $id, $street_line, $city, $state, $postcode)
    {
        $this->db->query("UPDATE customer SET firstName=:firstName, lastName=:lastName,phone=:phone,email=:email,updated_at=NOW() WHERE customer_id=:id");
        $this->db->bind(':firstName', $firstName);
        $this->db->bind(':lastName', $lastName);
        $this->db->bind(':phone', $phone);
        $this->db->bind(':email', $email);
        $this->db->bind(':id', $id);
        if (!$this->db->execute()) {
            return false;
        }

        // update / insert address table
        if (!empty($street_line) || !empty($city) || !empty($state) || !empty($postcode)) {
            $this->db->query("SELECT customer_id FROM address WHERE customer_id=:id");
            $this->db->bind(':id', $id);
            $existingAddress = $this->db->result();

            if ($existingAddress) {
                $this->db->query("UPDATE address SET recipient_name=:recipient_name,recipient_phone=:recipient_phone, street_line=:street_line, city=:city, state=:state, postcode=:postcode, is_default=:is_default WHERE customer_id=:cid");
            } else {
                $address_id = $this->db->generateId('address', 'address_id', 'AD');
                $this->db->query("INSERT INTO address (address_id,customer_id,recipient_name,recipient_phone, street_line, city, state, postcode, is_default) VALUES (:aid,:cid ,:recipient_name, :recipient_phone, :street_line, :city, :state, :postcode, :is_default)");
                $this->db->bind(':aid', $address_id);
            }
            $this->db->bind(':recipient_name', $firstName . ' ' . $lastName);
            $this->db->bind(':recipient_phone', $phone);
            $this->db->bind(':street_line', $street_line);
            $this->db->bind(':city', $city);
            $this->db->bind(':state', $state);
            $this->db->bind(':postcode', $postcode);
            $this->db->bind(':cid', $id);
            $this->db->bind(':is_default', 1);
            return $this->db->execute();
        }
        return true;
    }

    public function updateUserIsActive($email)
    {
        $this->db->query("UPDATE customer SET isActive = 1 WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->execute();
    }

    public function deleteUser($id)
    {
        $this->db->query("DELETE FROM customer WHERE customer_id=:id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function changePassword($id, $newPassword)
    {
        $this->db->query("UPDATE customer SET password = :password WHERE customer_id = :id");
        $this->db->bind(':password', $newPassword);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getUserCurrentPassword($id)
    {
        $this->db->query("SELECT password FROM customer WHERE customer_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->result();
    }

    public function isEmailExists($email)
    {
        $this->db->query("SELECT email FROM customer WHERE email = :email");
        $this->db->bind(':email', $email);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function isStaffEmailExists($email)
    {
        $this->db->query("SELECT email FROM admin WHERE email = :email");
        $this->db->bind(':email', $email);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function updateUserIsBlocked($email, $isBlocked)
    {
        $this->db->query("UPDATE customer SET isBlocked = :isBlocked WHERE email = :email");
        $this->db->bind(':isBlocked', $isBlocked);
        $this->db->bind(':email', $email);
        return $this->db->execute();
    }


    public function updatePasswordByEmail($email, $newHashedPassword)
    {

        $this->db->query("UPDATE customer SET password = :pass WHERE email = :email");
        $this->db->bind(':pass', $newHashedPassword);
        $this->db->bind(':email', $email);

        if ($this->db->execute()) {
            if ($this->db->rowCount() > 0) return true;
        }

        $this->db->query("UPDATE admin SET password = :pass WHERE email = :email");
        $this->db->bind(':pass', $newHashedPassword);
        $this->db->bind(':email', $email);

        return $this->db->execute();
    }

    // // home
    // public function getTopSalesData()
    // {
    //     $this->db->query("SELECT 
    // p.product_id,
    // p.product_name,
    // p.sale_price,
    // p.rate,
    // SUM(oi.order_qty) AS total_units_sold,
    // SUM(oi.order_qty * oi.price) AS total_revenue
    // FROM Product p JOIN Product_Variant pv ON p.product_id = pv.product_id
    // JOIN order_Items oi ON pv.product_variant_id = oi.product_variant_id
    // JOIN `ordertable` o ON oi.order_id = o.order_id
    // WHERE o.order_status = 'Completed'
    // GROUP BY p.product_id, p.product_name, p.sale_price, p.rate
    // ORDER BY total_units_sold DESC
    // LIMIT 3; ");
    //     return $this->db->resultAll();
    // }
    // zq
    public function getAllMembers()
    {
        $this->db->query("SELECT * FROM customer");
        return $this->db->resultAll();
    }

    public function countMembers()
    {
        $this->db->query("SELECT COUNT(*) AS total FROM customer");
        $result = $this->db->result();
        return $result["total"];
    }

    public function getMembers($offset, $limit)
    {
        $this->db->query("SELECT * FROM customer LIMIT $offset, $limit");
        return $this->db->resultAll();
    }

    public function getSpecificMember($custID)
    {
        $this->db->query("SELECT * FROM customer WHERE customer_id = :custID");
        $this->db->bind(':custID', $custID);
        return $this->db->result();
    }

    public function getAddress($custID)
    {
        $this->db->query("SELECT * FROM address WHERE customer_id = :custID");
        $this->db->bind(':custID', $custID);
        return $this->db->resultAll();
    }

    public function updateStatus($customerIds)
    {
        $idList = implode(",", array_map(function ($id) {
            return "'" . $id . "'";
        }, $customerIds));

        $sql = "UPDATE customer SET isBlocked = CASE 
                WHEN isBlocked = 1 THEN 0
                ELSE 1
                END,
                updated_at = NOW()
                WHERE customer_id IN ($idList)";

        $this->db->query($sql);

        return $this->db->execute();
    }

    public function updateMember($data)
    {
        $this->db->query("UPDATE customer SET 
                    firstName = :firstName,
                    lastName = :lastName,
                    phone = :phone,
                    email = :email,
                    updated_at = NOW(),
                    isBlocked = :isBlocked,
                    rewardPoint = :rewardPoint,
                    img_url = :img_url
                WHERE customer_id = :customer_id");
        $this->db->bind(':firstName', $data['firstName']);
        $this->db->bind(':lastName', $data['lastName']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':isBlocked', $data['isBlocked']);
        $this->db->bind(':rewardPoint', $data['rewardPoint']);
        $this->db->bind(':img_url', $data['img_url']);
        $this->db->bind(':customer_id', $data['customer_id']);

        return $this->db->execute();
    }

    public function updateAddress($address_id, $addressData)
    {
        $this->db->query("UPDATE address SET
                        street_line = :street_line,
                        city = :city,
                        state = :state,
                        postcode = :postcode,
                        recipient_phone = :recipient_phone
                    WHERE address_id = :address_id");

        $this->db->bind(':street_line', $addressData['street_line']);
        $this->db->bind(':city', $addressData['city']);
        $this->db->bind(':state', $addressData['state']);
        $this->db->bind(':postcode', $addressData['postcode']);
        $this->db->bind(':recipient_phone', $addressData['recipient_phone']);
        $this->db->bind(':address_id', $address_id);

        return $this->db->execute();
    }

    // update reward point (when order complete)
    public function updatePoint($custId, $point)
    {
        $this->db->query("
    UPDATE customer
    SET rewardPoint = rewardPoint + :rewardPoint
    WHERE customer_id = :customer_id
  ");

        $this->db->bind(':rewardPoint', $point);
        $this->db->bind(':customer_id', $custId);

        return $this->db->execute();
    }
}
