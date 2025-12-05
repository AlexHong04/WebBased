<?php
require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/validation.php';

$title = $product['product_name'] ?? "Product Details";
$pageCSS = "product_details.css";

include './header.php';

// Constants for photo path
define('IMG_BASE_URL', '/public/');
define('DEFAULT_IMG', 'https://via.placeholder.com/600x600/text=No+Image');
define('REVIEW_IMG_BASE_URL', '/public/images/');

// Image Path
function getImgPath($dbPath)
{
    if (empty($dbPath)) return DEFAULT_IMG;
    if (str_starts_with($dbPath, 'http')) return $dbPath;
    return IMG_BASE_URL . $dbPath;
}

// Renders Star
function renderStars($rating)
{
    $starsHtml = '<span class="star-rating" style="color:#f0ad4e; font-size:0.9rem;">';
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);
    for ($i = 0; $i < $fullStars; $i++) $starsHtml .= '<i class="fas fa-star"></i>';
    if ($halfStar) $starsHtml .= '<i class="fas fa-star-half-alt"></i>';
    for ($i = 0; $i < $emptyStars; $i++) $starsHtml .= '<i class="far fa-star"></i>';
    $starsHtml .= '</span>';
    return $starsHtml;
}

// Get Variant Photo
$galleryImages = $product['gallery'] ?? [];
function findVariantImage($vid, $gallery)
{
    foreach ($gallery as $photo) {
        $pVid = is_object($photo) ? ($photo->product_variant_id ?? null) : ($photo['product_variant_id'] ?? null);
        $pUrl = is_object($photo) ? $photo->img_url : $photo['img_url'];

        if ($pVid == $vid) {
            return getImgPath($pUrl);
        }
    }
    return null;
}

// Get default main photo
$mainImageUrl = !empty($product['img_url']) ? getImgPath($product['img_url']) : DEFAULT_IMG;

// --- Toast Message ---
$toastMsg = '';
$toastType = '';
$toastIcon = '';

if (isset($_SESSION['flash_error']) && !empty($_SESSION['flash_error'])) {
    $toastMsg = $_SESSION['flash_error'];
    $toastType = 'error';
    $toastIcon = 'fa-times-circle';
    unset($_SESSION['flash_error']);
} elseif (isset($_SESSION['flash_warning']) && !empty($_SESSION['flash_warning'])) {
    $toastMsg = $_SESSION['flash_warning'];
    $toastType = 'warning';
    $toastIcon = 'fa-exclamation-triangle';
    unset($_SESSION['flash_warning']);
} elseif (isset($_SESSION['flash_success']) && !empty($_SESSION['flash_success'])) {
    $toastMsg = $_SESSION['flash_success'];
    $toastType = 'success';
    $toastIcon = 'fa-check-circle';
    unset($_SESSION['flash_success']);
}

// Variant Logic
$selectedVid = $_SESSION['keep_variant_id'] ?? null;
unset($_SESSION['keep_variant_id']);

$currentVariant = !empty($product['variants']) ? $product['variants'][0] : null;

if ($selectedVid && !empty($product['variants'])) {
    foreach ($product['variants'] as $v) {
        if ($v['product_variant_id'] == $selectedVid) {
            $currentVariant = $v;
            break;
        }
    }
}

$currVid = $currentVariant ? $currentVariant['product_variant_id'] : '';
$currStock = $currentVariant ? $currentVariant['stock_qty'] : 0;
$currStatus = $currentVariant ? $currentVariant['stock_status'] : 'Out of Stock';

$variantImgFound = findVariantImage($currVid, $galleryImages);
$currImg = $variantImgFound ? $variantImgFound : $mainImageUrl;
// Variant Logic
$selectedVid = $_SESSION['keep_variant_id'] ?? null;
unset($_SESSION['keep_variant_id']);

$currentVariant = !empty($product['variants']) ? $product['variants'][0] : null;

if ($selectedVid && !empty($product['variants'])) {
    foreach ($product['variants'] as $v) {
        if ($v['product_variant_id'] == $selectedVid) {
            $currentVariant = $v;
            break;
        }
    }
}

$currVid = $currentVariant ? $currentVariant['product_variant_id'] : '';
$currStock = $currentVariant ? $currentVariant['stock_qty'] : 0;
$currStatus = $currentVariant ? $currentVariant['stock_status'] : 'Out of Stock';

$variantImgFound = findVariantImage($currVid, $galleryImages);

if ($selectedVid && $variantImgFound) {
    $currImg = $variantImgFound;
} else {
    $currImg = $mainImageUrl;
}

$realAvailable = $currStock;
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php if (isset($product) && $product): ?>
    <div class="product-container">
        <div class="product-images">
            <div class="main-image-wrapper">
                <button class="main-arrow prev-arrow" onclick="navigateImage(-1)"><i class="fas fa-chevron-left"></i></button>
                <img src="<?= encode($currImg) ?>" id="mainImage" class="main-image" alt="Product Image">
                <button class="main-arrow next-arrow" onclick="navigateImage(1)"><i class="fas fa-chevron-right"></i></button>
            </div>

            <div class="thumbnail-slider-container">
                <?php
                if (empty($galleryImages) && !empty($product['img_url'])) {
                    $singleUrl = getImgPath($product['img_url']);
                    echo '<img src="' . encode($singleUrl) . '" class="thumbnail active" onclick="changeMainImage(\'' . encode($singleUrl) . '\', this)">';
                }

                foreach ($galleryImages as $index => $photo):
                    $rawUrl = is_object($photo) ? $photo->img_url : $photo['img_url'];
                    $pUrl = getImgPath($rawUrl);
                ?>
                    <img src="<?= encode($pUrl) ?>"
                        class="thumbnail <?= ($pUrl === $currImg) ? 'active' : '' ?>"
                        onclick="changeMainImage('<?= encode($pUrl) ?>', this)"
                        alt="Thumbnail">
                <?php endforeach; ?>
            </div>
        </div>

        <div class="product-details">
            <span class="category"><?= encode($product['category_name'] ?? 'Category') ?></span>
            <h1><?= encode($product['product_name']) ?></h1>

            <div class="price-section">
                <span class="price">RM<?= number_format($product['sale_price'], 2) ?></span>
            </div>

            <p><?= nl2br(encode($product['description'])) ?></p>

            <div class="options-section">
                <strong>Variant:</strong>
                <div class="variant-list">
                    <?php if (!empty($product['variants'])): ?>
                        <?php foreach ($product['variants'] as $variant): ?>
                            <?php
                            $isOOS = $variant['stock_qty'] <= 0;
                            $isActive = ($variant['product_variant_id'] == $currVid);
                            $badgeClass = ($isActive ? 'active-variant ' : '') . ($isOOS ? 'out-of-stock-variant' : '');
                            $vImgUrl = findVariantImage($variant['product_variant_id'], $galleryImages);
                            $vImgUrlStr = $vImgUrl ? encode($vImgUrl) : '';
                            ?>

                            <span class="option-badge <?= $badgeClass ?>"
                                onclick="selectVariant(this, 
                                    '<?= $variant['stock_qty'] ?>', 
                                    '<?= $variant['stock_status'] ?>', 
                                    '<?= $variant['product_variant_id'] ?>', 
                                    '<?= $vImgUrlStr ?>')">

                                <?= encode($variant['variant_name']) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="color:red;">No variants found.</span>
                    <?php endif; ?>
                </div>
            </div>

            <form class="purchase-form" action="../controllers/cart_router.php?action=add" method="POST">
                <?php
                $GLOBALS['product_variant_id'] = $currVid;
                html_hidden('product_variant_id');
                ?>

                <div class="stock-status" id="stockDisplay">
                    <?php
                    if ($realAvailable > 0) {
                        echo '<span class="status-in-stock">' . encode($currStatus) . ' (' . $realAvailable . ' available)</span>';
                    } else {
                        echo '<span class="status-out-of-stock">Out of Stock (0 available)</span>';
                    }
                    ?>
                </div>

                <div class="action-buttons-container">
                    <div class="quantity-selector-fancy">
                        <button type="button" onclick="decreaseQuantity()">-</button>

                        <?php
                        $maxQty = ($realAvailable > 0) ? $realAvailable : 0;
                        $disabled = ($realAvailable <= 0) ? 'disabled' : '';

                        $GLOBALS['quantity'] = ($realAvailable > 0) ? 1 : 0;

                        html_number(
                            'quantity',
                            '0',
                            $maxQty,
                            '1',
                            "onchange='validateQuantity()' 
                             onkeydown=\"if(event.key === 'Enter'){ event.preventDefault(); this.blur(); }\" 
                             $disabled"
                        );
                        ?>

                        <button type="button" onclick="increaseQuantity()">+</button>
                    </div>
                    <?php $isOutOfStock = $realAvailable <= 0; ?>
                    <button type="submit" class="add-to-cart-btn <?= $isOutOfStock ? 'disabled' : '' ?>" <?= $isOutOfStock ? 'disabled' : '' ?>>
                        <i class="fas fa-shopping-bag"></i> ADD TO CART
                    </button>

                    <button type="button" class="wishlist-btn" onclick="toggleWishlist(this)">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="review-section" id="reviews-anchor">
        <h2>Customer Reviews</h2>
        <?php
        $totalReviews = $product['review_summary']['total_reviews'] ?? 0;
        $avgRating = $product['review_summary']['average_rating'] ?? 0;
        ?>

        <?php if ($totalReviews > 0): ?>
            <div class="review-summary">
                <div class="summary-score">
                    <span class="score-number"><?= number_format($avgRating, 1) ?></span>
                    <div style="display:flex; align-items:center; gap:5px;">
                        <?= renderStars($avgRating) ?>
                        <span class="out-of" style="font-size:0.9rem; color:#777;">over 5 stars</span>
                    </div>
                </div>
                <span class="score-total">(Based on <?= $totalReviews ?> reviews)</span>
            </div>

            <div class="review-list">
                <?php foreach ($product['reviews'] as $review):
                    $r_name = is_object($review) ? $review->customer_name : $review['customer_name'];
                    $r_rating = is_object($review) ? $review->rating : $review['rating'];
                    $r_desc = is_object($review) ? $review->description : $review['description'];
                    $r_date = is_object($review) ? $review->created_at : $review['created_at'];
                    $r_variant = is_object($review) ? ($review->variant_name ?? null) : ($review['variant_name'] ?? null);
                    $r_photos = is_object($review) ? ($review->photos ?? []) : ($review['photos'] ?? []);

                    $photoUrls = [];
                    foreach ($r_photos as $rp) {
                        $rawUrl = is_object($rp) ? $rp->img_url : $rp['img_url'];

                        if (str_starts_with($rawUrl, 'http')) {
                            $photoUrls[] = $rawUrl;
                        } else {
                            $photoUrls[] = REVIEW_IMG_BASE_URL . $rawUrl;
                        }
                    }
                    $photosJson = htmlspecialchars(json_encode($photoUrls), ENT_QUOTES, 'UTF-8');
                ?>
                    <div class="review-item">
                        <div class="review-avatar"><i class="fas fa-user"></i></div>
                        <div class="review-content">
                            <div class="review-author">
                                <?= encode($r_name) ?>
                                <span class="review-date"><?= date('d/m/Y H:i', strtotime($r_date)) ?></span>
                            </div>
                            <div style="margin-bottom:8px;">
                                <?= renderStars($r_rating) ?>
                            </div>

                            <?php if ($r_variant): ?>
                                <div class="review-meta">Variation: <?= encode($r_variant) ?></div>
                            <?php endif; ?>

                            <div class="review-text"><?= encode($r_desc) ?></div>

                            <?php if (!empty($photoUrls)): ?>
                                <div class="review-photos">
                                    <?php foreach ($photoUrls as $idx => $pUrl): ?>
                                        <img src="<?= encode($pUrl) ?>"
                                            class="review-photo"
                                            onclick="openReviewGallery(<?= $idx ?>, <?= $photosJson ?>)">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:40px; color:#999;">
                <i class="far fa-comment-dots" style="font-size:2rem; margin-bottom:10px;"></i>
                <p>No reviews yet. Be the first to review this product!</p>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <div class="not-found-container">
        <h1>Product Not Found</h1>
        <p>We couldn't find the product you were looking for.</p>
        <a href="index.php" style="color: #fc84a3;">Go Home</a>
    </div>
<?php endif; ?>

<div id="reviewLightbox" class="lightbox-modal" onclick="if(event.target === this) closeReviewGallery()">
    <span class="lightbox-close" onclick="closeReviewGallery()">&times;</span>
    <div class="lightbox-content-wrapper">
        <a class="lightbox-nav lightbox-prev" onclick="navigateReview(-1)">&#10094;</a>
        <img class="lightbox-image" id="lightboxImg" src="">
        <a class="lightbox-nav lightbox-next" onclick="navigateReview(1)">&#10095;</a>
    </div>
    <div class="lightbox-counter" id="lightboxCounter"></div>
</div>

<?php if (!empty($product['related_products'])): ?>
    <div class="related-products-section related-section">
        <h2>More Options</h2>
        <div class="related-product-grid related-grid">
            <?php foreach ($product['related_products'] as $related):
                $rawUrl = !empty($related['img_url']) ? $related['img_url'] : '';
                $rImg = getImgPath($rawUrl);
            ?>
                <a href="?id=<?= $related['product_id'] ?>" class="product-card">
                    <div class="product-card-img-wrapper">
                        <img src="<?= encode($rImg) ?>" class="product-card-img" alt="<?= encode($related['product_name']) ?>">
                    </div>
                    <div class="product-card-body">
                        <h3 class="product-card-name"><?= encode($related['product_name']) ?></h3>
                        <span class="product-card-price">RM<?= number_format($related['sale_price'], 2) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($toastMsg): ?>
    <div id="toast-notification" class="toast-notification toast-<?= $toastType ?>">
        <div class="toast-content">
            <i class="fas <?= $toastIcon ?> toast-icon"></i>
            <span class="toast-message"><?= encode($toastMsg) ?></span>
        </div>
        <div class="toast-progress"></div>
    </div>
<?php endif; ?>

<script>
    // --- Toast Notification ---
    document.addEventListener("DOMContentLoaded", function() {
        const toast = document.getElementById('toast-notification');
        if (toast) {
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-20px)';
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }
    });

    // --- Review Lightbox Logic --- 
    let currentReviewPhotos = [];
    let currentPhotoIndex = 0;

    function openReviewGallery(index, photos) {
        currentReviewPhotos = photos;
        currentPhotoIndex = index;
        updateLightboxImage();
        document.getElementById('reviewLightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeReviewGallery() {
        document.getElementById('reviewLightbox').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function navigateReview(direction) {
        currentPhotoIndex += direction;
        if (currentPhotoIndex >= currentReviewPhotos.length) currentPhotoIndex = 0;
        else if (currentPhotoIndex < 0) currentPhotoIndex = currentReviewPhotos.length - 1;
        updateLightboxImage();
    }

    function updateLightboxImage() {
        const img = document.getElementById('lightboxImg');
        img.src = currentReviewPhotos[currentPhotoIndex];
        const prevBtn = document.querySelector('.lightbox-prev');
        const nextBtn = document.querySelector('.lightbox-next');
        const counter = document.getElementById('lightboxCounter');

        if (currentReviewPhotos.length > 1) {
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
            counter.innerText = (currentPhotoIndex + 1) + " / " + currentReviewPhotos.length;
        } else {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            counter.innerText = "";
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") closeReviewGallery();
        if (document.getElementById('reviewLightbox').style.display === 'flex') {
            if (event.key === "ArrowLeft") navigateReview(-1);
            if (event.key === "ArrowRight") navigateReview(1);
        }
    });

    // --- Auto Slide & Image Navigation ---
    let slideInterval;
    let currentIndex = 0;
    const intervalTime = 3000;

    window.addEventListener('DOMContentLoaded', () => {
        const mainImage = document.getElementById('mainImage');
        if (!mainImage) return;

        const mainImageSrc = mainImage.src;
        const thumbnails = document.querySelectorAll('.thumbnail');
        thumbnails.forEach((thumb, index) => {
            thumb.classList.remove('active');
            if (thumb.src === mainImageSrc) {
                currentIndex = index;
                thumb.classList.add('active');
            }
        });
        startAutoSlide();
    });

    function startAutoSlide() {
        const thumbnails = document.querySelectorAll('.thumbnail');
        if (thumbnails.length <= 1) return;
        slideInterval = setInterval(() => navigateImage(1, true), intervalTime);
    }

    function stopAutoSlide() {
        if (slideInterval) {
            clearInterval(slideInterval);
            slideInterval = null;
        }
    }

    function navigateImage(direction, isAuto = false) {
        if (!isAuto) stopAutoSlide();
        const thumbnails = document.querySelectorAll('.thumbnail');
        if (thumbnails.length === 0) return;
        currentIndex += direction;
        if (currentIndex >= thumbnails.length) currentIndex = 0;
        else if (currentIndex < 0) currentIndex = thumbnails.length - 1;
        changeMainImage(thumbnails[currentIndex].src, thumbnails[currentIndex], true);
    }

    function changeMainImage(src, element, fromNav = false) {
        document.getElementById('mainImage').src = src;

        document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
            thumb.classList.remove('active');

            if (thumb === element && !fromNav) {
                currentIndex = index;
                stopAutoSlide();
            }
        });

        if (element) {
            element.classList.add('active');
        } else {
            document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
                if (thumb.src === src || thumb.getAttribute('src') === src) {
                    thumb.classList.add('active');
                    currentIndex = index;
                }
            });
        }
    }

    function selectVariant(element, qty, status, id, img) {
        stopAutoSlide();

        document.querySelectorAll('.option-badge').forEach(el => el.classList.remove('active-variant'));
        element.classList.add('active-variant');

        let stockQty = parseInt(qty);
        const stockDisplay = document.getElementById('stockDisplay');
        const qtyInput = document.getElementById('quantity');
        const addToCartBtn = document.querySelector('.add-to-cart-btn');

        if (stockQty > 0) {
            stockDisplay.innerHTML = `<span class="status-in-stock">${status} (${stockQty} available)</span>`;
            if (qtyInput) {
                qtyInput.max = stockQty;
                if (parseInt(qtyInput.value) > stockQty || parseInt(qtyInput.value) === 0) {
                    qtyInput.value = 1;
                }
                qtyInput.disabled = false;
            }
            if (addToCartBtn) {
                addToCartBtn.disabled = false;
                addToCartBtn.classList.remove('disabled');
            }
        } else {
            stockDisplay.innerHTML = `<span class="status-out-of-stock">Out of Stock (0 available)</span>`;
            if (qtyInput) {
                qtyInput.value = 0;
                qtyInput.disabled = true;
            }
            if (addToCartBtn) {
                addToCartBtn.disabled = true;
                addToCartBtn.classList.add('disabled');
            }
        }

        const variantInput = document.getElementById('product_variant_id');
        if (variantInput) {
            variantInput.value = id;
        }

        if (img && img.trim() !== '') {
            changeMainImage(img, null, false);
        }
    }

    function increaseQuantity() {
        var qty = document.getElementById('quantity');
        if (qty.disabled) return;

        if (parseInt(qty.value) < parseInt(qty.max)) {
            qty.value = parseInt(qty.value) + 1;
        }
    }

    function decreaseQuantity() {
        var qty = document.getElementById('quantity');
        if (qty.disabled) return;
        if (parseInt(qty.value) > 1) {
            qty.value = parseInt(qty.value) - 1;
        }
    }

    function validateQuantity() {
        var qty = document.getElementById('quantity');
        var val = parseInt(qty.value);
        var max = parseInt(qty.max);

        if (isNaN(val) || val < 1) {
            qty.value = 1;
        } else if (val > max) {
            qty.value = max;
        } else {
            qty.value = val;
        }
    }

    function toggleWishlist(btn) {
        btn.classList.toggle('active');
        const icon = btn.querySelector('i');
        if (btn.classList.contains('active')) {
            icon.className = 'fas fa-heart';
        } else {
            icon.className = 'far fa-heart';
        }
    }
</script>

<?php include './footer.php' ?>