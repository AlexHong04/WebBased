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
        // Added cu.img_url to the select list
        $this->db->query("SELECT cu.customer_id, cu.firstName, cu.lastName, cu.email, cu.gender, cu.phone, cu.img_url, ad.street_line, ad.city, ad.state, ad.postcode 
                      FROM customer cu 
                      LEFT JOIN address ad ON cu.customer_id = ad.customer_id 
                      WHERE cu.customer_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->result();
    }
    // get admin by id
    public function getAdminById($id)
    {
        $this->db->query("SELECT * FROM admin WHERE admin_id = :id");
        $this->db->bind(':id', $id);
        return $this->db->result();
    }

    // create new user
    public function createUser($firstName, $lastName, $email, $phone, $password)
    {
        $customerId = $this->db->generateId('customer', 'customer_id', 'CU');

        $this->db->query("INSERT INTO customer (customer_id,firstName,lastName,email,phone,password,created_at,isActive) VALUES (:customer_id, :firstName, :lastName, :email, :phone, :password, NOW(), 0)");
        $this->db->bind(':customer_id', $customerId);
        $this->db->bind(':firstName', $firstName);
        $this->db->bind(':lastName', $lastName);
        $this->db->bind(':email', $email);
        $this->db->bind(':phone', $phone);
        $this->db->bind(':password', $password);
        return $this->db->execute();
    }

    public function registerGoogleUser($email, $firstName, $lastName, $picture)
    {
        $customerId = $this->db->generateId('customer', 'customer_id', 'CU');
        $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        $this->db->query("INSERT INTO customer 
                          (customer_id, firstName, lastName, email, password, gender,phone, img_url, created_at, isActive, isBlocked) 
                          VALUES 
                          (:id, :fname, :lname, :email, :pass, :gender, :phone, :img, NOW(), 1, 0)");

        $this->db->bind(':id', $customerId);
        $this->db->bind(':fname', $firstName);
        $this->db->bind(':lname', $lastName);
        $this->db->bind(':email', $email);
        $this->db->bind(':pass', $randomPassword);
        $this->db->bind(':gender', 'U');
        $this->db->bind(':phone', '0000000000');
        $this->db->bind(':img', $picture);

        if ($this->db->execute()) {
            return $customerId;
        } else {
            return false;
        }
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
        $this->db->query("SELECT customer_id,firstName,lastName,gender,email,phone,password,isActive,isBlocked FROM customer WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->result();
    }

    public function getStaff($email)
    {
        $this->db->query("SELECT admin_id,firstName,lastName,email,phone,password,position FROM admin WHERE email = :email");
        $this->db->bind(':email', $email);
        return $this->db->result();
    }

    public function updateUser($data)
    {
        $sql = "UPDATE customer SET 
                firstName = :firstName, 
                lastName = :lastName, 
                phone = :phone, 
                gender = :gender,
                email = :email, 
                updated_at = NOW()";

        if (isset($data['img_url'])) {
            $sql .= ", img_url = :img_url";
        }

        $sql .= " WHERE customer_id = :id";

        $this->db->query($sql);

        $this->db->bind(':firstName', $data['firstName']);
        $this->db->bind(':lastName', $data['lastName']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':gender', $data['gender']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':id', $data['customer_id']);

        if (isset($data['img_url'])) {
            $this->db->bind(':img_url', $data['img_url']);
        }
        if (!$this->db->execute()) {
            return false;
        }
        $hasAddressData = !empty($data['streetLine']) ||
            !empty($data['city']) ||
            !empty($data['state']) ||
            !empty($data['postcode']);

        if ($hasAddressData) {
            $this->db->query("SELECT customer_id,is_default FROM address WHERE customer_id = :id");
            $this->db->bind(':id', $data['customer_id']);
            $existingAddress = $this->db->result();

            if ($existingAddress) {
                $this->db->query("UPDATE address SET 
                    recipient_name = :recipient_name,
                    recipient_phone = :recipient_phone, 
                    street_line = :street_line, 
                    city = :city, 
                    state = :state, 
                    postcode = :postcode  
                    WHERE customer_id = :cid AND is_default = 1");
            } else {
                $address_id = $this->db->generateId('address', 'address_id', 'AD');
                $this->db->query("INSERT INTO address (address_id, customer_id, recipient_name, recipient_phone, street_line, city, state, postcode, is_default) 
                                  VALUES (:aid, :cid, :recipient_name, :recipient_phone, :street_line, :city, :state, :postcode, 1)");
                $this->db->bind(':aid', $address_id);
            }

            $fullName = $data['firstName'] . ' ' . $data['lastName'];

            $this->db->bind(':recipient_name', $fullName);
            $this->db->bind(':recipient_phone', $data['phone']);
            $this->db->bind(':street_line', $data['streetLine']);
            $this->db->bind(':city', $data['city']);
            $this->db->bind(':state', $data['state']);
            $this->db->bind(':postcode', $data['postcode']);
            $this->db->bind(':cid', $data['customer_id']);

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

    public function updatePasswordByPhone($phone, $newHashedPassword)
    {
        // Update Customer Table
        $this->db->query("UPDATE customer SET password = :pass WHERE phone = :phone");
        $this->db->bind(':pass', $newHashedPassword);
        $this->db->bind(':phone', $phone);

        if ($this->db->execute()) {
            if ($this->db->rowCount() > 0) return true;
        }

        // Update Admin Table (if applicable)
        $this->db->query("UPDATE admin SET password = :pass WHERE phone = :phone");
        $this->db->bind(':pass', $newHashedPassword);
        $this->db->bind(':phone', $phone);

        return $this->db->execute();
    }

    public function getIsEmailExists($email)
    {
        $this->db->query("SELECT email FROM customer WHERE email = :email");
        $this->db->bind(':email', $email);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function getIsPhoneExists($phone)
    {
        $this->db->query("SELECT phone FROM customer WHERE phone = :phone");
        $this->db->bind(':phone', $phone);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function getIsStaffEmailExists($email)
    {
        $this->db->query("SELECT email FROM admin WHERE email = :email");
        $this->db->bind(':email', $email);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function getIsStaffPhoneExists($phone)
    {
        $this->db->query("SELECT phone FROM admin WHERE phone = :phone");
        $this->db->bind(':phone', $phone);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    // zq
    public function getAllMembers()
    {
        $this->db->query("SELECT * FROM customer");
        return $this->db->resultAll();
    }

    // public function countMembers()
    // {
    //     $this->db->query("SELECT COUNT(*) AS total FROM customer");
    //     $result = $this->db->result();
    //     return $result["total"];
    // }

    // public function getMembers($offset, $limit)
    // {
    //     $this->db->query("SELECT * FROM customer LIMIT $offset, $limit");
    //     return $this->db->resultAll();
    // }

    public function countMembers($activeStatus = '', $blockStatus = '')
    {
        $sql = "SELECT COUNT(*) AS total FROM customer WHERE 1";

        // Active filter
        if ($activeStatus === 'Yes') {
            $sql .= " AND isActive = 1";
        } elseif ($activeStatus === 'No') {
            $sql .= " AND isActive = 0";
        }

        // Blocked filter
        if ($blockStatus === 'Yes') {
            $sql .= " AND isBlocked = 1";
        } elseif ($blockStatus === 'No') {
            $sql .= " AND isBlocked = 0";
        }

        $this->db->query($sql);
        $result = $this->db->result();
        return (int)($result["total"] ?? 0);
    }


    public function getMembers($offset, $limit, $activeStatus = '', $blockStatus = '', $sort = 'customer_id', $dir = 'asc')
    {
        $allowedSort = ['customer_id', 'email', 'phone', 'lastName', 'firstName', 'created_at', 'updated_at', 'rewardPoint'];
        if (!in_array($sort, $allowedSort)) {
            $sort = 'customer_id';
        }

        $dir = strtolower($dir) === 'desc' ? 'DESC' : 'ASC';

        // FORCE integers (prevents SQL injection)
        $offset = (int)$offset;
        $limit  = (int)$limit;

        $sql = "SELECT * FROM customer";
        $conditions = [];

        if ($activeStatus !== '' && $activeStatus == 'Yes') {
            $conditions[] = "isActive = 1";
        } elseif ($activeStatus !== '' && $activeStatus == 'No') {
            $conditions[] = "isActive = 0";
        }

        if ($blockStatus !== '' && $blockStatus == 'Yes') {
            $conditions[] = "isBlocked = 1";
        } elseif ($blockStatus !== '' && $blockStatus == 'No') {
            $conditions[] = "isBlocked = 0";
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }

        $sql .= " ORDER BY $sort $dir LIMIT $offset, $limit";

        $this->db->query($sql);
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

    // public function updateMember($data)
    // {
    //     $this->db->query("UPDATE customer SET 
    //                 firstName = :firstName,
    //                 lastName = :lastName,
    //                 phone = :phone,
    //                 email = :email,
    //                 updated_at = NOW(),
    //                 isBlocked = :isBlocked,
    //                 rewardPoint = :rewardPoint,
    //                 img_url = :img_url
    //             WHERE customer_id = :customer_id");
    //     $this->db->bind(':firstName', $data['firstName']);
    //     $this->db->bind(':lastName', $data['lastName']);
    //     $this->db->bind(':phone', $data['phone']);
    //     $this->db->bind(':email', $data['email']);
    //     $this->db->bind(':isBlocked', $data['isBlocked']);
    //     $this->db->bind(':rewardPoint', $data['rewardPoint']);
    //     $this->db->bind(':img_url', $data['img_url']);
    //     $this->db->bind(':customer_id', $data['customer_id']);

    //     return $this->db->execute();
    // }

    public function updateMember($data)
    {
        $sql = "
        UPDATE customer SET
            firstName = :firstName,
            lastName = :lastName,
            phone = :phone,
            email = :email,
            isBlocked = :isBlocked,
            rewardPoint = :rewardPoint
    ";

        if (isset($data['img_url'])) {
            $sql .= ", img_url = :img_url";
        }

        $sql .= " WHERE customer_id = :customer_id";

        $this->db->query($sql);

        // 🔑 Bind parameters
        $this->db->bind(':customer_id', $data['customer_id']);
        $this->db->bind(':firstName', $data['firstName']);
        $this->db->bind(':lastName', $data['lastName']);
        $this->db->bind(':phone', $data['phone']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':isBlocked', $data['isBlocked']);
        $this->db->bind(':rewardPoint', $data['rewardPoint']);

        if (isset($data['img_url'])) {
            $this->db->bind(':img_url', $data['img_url']);
        }

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
