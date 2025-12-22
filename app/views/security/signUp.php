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