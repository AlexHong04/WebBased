<?php
$title = "Add Single Products";
$pageCSS = "addSingleProduct.css";

include_once '../../helpers/html.php';
include_once '../../helpers/validation.php';
include '../header.php';
include '../../controllers/productsController.php';

$controller = new ProductController();

$isEdit = isset($_GET['product_id']);
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
$controller->submitProductForm();

$errors = $_SESSION['form_errors'] ?? [];
$form_data = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_errors']);
unset($_SESSION['form_data']);
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
                <?php err('variant_id'); ?>
            </div>

            <div class="field">
                <label for="min_stock_level">Minimum Threshold</label>
                <?php html_number('min_stock_levels[]', '1', '20', '1', 'step="1"', ''); ?>
                <?php err('min_stock_level'); ?>
            </div>

            <div class="field">
                <label for="stock_qty">Stock Quantity</label>
                <?php html_number('stock_qtys[]', '1', '50', '1', 'step="1"', ''); ?>
                <?php err('stock_qty'); ?>
            </div>
        </div>

        <div class="variant-image-upload-section">
            <div class="field">
                <label class="custom-file-upload">
                    <div class="upload-dropzone">
                        <p class="upload-text">Click or drag your image files here</p>
                        <p class="upload-note">Accepted formats: JPG, PNG, WEBP</p>
                    </div>
                    <input type="file"
                        name="variant_images_group[__INDEX__]"
                        class="variant-image-input"
                        accept="image/*"
                        multiple
                        style="display:none;">
                    <?php err('variant_images'); ?>
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
    <?php if ($isEdit): ?>
        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
    <?php endif; ?>
    <div class="container">
        <div class="basicInfo">
            <h3>Product Basic Information</h3>

            <div class="form-row-group">
                <div class="form-column">
                    <label for="product_id">Product ID</label>
                    <?php html_text(
                        'product_id',
                        "maxlength='6' readonly",
                        $isEdit ? $product['product_id'] : $newProductId
                    ); ?>
                    <?php err('product_id'); ?>
                </div>
                <div class="form-column">
                    <label for="product_name">Product Name</label>
                    <?php html_text(
                        'product_name',
                        "maxlength='25'",
                        $product['product_name'] ?? ''
                    ); ?>
                    <?php err('product_name'); ?>
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
                    <?php err('category_id'); ?>
                </div>
                <div class="form-column">
                    <label for="description">Description</label>
                    <?php html_text(
                        'description',
                        "maxlength='50' placeholder='Enter description (eg. 18k Gold, 10cm long)'",
                        $product['description'] ?? ''
                    ); ?>
                    <?php err('description'); ?>
                </div>
            </div>

            <div class="form-row-group">
                <div class="form-column">
                    <label for="cost_price">Cost Price</label>
                    <?php html_number('cost_price', '0.01', '99999.99', '0.01', 'step="0.01"', $product['cost_price'] ?? ''); ?>
                    <?php err('cost_price'); ?>
                </div>
                <div class="form-column">
                    <label for="sales_price">Sales Price</label>
                    <?php html_number('sales_price', '0.01', '99999.99', '0.01', 'step="0.01"', $product['sale_price'] ?? ''); ?>
                    <?php err('sales_price'); ?>
                </div>
            </div>

            <div class="form-row-group">
                <div class="form-column">
                    <label for="product_images" class="custom-file-upload">
                        <div class="upload-dropzone">
                            <p class="upload-text">Click or drag your image files here</p>
                            <p class="upload-note">Accepted formats: JPG, PNG, WEBP</p>
                        </div>
                    </label>
                    <input type="file" name="product_images[]" id="product_images" accept="image/*" multiple style="display: none;">
                    <?php err('product_images'); ?>
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
                            <h3>Variant <?= $index + 1 ?></h3>
                            <!-- Add hidden field for variant ID -->
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
                                    <?php err('variant_id'); ?>
                                </div>

                                <div class="field">
                                    <label for="min_stock_level">Minimum Threshold</label>
                                    <?php html_number('min_stock_levels[]', '1', '20', '1', 'step="1"', $variant['min_stock_level']); ?>
                                    <?php err('min_stock_level'); ?>
                                </div>

                                <div class="field">
                                    <label for="stock_qty">Stock Quantity</label>
                                    <?php html_number('stock_qtys[]', '1', '50', '1', 'step="1"', $variant['stock_qty']); ?>
                                    <?php err('stock_qty'); ?>
                                </div>
                            </div>

                            <div class="variant-image-upload-section">
                                <div class="field">
                                    <label class="custom-file-upload">
                                        <div class="upload-dropzone">
                                            <p class="upload-text">Click or drag your image files here</p>
                                            <p class="upload-note">Accepted formats: JPG, PNG, WEBP</p>
                                        </div>
                                        <input type="file" name="variant_images_group[]" class="variant-image-input" accept="image/*" multiple style="display:none;">
                                        <?php err('variant_images'); ?>
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

        <div class="button-container">
        </div>
        <button type="submit" class="confirm-btn">
            <?= $isEdit ? 'Update Product' : 'Add Product' ?>
        </button>
    </div>
    <div id="submitModal" class="modal">
        <div class="modal-content">
            <h3>Submit Product</h3>
            <p id="deleteMessage">Are you sure want to add this item?</p>

            <div class="modal-buttons">
                <button class="cancel-btn" id="cancelSubmit">Cancel</button>
                <button type="submit" class="confirm-btn" id="confirmSubmit">Submit</button>
            </div>
        </div>
    </div>
</form>
<?php if ($isEdit): ?>
    <form id="deleteProductForm" method="POST">
        <input type="hidden" name="delete_product" value="1">
        <input type="hidden" name="final_deletion" value="1">
        <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">
        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this product?')">Delete</button>
    </form>
<?php endif; ?>


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