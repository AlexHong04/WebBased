<?php
require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/mail.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../lib/Pagination.php';
class userController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new userModel();
    }

    // handle user sign up
    public function signUp()
    {
        if (is_post()) {
            // retrieve form data
            $firstName = post('firstName');
            $lastName = post('lastName');
            $email = post('email');
            $gender = post('gender');
            $password = post('password');
            $confirm_password = post('confirm_password');
            // validate form data
            $errors = [];
            if (empty($firstName) || empty($lastName)) $errors['name'] = "Name is required.";
            if (empty($email) || !is_email($email)) $errors['email'] = "Valid email is required.";
            if (empty($password)) $errors['password'] = "Password is required.";
            if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";
            if (empty($gender)) $errors['gender'] = "Gender is required.";
            if (!empty($errors)) {
                $_SESSION['flash_error'] = $errors;
                $_SESSION['old'] = $_POST;
                redirect('signUp.php');
                return;
            }

            // hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            // save user to database
            $isCreated = $this->userModel->createUser($firstName, $lastName, $email, $gender, $hashed_password);
            if ($isCreated) {
                $loginLink = base('app/views/security/signIn.php?action=activate&email=' . $email);
                sendWelcomeEmail($email, $firstName . ' ' . $lastName, $loginLink);
                // redirect to login page or show success message
                redirect('signIn.php');
            } else {
                $_SESSION['flash_error']['global'] = "Failed to create account. Please try again.";
                redirect('signUp.php');
            }
        }
    }

    public function activeUser()
    {
        $emailToActivate = $_GET['email'];

        // Call a function in your controller to update status in DB
        $success = $this->userModel->updateUserIsActive($emailToActivate);
        if ($success) {
            $successMessage = "Account activated successfully! You can now login.";
        } else {
            $errorMessage = "Activation failed or account already active.";
        }
    }

    public function signOut()
    {
        // Destroy the session
        session_unset();
        session_destroy();
        // Redirect to home or login page
        redirect('../views/home.php');
    }

    // handle user sign in
    // public function signIn()
    // {
    //     if (is_post() && post('action') === 'login') {
    //         $email = $_POST['email'];
    //         $password = $_POST['password'];
    //         $remember = isset($_POST['remember']); // checkbox

    //         if (isset($_SESSION['flash_error'])) {
    //             unset($_SESSION['flash_error']);
    //         }

    //         // fetch user by email only
    //         $user = $this->userModel->getUser($email);
    //         $staff = $this->userModel->getStaff($email);

    //         if ($user['isActive'] != 1) {
    //             $_SESSION['flash_error']['login'] = "Account is not activated. Please check your email for the activation link.";
    //             redirect('signIn.php');
    //             return;
    //         }

    //         // verify password
    //         if ((!$user || !password_verify($password, $user['password'])) && (!$staff || !password_verify($password, $staff['password']))) {
    //             $_SESSION['flash_error']['login'] = "Invalid email or password.";
    //             redirect('signIn.php');
    //             return;
    //         }

    //         // Create JWT
    //         $secret = 'Lovine';
    //         $payload = [
    //             'customerId' => $user['customer_id'],
    //             'email' => $user['email'],
    //             'exp' => time() + (60 * 60) // 1 hour
    //         ];
    //         $token = createJWT($payload, $secret);

    //         // save in session
    //         $_SESSION['token'] = $token;

    //         // ---- Remember Me ----
    //         if ($remember) {
    //             setcookie(
    //                 "remember_token",
    //                 $token,
    //                 time() + (60 * 60 * 24 * 30), // 30 days
    //                 "/",
    //                 "",
    //                 false,
    //                 true
    //             );
    //         }

    //         // check staff id == AD direct to admin dashboard else direct to user home

    //         if ($staff) {
    //             // store staff id in session
    //             $_SESSION['adminId'] = $staff['admin_id'];
    //             $_SESSION['email'] = $staff['email'];
    //             $_SESSION['flash_success']['login'] = "Login successful.";
    //             redirect('');
    //             return;
    //         } else {
    //             // proceed with user login
    //             // store user id in session
    //             $_SESSION['customerId'] = $user['customer_id'];
    //             $_SESSION['email'] = $user['email'];
    //             $_SESSION['flash_success']['login'] = "Login successful.";
    //             redirect('../home.php');
    //         }
    //     }
    // }

    // handle user sign in
    public function signIn()
    {
        if (is_post() && post('action') === 'login') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            $remember = isset($_POST['remember']); // checkbox

            if (isset($_SESSION['flash_error'])) {
                unset($_SESSION['flash_error']);
            }

            $user = $this->userModel->getUser($email);
            $staff = $this->userModel->getStaff($email);

            $loggedInId = null;
            $loggedInEmail = null;
            $isStaffLogin = false;

            if ($user && $user['isBlocked'] == 1) {
                $_SESSION['flash_error']['login'] = "Your account has been blocked. Please contact support.";
                redirect('signIn.php');
                return;
            }

            if ($staff && password_verify($password, $staff['password'])) {
                $loggedInId = $staff['admin_id'];
                $loggedInEmail = $staff['email'];
                $isStaffLogin = true;
            } elseif ($user) {

                if (!isset($_SESSION['login_attempts_' . $email])) {
                    $_SESSION['login_attempts_' . $email] = 0;
                }

                if (password_verify($password, $user['password'])) {

                    if ($user['isActive'] != 1) {
                        $_SESSION['flash_error']['login'] = "Account is not activated.";
                        redirect('signIn.php');
                        return;
                    }

                    unset($_SESSION['login_attempts_' . $email]);

                    $loggedInId = $user['customer_id'];
                    $loggedInEmail = $user['email'];
                    $isStaffLogin = false;
                } else {
                    $_SESSION['login_attempts_' . $email] += 1;
                    $attempts = $_SESSION['login_attempts_' . $email];

                    if ($attempts > 5) {
                        $this->userModel->updateUserIsBlocked($email, 1);

                        unset($_SESSION['login_attempts_' . $email]);

                        $_SESSION['flash_error']['login'] = "Account blocked! You have entered the wrong password 5 times.";
                    } else {
                        $remaining = 5 - $attempts;
                        $_SESSION['flash_error']['login'] = "Invalid password. You have $remaining attempts left.";
                    }

                    redirect('signIn.php');
                    return;
                }
            } else {
                $_SESSION['flash_error']['login'] = "Invalid email or password.";
                redirect('signIn.php');
                return;
            }
            // Create JWT (Use the correct ID and Email based on who logged in)
            $secret = 'Lovine';
            $payload = [
                'id' => $loggedInId,
                'email' => $loggedInEmail,
                'role' => $isStaffLogin ? 'admin' : 'customer',
                'exp' => time() + (60 * 60)
            ];
            $token = createJWT($payload, $secret);

            // save in session
            $_SESSION['token'] = $token;
            $_SESSION['email'] = $loggedInEmail;

            //  Remember Me 
            if ($remember) {
                setcookie("remember_token", $token, time() + (60 * 60 * 24 * 30), "/", "", false, true);
            }
            $prefix = strtoupper(substr($loggedInId, 0, 2));

            if ($prefix === 'AD') {
                $_SESSION['adminId'] = $loggedInId;
                $_SESSION['flash_success']['login'] = "Welcome Admin.";

                redirect('../adminDashboard.php');
                return;
            } elseif ($prefix === 'CU') {
                $_SESSION['customerId'] = $loggedInId;
                $_SESSION['flash_success']['login'] = "Login successful.";

                redirect('../home.php');
                return;
            } else {
                $_SESSION['flash_error']['login'] = "Unknown account type.";
                redirect('signIn.php');
                return;
            }
        }
    }

    public function checkUserMultipleLogin() {}

    public function getProfile()
    {
        if (isset($_SESSION['customerId'])) {
            $customerId = $_SESSION['customerId'];
            return $this->userModel->getUserById($customerId);
        }
    }

    public function updateProfile()
    {
        if (is_post()) {
            if (isset($_SESSION['customerId'])) {
                $customerId = $_SESSION['customerId'];
                $firstName = post('firstName');
                $lastName = post('lastName');
                $phone = post('phone');
                $email = post('email');
                $addressLine = post('streetLine');

                $city = post('city');
                $state = post('state');
                $postcode = post('postcode');

                $result = $this->userModel->updateUser($firstName, $lastName, $phone, $email, $customerId, $addressLine, $city, $state, $postcode);

                redirect('profile.php');
            } else {
                redirect('signIn.php');
            }
        }
    }

    public function resetPassword()
    {
        if (isset($_SESSION['flash_error'])) {
            unset($_SESSION['flash_error']);
        }
        if (!is_post() || ($_POST['action'] ?? '') !== 'reset_password') {
            return;
        }

        if (!isset($_SESSION['customerId'])) {
            redirect('signIn.php');
            return;
        }

        $customerId = $_SESSION['customerId'];
        $currentPassword = post('currentPassword');
        $newPassword = post('newPassword');
        $confirmPassword = post('confirmPassword');

        $currentHashedPassword = $this->userModel->getUserCurrentPassword($customerId)['password'];

        if (!password_verify($currentPassword, $currentHashedPassword)) {
            $_SESSION['flash_error']['current_password_incorrect'] = "Current password is incorrect.";
            unset($_SESSION['flash_error']['current_password_incorrect']);
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['flash_error']['password_mismatch'] = "Passwords do not match.";
            unset($_SESSION['flash_error']['password_mismatch']);
            return;
        }

        $hashedNewPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->userModel->changePassword($customerId, $hashedNewPassword);
        redirect('resetPassword.php');
    }


    // public function getTopSalesData()
    // {
    //     return $this->userModel->getTopSalesData();
    // }


    public function forgetPasswordSendOTP($email)
    {
        $user = $this->userModel->getUser($email);
        $staff = null;

        if (!$user) {
            $staff = $this->userModel->getStaff($email);
        }
        if (!$user && !$staff) {
            return false;
        }
        $name = "User";
        if ($user) {
            $name = $user['firstName'] . ' ' . $user['lastName'];
        } elseif ($staff) {
            $name = $staff['firstName'] . ' ' . $staff['lastName'];
        }

        // crate OTP and send email
        $otp = rand(100000, 999999);

        // send email
        $isSent = sendOtpEmail($email, $name, $otp);

        if ($isSent) {
            return $otp;
        } else {
            return false;
        }
    }

    public function checkOTP($userInputOtp)
    {
        // check OPT
        if (!isset($_SESSION['otp']) || !isset($_SESSION['otp_expire'])) {
            return ['success' => false, 'message' => 'Session expired. Please request a new OTP.'];
        }

        // check OPT expiry (5 minutes)
        if (time() > $_SESSION['otp_expire']) {
            unset($_SESSION['otp']);
            unset($_SESSION['otp_expire']);
            return ['success' => false, 'message' => 'OTP has expired. Please request again.'];
        }

        // compare OTP
        if ($userInputOtp == $_SESSION['otp']) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Invalid OTP. Please try again.'];
        }
    }
    public function resetNewPassword($newPassword, $confirmPassword)
    {
        if (!isset($_SESSION['reset_email'])) {
            return ['success' => false, 'message' => 'Session expired. Please start over.'];
        }

        $email = $_SESSION['reset_email'];

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match.'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $result = $this->userModel->updatePasswordByEmail($email, $hashedPassword);

        if ($result) {
            unset($_SESSION['otp']);
            unset($_SESSION['otp_expire']);
            unset($_SESSION['reset_email']);

            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to update password. Database error.'];
        }
    }

    // zq
    public function index()
    {

        // Count total records
        $total = $this->userModel->countMembers();

        // Get current page
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        // Create pagination object
        $pagination = new Pagination($total, 10, $page); // 10 rows per page

        // Fetch paginated members
        $members = $this->userModel->getMembers($pagination->offset, $pagination->recordsPerPage);

        return [
            "members" => $members,
            "pagination" => $pagination
        ];
    }

    public function getMembers()
    {
        if (!isset($_GET['id'])) {
            die("No customer ID provided.");
        }

        $custID = $_GET['id'];

        $member = $this->userModel->getSpecificMember($custID);

        if (!$member) {
            die("Member not found.");
        }

        return $member;
    }

    public function getMemberAddress()
    {
        $custID = $_GET['id'];

        $address = $this->userModel->getAddress($custID);

        return $address;
    }

    public function updateStatus($customerIds)
    {

        if (empty($customerIds) || !is_array($customerIds)) {
            return false;
        }

        return $this->userModel->updateStatus($customerIds);
    }

    public function updateMember()
    {
        // Only handle POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return false;
        }

        // Validate required POST data
        $customer_id = $_POST['customer_id'] ?? null;
        if (!$customer_id) {
            $_SESSION['error_message'] = "Invalid member ID.";
            return false;
        }

        if (!empty($_FILES['profile_pic']['name'])) {
            $file = $_FILES['profile_pic'];

            // Validate
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($file['type'], $allowed)) {
                $_SESSION['error_message'] = "Invalid image type";
                return;
            }

            $targetDir = __DIR__ . "/../../public/images/profile/";
            $fileName = basename($file['name']);
            $targetPath = $targetDir . $fileName;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Save relative path to DB
                $img_url = $fileName;
            } else {
                $_SESSION['error_message'] = "Failed to upload image.";
                return false;
            }
        }

        // Collect member data safely
        $memberData = [
            'customer_id' => $customer_id,
            'firstName'   => $_POST['firstName'] ?? '',
            'lastName'    => $_POST['lastName'] ?? '',
            'phone'       => $_POST['phone'] ?? '',
            'email'       => $_POST['email'] ?? '',
            'isBlocked'   => $_POST['isBlocked'] ?? 0,
            'rewardPoint' => $_POST['rewardPoint'] ?? 0,
            'img_url' => $img_url ?? null
        ];

        // Update member in database
        $updated = $this->userModel->updateMember($memberData);

        if (!$updated) {
            $_SESSION['error_message'] = "Failed to update member.";
        }

        // Handle addresses (optional)
        $addresses = $_POST['addresses'] ?? [];
        if (!empty($addresses)) {
            foreach ($addresses as $addr) {
                // Update existing address
                $this->userModel->updateAddress($addr['address_id'], $addr);
            }
        }

        // Save success message
        $_SESSION['success_message'] = "Member updated successfully!";
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }

    public function updateRewardPoint($custId)
    {
        if (empty($custId)) {
            return false;
        }

        $member = $this->userModel->getSpecificMember($custId);

        if (!$member) {
            return false;
        }

        $newPoint = (int)$member['rewardPoint'] + 10;

        return $this->userModel->updatePoint($custId, $newPoint);
    }
}
