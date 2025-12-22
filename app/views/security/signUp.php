<?php
$title = "Register Page";
$pageCSS = "signUp.css";


require_once __DIR__ . '/../../controllers/userController.php';

$controller = new userController();
$controller->signUp();

require_once __DIR__ . '/../header.php';
require_once __DIR__ . '/../../helpers/html.php';
?>

<div class="wrapper">
    <div class="card">
        <form class="signUp-container" id="signUpForm" method="post" action="#">
            <div class="top">
                <h2>Sign Up</h2>
            </div>

            <div class="row">
                <div class="col-half">
                    <div class="input-box">
                        <label class="field-label" for="firstName">First Name</label>
                        <div class="input-wrapper">
                            <?= html_text('firstName', 'class="input-field" placeholder="First Name" id="firstName"'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="18" height="18" fill="currentColor">
                                <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z" />
                            </svg>
                        </div>
                        <small id="firstNameError" class="error-message"></small>
                    </div>
                </div>

                <div class="col-half">
                    <div class="input-box">
                        <label class="field-label" for="lastName">Last Name</label>
                        <div class="input-wrapper">
                            <?= html_text('lastName', 'class="input-field" placeholder="Last Name" id="lastName"'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="18" height="18" fill="currentColor">
                                <path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-45.7 48C79.8 304 0 383.8 0 482.3C0 498.7 13.3 512 29.7 512H418.3c16.4 0 29.7-13.3 29.7-29.7C448 383.8 368.2 304 269.7 304H178.3z" />
                            </svg>
                        </div>
                        <small id="lastNameError" class="error-message"></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-half">
                    <div class="input-box">
                        <label class="field-label" for="email">Email</label>
                        <div class="input-wrapper">
                            <?= html_text('email', 'class="input-field" placeholder="Email" id="email"'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 512 512" width="18" height="18" fill="currentColor">
                                <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" />
                            </svg>
                        </div>
                        <small id="emailError" class="error-message"></small>
                    </div>
                </div>

                <div class="col-half">
                    <div class="input-box">
                        <label class="field-label" for="gender">Gender</label>
                        <div class="input-wrapper">
                            <?php
                            $genders = ['Male' => 'Male', 'Female' => 'Female'];
                            html_select('gender', $genders, 'Select Gender', null, 'class="input-field" id="gender"');
                            ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 512 512" width="18" height="18" fill="currentColor">
                                <path d="M320 64c0-17.7 14.3-32 32-32h80c17.7 0 32 14.3 32 32v80c0 17.7-14.3 32-32 32s-32-14.3-32-32V96l-74.5 74.5C368.6 221.7 384 283.4 384 352c0 88.4-71.6 160-160 160S64 440.4 64 352c0-82 62-149.8 141.6-159.2L254.4 144H192c-17.7 0-32-14.3-32-32s14.3-32 32-32h128zM224 448c53 0 96-43 96-96s-43-96-96-96s-96 43-96 96s43 96 96 96z" />
                            </svg>
                        </div>
                        <small id="genderError" class="error-message"></small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-half">
                    <div class="input-box">
                        <label class="field-label" for="password">Password</label>
                        <div class="input-wrapper">
                            <?= html_password('password', 'class="input-field" placeholder="Password" id="password"'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="18" height="18" fill="currentColor">
                                <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                            </svg>

                            <svg id="togglePassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" fill="#666" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10;">
                                <path id="eyeIconPath" d="M73 39.1C63.6 29.7 48.4 29.7 39.1 39.1C29.8 48.5 29.7 63.7 39 73.1L567 601.1C576.4 610.5 591.6 610.5 600.9 601.1C610.2 591.7 610.3 576.5 600.9 567.2L504.5 470.8C507.2 468.4 509.9 466 512.5 463.6C559.3 420.1 590.6 368.2 605.5 332.5C608.8 324.6 608.8 315.8 605.5 307.9C590.6 272.2 559.3 220.2 512.5 176.8C465.4 133.1 400.7 96.2 319.9 96.2C263.1 96.2 214.3 114.4 173.9 140.4L73 39.1zM236.5 202.7C260 185.9 288.9 176 320 176C399.5 176 464 240.5 464 320C464 351.1 454.1 379.9 437.3 403.5L402.6 368.8C415.3 347.4 419.6 321.1 412.7 295.1C399 243.9 346.3 213.5 295.1 227.2C286.5 229.5 278.4 232.9 271.1 237.2L236.4 202.5zM357.3 459.1C345.4 462.3 332.9 464 320 464C240.5 464 176 399.5 176 320C176 307.1 177.7 294.6 180.9 282.7L101.4 203.2C68.8 240 46.4 279 34.5 307.7C31.2 315.6 31.2 324.4 34.5 332.3C49.4 368 80.7 420 127.5 463.4C174.6 507.1 239.3 544 320.1 544C357.4 544 391.3 536.1 421.6 523.4L357.4 459.2z" />
                            </svg>
                        </div>
                        <small id="passwordError" class="error-message"></small>
                    </div>
                </div>

                <div class="col-half">
                    <div class="input-box">
                        <label class="field-label" for="confirm_password">Confirm Password</label>
                        <div class="input-wrapper">
                            <?= html_password('confirm_password', 'class="input-field" placeholder="Confirm Password" id="confirm_password"'); ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="18" height="18" fill="currentColor">
                                <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                            </svg>

                            <svg id="toggleConfirmPassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" fill="#666" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10;">
                                <path id="confirmEyeIconPath" d="M73 39.1C63.6 29.7 48.4 29.7 39.1 39.1C29.8 48.5 29.7 63.7 39 73.1L567 601.1C576.4 610.5 591.6 610.5 600.9 601.1C610.2 591.7 610.3 576.5 600.9 567.2L504.5 470.8C507.2 468.4 509.9 466 512.5 463.6C559.3 420.1 590.6 368.2 605.5 332.5C608.8 324.6 608.8 315.8 605.5 307.9C590.6 272.2 559.3 220.2 512.5 176.8C465.4 133.1 400.7 96.2 319.9 96.2C263.1 96.2 214.3 114.4 173.9 140.4L73 39.1zM236.5 202.7C260 185.9 288.9 176 320 176C399.5 176 464 240.5 464 320C464 351.1 454.1 379.9 437.3 403.5L402.6 368.8C415.3 347.4 419.6 321.1 412.7 295.1C399 243.9 346.3 213.5 295.1 227.2C286.5 229.5 278.4 232.9 271.1 237.2L236.4 202.5zM357.3 459.1C345.4 462.3 332.9 464 320 464C240.5 464 176 399.5 176 320C176 307.1 177.7 294.6 180.9 282.7L101.4 203.2C68.8 240 46.4 279 34.5 307.7C31.2 315.6 31.2 324.4 34.5 332.3C49.4 368 80.7 420 127.5 463.4C174.6 507.1 239.3 544 320.1 544C357.4 544 391.3 536.1 421.6 523.4L357.4 459.2z" />
                            </svg>
                        </div>
                        <small id="confirm_passwordError" class="error-message"></small>
                    </div>
                </div>
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
                <div class="row" style="margin-top: 5px; gap: 10px;">
                    <div class="col-half">
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
                    <div class="col-half">
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

            <div class="input-box">
                <Button type="submit" class="submit" value="Sign Up">Sign Up</Button>
            </div>

            <div class="two-col">
                <div class="text">Already have an account?</div>
                <a class="signIn" href="signIn.php">Sign In</a>
            </div>
        </form>
    </div>
</div>
<?php showToast(); ?>

<script src="/public/js/validation.js"></script>
<script>
   document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('signUpForm');
        initPasswordStrength();

        // 1. Define Paths
        const openEyePath = "M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z";
        const slashEyePath = "M73 39.1C63.6 29.7 48.4 29.7 39.1 39.1C29.8 48.5 29.7 63.7 39 73.1L567 601.1C576.4 610.5 591.6 610.5 600.9 601.1C610.2 591.7 610.3 576.5 600.9 567.2L504.5 470.8C507.2 468.4 509.9 466 512.5 463.6C559.3 420.1 590.6 368.2 605.5 332.5C608.8 324.6 608.8 315.8 605.5 307.9C590.6 272.2 559.3 220.2 512.5 176.8C465.4 133.1 400.7 96.2 319.9 96.2C263.1 96.2 214.3 114.4 173.9 140.4L73 39.1zM236.5 202.7C260 185.9 288.9 176 320 176C399.5 176 464 240.5 464 320C464 351.1 454.1 379.9 437.3 403.5L402.6 368.8C415.3 347.4 419.6 321.1 412.7 295.1C399 243.9 346.3 213.5 295.1 227.2C286.5 229.5 278.4 232.9 271.1 237.2L236.4 202.5zM357.3 459.1C345.4 462.3 332.9 464 320 464C240.5 464 176 399.5 176 320C176 307.1 177.7 294.6 180.9 282.7L101.4 203.2C68.8 240 46.4 279 34.5 307.7C31.2 315.6 31.2 324.4 34.5 332.3C49.4 368 80.7 420 127.5 463.4C174.6 507.1 239.3 544 320.1 544C357.4 544 391.3 536.1 421.6 523.4L357.4 459.2z";

        // 2. Setup Toggle for Main Password
        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIconPath = document.getElementById('eyeIconPath');

        if (toggleBtn && passwordInput && eyeIconPath) {
            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                if (type === 'text') {
                    eyeIconPath.setAttribute('d', openEyePath); // Show OPEN eye
                } else {
                    eyeIconPath.setAttribute('d', slashEyePath); // Show CLOSED eye
                }
            });
        }

        // 3. Setup Toggle for Confirm Password
        const toggleConfirmBtn = document.getElementById('toggleConfirmPassword');
        const confirmInput = document.getElementById('confirm_password');
        const confirmEyeIconPath = document.getElementById('confirmEyeIconPath');

        if (toggleConfirmBtn && confirmInput && confirmEyeIconPath) {
            toggleConfirmBtn.addEventListener('click', function() {
                const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmInput.setAttribute('type', type);

                if (type === 'text') {
                    confirmEyeIconPath.setAttribute('d', openEyePath); // Show OPEN eye
                } else {
                    confirmEyeIconPath.setAttribute('d', slashEyePath); // Show CLOSED eye
                }
            });
        }

        // 4. Form Validation Logic
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const isValid = validateSignUpForm(this);
                if (isValid) {
                    this.submit();
                }
            });
        }
    });
</script>

<?php include '../footer.php' ?>