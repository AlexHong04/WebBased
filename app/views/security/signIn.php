<?php
$title = "Sign In Page";
$pageCSS = "signIn.css";

include  '../header.php';
include '../../helpers/html.php';
include '../../controllers/userController.php';

$controller = new userController();
$controller->signIn();

?>

<div class="wrapper">
    <div class="card">
        <div class="form-box split">
            <div class="left-panel">
                <!-- Login form -->
                <form class="login-container" id="login" method="POST" action="#">
                    <div class="top">
                        <h2>Login</h2>
                    </div>
                    <div class="input-box">
                        <label class="field-label" for="email">Email</label>
                        <?= html_text('email', 'class="input-field" placeholder="Email"'); ?>
                        <!-- <input id="login_email" type="text" class="input-field" name="email" placeholder="Email"> -->
                        <i class="bx bx-user"></i>
                    </div>
                    <div class="input-box">
                        <label class="field-label" for="password">Password</label>
                        <?= html_password('password', 'class="input-field" placeholder="Password"'); ?>
                        <!-- <input id="login_password" type="password" class="input-field" name="password" placeholder="Password"> -->
                        <i class="bx bx-lock-alt"></i>
                    </div>
                    <div class="two-col">
                        <div class="input-box remember">
                            <?= html_checkbox('remember', '', 'class="remember-me"'); ?>
                            <label for="remember">Remember me</label>
                            <!-- <input id="remember" name="remember" type="checkbox" value="1"> -->
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
                <!-- <div class="feature-card">
                    <div class="card-inner">
                        <h3 class="feature-title">Welcome to Company Name</h3>
                        <p class="feature-body"></p>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>
<?php include '../footer.php' ?>
