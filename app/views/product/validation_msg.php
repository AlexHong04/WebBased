<?php
session_start();
include '../../helpers/validation.php';

$errors = [];
$form_data = $_POST;

// 1. BASIC FIELD VALIDATION
if (empty($_POST['product_id'])) {
    $errors['product_id'] = "Product ID is required.";
}

if (empty($_POST['product_name'])) {
    $errors['product_name'] = "Product name is required.";
}

if (empty($_POST['description'])) {
    $errors['description'] = "Product description is required.";
}

if (empty($_POST['category_id'])) {
    $errors['category_id'] = "Please select a category.";
}

if (!is_numeric($_POST['cost_price']) || $_POST['cost_price'] <= 0 || !is_money($_POST['cost_price'])) {
    $errors['cost_price'] = "Invalid cost price.";
}

if (!is_numeric($_POST['sales_price']) || $_POST['sales_price'] <= 0 || !is_money($_POST['sales_price'])) {
    $errors['sales_price'] = "Invalid sales price.";
}

if (!empty($_POST['sales_price']) && !empty($_POST['cost_price'])) {
    if ($_POST['sales_price'] < $_POST['cost_price']) {
        $errors['sales_price'] = "Sales price cannot be less than cost price.";
    }
}

// 2. IMAGE VALIDATION
$uploaded_files = [];

if (!empty($_FILES['product_images']['name'][0])) {
    $totalFiles = count($_FILES['product_images']['name']);

    // Limit max number of images (e.g., 5)
    if ($totalFiles > 5) {
        $errors['product_images'] = "You can upload up to 5 images only.";
    }

    // Allowed file types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

    for ($i = 0; $i < $totalFiles; $i++) {
        $tmpFile = $_FILES['product_images']['tmp_name'][$i];
        $filename = uniqid('prod_') . '-' . basename($_FILES['product_images']['name'][$i]);
        $fileType = $_FILES['product_images']['type'][$i];
        $fileSize = $_FILES['product_images']['size'][$i];

        // Validate type
        if (!in_array($fileType, $allowedTypes)) {
            $errors['product_images'] = "Only JPG, PNG, and WEBP images are allowed.";
            continue;
        }

        // Validate size (max 2MB)
        if ($fileSize > 2 * 1024 * 1024) {
            $errors['product_images'] = "Each image must be smaller than 2MB.";
            continue;
        }

        // Move file to upload directory
        $uploadDir = "../../uploads/products/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $targetPath = $uploadDir . $filename;
        if (move_uploaded_file($tmpFile, $targetPath)) {
            $uploaded_files[] = $filename; // keep track for saving in DB
        }
    }
} else {
    $errors['product_images'] = "Please upload at least one image.";
}

// 3. RETURN TO FORM IF ERRORS
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = $form_data;
    header("Location: addSingleProduct.php");
    exit();
}

// ------------------------------
// 4. SAVE DATA TO DATABASE
// ------------------------------
// Example: $uploaded_files contains all uploaded filenames
// You can store $form_data and $uploaded_files in your database here

echo "SUCCESS";
?>
