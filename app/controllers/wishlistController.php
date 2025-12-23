<?php
require_once __DIR__ . '/../models/WishlistModel.php';

include __DIR__ . '/../helpers/request.php';

class wishlistController {
    private $model;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->model = new WishlistModel();
    }

    public function index() {
        if (!isset($_SESSION['customerId'])) {
            redirect('/app/views/security/signIn.php');
        }

        $customerId = $_SESSION['customerId'];
        $rawItems = $this->model->getWishlistItems($customerId);
        $wishlistItems = [];

        foreach ($rawItems as $item) {
            $rawImg = !empty($item['variant_img']) ? $item['variant_img'] : $item['main_img'];
            $catName = $item['category_name'] ?? '';
            $finalImg = $this->resolveImagePath($rawImg, $catName);
            $isDeleted = ($item['p_deleted'] ?? 0) == 1 || ($item['pv_deleted'] ?? 0) == 1;

            $wishlistItems[] = [
                'product_variant_id' => $item['product_variant_id'],
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'variant_name' => $item['variant_name'],
                'price' => $item['price'],
                'img_url' => $finalImg,
                'stock_qty' => (int)$item['stock_qty'],
                'is_deleted'   => $isDeleted
            ];
        }

        return $wishlistItems;
    }

    private function resolveImagePath($path, $categoryName = '') {
        $basePath = '/public/';
        if (empty($path)) return 'https://via.placeholder.com/300';
        if (str_starts_with($path, 'http')) return $path;
        if (str_contains($path, '/')) return $basePath . $path;
        
        $folder = !empty($categoryName) ? trim($categoryName) . '/' : '';
        return $basePath . 'images/' . $folder . $path;
    }

    public function deleteBatch() {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        if (!isset($_SESSION['customerId'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in']);
            exit;
        }

        $variantIds = $_POST['product_variant_ids'] ?? [];

        if (empty($variantIds) || !is_array($variantIds)) {
            echo json_encode(['success' => false, 'message' => 'No items selected']);
            exit;
        }

        $result = $this->model->removeBatchWishlistItems($_SESSION['customerId'], $variantIds);

        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        exit;
    }
    
    public function toggle() {
        while (ob_get_level()) ob_end_clean();
        header('Content-Type: application/json');

        if (!isset($_SESSION['customerId'])) {
            echo json_encode([
                'success' => false, 
                'message' => 'Login required',
                'code' => 'LOGIN_REQUIRED' 
            ]);
            exit;
        }

        $variantId = post('product_variant_id');
        if (!$variantId) {
            echo json_encode(['success' => false, 'message' => 'Missing ID']);
            exit;
        }

        $result = $this->model->toggleWishlist($_SESSION['customerId'], $variantId);

        if ($result === 'added') {
            echo json_encode([
                'success' => true, 
                'status' => 'added', 
                'message' => 'Added to Wishlist'
            ]);
        } elseif ($result === 'removed') {
            echo json_encode([
                'success' => true, 
                'status' => 'removed', 
                'message' => 'Removed from Wishlist'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database Error']);
        }
        exit;
    }
}
?>