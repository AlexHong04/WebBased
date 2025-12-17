<?php
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
                    <input type="tel" id="phone" class="input-field" placeholder="Phone Number">
                    <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 512 512" width="20" height="20" fill="currentColor">
                        <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" />
                    </svg>
                    <small id="emailError" class="error-message"></small>
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
                    <span><a href="#" onclick="backToStep1(event)">Wrong Email? Go Back</a></span>
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
            </form>
        </div>

    </div>
</div>

<script src="/public/js/validation.js"></script>

<script>
    const step1 = document.getElementById("step-1");
    const step2 = document.getElementById("step-2");
    const step3 = document.getElementById("step-3");

    // ========================
    // 验证逻辑 (调用 validation.js 的 helper)
    // ========================

    // 验证第一步：邮箱
    function validateStep1Form() {
        const form = document.getElementById('form-step-1');
        clearErrors(form); // 来自 validation.js

        const email = document.getElementById('email');
        let isValid = true;

        if (isEmpty(email.value)) {
            showError('email', 'Email Address is required');
            isValid = false;
        } else if (!isValidEmail(email.value)) {
            showError('email', 'Please enter a valid email address');
            isValid = false;
        }
        return isValid;
    }

    // 验证第二步：OTP
    function validateStep2Form() {
        const form = document.getElementById('form-step-2');
        clearErrors(form);

        const otp = document.getElementById('otp');
        let isValid = true;

        if (isEmpty(otp.value)) {
            showError('otp', 'OTP is required');
            isValid = false;
        } else if (!/^\d{6}$/.test(otp.value)) {
            showError('otp', 'OTP must be exactly 6 digits');
            isValid = false;
        }
        return isValid;
    }

    // 验证第三步：重置密码
    function validateStep3Form() {
        const form = document.getElementById('form-step-3');
        clearErrors(form);

        const newPass = document.getElementById('newPassword');
        const confirmPass = document.getElementById('confirmPassword');
        let isValid = true;

        // 新密码验证
        if (isEmpty(newPass.value)) {
            showError('newPassword', 'New password is required');
            isValid = false;
        } else if (newPass.value.length < 8) {
            showError('newPassword', 'Password must be at least 8 characters');
            isValid = false;
        }

        // 确认密码验证
        if (isEmpty(confirmPass.value)) {
            showError('confirmPassword', 'Please confirm your password');
            isValid = false;
        } else if (newPass.value !== confirmPass.value) {
            showError('confirmPassword', 'Passwords do not match');
            isValid = false;
        }

        return isValid;
    }

    function animateToStep2() {
        step1.style.left = "-510px";
        step1.style.opacity = "0";
        step1.style.visibility = "hidden";
        step2.style.right = "5px";
        step2.style.left = "auto";
        step2.style.opacity = "1";
        step2.style.visibility = "visible";
    }

    function animateToStep3() {
        step2.style.right = "520px";
        step2.style.opacity = "0";
        step2.style.visibility = "hidden";
        step3.style.right = "5px";
        step3.style.left = "auto";
        step3.style.opacity = "1";
        step3.style.visibility = "visible";
    }

    function backToStep1(e) {
        if (e) e.preventDefault();
        step1.style.left = "4px";
        step1.style.opacity = "1";
        step1.style.visibility = "visible";
        step2.style.right = "-520px";
        step2.style.opacity = "0";
        step2.style.visibility = "hidden";
        clearErrors(document.getElementById('form-step-2'));
    }

    document.getElementById('form-step-1').addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!validateStep1Form()) return;

        const btn = this.querySelector('.submit');
        const oldText = btn.value;

        btn.value = "Sending...";
        btn.disabled = true;

        setTimeout(() => {
            animateToStep2();
            btn.value = oldText;
            btn.disabled = false;
        }, 1000);
    });

    document.getElementById('form-step-2').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validateStep2Form()) return;

        const btn = this.querySelector('.submit');
        btn.value = "Verifying...";
        btn.disabled = true;

        setTimeout(() => {
            animateToStep3();
            btn.value = "Verify";
            btn.disabled = false;
        }, 1000);
    });

    document.getElementById('form-step-3').addEventListener('submit', function(e) {
        e.preventDefault();

        if (!validateStep3Form()) return;

        alert("Success! Redirecting...");
        window.location.href = "/app/views/security/signIn.php";
    });
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>