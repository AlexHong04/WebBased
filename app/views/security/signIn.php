<?php
$title = "Sign In Page";
$pageCSS = "signIn.css";

include  '../header.php';
require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../controllers/userController.php';

$controller = new userController();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $controller->signIn();
}

if (isset($_GET['action']) && $_GET['action'] === 'activate') {
    $controller->activeUser();
}
?>

<div class="wrapper">
    <div class="card">
        <div class="form-box split">
            <div class="left-panel">
                <form class="login-container" id="loginForm" method="POST">
                    <input type="hidden" name="action" value="login">
                    <div class="top">
                        <h2>Login</h2>
                    </div>

                    <div class="input-box">
                        <label class="field-label" for="email">Email</label>
                        <div class="input-wrapper">
                            <?= html_text('email', 'class="input-field" placeholder="Email" id="email"'); ?>

                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 512 512" width="20" height="20" fill="currentColor">
                                <path d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48H48zM0 176V384c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V176L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z" />
                            </svg>
                        </div>
                        <small id="emailError" class="error-message text-danger" style="display:none; color: red; font-size: 0.85em; margin-top: 5px;"></small>
                    </div>

                    <div class="input-box">
                        <label class="field-label" for="password">Password</label>
                        <div class="input-wrapper">
                            <?= html_password('password', 'class="input-field" placeholder="Password" id="password"'); ?>

                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="20" height="20" fill="currentColor">
                                <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                            </svg>
                        </div>
                         <small id="passwordError" class="error-message text-danger" style="display:none; color: red; font-size: 0.85em; margin-top: 5px;"></small>
                    </div>

                    <div class="two-col">
                        <div class="input-box remember">
                            <?= html_checkbox('remember', '', 'class="remember-me"'); ?>
                            <label for="remember">Remember me</label>
                        </div>
                        <div class="two">
                            <label><a href="forgetPassword.php" class="forgetPassword">Forget password?</a></label>
                        </div>
                    </div>

                    <div class="input-box">
                        <Button type="submit" class="submit" value="Sign In">Sign In</Button>
                    </div>

                    <div class="two-col">
                        <div class="text">Don't have an account?</div>
                        <a class="signup" href="signUp.php">Create account</a>
                    </div>
                </form>
            </div>
            <div class="right-panel" aria-hidden="false">
                <img src="../../../public/images/loginImage.png" alt="Login background" class="right-bg">
            </div>
        </div>
    </div>
</div>
<?php showToast(); ?>

<script src="/public/js/validation.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('loginForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const isValid = validateLoginForm(this);
                
                if (isValid) {
                    this.submit();
                }
            });
        }
    });
</script>

<?php include '../footer.php' ?>