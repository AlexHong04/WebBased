<?php
require_once __DIR__ . '/../models/productModel.php';
require_once __DIR__ . '/../models/wishlistModel.php';
require_once __DIR__ . '/../helpers/request.php'; 

class productController
{
    const IMG_BASE_URL = '/public/';
    const REVIEW_IMG_BASE_PATH = '/public/images/review/';

    private function resolveImagePath($path, $categoryName = '')
    {
        if (empty($path)) return 'https://via.placeholder.com/300';
        
        if (preg_match('/^https?:\/\//i', $path)) return $path;

        $filenameOnly = basename($path);
        $categoryFolder = '';

        if (!empty($categoryName)) {
            $categoryFolder = trim($categoryName) . '/';
        }

        return self::IMG_BASE_URL . 'images/' . $categoryFolder . $filenameOnly;
    }

    public function getProductDetails($productId)
    {
        
        if (!$productId) {
            return null;
        }

        $model = new productModel();
        $product = $model->getProductById($productId);

        if (!$product) {
            return null;
        }

        // Get Category Name
        $categoryName = is_array($product) ? ($product['category_name'] ?? '') : ($product->category_name ?? '');

        // Resolve Main Image
        $rawMainImg = $product['img_url'] ?? $product['photo'] ?? '';
        
        $mainImgParts = explode(',', $rawMainImg);
        
        $firstMainImg = trim($mainImgParts[0]); 
        
        $product['img_url'] = empty($firstMainImg) ? '' : $this->resolveImagePath($firstMainImg, $categoryName);
        $product['display_img_url'] = $product['img_url'];

        // Process Gallery
        $photos = $model->getProductVariants($productId);
        $gallery = [];

        if (!empty($rawMainImg)) {
            foreach ($mainImgParts as $imgPart) {
                $cleanImgPart = trim($imgPart);
                if (!empty($cleanImgPart)) {
                    $resolvedMainPath = $this->resolveImagePath($cleanImgPart, $categoryName);
                    $gallery[] = [
                        'img_url' => $resolvedMainPath, 
                        'display_url' => $resolvedMainPath
                    ];
                }
            }
        }

        if (!empty($photos)) {
            foreach ($photos as $photo) {
                $pUrl = is_object($photo) ? ($photo->img_url ?? $photo->photo ?? '') : ($photo['img_url'] ?? $photo['photo'] ?? '');
                $resolvedUrl = $this->resolveImagePath($pUrl, $categoryName);
                
                if (is_object($photo)) { $photo->img_url = $resolvedUrl; } 
                else { $photo['img_url'] = $resolvedUrl; }
                
                $gallery[] = [
                    'product_variant_id' => is_object($photo) ? $photo->product_variant_id : $photo['product_variant_id'],
                    'img_url' => $resolvedUrl,
                    'display_url' => $resolvedUrl
                ];
            }
        }
        $product['gallery'] = $gallery;

        // Process Variants
        $variants = $model->getProductVariants($productId);
        $processedVariants = [];
        if (!empty($variants)) {
            foreach ($variants as $v) {
                $vRaw = is_object($v) ? ($v->variant_img ?? $v->photo ?? '') : ($v['variant_img'] ?? $v['photo'] ?? '');
                $resolvedVImg = $this->resolveImagePath($vRaw, $categoryName);

                $vArray = is_object($v) ? (array)$v : $v;
                $vArray['variant_img'] = $resolvedVImg;
                $vArray['display_variant_img'] = $resolvedVImg; 
                
                $processedVariants[] = $vArray;
            }
        }
        $product['variants'] = $processedVariants;
        
        // Calculate Total Stock
        $totalStock = 0;
        if (!empty($product['variants'])) { 
            foreach ($product['variants'] as $v) {
                $qty = $v['stock_qty'];
                $totalStock += $qty;
            }
        }
        $product['total_stock'] = $totalStock;

        // Reviews
        $stats = $model->getProductGlobalStats($productId);
        $product['review_summary'] = [
            'total_reviews' => is_object($stats) ? $stats->total_reviews : ($stats['total_reviews'] ?? 0),
            'average_rating' => is_object($stats) ? $stats->avg_rating : ($stats['avg_rating'] ?? 0)
        ];

        // Process Reviews Images
        $rawReviews = $model->getProductReviews($productId);
        $processedReviews = [];

        if (!empty($rawReviews)) {
            foreach ($rawReviews as $r) {
                $r = is_object($r) ? (array)$r : $r;
                $imgFilename = $r['img_url'] ?? '';
                $reviewPhotos = []; 

                if (!empty($imgFilename)) {
                    $fullPath = self::REVIEW_IMG_BASE_PATH . $imgFilename;
                    $reviewPhotos[] = $fullPath;
                }

                $r['processed_photos'] = $reviewPhotos;
                $processedReviews[] = $r;
            }
        }
        $product['reviews'] = $processedReviews;

        // Related Products
        $catId = is_array($product) ? ($product['category_id'] ?? null) : ($product->category_id ?? null);
        
        if ($catId) {
            $related = $model->getRelatedProducts($catId, $productId);
            $processedRelated = [];
            foreach ($related as $r) {
                $r = is_object($r) ? (array)$r : $r;
                $rRawImg = $r['img_url'] ?? ''; 
                $rCatName = $r['category_name'] ?? $categoryName;
                $rParts = explode(',', $rRawImg);
                $rFirst = trim($rParts[0]);
                
                $r['img_url'] = $this->resolveImagePath($rFirst, $rCatName);
                
                $processedRelated[] = $r;
            }
            $product['related_products'] = $processedRelated;
        } else {
            $product['related_products'] = [];
        }

        // Wishlist Status
        $isWishlisted = false;
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['customerId'])) {
            $wishlistModel = new WishlistModel();
            
            $defaultVariantId = null;
            if (!empty($processedVariants)) {
                $firstV = $processedVariants[0];
                $defaultVariantId = $firstV['product_variant_id'];
            }

            if ($defaultVariantId) {
                $isWishlisted = $wishlistModel->isItemInWishlist($_SESSION['customerId'], $defaultVariantId);
            }
        }
        
        $product['is_wishlisted'] = $isWishlisted;
        $product['is_wishlisted_default'] = $isWishlisted;

        return $product;
    }
}
?>