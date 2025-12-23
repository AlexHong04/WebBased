<?php
require_once __DIR__ . '/../models/CartModel.php';
require_once __DIR__ . '/../helpers/request.php';

class shoppingCartController
{
    private $cartModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->cartModel = new CartModel();
    }

    //orepares data for the main Cart View page
    // app/controllers/shoppingCartController.php

    public function getCartData()
    {
        if (!defined('IMG_BASE_PATH')) define('IMG_BASE_PATH', '/public/');

        $deliveryFee = 5.00;
        $cartItems = [];
        $dbItems = [];

        if (isset($_SESSION['customerId'])) {
            $customerId = $_SESSION['customerId'];
            $dbItems = $this->cartModel->getMemberCartDetails($customerId);
        }

        if (!empty($dbItems)) {
            foreach ($dbItems as $item) {
                // Determine which image to use (Variant specific or Main)
                $rawImg = !empty($item['variant_img']) ? $item['variant_img'] : $item['main_img'];
                $catName = $item['category_name'] ?? '';
                $finalImg = $this->resolveCartImage($rawImg, $catName);
                $isDeleted = ($item['p_deleted'] ?? 0) == 1 || ($item['pv_deleted'] ?? 0) == 1;

                $cartItems[] = [
                    'product_id'   => $item['product_variant_id'],
                    'product_name' => $item['product_name'] . ' (' . $item['variant_name'] . ')',
                    'price'        => $item['sale_price'],
                    'quantity'     => $item['quantity'],
                    'stock_qty'    => $item['stock_qty'],
                    'img_url'      => $finalImg,
                    'is_deleted'   => $isDeleted  
                ];
            }
        }

        // Sort: Out of Stock items go to the bottom
        usort($cartItems, function ($a, $b) {
            if ($a['is_deleted'] != $b['is_deleted']) {
                return $a['is_deleted'] ? 1 : -1;
            }

            $stockA = $a['stock_qty'] > 0 ? 1 : 0;
            $stockB = $b['stock_qty'] > 0 ? 1 : 0;
            if ($stockA !== $stockB) return $stockB - $stockA;
            return 0;
        });

        return [
            'cartItems'   => $cartItems,
            'isEmpty'     => empty($cartItems),
            'deliveryFee' => $deliveryFee,
            'title'       => "My Shopping Cart | Lovine",
            'pageCSS'     => "cart.css"
        ];
    }

    //format image paths correctly
    private function resolveCartImage($dbPath, $categoryName = '')
    {
        if (empty($dbPath)) return 'https://via.placeholder.com/150';
        if (str_starts_with($dbPath, 'http')) return $dbPath;

        if (str_contains($dbPath, ',')) {
            $parts = explode(',', $dbPath);
            $dbPath = trim($parts[0]);
        }

        if (str_contains($dbPath, '/')) return IMG_BASE_PATH . $dbPath;
        $categoryFolder = !empty($categoryName) ? trim($categoryName) . '/' : '';
        return IMG_BASE_PATH . 'images/' . $categoryFolder . $dbPath;
    }

    //Add item to cart
    public function add()
    {
        if (is_post()) {
            if (!isset($_SESSION['customerId'])) {
                temp('flash_login_required', 'You must log in to add items to your cart.');
                $this->redirectBack();
                return;
            }

            $variantId = post('product_variant_id');
            $quantity  = (int)post('quantity', 1);

            if (!$variantId || $quantity <= 0) {
                $this->redirectBack();
            }

            // Stock Validation
            $stockQty = $this->cartModel->getProductStock($variantId);
            $cartId = $this->cartModel->getOrCreateCart($_SESSION['customerId']);
            $currentInCart = $this->cartModel->getCartItemQty($cartId, $variantId);

            $allowedQty = $stockQty - $currentInCart;

            if ($allowedQty <= 0) {
                temp('flash_error', "Max limit reached ($stockQty). You cannot add more.");
                $this->redirectBack();
                return;
            }

            // adjust quantity if requested exceeds available
            $finalAddQty = $quantity;
            if ($quantity > $allowedQty) {
                $finalAddQty = $allowedQty;
                temp('flash_warning', "Stock limited. Quantity adjusted to maximum available ($stockQty).");
            } else {
                temp('flash_success', "Successfully added to cart!");
            }

            $this->cartModel->addCartItem($cartId, $variantId, $finalAddQty);

            temp('keep_variant_id', $variantId);
            $this->redirectBack();
        }
    }

    //when user changes quantity input in cart
    public function update()
    {
        while (ob_get_level()) ob_end_clean(); // Clear buffer

        if (is_post()) {
            $variantId = post('product_variant_id');
            $quantity  = (int)post('quantity', 0);

            if (!$variantId || $quantity <= 0) {
                echo "Error: Invalid Data";
                exit;
            }

            $cartId = $this->cartModel->getOrCreateCart($_SESSION['customerId']);
            if (!$cartId) {
                echo "Error: Cart ID not found";
                exit;
            }

            $result = $this->cartModel->updateCartItemQty($cartId, $variantId, $quantity);
            echo $result ? "Success" : "Error: Database Update Failed";
            exit;
        }
    }

    //Delete Single Item
    public function delete()
    {
        while (ob_get_level()) ob_end_clean();

        if (is_post()) {
            if (!isset($_SESSION['customerId'])) {
                echo "Error: Login Required";
                exit;
            }

            $variantId = post('product_variant_id');

            if (!$variantId) {
                echo "Error: Missing ID";
                exit;
            }

            $cartId = $this->cartModel->getOrCreateCart($_SESSION['customerId']);
            if ($this->cartModel->removeCartItem($cartId, $variantId)) {
                echo "Success";
            } else {
                echo "Error: DB Delete Failed";
            }
            exit;
        }
    }

    //Batch Delete Items
    public function deleteBatch()
    {
        while (ob_get_level()) ob_end_clean();

        if (is_post()) {
            if (!isset($_SESSION['customerId'])) {
                echo "Error: Login Required";
                exit;
            }

            $variantIds = isset($_POST['product_variant_ids']) ? $_POST['product_variant_ids'] : [];

            if (empty($variantIds) || !is_array($variantIds)) {
                echo "Error: No items selected";
                exit;
            }

            $cartId = $this->cartModel->getOrCreateCart($_SESSION['customerId']);
            if ($this->cartModel->removeBatchCartItems($cartId, $variantIds)) {
                echo "Success";
            } else {
                echo "Error: DB Delete Failed";
            }
            exit;
        }
    }

    //Get Cart Count(updating the cart badge in the header dynamically)
    public function count()
    {
        while (ob_get_level()) ob_end_clean();

        $count = 0;
        if (isset($_SESSION['customerId'])) {
            $count = $this->cartModel->getCartCount($_SESSION['customerId']);
        }

        echo $count;
        exit;
    }

    private function redirectBack()
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/app/views/home.php';
        redirect($referer);
    }
}
