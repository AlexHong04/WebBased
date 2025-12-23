<?php
$title = "Active Page";

require_once '../../helpers/html.php';
require_once '../../controllers/userController.php';

$controller = new userController();
// $controller->activeUser();
$loginLink = base("app/views/security/signIn.php" );
// if (isset($_GET['action']) && $_GET['action'] === 'activate') {
//     $controller->activeUser();
// }
?>
<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background-color: #fc84a3;
            padding: 25px;
            text-align: center;
            color: white;
        }

        .content {
            padding: 30px;
            background-color: #ffffff;
            text-align: center;
            /* Centered text looks good for welcome emails */
        }

        /* Reusing your info-box style for the message area */
        .info-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 25px;
            font-size: 16px;
            border: 1px solid #eee;
            text-align: left;
        }

        .btn {
            display: inline-block;
            background-color: #333;
            color: #ffffff !important;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>

<body style='margin:0; padding:0; background-color:#f4f4f4;'>
    <br>
    <div class='container'>
        <div class='header'>
            <h1 style='margin:0; font-size: 24px;'>Welcome to Lovine!</h1>
            <p style='margin:5px 0 0 0; opacity: 0.9;'>We are happy to have you.</p>
        </div>

        <div class='content'>
            <p style="text-align: left;">Hi <strong><?= htmlspecialchars($toName) ?></strong>,</p>

            <div class="info-box">
                <p style="margin-top: 0;">Thank you for creating an account with us.</p>
                <p>Your account has been successfully created. You can now login to start shopping for exclusive jewelry, track your orders, and earn rewards.</p>
            </div>

            <div style="margin-bottom: 20px;">
                <!-- <button class="btn" onclick="window.location.href='<?= $loginLink ?>'">Activate Account 1</button> -->
                <a href="<?= $loginLink ?>?action=activate&email=<?= $toEmail ?>" class="btn">Activate Account</a>
                <!-- <form action="signIn.php" method="POST">
                    <button type="submit" name="activate">Activate Account</button>
                </form> -->
            </div>

            <p style="font-size: 12px; color: #999;">If the button above does not work, please copy and paste this link into your browser:<br>
                <a href="<?= $loginLink ?>?action=activate" style="color: #fc84a3;"><?= $loginLink ?></a>
            </p>
        </div>

        <div class='footer'>
            <p style='margin:0;'>Need help? Contact us at support@lovine.com</p>
            <p style='margin:5px 0 0 0;'>&copy; <?= date('Y') ?> Lovine. All rights reserved.</p>
        </div>
    </div>
    <br>
</body>

</html>