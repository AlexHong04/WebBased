<?php
require_once __DIR__ . '/../models/AddressModel.php';
require_once __DIR__ . '../../helpers/request.php'; 

class AddressController {
    private $addressModel;
    private $customerId;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->customerId = $_SESSION['customerId'] ?? null;
        if (!$this->customerId) {
            $this->sendResponse(false, "Authentication required.");
        }

        try {
            $this->addressModel = new AddressModel();
        } catch (Exception $e) {
            $this->sendResponse(false, "DB Init Error: " . $e->getMessage());
        }
    }

    public function listAddresses() {
        try {
            $addresses = $this->addressModel->getCustomerAddresses($this->customerId);
            $this->sendResponse(true, "Loaded.", ['addresses' => $addresses]);
        } catch (Exception $e) {
            $this->sendResponse(false, "Error: " . $e->getMessage());
        }
    }

    public function getAddress() {
        $id = get('id');
        if (!$id) $this->sendResponse(false, "Missing ID.");

        $address = $this->addressModel->getAddressById($this->customerId, $id);
        if ($address) {
            $this->sendResponse(true, "Loaded.", $address);
        } else {
            $this->sendResponse(false, "Not found.");
        }
    }

    public function saveAddress() {
        if (!is_post()) $this->sendResponse(false, "Invalid Method.");

        $id = post('address_id');
        $name = post('ReceiverName');
        $phone = post('phoneNumber');
        $street = post('street_line');
        $city = post('City');
        $state = post('State');
        $postcode = post('postCode');
        // $name = post('name');
        // $phone = post('phone');
        // $street = post('street_line');
        // $city = post('city');
        // $state = post('state');
        // $postcode = post('postcode');

        if (empty($name) || empty($phone) || empty($street) || empty($city) || empty($state) || empty($postcode)) {
            $this->sendResponse(false, "All fields are required.");
        }

        try {
            $resultId = $this->addressModel->saveAddress($this->customerId, $id, $name, $phone, $street, $city, $state, $postcode);
            
            if ($resultId) {
                $savedAddr = $this->addressModel->getAddressById($this->customerId, $resultId);
                $this->sendResponse(true, "Saved.", $savedAddr);
            } else {
                $this->sendResponse(false, "Save Failed.");
            }
        } catch (Exception $e) {
            $this->sendResponse(false, "Error: " . $e->getMessage());
        }
    }

    public function deleteAddress() {
        if (!is_post()) $this->sendResponse(false, "Invalid Method.");
        
        $id = post('address_id');
        if (!$id) $this->sendResponse(false, "Missing ID.");

        $addr = $this->addressModel->getAddressById($this->customerId, $id);
        if ($addr && $addr['is_default'] == 1) {
            $this->sendResponse(false, "Cannot delete default address.");
        }

        if ($this->addressModel->deleteAddress($this->customerId, $id)) {
            $this->sendResponse(true, "Deleted.");
        } else {
            $this->sendResponse(false, "Delete Failed.");
        }
    }

    private function sendResponse($success, $message = '', $data = []) {
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
        exit;
    }
}
?>