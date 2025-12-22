<?php
$title = "Sign In Page";
$pageCSS = "signIn.css";


require_once __DIR__ . '/../../controllers/userController.php';
require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../helpers/googleCallback.php';

$controller = new userController();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    $controller->signIn();
}

if (isset($_GET['action']) && $_GET['action'] === 'googleLogin') {
    $controller->loginWithGoogle();
}

if (isset($_GET['action']) && $_GET['action'] === 'googleCallback') {
    $controller->handleGoogleLogin();
}

if (isset($_GET['action']) && $_GET['action'] === 'activate') {
    $controller->activeUser();
}

require_once __DIR__ . '/../header.php';

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
                        <div class="input-wrapper" style="position: relative;">
                            <?= html_password('password', 'class="input-field" placeholder="Password" id="password" style="padding-right: 40px;"'); ?>

                            <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 448 512" width="20" height="20" fill="currentColor">
                                <path d="M144 144v48H304V144c0-44.2-35.8-80-80-80s-80 35.8-80 80zM80 192V144C80 64.5 144.5 0 224 0s144 64.5 144 144v48h16c35.3 0 64 28.7 64 64V448c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V256c0-35.3 28.7-64 64-64H80z" />
                            </svg>

                            <svg id="togglePassword" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" fill="#666" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; z-index: 10;">
                                <path id="eyeIconPath" d="M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z" />
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
                    <div class="input-box" style="display: flex; justify-content: center; margin-bottom: 20px;">
                        <div class="g-recaptcha" data-sitekey="6Ld81TEsAAAAAE51BYaPBpdY-Efxbq246sfmgG1k"></div>
                    </div>
                    <div class="input-box">
                        <Button type="submit" class="submit" value="Sign In">Sign In</Button>
                    </div>
                    <div class="google-login">
                        <a href="signIn.php?action=googleLogin" class="btn-google">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20px" height="20px">
                                <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z" />
                                <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z" />
                                <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z" />
                                <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z" />
                            </svg>
                            <span>Sign in with Google</span>
                        </a>
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
<script src="https://www.google.com/recaptcha/api.js?hl=en" async defer></script>
<script src="/public/js/validation.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('loginForm');

        const toggleBtn = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIconPath = document.getElementById('eyeIconPath');

        const openEyePath = "M320 96C239.2 96 174.5 132.8 127.4 176.6C80.6 220.1 49.3 272 34.4 307.7C31.1 315.6 31.1 324.4 34.4 332.3C49.3 368 80.6 420 127.4 463.4C174.5 507.1 239.2 544 320 544C400.8 544 465.5 507.2 512.6 463.4C559.4 419.9 590.7 368 605.6 332.3C608.9 324.4 608.9 315.6 605.6 307.7C590.7 272 559.4 220 512.6 176.6C465.5 132.9 400.8 96 320 96zM176 320C176 240.5 240.5 176 320 176C399.5 176 464 240.5 464 320C464 399.5 399.5 464 320 464C240.5 464 176 399.5 176 320zM320 256C320 291.3 291.3 320 256 320C244.5 320 233.7 317 224.3 311.6C223.3 322.5 224.2 333.7 227.2 344.8C240.9 396 293.6 426.4 344.8 412.7C396 399 426.4 346.3 412.7 295.1C400.5 249.4 357.2 220.3 311.6 224.3C316.9 233.6 320 244.4 320 256z";
        const slashEyePath = "M73 39.1C63.6 29.7 48.4 29.7 39.1 39.1C29.8 48.5 29.7 63.7 39 73.1L567 601.1C576.4 610.5 591.6 610.5 600.9 601.1C610.2 591.7 610.3 576.5 600.9 567.2L504.5 470.8C507.2 468.4 509.9 466 512.5 463.6C559.3 420.1 590.6 368.2 605.5 332.5C608.8 324.6 608.8 315.8 605.5 307.9C590.6 272.2 559.3 220.2 512.5 176.8C465.4 133.1 400.7 96.2 319.9 96.2C263.1 96.2 214.3 114.4 173.9 140.4L73 39.1zM236.5 202.7C260 185.9 288.9 176 320 176C399.5 176 464 240.5 464 320C464 351.1 454.1 379.9 437.3 403.5L402.6 368.8C415.3 347.4 419.6 321.1 412.7 295.1C399 243.9 346.3 213.5 295.1 227.2C286.5 229.5 278.4 232.9 271.1 237.2L236.4 202.5zM357.3 459.1C345.4 462.3 332.9 464 320 464C240.5 464 176 399.5 176 320C176 307.1 177.7 294.6 180.9 282.7L101.4 203.2C68.8 240 46.4 279 34.5 307.7C31.2 315.6 31.2 324.4 34.5 332.3C49.4 368 80.7 420 127.5 463.4C174.6 507.1 239.3 544 320.1 544C357.4 544 391.3 536.1 421.6 523.4L357.4 459.2z";

        if (toggleBtn && passwordInput && eyeIconPath) {
            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                if (type === 'text') {
                    eyeIconPath.setAttribute('d', openEyePath);
                } else {
                    eyeIconPath.setAttribute('d', slashEyePath);
                }
            });
        }

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