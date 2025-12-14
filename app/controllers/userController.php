<!-- connect models -->
<?php
include __DIR__ . '/../models/userModel.php';
include __DIR__ . '/../helpers/request.php';
include __DIR__ . '/../helpers/validation.php';
class userController
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new userModel();
    }

    // handle user sign in
    public function signIn()
    {
        if (is_post()) {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->userModel->getUser($email, $password);

            if ($user && password_verify($password, $user['password'])) {
                session_start();
                // Password is correct, start a session
                $_SESSION['email'] = $user['email'];
                $_SESSION['customerId'] = $user['customer_id'];
                // Redirect to dashboard or home page
                header("Location: ../home.php");
                exit();
            } else {
                // Invalid password
                $error = "Invalid email or password.";
                echo "<script>console.log('" . $error . "');</script>";
            }
        }
    }
}
?>