<?php
require_once __DIR__ . '../../helpers/request.php';
require_once __DIR__ . '/checkoutController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controller = new CheckoutController();
$action = req('action', 'index');

switch ($action) {
    case 'placeOrder':
        $controller->placeOrder();
        break;
        
    case 'index':
    default:
        redirect('/app/views/shoppingCart/checkout.php');
        break;
}
?>