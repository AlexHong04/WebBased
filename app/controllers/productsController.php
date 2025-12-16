<?php
require_once __DIR__ . '/../models/productsModel.php';
require_once __DIR__ . '/../helpers/mail.php';

class ProductController
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
                $imgFileName = null;
                if (!empty($_FILES['variant_images_group']['name'][$i])) {
                    $ext = pathinfo($_FILES['variant_images_group']['name'][$i], PATHINFO_EXTENSION);
                    $imgFileName = $variantId . "." . $ext;
                    $destination = $categoryFolder . $imgFileName;

                    if (move_uploaded_file($_FILES['variant_images_group']['tmp_name'][$i], $destination)) {
                        $variantsData[$i]['img_url'] = $imgFileName;
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
            return [
                'success' => true,
                'product_id' => $productId
            ];
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

            if (isset($_POST['ajax_validation'])) {
                $result = $this->validateProductForm($_POST, $_FILES);
                header('Content-Type: application/json');
                echo json_encode($result);
                exit();
            }

            if (isset($_POST['final_submission'])) {

                $isEdit = !empty($_POST['product_id']) && strpos($_POST['product_id'], 'PR') === 0;

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

            // 1️⃣ Validate required fields
            $validation = $this->validateProductForm($_POST, $_FILES);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validation['errors']
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

            return [
                'success' => true,
                'message' => "Product updated successfully!"
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => "Error updating product: " . $e->getMessage(),
                'errors' => []
            ];
        }
    }

    function sendLowStockPdf() {

        echo "call 1";
        // if (isset($_SESSION['email'])) {
            echo "call 2";
            // $email = $_SESSION['email'];
            $loginLink = base('app/views/product/lowStockAlert.php?action=sendPdf&email=wongweixin116@gmail.com');
            // $loginLink = base('app/views/product/lowStockAlert.php?action=sendPdf&email=' . $email);
            sendPdf('wongweixin116@gmail.com', "Wei Xin", $loginLink);
            return true;
        // }
    }
}
