<?php
include '../../controllers/productsController.php';

$controller = new ProductsController();
$controller->submitProductForm();
$title = "Add Single Products";
$pageCSS = "addSingleProduct.css";

include_once '../../helpers/html.php';
include_once '../../helpers/validation.php';
$productId = $_GET['product_id'] ?? null;
$draftKey = $_GET['draft_key'] ?? null;
$isEdit = ($productId && $productId !== 'new' && !$draftKey);
$product = null;

if ($isEdit) {
    $product_id = $_GET['product_id'];
    $product = $controller->getProductById($product_id);
    $variantsData = $controller->getProductVariantsByProductId($product_id);

    if (!empty($product['img_url'])) {
        $cleaned = str_replace('，', ',', $product['img_url']);
        $existingImages = array_map('trim', explode(',', $cleaned));
    } else {
        $existingImages = [];
    }
} else {
    $product_id = $newProductId ?? null;
}

if ($isEdit) {
    $cost_price = $product['cost_price'] ?? '';
    $sales_price = $product['sale_price'] ?? '';
    $category_id = $product['category_id'] ?? '';
    $product_name = $product['product_name'] ?? '';
}

$viewData = $controller->showAddProductForm();
if (is_array($viewData)) {
    extract($viewData);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_variants_submit'])) {
    $variantIdsRaw = $_POST['delete_variant_ids'] ?? '';
    $variantIds = !empty($variantIdsRaw) ? explode(',', $variantIdsRaw) : [];

    if (!empty($variantIds)) {
        foreach ($variantIds as $variantId) {
            $controller->handleDeleteProductVariantById($variantId);
        }
    }
    header("Location: addSingleProduct.php?product_id=" . $product_id);
    exit;
}

include '../header.php';
?>

<template id="variantTemplate">
    <div class="variant-section-basicInfo">
        <h3>Variant </h3>

        <div class="three-field-row">
            <div class="field">
                <label for="variant_id">Variant</label>
                <?php
                // New variant starts empty (null value)
                html_select('variant_ids[]', $variants, 'Select a variant', null);
                ?>
            </div>

            <div class="field">
                <label for="min_stock_level">Minimum Threshold</label>
                <?php html_number('min_stock_levels[]', '1', '20', '1', 'step="1"', ''); ?>
            </div>

            <div class="field">
                <label for="stock_qty">Stock Quantity</label>
                <?php html_number('stock_qtys[]', '1', '50', '1', 'step="1"', ''); ?>
            </div>
        </div>

        <div class="variant-image-upload-section">
            <div class="field">
                <label class="custom-file-upload">
                    <div class="upload-dropzone variant-drop-zone">
                        <p class="upload-text">Click or drag your image files here</p>
                        <p class="upload-note">Accepted formats: JPG, PNG, WEBP</p>
                    </div>
                    <input type="file"
                        name="variant_images_group[]"
                        class="variant-image-input"
                        accept="image/*"
                        style="display:none;">
                </label>
            </div>

            <div class="field">
                <div class="variant-preview-container" style="display:flex;flex-wrap:wrap;">
                </div>
            </div>
        </div>

        <div class="button-container">
            <button type="button" class="remove-variant-btn">Remove</button>
        </div>
    </div>
</template>


<form id="productForm" method="POST" enctype="multipart/form-data">
    <?php
    showToast();
    ?>
    <input type="hidden" name="is_edit" value="<?= $isEdit ? '1' : '0' ?>">
    <div class="container">
        <div class="form-header-actions">
            <?php if ($isEdit): ?>
                <a href="#" class="back-link" onclick="window.location.href='/app/views/product/productList.php'">&#x293A;</a>
                <button type="button" id="removeVariants" class="removeVariantsBtn">
                    <i class="fa-solid fa-trash"></i> Remove Variants
                </button>
        </div>
    <?php endif; ?>

    <div class="basicInfo">
        <h3>Product Basic Information</h3>

        <div class="form-row-group">
            <div class="form-column">
                <label for="product_id">Product ID</label>
                <?php html_text(
                    'product_id',
                    "maxlength='6' readonly",
                    ($isEdit && isset($product['product_id'])) ? $product['product_id'] : ($newProductId ?? '')
                ); ?>
            </div>
            <div class="form-column">
                <label for="product_name">Product Name</label>
                <?php html_text(
                    'product_name',
                    "maxlength='25'",
                    $product['product_name'] ?? ''
                ); ?>
            </div>
        </div>

        <div class="form-row-group">
            <div class="form-column">
                <label for="category_id">Category</label>
                <?php
                // FIX: Ensure 5th argument ($attr) is passed as empty string if unused
                html_select(
                    'category_id',
                    $categories,
                    'Select a Category',
                    $product['category_id'] ?? null,
                    '' // $attr
                ); ?>
            </div>
            <div class="form-column">
                <label for="description">Description</label>
                <?php html_text(
                    'description',
                    "maxlength='50' placeholder='Enter description (eg. 18k Gold, 10cm long)'",
                    $product['description'] ?? ''
                ); ?>
            </div>
        </div>

        <div class="form-row-group">
            <div class="form-column">
                <label for="cost_price">Cost Price</label>
                <?php html_number('cost_price', '0.01', '99999.99', '0.01', 'step="0.01"', $product['cost_price'] ?? ''); ?>
            </div>
            <div class="form-column">
                <label for="sales_price">Sales Price</label>
                <?php html_number('sales_price', '0.01', '99999.99', '0.01', 'step="0.01"', $product['sale_price'] ?? ''); ?>
            </div>
        </div>

        <div class="form-row-group">
            <div class="form-column">
                <label for="product_images" class="custom-file-upload">
                    <div class="upload-dropzone" id="productDropZone">
                        <p class="upload-text">Click or drag your image files here</p>
                        <p class="upload-note">Accepted formats: JPG, PNG, WEBP</p>
                    </div>
                </label>
                <input type="file" name="product_images[]" id="product_images" accept="image/*" multiple style="display: none;">
            </div>

            <div class="form-column">
                <div id="previewContainer" style="display:flex; flex-wrap: wrap; gap:10px;">

                </div>
            </div>
        </div>

        <div class="button-container">
            <button type='button' id='add-variant-btn'>Variant +</button>
        </div>

    </div>

    <div class="basicInfo">
        <div id="activeVariantsContainer">
            <?php if (!empty($variantsData)): ?>
                <?php foreach ($variantsData as $index => $variant): ?>
                    <div class="variant-section-basicInfo">
                        <div class="variantStatus">
                            <h3>Variant <?= $index + 1 ?></h3>
                            <?php if ($isEdit):
                                $stockClass = strtolower(str_replace(' ', '_', $variant['stock_status']));
                            ?>
                                <span class="stock-status <?= $stockClass ?>">
                                    <?= $variant['stock_status'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <br>

                        <input type="hidden" name="product_variant_ids[]" value="<?= $variant['product_variant_id'] ?>">

                        <!-- Add hidden field for existing variant image -->
                        <input type="hidden" name="variant_existing_images[]" value="<?= $variant['img_url'] ?>">
                        <div class="three-field-row">
                            <div class="field">
                                <label for="variant_id">Variant</label>
                                <?php html_select(
                                    'variant_ids[]',
                                    $variants,
                                    'Select a variant',
                                    $variant['variant_id']
                                ); ?>
                            </div>

                            <div class="field">
                                <label for="min_stock_level">Minimum Threshold</label>
                                <?php html_number('min_stock_levels[]', '1', '20', '1', 'step="1"', $variant['min_stock_level']); ?>
                            </div>

                            <div class="field">
                                <label for="stock_qty">Stock Quantity</label>
                                <?php html_number('stock_qtys[]', '1', '50', '1', 'step="1"', $variant['stock_qty']); ?>
                            </div>
                        </div>

                        <div class="variant-image-upload-section">
                            <div class="field">
                                <label class="custom-file-upload">
                                    <div class="upload-dropzone variant-drop-zone">
                                        <p class="upload-text">Click or drag your image files here</p>
                                        <p class="upload-note">Accepted formats: JPG, PNG, WEBP</p>
                                    </div>
                                    <input type="file" name="variant_images_group[]" class="variant-image-input" accept="image/*" style="display:none;">

                                </label>
                            </div>

                            <div class="field">
                                <div class="variant-preview-container" style="display:flex;flex-wrap:wrap;">

                                </div>
                            </div>
                        </div>

                        <div class="button-container">
                            <button type="button" class="remove-variant-btn">Remove</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

    <br>
    <div class="form-footer-actions">
        <button type="button" class="confirm-btn">
            <?= $isEdit ? 'Update Product' : 'Add Product' ?>
        </button>
    </div>

    </div>
    <div id="submitModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
        <div style="background-color:#fff; margin:15% auto; padding:20px; border-radius:8px; width:400px; text-align:center; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
            <h2 style="margin-top:0;">Confirm Action</h2>
            <p id="deleteMessage">Are you sure you want to proceed?</p>
            <div style="display:flex; justify-content: space-around; margin-top:20px;">
                <button id="confirmSubmit" style="background-color:#28a745; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Confirm</button>
                <button id="cancelSubmit" style="background-color:#dc3545; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Cancel</button>
            </div>
        </div>
    </div>
</form>

<div id="deleteVariantModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.5);">
    <div style="background-color:#fff; margin:10% auto; padding:20px; border-radius:8px; width:400px; max-height:70%; overflow-y:auto; text-align:center; box-shadow:0 4px 8px rgba(0,0,0,0.2);">
        <h2 style="margin-top:0;">Delete Variants</h2>
        <p>Select the variants you want to delete:</p>
        <form id="deleteVariantsForm" method="POST" action="addSingleProduct.php?product_id=<?= $product_id ?>">
            <input type="hidden" name="delete_variant_ids" id="delete_variant_ids">

            <div id="variantCheckboxContainer" style="text-align:left; margin-top:15px;"></div>

            <div style="display:flex; justify-content: space-around; margin-top:20px;">
                <button type="button" id="cancelDeleteVariants" style="background-color:#6c757d; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Cancel</button>
                <button type="submit" name="delete_variants_submit" value="1" id="confirmDeleteVariants" style="background-color:red; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Delete</button>
            </div>
        </form>
    </div>
</div>
<script>
    const existingProductImages = <?php
                                    echo json_encode(
                                        array_map(
                                            fn($img) => "../../../public/images/" . $product['category_name'] . "/" . trim($img),
                                            $existingImages ?? []
                                        )
                                    );
                                    ?>;

    const existingVariantImages = <?php
                                    $variantImages = [];

                                    if (!empty($variantsData)) {
                                        foreach ($variantsData as $index => $variant) {
                                            $variantImages[$index] = !empty($variant['img_url'])
                                                ? array_map(
                                                    fn($img) => "../../../public/images/" . $product['category_name'] . "/" . trim($img),
                                                    explode(',', $variant['img_url'])
                                                )
                                                : [];
                                        }
                                    }

                                    echo json_encode($variantImages);
                                    ?>;
</script>

<script src="/public/js/addSingleProduct.js" defer></script>

<?php include '../footer.php'; ?>