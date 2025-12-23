<?php
require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/mail.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/captcha.php';
require_once __DIR__ . '/../helpers/auth.php';
require_once __DIR__ . '/../helpers/googleCallback.php';
require_once __DIR__ . '/../lib/Pagination.php';
require_once __DIR__ . '/../lib/TwilioSMS.php';
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
            $phone = post('phone');
            $password = post('password');
            $confirm_password = post('confirm_password');
            // validate form data
            $errors = [];
            // if (empty($firstName) || empty($lastName)) $errors['name'] = "Name is required.";
            if ($this->userModel->getIsEmailExists($email) || $this->userModel->getIsStaffEmailExists($email)) {
                $errors['email'] = "This Email is already registered.";
            } else if (!isValidEmailDomain($email)) {
                $errors['email'] = "Email domain does not exist.";
            }

            if ($this->userModel->getIsPhoneExists($phone) || $this->userModel->getIsStaffPhoneExists($phone)) {
                $errors['phone'] = "This phone number is already registered.";
            }

            // if (empty($password)) $errors['password'] = "Password is required.";
            // if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";
            // if (empty($gender)) $errors['gender'] = "Gender is required.";
            if (!empty($errors)) {
                $_SESSION['flash_error'] = $errors;
                $_SESSION['old'] = $_POST;
                redirect('signUp.php');
                return;
            }

            // hash the password
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            // save user to database
            $isCreated = $this->userModel->createUser($firstName, $lastName, $email, $phone, $hashed_password);
            $_SESSION['flash_success'] = "Account created successfully! Please check your email to activate your account.";
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

        if (isset($_COOKIE['remember_token'])) {
            setcookie("remember_token", "", time() - 3600, "/", "", false, true);
            // unset($_COOKIE['remember_token']);
        }

        // Redirect to home or login page
        redirect('../views/home.php');
    }

    // handle user sign in
    public function signIn()
    {
        if (is_post() && post('action') === 'login') {
            $secretKey = "6Ld81TEsAAAAAJsuOwHaPEL0WHyMYPIhisH8CUmX";
            if (!verifyRecaptcha($secretKey)) {
                $_SESSION['flash_error']['login'] = "CAPTCHA verification failed. Please try again.";
                $_SESSION['old']['email'] = $_POST['email'];

                redirect('signIn.php');
                return;
            }
            $email = $_POST['email'];
            $password = $_POST['password'];
            $remember = !empty($_POST['remember']); // checkbox

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
            if ($this->userModel->getIsEmailExists($email) || $this->userModel->getIsStaffEmailExists($email)) {
                $errors['email'] = "This Email is already registered.";
            } else if (!isValidEmailDomain($email)) {
                $errors['email'] = "Email domain does not exist.";
            }

            if ($staff && password_verify($password, $staff['password'])) {
                $loggedInId = $staff['admin_id'];
                $loggedInEmail = $staff['email'];
                $isStaffLogin = true;
            } elseif ($user && password_verify($password, $user['password'])) {

                if (!isset($_SESSION['login_attempts_' . $email])) {
                    $_SESSION['login_attempts_' . $email] = 0;
                }

                if (password_verify($password, $user['password'])) {

                    if ($user['isActive'] != 1) {
                        $_SESSION['flash_error']['login'] = "Account is not activated.";
                        redirect('signIn.php');
                        return;
                    }

                    if (isset($_SESSION['login_attempts_' . $email])) {
                        unset($_SESSION['login_attempts_' . $email]);
                    }

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
            } else {
                if (isset($_COOKIE['remember_token'])) {
                    setcookie("remember_token", "", time() - 3600, "/", "", false, true);
                    unset($_COOKIE['remember_token']);
                }
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
    public function loginWithGoogle()
    {
        $client_id = '129399541762-kulgn2g9c3cp5gpdt18sgopu2u1volcg.apps.googleusercontent.com';
        $redirect_uri = 'http://localhost/app/views/security/signIn.php?action=googleCallback';

        $params = [
            'response_type' => 'code',
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'scope' => 'email profile',
            'access_type' => 'online'
        ];

        $url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query($params);

        redirect($url);
        exit;
    }

    public function handleGoogleLogin()
    {
        $client_id = '129399541762-kulgn2g9c3cp5gpdt18sgopu2u1volcg.apps.googleusercontent.com';
        $client_secret = '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';
        $redirect_uri = 'http://localhost/app/views/security/signIn.php?action=googleCallback';

        $userInfo = handleGoogleCallback($client_id, $client_secret, $redirect_uri);

        if (isset($userInfo['error'])) {
            $_SESSION['error_message'] = "Google Login Failed: " . $userInfo['error'];
            redirect("signIn.php");
            exit;
        }

        $email = $userInfo['email'];
        $firstName = $userInfo['given_name'];
        $lastName = $userInfo['family_name'];
        $picture = $userInfo['picture'];

        $existingUser = $this->userModel->getUser($email);

        if ($existingUser) {
            if ($existingUser->isBlocked == 1) {
                $_SESSION['error_message'] = "Account is blocked. Please contact support.";
                redirect("signIn.php");
                exit;
            }

            $_SESSION['customerId'] = $existingUser['customer_id'];
            $_SESSION['user_name'] = $existingUser->firstName;
            $_SESSION['user_email'] = $existingUser->email;
            $_SESSION['role'] = 'member';
            $_SESSION['img_url'] = $existingUser->img_url;
        } else {
            $newUserId = $this->userModel->registerGoogleUser($email, $firstName, $lastName, $picture);

            if ($newUserId) {
                $_SESSION['customerId'] = $newUserId;
                $_SESSION['user_name'] = $firstName;
                $_SESSION['user_email'] = $email;
                $_SESSION['role'] = 'member';
                $_SESSION['img_url'] = $picture;
            } else {
                $_SESSION['error_message'] = "Registration failed. Please try again.";
                redirect("signIn.php");
                exit;
            }
        }
        $_SESSION['flash_success']['login'] = "Login successful.";
        redirect('../home.php');
        exit;
    }

    public function getProfile()
    {
        if (isset($_SESSION['customerId'])) {
            $customerId = $_SESSION['customerId'];
            return $this->userModel->getUserById($customerId);
        } elseif (isset($_SESSION['adminId'])) {
            $adminId = $_SESSION['adminId'];
            return $this->userModel->getAdminById($adminId);
        }
        return null;
    }
    public function updateProfile()
    {
        if (is_post()) {
            if (isset($_SESSION['customerId'])) {
                $customerId = $_SESSION['customerId'];
                // Get current user data
                $currentUserData = $this->userModel->getUserById($customerId);

                $firstName = post('firstName');
                $lastName  = post('lastName');
                $phone     = post('phone');
                $gender    = post('gender');
                $email     = post('email');
                $addressLine = post('streetLine');
                $city      = post('city');
                $state     = post('state');
                $postcode  = post('postcode');

                $errors = [];

                // Validation Logic (Email & Phone)
                if ($email != $currentUserData['email']) {
                    if ($this->userModel->getIsEmailExists($email) || $this->userModel->getIsStaffEmailExists($email)) {
                        $errors['email'] = "This Email is already registered.";
                    } else if (!isValidEmailDomain($email)) {
                        $errors['email'] = "Email domain does not exist.";
                    }
                }

                if ($phone != $currentUserData['phone']) {
                    if ($this->userModel->getIsPhoneExists($phone) || $this->userModel->getIsStaffPhoneExists($phone)) {
                        $errors['phone'] = "This phone number is already registered.";
                    }
                }

                $uploadedImgName = null;

                // Check if file is uploaded and no errors
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {

                    $file = $_FILES['profile_pic'];
                    $fileName = $file['name'];
                    $fileTmpPath = $file['tmp_name'];

                    // Get extension for validation
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));
                    $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg', 'webp');

                    if (in_array($fileExtension, $allowedfileExtensions)) {

                        $newFileName = basename($fileName);

                        $uploadFileDir = __DIR__ . '/../../public/images/profile/';
                        $dest_path = $uploadFileDir . $newFileName;

                        // 3. Move file
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $uploadedImgName = $newFileName;
                        } else {
                            $errors['profile_pic'] = "Error saving file to directory.";
                        }
                    } else {
                        $errors['profile_pic'] = "Invalid file type. Allowed: " . implode(',', $allowedfileExtensions);
                    }
                }

                if (!empty($errors)) {
                    $_SESSION['flash_error'] = $errors;
                    $_SESSION['old'] = $_POST;
                    redirect('profile.php');
                    return;
                }

                $updateData = [
                    'customer_id' => $customerId,
                    'firstName'   => $firstName,
                    'lastName'    => $lastName,
                    'gender'      => $gender,
                    'phone'       => $phone,
                    'email'       => $email,
                    'streetLine'  => $addressLine,
                    'city'        => $city,
                    'state'       => $state,
                    'postcode'    => $postcode,
                    'updateRecipient' => $_POST['updateRecipient'] ?? 0
                ];

                if ($uploadedImgName) {
                    $updateData['img_url'] = $uploadedImgName;
                }

                $this->userModel->updateUser($updateData);
                $_SESSION['flash_success'] = "Profile updated successfully!";
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
        $_SESSION['flash_success'] = "Password reset successfully!";
        redirect('resetPassword.php');
    }

    public function forgetPasswordSendOTP($phone)
    {
        if (!$this->userModel->getIsPhoneExists($phone)) {
            return false;
        }

        if (isset($_SESSION['last_otp_sent']) && (time() - $_SESSION['last_otp_sent'] < 60)) {
            return false;
        }
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '01')) {
            $phone = '6' . $phone;
        }
        $phone = '+' . $phone;
        $otp = random_int(100000, 999999);

        // Twilio credentials
        $sid   = 'AC691f78ade95d9649a59a8e5c7a431e7a';
        $token = '242e93ca44f709be3f93cd4ea0bd012a';
        $from  = '+14199241697';

        $twilio = new TwilioSMS($sid, $token, $from);

        $message = "Your OTP is {$otp}. Do not share this code.";

        $isSent = $twilio->sendSMS($phone, $message);
        $_SESSION['flash_success'] = "OTP sent successfully!";
        if ($isSent) {
            $_SESSION['last_otp_sent'] = time();
            return $otp;
        }

        return false;
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
            $_SESSION['flash_success'] = "OTP verified successfully!";
            return ['success' => true];
        } else {
            $_SESSION['flash_error'] = "Invalid OTP. Please try again.";
            return ['success' => false, 'message' => 'Invalid OTP. Please try again.'];
        }
    }
    public function resetNewPassword($newPassword, $confirmPassword)
    {
        if (!isset($_SESSION['reset_phone'])) {
            return ['success' => false, 'message' => 'Session expired. Please start over.'];
        }

        $phone = $_SESSION['reset_phone'];

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
        }

        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'message' => 'Passwords do not match.'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $result = $this->userModel->updatePasswordByPhone($phone, $hashedPassword);

        if ($result) {
            unset($_SESSION['otp']);
            unset($_SESSION['otp_expire']);
            unset($_SESSION['reset_phone']);
            $_SESSION['flash_success'] = "Password reset successfully!";
            return ['success' => true];
        } else {
            $_SESSION['flash_error'] = "Failed to update password. Database error.";
            return ['success' => false, 'message' => 'Failed to update password. Database error.'];
        }
    }

    // zq
    public function index()
    {
        $filters = [
            'page'   => $_GET['page'] ?? 1,
            'activeStatus' => $_GET['activeStatus'] ?? '',
            'blockStatus' => $_GET['blockStatus'] ?? '',
            'sort'   => $_GET['sort'] ?? 'customer_id',
            'dir'    => $_GET['dir'] ?? 'asc',
            'search'    => $_GET['search'] ?? ''
        ];

        $total = $this->userModel->countMembers($filters['activeStatus'], $filters['blockStatus'], $filters['search']);
        $pagination = new Pagination($total, 10, $filters['page']);

        $members = $this->userModel->getMembers(
            $pagination->offset,
            $pagination->recordsPerPage,
            $filters['activeStatus'],
            $filters['blockStatus'],
            $filters['sort'],
            $filters['dir'],
            $filters['search']
        );

        return [
            'members'     => $members,
            'pagination' => $pagination,
            'filters'    => $filters
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
                $memberData['img_url'] = $fileName;
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
            'rewardPoint' => $_POST['rewardPoint'] ?? 0
        ];

        if (isset($fileName)) {
            $memberData['img_url'] = $fileName;
        }

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
