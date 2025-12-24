<?php
error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/addressController.php';
require_once __DIR__ . '/../helpers/request.php';

$controller = new addressController();
$action = req('action');


switch ($action) {
    case 'list':
        $controller->listAddresses();
        break;
    case 'get':
        $controller->getAddress();
        break;
    case 'add':
    case 'update':
    case 'save':
        $controller->saveAddress();
        break;
    case 'delete':
        $controller->deleteAddress();
        break;
    case 'set_default':
        $controller->setDefault();
        break;
    default:
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid Action']);
        exit;
}
