<?php
require_once __DIR__ . '../../helpers/request.php'; 
require_once __DIR__ . '../../helpers/html.php';

require_once __DIR__ . '/../models/cartModel.php';
require_once __DIR__ . '/../models/orderModel.php';
require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/../models/addressModel.php';

class CheckoutController
{
    private $cartModel;
    private $userModel;
    private $orderModel;
    private $addressModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->cartModel = new cartModel();
        $this->userModel = new userModel();
        $this->orderModel = new orderModel();
        $this->addressModel = new addressModel();
    }

    public function getCheckoutData()
    {
        
        if (!isset($_SESSION['customerId'])) {
            redirect('/app/views/security/signIn.php');
            return [];
        }

        if (is_post()) { 
            $this->processCartPostData();
        } elseif (!isset($_SESSION['checkout_data'])) {
            redirect('/app/views/shoppingCart/cart.php');
            return [];
        }

        $data = $_SESSION['checkout_data'];
        $customerId = $_SESSION['customerId'];

        $currentUser = [];
        $defaultAddress = $this->addressModel->getDefaultAddress($customerId);
        $custProfile = $this->userModel->getCustomerDetails($customerId);

        if ($defaultAddress) {
            $currentUser['username'] = $defaultAddress['name'];
            $currentUser['phone'] = $defaultAddress['phone'];
            $currentUser['address'] = $defaultAddress['address'];
            $currentUser['address_id'] = $defaultAddress['id'];
        } elseif ($custProfile) {
            $currentUser['username'] = $custProfile['username'];
            $currentUser['phone'] = $custProfile['phone'];
            $currentUser['address'] = '';
            $currentUser['address_id'] = '';
        }

        $userPoints = $custProfile['rewardPoint'] ?? 0;

        $data['customer_info'] = $currentUser;
        $data['user_points'] = $userPoints;

        return $data;
    }
private function processCartPostData() {
        $selectedItems = post('selected_items') ?? [];
        $quantities = post('qty') ?? [];

        if (empty($selectedItems)) {
            temp('flash_warning', "Please select items.");
            redirect('/app/views/shoppingCart/cart.php');
            return;
        }

        $checkoutVariantIds = [];
        $checkoutQuantities = [];

        foreach ($selectedItems as $vid) {
            if (isset($quantities[$vid]) && (int)$quantities[$vid] > 0) {
                $checkoutVariantIds[] = $vid;
                $checkoutQuantities[$vid] = (int)$quantities[$vid];
            }
        }

        $dbItems = $this->cartModel->getGuestCartDetails($checkoutVariantIds);
        
        $checkoutItems = [];
        $subtotal = 0;
        $totalItemCount = 0;
        $imgBasePath = '/public/';

        foreach ($dbItems as $item) {
            $vid = $item['product_variant_id'];
            $qty = $checkoutQuantities[$vid] ?? 0;
            
            $rawImg = !empty($item['variant_img']) ? $item['variant_img'] : $item['main_img'];
            $catName = $item['category_name'] ?? '';
            
            if (empty($rawImg)) {
                $finalImg = 'https://via.placeholder.com/150';
            } elseif (str_starts_with($rawImg, 'http')) {
                $finalImg = $rawImg;
            } else {
                $filename = basename($rawImg);
                $folder = !empty($catName) ? trim($catName) . '/' : '';
                $finalImg = $imgBasePath . 'images/' . $folder . $filename;
            }

            $checkoutItems[] = [
                'variant_id'   => $vid,
                'product_name' => $item['product_name'] . ' (' . $item['variant_name'] . ')',
                'sale_price'   => $item['sale_price'],
                'quantity'     => $qty,
                'img_url'      => $finalImg,
                'unit_price'   => $item['sale_price'],
            ];

            $subtotal += $item['sale_price'] * $qty;
            $totalItemCount += $qty;
        }

        $shippingFee = 5.00;
        $taxPercentage = 6;
        $taxFee = $subtotal * ($taxPercentage / 100);
        $totalAmount = $subtotal + $shippingFee + $taxFee;

        $_SESSION['checkout_data'] = [
            'items' => $checkoutItems,
            'subtotal' => $subtotal,
            'shippingFee' => $shippingFee,
            'taxPercentage' => $taxPercentage,
            'taxFee' => $taxFee,
            'totalAmount' => $totalAmount,
            'totalItemCount' => $totalItemCount,
            'customer_info' => [],
        ];
        
        redirect('/app/views/shoppingCart/checkout.php');
    }

    public function placeOrder() {
        
        if (!is_post()) {
            redirect('/app/views/shoppingCart/cart.php');
            return;
        }

        $customerId = $_SESSION['customerId'];
        $addressId = post('address_id');
        $paymentMethod = post('payment_method');
        
        $sessionData = $_SESSION['checkout_data'] ?? [];
        if (empty($sessionData)) {
            redirect('/app/views/shoppingCart/cart.php');
            return;
        }

        $originalTotal = $sessionData['totalAmount'];
        $shippingFee = $sessionData['shippingFee'];
        $taxFee = $sessionData['taxFee'];
        $items = $sessionData['items'];

        $pointsRedeemed = (int)post('points_redeemed', 0);

        $discountAmount = 0;
        if ($pointsRedeemed > 0) {
            $discountAmount = $pointsRedeemed / 100;
        }

        $finalTotalAmount = $originalTotal - $discountAmount;
        
        if ($finalTotalAmount < 0) $finalTotalAmount = 0;

        if (empty($addressId)) {
            temp('flash_warning', 'Please select a shipping address before placing order.');
            $this->redirectBack();
            return;
        }

        $totalOrderQty = 0;
        foreach($items as $itm) $totalOrderQty += $itm['quantity'];

        $paymentId = $this->orderModel->createOrder(
            $customerId,
            $addressId,
            $items,
            $finalTotalAmount, 
            $shippingFee,
            $taxFee,
            $totalOrderQty,
            $pointsRedeemed,
            $paymentMethod
        );

        if ($paymentId) {
            unset($_SESSION['checkout_data']);
            redirect("/app/views/shoppingCart/payment.php?payment_id=" . $paymentId);
        } else {
            temp('flash_error', 'Order failed. Please try again.');
            $this->redirectBack();
        }
    }

    private function redirectBack() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/index.php';
        redirect($referer);
    }
}
?>