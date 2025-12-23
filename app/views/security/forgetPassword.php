<?php
session_start();
require_once __DIR__ . '/../../controllers/userController.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    header('Content-Type: application/json');
    $controller = new userController();

    if ($_POST['action'] === 'sendOtp') {


        $phone = trim($_POST['phone'] ?? '');

        if (empty($phone) || !preg_match('/^\+?6?01[0-9]{8}$/', $phone)) {
            echo json_encode(['success' => false, 'message' => 'Valid phone number required']);
            exit;
        }
        $otp = $controller->forgetPasswordSendOTP($phone);
        if ($otp !== false) {
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_expire'] = time() + 300; // 5 minutes expiry
            $_SESSION['reset_phone'] = $phone;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Phone not found or send failed']);
        }
        exit;
    }

    if ($_POST['action'] === 'checkOtp') {
        $otpInput = trim($_POST['otp'] ?? '');

        if (empty($otpInput)) {
            echo json_encode(['success' => false, 'message' => 'OTP is required']);
            exit;
        }

        $result = $controller->checkOTP($otpInput);

        echo json_encode($result);
        exit;
    }

    if ($_POST['action'] === 'resetPassword') {
        $newPass = $_POST['newPassword'] ?? '';
        $confirmPass = $_POST['confirmPassword'] ?? '';

        if (empty($newPass) || empty($confirmPass)) {
            echo json_encode(['success' => false, 'message' => 'Both fields are required']);
            exit;
        }
        $result = $controller->resetNewPassword($newPass, $confirmPass);
        echo json_encode($result);
        exit;
    }
}
$title = "Forgot Password";
$pageCSS = "forgetPassword.css";
require_once __DIR__ . '/../header.php';

?>
<div class="wrapper">
    <div class="form-box">

        <div id="step-1" class="step-container" style="left: 4px; opacity: 1; visibility: visible; right: auto;">
            <div class="top">
                <div class="top">
                    <h1>Forget Password</h1>
                    <span>Enter your Phone to receive a code.</span>
                </div>
            </div>
            <form id="form-step-1">
                <div class="input-box">
                    <input type="phone" id="phone" class="input-field" placeholder="Phone Number">
                    <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 512 512" width="20" height="20" fill="currentColor">
                        <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" />
                    </svg>
                    <small id="phoneError" class="error-message"></small>
                </div>
                <div class="input-box">
                    <input type="submit" class="submit" value="Send Code">
                </div>
                <div class="top">
                    <span><a href="/app/views/security/signIn.php">Back to Sign In</a></span>
                </div>
            </form>
        </div>

        <div id="step-2" class="step-container">
            <div class="top">
                <h1>Verify OTP</h1>
                <span>Enter the 6-digit code.</span>
            </div>
            <form id="form-step-2">
                <div class="input-box">
                    <input type="text" id="otp" name="otp" class="input-field" placeholder="123456" maxlength="6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 512 512" width="20" height="20" fill="currentColor">
                        <path d="M336 352c97.2 0 176-78.8 176-176S433.2 0 336 0S160 78.8 160 176c0 18.7 2.9 36.6 8.3 53.3L7.4 390.6c-4.5 4.5-7.4 10.7-7.4 17V480c0 17.7 14.3 32 32 32h32c17.7 0 32-14.3 32-32v-32h32c17.7 0 32-14.3 32-32v-32h32c17.7 0 32-14.3 32-32V352h32zM336 256c-44.2 0-80-35.8-80-80s35.8-80 80-80s80 35.8 80 80s-35.8 80-80 80z" />
                    </svg>
                    <small id="otpError" class="error-message"></small>
                </div>
                <div class="input-box">
                    <input type="submit" class="submit" value="Verify">
                </div>
                <div class="top">
                    <span><a href="#" onclick="backToStep1(event)">Wrong Phone? Go Back</a></span>
                </div>
            </form>
        </div>

        <div id="step-3" class="step-container">
            <div class="top">
                <h1>New Password</h1>
                <span>Create a secure password.</span>
            </div>
            <form id="form-step-3">
                <div class="input-box">
                    <input type="password" id="newPassword" class="input-field" placeholder="New Password">
                    <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="20" height="20" fill="currentColor">
                        <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                    </svg>
                    <small id="newPasswordError" class="error-message"></small>
                </div>
                <div class="input-box">
                    <input type="password" id="confirmPassword" class="input-field" placeholder="Confirm Password">
                    <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="20" height="20" fill="currentColor">
                        <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                    </svg>
                    <small id="confirmPasswordError" class="error-message"></small>
                </div>
                <div class="input-box">
                    <input type="submit" class="submit" value="Reset Password">
                </div>
                <div class="container">
                    <div class="col-half" style="width: 100%;">
                        <p class="label">Password Strength</p>
                        <div class="bars">
                            <div class="bar"></div>
                            <div class="bar"></div>
                            <div class="bar"></div>
                            <div class="bar"></div>
                        </div>
                    </div>
                    <div class="requirements-grid " style="margin-top: 5px; gap: 10px; justify-content: center; ">
                        <div class="requirements-grid ">
                            <div class="check-item" id="req-length">
                                <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" width="20" height="20">
                                    <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.5" />
                                    <path d="M7.5 12.5l2.5 2.5L16.5 9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="check-text">8+ characters</p>
                            </div>
                            <div class="check-item" id="req-num">
                                <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" width="20" height="20">
                                    <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.5" />
                                    <path d="M7.5 12.5l2.5 2.5L16.5 9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="check-text">One number</p>
                            </div>
                        </div>
                        <div class="requirements-grid ">
                            <div class="check-item" id="req-upper">
                                <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" width="20" height="20">
                                    <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.5" />
                                    <path d="M7.5 12.5l2.5 2.5L16.5 9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="check-text">One uppercase</p>
                            </div>
                            <div class="check-item" id="req-special">
                                <svg class="icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" width="20" height="20">
                                    <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="1.5" />
                                    <path d="M7.5 12.5l2.5 2.5L16.5 9" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="check-text">One special char</p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
</div>
<?php showToast(); ?>
<script src="/public/js/validation.js"></script>
<script src="/public/js/forgetPassword.js"></script>
<?php require_once __DIR__ . '/../footer.php'; ?>