<?php
require_once __DIR__ . '/wishlistController.php';
require_once __DIR__ . '/../helpers/request.php';

$controller = new wishlistController();
$action = req('action', 'index');

switch ($action) {
    case 'toggle':
        $controller->toggle();
        break;
        
    case 'delete_batch': 
        $controller->deleteBatch();
        break;

    case 'index':
    default:
        redirect('/app/views/userProfile/wishlist.php');
        break;
}
?>