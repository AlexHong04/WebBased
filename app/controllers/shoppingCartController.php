<?php
require_once __DIR__ . '/../models/CartModel.php';
require_once __DIR__ . '/../helpers/request.php'; 

class shoppingCartController {
    private $cartModel;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
             session_start();
        }
        $this->cartModel = new CartModel();
    }

    public function getCartData() {
        if (!defined('IMG_BASE_PATH')) define('IMG_BASE_PATH', '/public/');
        
        $deliveryFee = 5.00;
        $cartItems = [];
        $dbItems = [];

        if (isset($_SESSION['customerId'])) {
            $customerId = $_SESSION['customerId'];
            $dbItems = $this->cartModel->getMemberCartDetails($customerId);
        } else {
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                $variantIds = array_keys($_SESSION['cart']);
                $dbItems = $this->cartModel->getGuestCartDetails($variantIds);
                foreach ($dbItems as &$item) {
                    $vid = $item['product_variant_id'];
                    if (isset($_SESSION['cart'][$vid])) {
                        $item['quantity'] = $_SESSION['cart'][$vid];
                    }
                }
            }
        }

        if (!empty($dbItems)) {
            foreach ($dbItems as $item) {
                $rawImg = !empty($item['variant_img']) ? $item['variant_img'] : $item['main_img'];
                $catName = $item['category_name'] ?? '';
                $finalImg = $this->resolveCartImage($rawImg, $catName);

                $cartItems[] = [
                    'product_id'   => $item['product_variant_id'],
                    'product_name' => $item['product_name'] . ' (' . $item['variant_name'] . ')',
                    'price'        => $item['sale_price'],
                    'quantity'     => $item['quantity'],
                    'stock_qty'    => $item['stock_qty'],
                    'img_url'      => $finalImg
                ];
            }
        }

        usort($cartItems, function ($a, $b) {
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

    private function resolveCartImage($dbPath, $categoryName = '') {
        if (empty($dbPath)) return 'https://via.placeholder.com/150';
        if (str_starts_with($dbPath, 'http')) return $dbPath;
        if (str_contains($dbPath, '/')) return IMG_BASE_PATH . $dbPath;
        $categoryFolder = !empty($categoryName) ? trim($categoryName) . '/' : '';
        return IMG_BASE_PATH . 'images/' . $categoryFolder . $dbPath;
    }

    public function add() {
        if (is_post()) {
            $variantId = post('product_variant_id');
            $quantity  = (int)post('quantity', 1);

            if (!$variantId || $quantity <= 0) {
                $this->redirectBack();
            }

            $stockQty = $this->cartModel->getProductStock($variantId);
            $currentInCart = 0;

            if (isset($_SESSION['customerId'])) {
                $cartId = $this->cartModel->getOrCreateCart($_SESSION['customerId']);
                $currentInCart = $this->cartModel->getCartItemQty($cartId, $variantId);
            } else {
                if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
                $currentInCart = $_SESSION['cart'][$variantId] ?? 0;
            }

            $allowedQty = $stockQty - $currentInCart;

            if ($allowedQty <= 0) {
                temp('flash_error', "Max limit reached ($stockQty). You cannot add more.");
                $this->redirectBack();
                return;
            }

            $finalAddQty = $quantity;
            if ($quantity > $allowedQty) {
                $finalAddQty = $allowedQty;
                temp('flash_warning', "Stock limited. Quantity adjusted to maximum available ($stockQty).");
            } else {
                temp('flash_success', "Successfully added to cart!");
            }

            if (isset($_SESSION['customerId'])) {
                $this->cartModel->addCartItem($cartId, $variantId, $finalAddQty);
            } else {
                if (isset($_SESSION['cart'][$variantId])) {
                    $_SESSION['cart'][$variantId] += $finalAddQty;
                } else {
                    $_SESSION['cart'][$variantId] = $finalAddQty;
                }
            }
            temp('keep_variant_id', $variantId);
            $this->redirectBack();
        }
    }

    public function update() {
        while (ob_get_level()) ob_end_clean(); 

        if (is_post()) {
            $variantId = post('product_variant_id');
            $quantity  = (int)post('quantity', 0);

            if (!$variantId || $quantity <= 0) {
                echo "Error: Invalid Data";
                exit;
            }

            if (isset($_SESSION['customerId'])) {
                $cartId = $this->cartModel->getOrCreateCart($_SESSION['customerId']);
                if (!$cartId) {
                    echo "Error: Cart ID not found"; 
                    exit;
                }
                
                $result = $this->cartModel->updateCartItemQty($cartId, $variantId, $quantity);
                echo $result ? "Success" : "Error: Database Update Failed";
            } else {
                if (isset($_SESSION['cart'][$variantId])) {
                    $_SESSION['cart'][$variantId] = $quantity;
                    echo "Success";
                } else {
                    echo "Error: Session Item Not Found";
                }
            }
            exit;
        }
    }

    public function delete() {
        while (ob_get_level()) ob_end_clean();

        if (is_post()) {
            $variantId = post('product_variant_id');

            if (!$variantId) {
                echo "Error: Missing ID";
                exit;
            }
            
            if (isset($_SESSION['customerId'])) {
                $cartId = $this->cartModel->getOrCreateCart($_SESSION['customerId']);
                if ($this->cartModel->removeCartItem($cartId, $variantId)) {
                    echo "Success";
                } else {
                    echo "Error: DB Delete Failed";
                }
            } else {
                if (isset($_SESSION['cart'][$variantId])) {
                    unset($_SESSION['cart'][$variantId]);
                    echo "Success";
                } else {
                    echo "Error: Item not in session";
                }
            }
            exit;
        }
    }

    public function deleteBatch() {
        while (ob_get_level()) ob_end_clean();

        if (is_post()) {
            $variantIds = isset($_POST['product_variant_ids']) ? $_POST['product_variant_ids'] : [];

            if (empty($variantIds) || !is_array($variantIds)) {
                echo "Error: No items selected";
                exit;
            }

            if (isset($_SESSION['customerId'])) {
                $cartId = $this->cartModel->getOrCreateCart($_SESSION['customerId']);
                if ($this->cartModel->removeBatchCartItems($cartId, $variantIds)) {
                    echo "Success";
                } else {
                    echo "Error: DB Delete Failed";
                }
            } else {
                // Session Guest Cart
                foreach ($variantIds as $vid) {
                    if (isset($_SESSION['cart'][$vid])) {
                        unset($_SESSION['cart'][$vid]);
                    }
                }
                echo "Success";
            }
            exit;
        }
    }

    public function count() {
        while (ob_get_level()) ob_end_clean();

        $count = 0;
        if (isset($_SESSION['customerId'])) {
            $count = $this->cartModel->getCartCount($_SESSION['customerId']);
        } else {
            $count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
        }
        
        echo $count;
        exit;
    }
    
    private function redirectBack() {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/index.php';
        redirect($referer);
    }
}