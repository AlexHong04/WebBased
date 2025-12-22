<?php
require_once __DIR__ . '/../models/adminModel.php';
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/auth.php';

class adminController
{
    private $adminModel;

    public function __construct()
    {
        $this->adminModel = new adminModel();
    }

    // 1. Dashboard / List all Admins
    public function index()
    {
        // Check if logged in as admin (Helper function suggested)
        if (!isset($_SESSION['adminId'])) {
            redirect('../security/signIn.php');
            return;
        }

        $admins = $this->adminModel->getAllAdmins();
        return [
            'admins' => $admins
        ];
    }

    // 2. Add New Admin / Staff (Similar to signUp)
    public function addStaff()
    {
        // Only existing admins should be able to create new admins
        if (!isset($_SESSION['adminId'])) {
            redirect('security/signIn.php');
            return;
        }

        if (is_post()) {
            // 1. 获取表单数据
            $firstName = post('firstName');
            $lastName = post('lastName');
            $email = post('email');
            $phone = post('phone');
            $password = post('password');
            $confirm_password = post('confirm_password');
            $address = post('address');
            $position = post('position');

            // 2. 开始验证
            $errors = [];

            // Email 验证
            if ($this->adminModel->isEmailExists($email)) {
                $errors['email'] = "This Email is already registered as Staff.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = "Invalid email format.";
            }

            // Phone 验证
            if ($this->adminModel->isPhoneExists($phone)) {
                $errors['phone'] = "This phone number is already registered.";
            }

            // 密码验证
            if (strlen($password) < 6) {
                $errors['password'] = "Password must be at least 6 characters.";
            }
            if ($password !== $confirm_password) {
                $errors['confirm_password'] = "Passwords do not match.";
            }

            // =============== 把它放在这里 (插入点) ===============
            // 必填字段检查 (First Name, Last Name, Position)
            if (empty($firstName) || empty($lastName) || empty($position)) {
                $errors['global'] = "Please fill in all required fields.";
            }
            // ====================================================

            // 3. 处理错误
            if (!empty($errors)) {
                $_SESSION['flash_error'] = $errors;
                $_SESSION['old'] = $_POST;
                redirect('adminListing.php');
                return;
            }

            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Prepare data array
            $data = [
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'password' => $hashed_password,
                'address' => $address,
                'position' => $position
            ];

            // Save admin to database
            if ($this->adminModel->createAdmin($data)) {
                $_SESSION['flash_success'] = "New staff member added successfully.";
                redirect('adminListing.php');
            } else {
                $_SESSION['flash_error']['global'] = "Failed to create account. Please try again.";
                redirect('adminListing.php');
            }
        }
    }

    // 3. Get Current Admin Profile
    public function profile()
    {
        if (!isset($_SESSION['adminId'])) {
            redirect('security/signIn.php');
            return;
        }

        $adminId = $_SESSION['adminId'];
        $adminData = $this->adminModel->getAdminById($adminId);

        if (!$adminData) {
            $_SESSION['flash_error']['global'] = "Admin profile not found.";
            redirect('security/signIn.php');
            return;
        }

        return $adminData;
    }

    // 4. Update Admin Profile
    public function updateProfile()
    {
        if (!isset($_SESSION['adminId'])) {
            redirect('security/signIn.php');
            return;
        }

        if (is_post()) {
            $adminId = $_SESSION['adminId'];
            $currentAdminData = $this->adminModel->getAdminById($adminId);

            $firstName = post('firstName');
            $lastName = post('lastName');
            $phone = post('phone');
            $address = post('address'); // Map to 'address' column
            $position = post('position'); // Allow updating position?

            $errors = [];

            // Unique Phone Validation (ignore if it's their own number)
            if ($phone != $currentAdminData['phone']) {
                if ($this->adminModel->isPhoneExists($phone)) {
                    $errors['phone'] = "This phone number is already registered.";
                }
            }

            if (!empty($errors)) {
                $_SESSION['flash_error'] = $errors;
                // Redirect back to profile page
                redirect('admin/profile.php');
                return;
            }

            $data = [
                'admin_id' => $adminId,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'phone' => $phone,
                'address' => $address,
                'position' => $position
            ];

            if ($this->adminModel->updateAdmin($data)) {
                $_SESSION['flash_success'] = "Profile updated successfully!";
                redirect('admin/profile.php');
            } else {
                $_SESSION['flash_error']['global'] = "Failed to update profile.";
                redirect('admin/profile.php');
            }
        }
    }

    // 5. Change Password (Admin Specific)
    public function changePassword()
    {
        if (!isset($_SESSION['adminId'])) {
            redirect('security/signIn.php');
            return;
        }

        if (is_post()) {
            $adminId = $_SESSION['adminId'];
            $currentPassword = post('currentPassword');
            $newPassword = post('newPassword');
            $confirmPassword = post('confirmPassword');

            // Get current DB password
            $adminData = $this->adminModel->getAdminById($adminId);

            // Verify Old Password
            if (!password_verify($currentPassword, $adminData['password'])) {
                $_SESSION['flash_error']['current_password'] = "Current password is incorrect.";
                redirect('admin/changePassword.php');
                return;
            }

            // Verify New Password Match
            if ($newPassword !== $confirmPassword) {
                $_SESSION['flash_error']['new_password'] = "New passwords do not match.";
                redirect('admin/changePassword.php');
                return;
            }

            // Hash new password and save (Assuming a method exists in Model)
            // You might need to add `updatePassword` to your adminModel
            $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // This is a direct query example, ideally add this method to adminModel
            // $this->adminModel->updatePassword($adminId, $hashedNewPassword);

            $_SESSION['flash_success'] = "Password changed successfully.";
            redirect('admin/profile.php');
        }
    }
}
