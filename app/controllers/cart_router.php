<?php
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/shoppingCartController.php';

$controller = new shoppingCartController();
$action = req('action', 'index');

switch ($action) {
    case 'add':
        $controller->add();
        break;
        
    case 'update':
        $controller->update();
        break;
        
    case 'delete':
        $controller->delete();
        break;

    case 'delete_batch':
        $controller->deleteBatch();
        break;
        
    case 'count':
        $controller->count();
        break;
        
    case 'index':
    default:
        redirect('/app/views/shoppingCart/cart.php');
        break;
}
?>