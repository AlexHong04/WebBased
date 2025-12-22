<?php
require_once __DIR__ . '/../models/productsModel.php';
require_once __DIR__ . '/../models/wishlistModel.php';
require_once __DIR__ . '/../helpers/request.php';
require_once __DIR__ . '/../helpers/mail.php';
require_once __DIR__ . '/../lib/TwilioSMS.php';

class ProductsController
{
    private $productModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
    }

    public function showAddProductForm()
    {

        $rawCategories = $this->productModel->getAllCategories();
        $rawVariants = $this->productModel->getAllVariants();
        $newProductId = $this->productModel->generateId('product', 'product_id', 'PR');

        $categories = [];
        foreach ($rawCategories as $cat) {
            $categories[$cat['category_id']] = $cat['category_name'];
        }

        $variants = [];
        foreach ($rawVariants as $var) {
            $variants[$var['variant_id']] = $var['variant_name'];
        }

        return [
            'categories' => $categories,
            'variants' => $variants,
            'newProductId' => $newProductId,
        ];
    }

    public function validateProductForm($postData, $filesData)
    {
        $errors = [];

        if (empty($postData['product_name'])) {
            $errors['product_name'] = "Product name is required.";
        }

        if (empty($postData['description'])) {
            $errors['description'] = "Product description is required.";
        }

        if (empty($postData['category_id'])) {
            $errors['category_id'] = "Please select a category.";
        }

        $cost_price = $postData['cost_price'] ?? 0;
        if (!is_numeric($cost_price) || $cost_price <= 0) {
            $errors['cost_price'] = "Invalid cost price.";
        }

        $sales_price = $postData['sales_price'] ?? 0;
        if (!is_numeric($sales_price) || $sales_price <= 0) {
            $errors['sales_price'] = "Invalid sales price.";
        }

        if (!empty($sales_price) && !empty($cost_price)) {
            if ($sales_price < $cost_price) {
                $errors['sales_price'] = "Sales price cannot be less than cost price.";
            }
        }

        if (isset($postData['variant_ids']) && is_array($postData['variant_ids'])) {
            $variantCount = count($postData['variant_ids']);

            for ($i = 0; $i < $variantCount; $i++) {
                $variant_id = $postData['variant_ids'][$i] ?? null;
                $min_stock = $postData['min_stock_levels'][$i] ?? null;
                $stock_qty = $postData['stock_qtys'][$i] ?? null;

                if (empty($variant_id)) {
                    $errors["variant_ids[{$i}]"] = "Please select a variant for variant " . ($i + 1) . ".";
                }

                if (!is_numeric($min_stock) || $min_stock < 0 || intval($min_stock) != $min_stock) {
                    $errors["min_stock_levels[{$i}]"] = "Invalid minimum stock level for variant " . ($i + 1) . ".";
                } elseif ($min_stock < 1 || $min_stock > 20) {
                    $errors["min_stock_levels[{$i}]"] = "Minimum stock must be between 1 and 20 for variant " . ($i + 1) . ".";
                }

                if (!is_numeric($stock_qty) || $stock_qty < 0 || intval($stock_qty) != $stock_qty) {
                    $errors["stock_qtys[{$i}]"] = "Invalid stock quantity for variant " . ($i + 1) . ".";
                } elseif ($stock_qty < 1 || $stock_qty > 50) {
                    $errors["stock_qtys[{$i}]"] = "Stock quantity must be between 1 and 50 for variant " . ($i + 1) . ".";
                }
            }
        } else {
            $errors['variant_ids'] = "At least one variant is required.";
        }

        if (isset($filesData['product_images']) && empty($filesData['product_images']['name'][0])) {
            $errors['product_images'] = "Please upload at least one product image.";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    public function addProduct()
    {
        $productId = $this->productModel->generateId('product', 'product_id', 'PR');
        $categoryId = $_POST['category_id'];
        $category = $this->productModel->getCategoryById($categoryId);
        $categoryName = $category['category_name'] ?? 'uncategorized';
        $categoryNameSafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $categoryName);

        $categoryFolder = "../../../public/images/$categoryNameSafe/";
        if (!is_dir($categoryFolder)) {
            mkdir($categoryFolder, 0777, true);
        }

        $productImages = [];
        if (!empty($_FILES['product_images']['name'][0])) {
            foreach ($_FILES['product_images']['name'] as $i => $name) {
                if (empty($name)) continue;

                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $newName = $productId . "_img" . ($i + 1) . "." . $ext;
                $destination = $categoryFolder . $newName;

                if (move_uploaded_file($_FILES['product_images']['tmp_name'][$i], $destination)) {
                    $productImages[] = $newName;
                }
            }
        }

        $variantsData = [];
        if (!empty($_POST['variant_ids'])) {
            foreach ($_POST['variant_ids'] as $i => $vid) {
                $minStock = $_POST['min_stock_levels'][$i] ?? 1;
                $stockQty = $_POST['stock_qtys'][$i] ?? 1;
                $stockStatus = ($stockQty == 0) ? 'out_of_stock' : (($stockQty <= $minStock) ? 'low_stock' : 'in_stock');
                $variantId = $this->productModel->generateId('product_variant', 'product_variant_id', 'PV');

                $imgFileName = null;
                // Check if a file was uploaded for THIS specific variant index
                if (!empty($_FILES['variant_images_group']['name'][$i])) {
                    $ext = pathinfo($_FILES['variant_images_group']['name'][$i], PATHINFO_EXTENSION);
                    $imgFileName = $variantId . "." . $ext;
                    $destination = $categoryFolder . $imgFileName;
                    move_uploaded_file($_FILES['variant_images_group']['tmp_name'][$i], $destination);
                }

                // Single array entry per variant
                $variantsData[] = [
                    'product_variant_id' => $variantId,
                    'min_stock_level' => $minStock,
                    'stock_qty' => $stockQty,
                    'stock_status' => $stockStatus,
                    'variant_id' => $vid,
                    'product_id' => $productId,
                    'img_url' => $imgFileName
                ];
            }
        }

        $productData = [
            'product_id' => $productId,
            'product_name' => $_POST['product_name'],
            'description' => $_POST['description'],
            'cost_price' => $_POST['cost_price'],
            'sale_price' => $_POST['sales_price'],
            'category_id' => $categoryId,
            'img_url' => implode(',', $productImages)
        ];

        try {
            $this->productModel->addProduct([
                'product' => $productData,
                'variants' => $variantsData
            ]);

            $_SESSION['success_message'] = "Product and variants added successfully!";
            header("Location: productList.php");
            exit();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error saving product: " . $e->getMessage();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }


    public function getAllCategories()
    {
        return $this->productModel->getAllCategories();
    }

    public function getAllProducts(
        $sort = 'product_id',
        $order = 'asc'
    ) {
        $rawProducts = $this->productModel->getAllProducts($sort, $order);
        $categories = $this->getAllCategories();

        $category_lookup = [];
        if (is_array($categories)) {
            foreach ($categories as $category) {
                $category_lookup[$category['category_id']] = $category['category_name'];
            }
        }

        $enrichedProducts = [];
        if (is_array($rawProducts)) {
            foreach ($rawProducts as $product) {
                $category_id = $product['category_id'] ?? null;

                $product['category_name'] = $category_lookup[$category_id] ?? 'N/A';

                $enrichedProducts[] = $product;
            }
        }

        return $enrichedProducts;
    }

    function get_sort_link($column_name, $current_sort, $current_order)
    {
        if ($column_name === $current_sort) {
            $new_order = ($current_order === 'asc') ? 'desc' : 'asc';
        } else {
            $new_order = 'asc';
        }

        $icon = '';
        if ($column_name === $current_sort) {
            $icon = ($current_order === 'asc') ? ' <i class="fa-solid fa-arrow-up-long"></i>' : ' <i class="fa-solid fa-arrow-down-long"></i>';
        }

        $url = "?sort={$column_name}&order={$new_order}";

        return [
            'url' => $url,
            'icon' => $icon
        ];
    }

    public function getAllProductVariant(
        $sort = 'product_variant_id',
        $order = 'asc',
        $filter = 'lowStock'
    ) {

        if ($filter === 'countLowStock') {
            return $this->productModel->getProductVariantsByFilter('countLowStock', $sort, $order);
        }
        if ($filter === 'countOutOfStock') {
            return $this->productModel->getProductVariantsByFilter('countOutOfStock', $sort, $order);
        }
        if ($filter === 'countAll') {
            return $this->productModel->getProductVariantsByFilter('countAll', $sort, $order);
        }
        $productVariant = $this->productModel->getProductVariantsByFilter($filter, $sort, $order);

        $products = $this->productModel->getAllProducts();
        $variants = $this->productModel->getAllVariants();
        $categories = $this->productModel->getAllCategories();

        $product_lookup = [];
        if (is_array($products)) {
            foreach ($products as $pro) {
                $product_lookup[$pro['product_id']] = $pro['product_name'];
            }
        }

        $variant_lookup = [];
        if (is_array($variants)) {
            foreach ($variants as $var) {
                $variant_lookup[$var['variant_id']] = $var['variant_name'];
            }
        }

        $category_lookup = [];
        if (is_array($categories)) {
            foreach ($categories as $category) {
                $category_lookup[$category['category_id']] = $category['category_name'];
            }
        }

        // Enrich productVariant list
        $enrichedProducts = [];
        if (is_array($productVariant)) {
            foreach ($productVariant as $proVariant) {
                $product_id = $proVariant['product_id'] ?? null;
                $variant_id = $proVariant['variant_id'] ?? null;

                $proVariant['product_name'] = $product_lookup[$product_id] ?? 'N/A';
                $proVariant['variant_name'] = $variant_lookup[$variant_id] ?? 'N/A';

                $category_id = null;
                foreach ($products as $p) {
                    if ($p['product_id'] === $product_id) {
                        $category_id = $p['category_id'] ?? null;
                        break;
                    }
                }

                $proVariant['category_name'] = $category_lookup[$category_id] ?? 'N/A';

                $enrichedProducts[] = $proVariant;
            }
        }

        return $enrichedProducts;
    }

    public function getProductById($id)
    {
        if (empty($id)) {
            return null;
        }

        $product = $this->productModel->getProductById($id);

        if (!$product) {
            return null;
        }

        $category = $this->productModel->getCategoryById($product['category_id']);
        $product['category_name'] = $category['category_name'] ?? 'N/A';

        return $product;
    }

    public function getProductVariantsByProductId($productId)
    {
        if (empty($productId)) {
            return [];
        }

        $variants = $this->productModel->getProductVariantsByProductId($productId);

        if (!is_array($variants)) {
            return [];
        }

        $allVariants = $this->productModel->getAllVariants();
        $variant_lookup = [];

        foreach ($allVariants as $v) {
            $variant_lookup[$v['variant_id']] = $v['variant_name'];
        }

        foreach ($variants as &$variant) {
            $variantId = $variant['variant_id'] ?? null;
            $variant['variant_name'] = $variant_lookup[$variantId] ?? 'N/A';
        }

        return $variants;
    }
    public function handleDeleteProduct()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product']) && isset($_POST['final_deletion'])) {

            $productId = $_POST['product_id'] ?? '';

            if (empty($productId)) {
                $_SESSION['error_message'] = "Product ID is required.";
                header("Location: addSingleProduct.php");
                exit();
            }

            try {
                $result = $this->productModel->deleteProductById($productId);

                if ($result['success']) {
                    $_SESSION['success_message'] = $result['message'];

                    // Also delete image files
                    $product = $this->getProductById($productId);
                    if ($product && !empty($product['img_url'])) {
                        $images = explode(',', $product['img_url']);
                        $categoryName = $product['category_name'] ?? '';
                        $categoryFolder = "../../../public/images/" . $categoryName . "/";

                        foreach ($images as $img) {
                            $file = $categoryFolder . trim($img);
                            if (file_exists($file)) {
                                unlink($file);
                            }
                        }
                    }

                    // Delete variant images
                    if (isset($result['variant_images'])) {
                        foreach ($result['variant_images'] as $img) {
                            if (!empty($img)) {
                                $file = $categoryFolder . trim($img);
                                if (file_exists($file)) {
                                    unlink($file);
                                }
                            }
                        }
                    }
                } else {
                    $_SESSION['error_message'] = $result['message'];
                }
            } catch (Exception $e) {
                $_SESSION['error_message'] = "Error deleting product: " . $e->getMessage();
            }

            header("Location: productList.php");
            exit();
        }
    }

    public function submitProductForm()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            if (isset($_POST['delete_product']) && isset($_POST['final_deletion'])) {
                $productId = $_POST['product_id'] ?? '';

                if (empty($productId)) {
                    $_SESSION['error_message'] = "Product ID is required.";
                    header("Location: addSingleProduct.php");
                    exit();
                }

                try {
                    $this->handleDeleteProduct();
                    exit();
                } catch (Exception $e) {
                    $_SESSION['error_message'] = "Error deleting product: " . $e->getMessage();
                    header("Location: addSingleProduct.php?product_id=" . $productId);
                    exit();
                }
            }

            if (isset($_POST['final_submission'])) {

                $isEdit = !empty($_POST['is_edit']) && $_POST['is_edit'] === "1";

                if ($isEdit) {
                    $result = $this->updateProductForm();
                } else {
                    $result = $this->addProduct();
                }

                if (
                    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
                ) {
                    header('Content-Type: application/json');
                    echo json_encode($result);
                } else {
                    if ($result['success']) {
                        $_SESSION['success_message'] = $result['message'] ?? 'Operation completed successfully.';

                        if ($isEdit) {
                            header("Location: addSingleProduct.php?product_id=" . $_POST['product_id'] . "&updated=1");
                        } else {
                            header("Location: addSingleProduct.php");
                        }
                    } else {
                        $_SESSION['error_message'] = $result['message'] ?? 'An error occurred.';
                        $_SESSION['form_errors'] = $result['errors'] ?? [];
                        $_SESSION['form_data'] = $_POST;

                        if ($isEdit && !empty($_POST['product_id'])) {
                            header("Location: addSingleProduct.php?product_id=" . $_POST['product_id']);
                        } else {
                            header("Location: addSingleProduct.php");
                        }
                    }
                }
                exit();
            }
        }
    }

    public function updateProductForm()
    {
        try {
            $productId = $_POST['product_id'];

            if (empty($productId)) {
                return [
                    'success' => false,
                    'message' => "Product ID is required.",
                    'errors' => ['product_id' => 'Product ID is required.']
                ];
            }

            $categoryId = $_POST['category_id'];
            $category = $this->productModel->getCategoryById($categoryId);
            $categoryName = $category['category_name'] ?? 'uncategorized';
            $categoryNameSafe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $categoryName);

            $categoryFolder = "../../../public/images/$categoryNameSafe/";
            if (!is_dir($categoryFolder)) {
                mkdir($categoryFolder, 0777, true);
            }

            $productImages = [];

            if (isset($_POST['product_images_existing'])) {
                if (is_array($_POST['product_images_existing'])) {
                    foreach ($_POST['product_images_existing'] as $existingString) {
                        if (!empty($existingString) && is_string($existingString)) {
                            $images = explode(',', $existingString);
                            $productImages = array_merge($productImages, array_filter($images));
                        }
                    }
                } elseif (is_string($_POST['product_images_existing']) && !empty($_POST['product_images_existing'])) {
                    $images = explode(',', $_POST['product_images_existing']);
                    $productImages = array_filter($images);
                }
            }

            if (!empty($_FILES['product_images']['name'][0])) {
                foreach ($_FILES['product_images']['name'] as $i => $name) {
                    if (empty($name)) continue;

                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $newName = $productId . "_img" . (count($productImages) + 1) . "." . $ext;
                    $destination = $categoryFolder . $newName;

                    if (move_uploaded_file($_FILES['product_images']['tmp_name'][$i], $destination)) {
                        $productImages[] = $newName;
                    }
                }
            }

            $variantsData = [];
            if (!empty($_POST['variant_ids']) && is_array($_POST['variant_ids'])) {
                foreach ($_POST['variant_ids'] as $i => $vid) {
                    if (empty($vid)) continue;

                    $minStock = $_POST['min_stock_levels'][$i] ?? 1;
                    $stockQty = $_POST['stock_qtys'][$i] ?? 1;
                    $stockStatus = ($stockQty == 0) ? 'out_of_stock' : (($stockQty <= $minStock) ? 'low_stock' : 'in_stock');

                    $variantId = $_POST['product_variant_ids'][$i] ?? $this->productModel->generateId('product_variant', 'product_variant_id', 'PV');

                    $imgFileName = null;

                    if (isset($_POST['variant_existing_images']) && is_array($_POST['variant_existing_images'])) {
                        if (isset($_POST['variant_existing_images'][$i]) && !empty($_POST['variant_existing_images'][$i])) {
                            $imgFileName = $_POST['variant_existing_images'][$i];
                        }
                    }

                    if (!empty($_FILES['variant_images_group']['name'][$i])) {
                        $ext = pathinfo($_FILES['variant_images_group']['name'][$i], PATHINFO_EXTENSION);
                        $imgFileName = $variantId . "." . $ext;
                        $destination = $categoryFolder . $imgFileName;

                        if (move_uploaded_file($_FILES['variant_images_group']['tmp_name'][$i], $destination)) {
                            // Image uploaded successfully
                        }
                    }

                    $variantsData[] = [
                        'product_variant_id' => $variantId,
                        'min_stock_level' => $minStock,
                        'stock_qty' => $stockQty,
                        'stock_status' => $stockStatus,
                        'variant_id' => $vid,
                        'product_id' => $productId,
                        'img_url' => $imgFileName
                    ];
                }
            }

            $productData = [
                'product_name' => $_POST['product_name'],
                'description' => $_POST['description'],
                'cost_price' => $_POST['cost_price'],
                'sale_price' => $_POST['sales_price'],
                'category_id' => $categoryId,
                'img_url' => implode(',', $productImages)
            ];

            $this->productModel->updateProduct($productId, [
                'product' => $productData,
                'variants' => $variantsData
            ]);
            $_SESSION['flash_success'] = "Product updated successfully!";
            return [
                'success' => true,
                'message' => "Product updated successfully!"
            ];
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error updating product: " . $e->getMessage();
            return [
                'success' => false,
                'message' => "Error updating product: " . $e->getMessage(),
                'errors' => []
            ];
        }
    }

    function sendLowStockPdf()
    {

        // if (isset($_SESSION['email'])) {
        // $email = $_SESSION['email'];
        $loginLink = base('app/views/product/lowStockAlert.php?action=sendPdf&email=wongweixin116@gmail.com');
        // $loginLink = base('app/views/product/lowStockAlert.php?action=sendPdf&email=' . $email);
        sendPdf('wongweixin116@gmail.com', "Wei Xin", $loginLink);
        return true;
        // }
    }

    public function handleDeleteProductById($productId)
    {
        if (empty($productId)) return false;
        try {
            $result = $this->productModel->deleteProductById($productId);
            $product = $this->getProductById($productId);
            if ($product && !empty($product['img_url'])) {
                $images = explode(',', $product['img_url']);
                $categoryName = $product['category_name'] ?? '';
                $categoryFolder = "../../../public/images/" . $categoryName . "/";

                foreach ($images as $img) {
                    $file = $categoryFolder . trim($img);
                    if (file_exists($file)) unlink($file);
                }
            }
            return $result;
        } catch (Exception $e) {
            error_log("Failed to delete product $productId: " . $e->getMessage());
            return false;
        }
    }

    public function getAllUserWishlist(
        $sort = 'customer_id',
        $order = 'asc'
    ) {
        return $this->productModel->getWishlistBackInStockItems($sort, $order);
    }

    function getPriorityClass($waitingCount, $stockQty)
    {
        if ($stockQty <= 0) return 'priority-out';

        $ratio = $waitingCount / $stockQty;

        if ($ratio >= 1.5) return 'priority-critical';
        if ($ratio >= 1.0) return 'priority-high';
        if ($ratio >= 0.5) return 'priority-medium';
        return 'priority-low';
    }

    public function handleRestockProductById($productVariantId, $qty, $productId)
    {
        $successVariant = $this->productModel->restockVariant($productVariantId, $qty);

        $successProduct = $this->productModel->updateProductUpdatedAt($productId);

        return $successVariant && $successProduct;
    }

    function displayProducts()
    {
        if (is_get()) {
            $topSelling = $this->productModel->getTopSellingProducts(3);
            $newArrivals = $this->productModel->getNewArrivalsProducts(3);

            return [
                'topSelling' => $topSelling,
                'newArrivals' => $newArrivals
            ];
        }
    }
    const IMG_BASE_URL = '/public/';
    const REVIEW_IMG_BASE_PATH = '/public/images/review/';

    // resolve the full URL of an image
    private function resolveImagePath($path, $categoryName = '')
    {
        if (empty($path)) return 'https://via.placeholder.com/300';

        if (preg_match('/^https?:\/\//i', $path)) return $path;

        $filenameOnly = basename($path);
        $categoryFolder = '';
        // append category folder if provided to organize images
        if (!empty($categoryName)) {
            $categoryFolder = trim($categoryName) . '/';
        }

        return self::IMG_BASE_URL . 'images/' . $categoryFolder . $filenameOnly;
    }

    //fetch data --> Product Details
    public function getProductDetails($productId)
    {

        if (!$productId) {
            return null;
        }

        $product = $this->productModel->getProductWithCategory($productId);

        if (!$product) {
            return null;
        }

        // Get Category Name
        $categoryName = is_array($product) ? ($product['category_name'] ?? '') : ($product->category_name ?? '');

        // Resolve Main Image
        $rawMainImg = $product['img_url'] ?? $product['photo'] ?? '';

        $mainImgParts = explode(',', $rawMainImg);

        $firstMainImg = trim($mainImgParts[0]);

        // primary display image
        $product['img_url'] = empty($firstMainImg) ? '' : $this->resolveImagePath($firstMainImg, $categoryName);
        $product['display_img_url'] = $product['img_url'];

        // Process Gallery
        $photos = $this->productModel->getProductVariants($productId);
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

        // add images from variants to the gallery
        if (!empty($photos)) {
            foreach ($photos as $photo) {
                $pUrl = is_object($photo) ? ($photo->img_url ?? $photo->photo ?? '') : ($photo['img_url'] ?? $photo['photo'] ?? '');
                $resolvedUrl = $this->resolveImagePath($pUrl, $categoryName);

                if (is_object($photo)) {
                    $photo->img_url = $resolvedUrl;
                } else {
                    $photo['img_url'] = $resolvedUrl;
                }

                $gallery[] = [
                    'product_variant_id' => is_object($photo) ? $photo->product_variant_id : $photo['product_variant_id'],
                    'img_url' => $resolvedUrl,
                    'display_url' => $resolvedUrl
                ];
            }
        }
        $product['gallery'] = $gallery;

        // Process Variants
        $variants = $this->productModel->getProductVariants($productId);
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
        $stats = $this->productModel->getProductGlobalStats($productId);
        $product['review_summary'] = [
            'total_reviews' => is_object($stats) ? $stats->total_reviews : ($stats['total_reviews'] ?? 0),
            'average_rating' => is_object($stats) ? $stats->avg_rating : ($stats['avg_rating'] ?? 0)
        ];

        // Process Reviews Images
        $rawReviews = $this->productModel->getProductReviews($productId);
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
            $related = $this->productModel->getRelatedProducts($catId, $productId);
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
        // checks if the current user has already liked this product
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

    public function index()
    {
        $products = $this->productModel->fetchAllProducts();
        return $products;
    }

    public function fetchAllCategories()
    {
        return $this->productModel->fetchAllCategories();
    }

    public function getProductsByCategoryId()
    {
        if (!isset($_GET['id'])) {
            die("No category ID provided.");
        }

        $categoryID = $_GET['id'];
        return $this->productModel->getProductsByCategoryId($categoryID);
    }

    public function getCategory()
    {
        if (!isset($_GET['id'])) {
            die("No category ID provided.");
        }

        $categoryID = $_GET['id'];
        return $this->productModel->getCategory($categoryID);
    }

    public function getSearchSuggestions()
    {
        header('Content-Type: application/json');

        $keyword = $_GET['q'] ?? '';

        if (strlen($keyword) < 2) {
            echo json_encode([]);
            exit;
        }

        try {
            $results = $this->productModel->searchProductsByName($keyword);

            foreach ($results ?? [] as &$item) {
                $catName = $item['category_name'] ?? 'Uncategorized';
                $safeCatName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $catName);

                $images = explode(',', $item['img_url']);
                if (count($images) === 1) {
                    $images = explode('，', $item['img_url']);
                }
                $display_img = trim($images[0]);

                $item['image'] = empty($display_img)
                    ? '/public/images/default.png'
                    : "/public/images/$safeCatName/$display_img";
            }

            echo json_encode($results);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }

        exit;
    }

    public function systemCall($phone)
    {
        // Remove non-digits
        $phone = preg_replace('/\D/', '', $phone);

        // Malaysia format
        if (str_starts_with($phone, '0')) {
            $phone = '6' . ltrim($phone, '0');
        }

        if (!str_starts_with($phone, '6')) {
            $phone = '6' . $phone;
        }

        $phone = '+' . $phone;

        // DEBUG: confirm number
        error_log("Calling number: $phone");

        $twilio = new TwilioSMS(
            'AC691f78ade95d9649a59a8e5c7a431e7a',
            '242e93ca44f709be3f93cd4ea0bd012a',
            '+14199241697' // MUST be voice-enabled
        );

        $msg = "Hi Wei Xin. How about today !!!";

        return $twilio->call($phone, $msg);
    }


    public function handleDeleteProductVariantById(string $variantId)
    {
        if (empty($variantId)) return false;

        try {
            $result = $this->productModel->deleteVariantById($variantId);

            if (!empty($variant['img_url'])) {
                $images = explode(',', $variant['img_url']);
                $categoryName = $variant['category_name'] ?? 'uncategorized';
                $categoryFolder = "../../../public/images/" . $categoryName . "/";

                foreach ($images as $img) {
                    $file = $categoryFolder . trim($img);
                    if (file_exists($file)) unlink($file);
                }
            }
            if ($result) {
                $_SESSION['flash_success'] = "Variant deleted successfully.";
            } else {
                $_SESSION['flash_error'] = "Failed to delete variant.";
            }

            return $result;
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Error: " . $e->getMessage();
            error_log("Failed to delete product variant $variantId: " . $e->getMessage());
            return false;
        }
    }

    public function getAllProductVariantsGrouped()
    {
        $variants = $this->productModel->getAllProductVariants();
        $grouped = [];

        foreach ($variants as $v) {
            $grouped[$v['product_id']][] = $v;
        }

        return $grouped;
    }
}
