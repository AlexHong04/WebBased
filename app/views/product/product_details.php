<?php
// Enable error display for debugging purposes
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start the session if it hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/productsController.php';
require_once __DIR__ . '/../../helpers/html.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/validation.php';

$id = get('id', 'P00001');

$controller = new productsController();
$product = $controller->getProductDetails($id);

if (!$product) {
    $product = null;
}

$title = $product['product_name'] ?? "Product Details";
$pageCSS = "product_details.css";

include '../header.php';

// Constants
define('DEFAULT_IMG', 'https://via.placeholder.com/600x600/text=No+Image');

// UI Helper Functions (Render Stars)
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

// Find Variant Image
$galleryImages = $product['gallery'] ?? [];
function findVariantImage($vid, $gallery)
{
    foreach ($gallery as $photo) {
        $pVid = is_object($photo) ? ($photo->product_variant_id ?? null) : ($photo['product_variant_id'] ?? null);
        $pUrl = is_object($photo) ? $photo->img_url : $photo['img_url'];

        if ($pVid == $vid) {
            return $pUrl;
        }
    }
    return null;
}

// Get default main photo 
$mainImageUrl = !empty($product['img_url']) ? $product['img_url'] : DEFAULT_IMG;

// Toast Message
$toastMsg = '';
$toastType = '';
$toastIcon = '';

if ($msg = temp('flash_error')) {
    $toastMsg = $msg;
    $toastType = 'error';
    $toastIcon = 'fa-times-circle';
} elseif ($msg = temp('flash_warning')) {
    $toastMsg = $msg;
    $toastType = 'warning';
    $toastIcon = 'fa-exclamation-triangle';
} elseif ($msg = temp('flash_success')) {
    $toastMsg = $msg;
    $toastType = 'success';
    $toastIcon = 'fa-check-circle';
}

// Check Login Required Flash
$loginRequiredMsg = temp('flash_login_required');

// Variant 
$selectedVid = temp('keep_variant_id');

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
$isWishlisted = $product['is_wishlisted'] ?? false;
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php if (isset($product) && $product): ?>
    <div class="product-container">
        <div class="product-header-nav">
            <a href="javascript:history.back();" class="back-link-header">
                &#x293A;
            </a>
        </div>

        <div class="product-content-row">
            <div class="product-images">
                <div class="main-image-wrapper">
                    <button class="main-arrow prev-arrow" onclick="navigateImage(-1)"><i class="fas fa-chevron-left"></i></button>
                    <img src="<?= encode($currImg) ?>" id="mainImage" class="main-image" alt="Product Image">
                    <button class="main-arrow next-arrow" onclick="navigateImage(1)"><i class="fas fa-chevron-right"></i></button>
                </div>

                <div class="thumbnail-slider-container">
                    <?php
                    // Logic to remove duplicate images
                    $displayedUrls = [];
                    $finalImages = [];

                    // Add Main Image
                    if (!empty($product['img_url'])) {
                        $finalImages[] = $product['img_url'];
                        $displayedUrls[] = $product['img_url'];
                    }

                    // Add Gallery Images 
                    foreach ($galleryImages as $photo) {
                        $pUrl = is_object($photo) ? $photo->img_url : $photo['img_url'];
                        if (!empty($pUrl) && !in_array($pUrl, $displayedUrls)) {
                            $finalImages[] = $pUrl;
                            $displayedUrls[] = $pUrl;
                        }
                    }

                    // Render Unique Images
                    foreach ($finalImages as $index => $imgUrl):
                    ?>
                        <img src="<?= encode($imgUrl) ?>"
                            class="thumbnail <?= ($imgUrl === $currImg) ? 'active' : '' ?>"
                            onclick="changeMainImage('<?= encode($imgUrl) ?>', this)"
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

                <!-- nl2br = New Link To Break -->
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

                <form class="purchase-form" action="../../controllers/cart_router.php?action=add" method="POST">
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
                            $isDisabledStr = ($realAvailable <= 0) ? 'disabled' : '';

                            $GLOBALS['quantity'] = ($realAvailable > 0) ? 1 : 0;

                            $qtyAttrs = "onchange='validateQuantity()' 
                                         onkeydown=\"if(event.key === 'Enter'){ event.preventDefault(); this.blur(); }\" 
                                         $isDisabledStr";

                            html_number('quantity', '0', $maxQty, '1', $qtyAttrs);
                            ?>

                            <button type="button" onclick="increaseQuantity()">+</button>
                        </div>
                        <?php $isOutOfStock = $realAvailable <= 0; ?>
                        <button type="submit" class="add-to-cart-btn <?= $isOutOfStock ? 'disabled' : '' ?>" <?= $isOutOfStock ? 'disabled' : '' ?>>
                            <i class="fas fa-shopping-bag"></i> ADD TO CART
                        </button>

                        <button type="button" class="wishlist-btn <?= $isWishlisted ? 'active' : '' ?>" onclick="toggleWishlist(this)">
                            <i class="<?= $isWishlisted ? 'fas' : 'far' ?> fa-heart"></i>
                        </button>

                        <button type="button" class="share-btn" onclick="openShareModal()" title="Share Product">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </form>
            </div>
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

            <div class="review-list" id="reviewList">
                <?php foreach ($product['reviews'] as $review):
                    $review = is_object($review) ? (array)$review : $review;
                    $r_name = $review['customer_name'] ?? 'Anonymous';
                    $r_rating = $review['rating'] ?? 5;
                    $r_desc = $review['description'] ?? '';
                    $r_date = $review['created_at'] ?? '';
                    $r_variant = $review['variant_name'] ?? '';

                    $photoUrls = $review['processed_photos'] ?? [];
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

            <div class="pagination-container" id="reviewPagination" style="margin-top: 20px; display: flex; justify-content: center; gap: 5px;"></div>

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
        <a href="/app/views/home.php" style="color: #fc84a3;">Go Home</a>
    </div>
<?php endif; ?>

<div class="login-modal-overlay" id="loginModalOverlay">
    <div class="login-modal-content">
        <div style="font-size: 3.5rem; color: #ffcc00; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <h3 style="font-size: 1.6rem; margin-bottom: 10px; color:#333;">Login Required</h3>
        <p style="color: #666; margin-bottom: 25px; font-size:1rem;">You need to log in to add items to your cart.</p>
        <p style="font-size: 0.9rem; color: #999; margin-bottom: 25px;">
            Redirecting to login in <span id="countdown" style="font-weight:bold; color:#333;">5</span> seconds...
        </p>

        <a href="/app/views/security/signIn.php"
            style="display:inline-block; background:#fc84a3; color:white; width:100px; border-radius:30px; text-decoration:none; font-weight:600; box-shadow:0 5px 15px rgba(252,132,163,0.4);">
            Login Now
        </a>
    </div>
</div>

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
        <h2 style="margin-bottom: 20px;">More Options</h2>

        <div class="related-slider-wrapper">
            <button class="related-arrow related-prev" onclick="scrollRelated(-1)">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="related-product-grid related-grid" id="relatedGrid">
                <?php foreach ($product['related_products'] as $related):
                    $rImg = !empty($related['img_url']) ? $related['img_url'] : DEFAULT_IMG;
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

            <button class="related-arrow related-next" onclick="scrollRelated(1)">
                <i class="fas fa-chevron-right"></i>
            </button>
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

<div id="shareModalOverlay" class="share-modal-overlay" onclick="closeShareModal(event)">
    <div class="share-modal-content">
        <span class="share-modal-close" onclick="closeShareModal(event, true)">&times;</span>

        <h3 style="margin:0; color:#333;">Share this Product</h3>
        <p style="color:#777; font-size:0.9rem; margin-top:5px;">Scan QR code to view</p>

        <div id="qrcode-container"></div>

        <div class="share-actions">
            <button class="btn-share-action btn-copy" onclick="copyLinkFromModal(this)">
                <i class="fas fa-link"></i> Copy Link
            </button>

            <button class="btn-share-action btn-download" onclick="downloadQRCode()">
                <i class="fas fa-download"></i> Save QR
            </button>
        </div>
    </div>
</div>

<script>
    const WISHLIST_API_URL = '/app/controllers/wishlist_router.php';

    // Toast Auto Hide
    // Automatically hide toast messages after 4 seconds
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
    <?php if ($loginRequiredMsg): ?>
        const loginModal = document.getElementById('loginModalOverlay');
        const countdownEl = document.getElementById('countdown');
        let seconds = 5;

        if (loginModal) {
            loginModal.style.display = 'flex';

            const timer = setInterval(() => {
                seconds--;
                if (countdownEl) countdownEl.innerText = seconds;

                if (seconds <= 0) {
                    clearInterval(timer);
                    window.location.href = '/app/views/security/signIn.php';
                }
            }, 1000);
        }
    <?php endif; ?>


    // Review Lightbox Logic
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
        // Show arrows only if multiple images
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

    //keyboard action
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") closeReviewGallery();
        if (document.getElementById('reviewLightbox').style.display === 'flex') {
            if (event.key === "ArrowLeft") navigateReview(-1);
            if (event.key === "ArrowRight") navigateReview(1);
        }
    });

    // Auto Slide & Image Navigation
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

    // Change main image when clicking thumbnails
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

    // Variant Selection 
    function selectVariant(element, qty, status, id, img) {
        stopAutoSlide();

        // Update active badge
        document.querySelectorAll('.option-badge').forEach(el => el.classList.remove('active-variant'));
        element.classList.add('active-variant');

        let stockQty = parseInt(qty);
        const stockDisplay = document.getElementById('stockDisplay');
        const qtyInput = document.getElementById('quantity');
        const addToCartBtn = document.querySelector('.add-to-cart-btn');

        // Update Stock Display & Button State
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

        // Change image to variant image if available
        if (img && img.trim() !== '') {
            changeMainImage(img, null, false);
        }
    }

    //Quantity Controls
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

    // Dynamically Show Toast
    function showToast(message, type = 'success') {
        const oldToast = document.getElementById('js-toast');
        if (oldToast) oldToast.remove();

        const div = document.createElement('div');
        div.id = 'js-toast';
        div.className = `toast-notification toast-${type}`;

        let iconClass = 'fa-check-circle';
        if (type === 'error') iconClass = 'fa-times-circle';
        if (type === 'warning') iconClass = 'fa-exclamation-triangle';

        div.innerHTML = `
            <div class="toast-content">
                <i class="fas ${iconClass} toast-icon"></i>
                <span class="toast-message">${message}</span>
            </div>
            <div class="toast-progress"></div>
        `;

        document.body.appendChild(div);

        setTimeout(() => {
            div.style.opacity = '0';
            div.style.transform = 'translateY(-20px)';
            setTimeout(() => div.remove(), 500);
        }, 4000);
    }

    // Wishlist Functionality
    function toggleWishlist(btn) {
        const variantId = document.getElementById('product_variant_id').value;

        if (!variantId) {
            showToast('Please select a variant first.', 'warning');
            return;
        }

        const formData = new URLSearchParams();
        formData.append('product_variant_id', variantId);

        fetch(WISHLIST_API_URL + '?action=toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const icon = btn.querySelector('i');
                    if (data.status === 'added') {
                        btn.classList.add('active');
                        icon.className = 'fas fa-heart';
                        showToast(data.message, 'success');
                    } else {
                        btn.classList.remove('active');
                        icon.className = 'far fa-heart';
                        showToast(data.message, 'success');
                    }
                } else {
                    if (data.code === 'LOGIN_REQUIRED') {
                        // Show Login Modal
                        const loginModal = document.getElementById('loginModalOverlay');
                        const countdownEl = document.getElementById('countdown');
                        if (loginModal) {
                            loginModal.style.display = 'flex';
                            let seconds = 5;
                            const timer = setInterval(() => {
                                seconds--;
                                if (countdownEl) countdownEl.innerText = seconds;
                                if (seconds <= 0) {
                                    clearInterval(timer);
                                    window.location.href = '/app/views/security/signIn.php';
                                }
                            }, 1000);
                        } else {
                            window.location.href = '/app/views/security/signIn.php';
                        }
                    } else {
                        showToast(data.message, 'error');
                    }
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Something went wrong.', 'error');
            });
    }

    // Related Products
    function scrollRelated(direction) {
        const container = document.getElementById('relatedGrid');
        const scrollAmount = 300;

        if (direction === 1) {
            container.scrollLeft += scrollAmount;
        } else {
            container.scrollLeft -= scrollAmount;
        }
    }

    // Review Pagination 
    const reviewItemsPerPage = 4;
    let currentReviewPage = 1;
    let allReviewCards = [];

    window.addEventListener('DOMContentLoaded', () => {
        const list = document.getElementById('reviewList');
        if (list) {
            allReviewCards = Array.from(list.getElementsByClassName('review-item'));
            if (allReviewCards.length > 0) {
                renderReviewPage();
            }
        }
    });

    function renderReviewPage() {
        const totalItems = allReviewCards.length;
        const totalPages = Math.ceil(totalItems / reviewItemsPerPage);

        allReviewCards.forEach(card => card.style.display = 'none');

        // Calculate range
        const start = (currentReviewPage - 1) * reviewItemsPerPage;
        const end = start + reviewItemsPerPage;
        const itemsToShow = allReviewCards.slice(start, end);

        // Show items for current page
        itemsToShow.forEach(card => {
            card.style.display = 'flex';
        });

        renderReviewPaginationHTML(totalPages);
    }

    function renderReviewPaginationHTML(totalPages) {
        const container = document.getElementById('reviewPagination');
        if (!container) return;

        container.innerHTML = '';

        if (totalPages <= 1) return;

        const firstBtn = createPageBtn("&laquo;", "Go to First Page", currentReviewPage === 1, () => changePage(1));
        container.appendChild(firstBtn);

        const range = 2;

        // Generate Page Numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentReviewPage - range && i <= currentReviewPage + range)) {
                const isActive = (i === currentReviewPage);
                const pageBtn = createPageBtn(i, `Go to Page ${i}`, false, () => changePage(i));
                if (isActive) pageBtn.classList.add('active');
                container.appendChild(pageBtn);
            } else if (i === currentReviewPage - range - 1 || i === currentReviewPage + range + 1) {
                const ellipsis = document.createElement('span');
                ellipsis.className = 'page-ellipsis';
                ellipsis.innerText = '...';
                container.appendChild(ellipsis);
            }
        }

        const lastBtn = createPageBtn("&raquo;", "Go to Last Page", currentReviewPage === totalPages, () => changePage(totalPages));
        container.appendChild(lastBtn);
    }

    function createPageBtn(html, title, isDisabled, onClick) {
        const a = document.createElement('a');
        a.className = `page-link ${isDisabled ? 'disabled' : ''}`;
        a.innerHTML = html;
        a.title = title;
        if (!isDisabled) {
            a.onclick = onClick;
        }
        return a;
    }

    function changePage(newPage) {
        if (newPage === currentReviewPage) return;

        currentReviewPage = newPage;
        renderReviewPage();

        const anchor = document.getElementById('reviews-anchor');
        if (anchor) {
            const headerOffset = 100;
            const elementPosition = anchor.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

            window.scrollTo({
                top: offsetPosition,
                behavior: "smooth"
            });
        }
    }

    // --- Share & QR Code Logic ---
    function openShareModal() {
        const overlay = document.getElementById('shareModalOverlay');
        const container = document.getElementById('qrcode-container');
        const currentUrl = window.location.href;

        // clear previous QR code (prevent duplicates)
        container.innerHTML = "";

        // new QRCode
        try {
            new QRCode(container, {
                text: currentUrl,
                width: 180,
                height: 180,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        } catch (e) {
            console.error("QR Code Error:", e);
            container.innerHTML = "<p>Error generating QR Code</p>";
        }

        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden'; 
    }

    function closeShareModal(event, force = false) {
        if (force || event.target.id === 'shareModalOverlay') {
            document.getElementById('shareModalOverlay').style.display = 'none';
            document.body.style.overflow = 'auto'; 
        }
    }

    // copy link functionality 
    function copyLinkFromModal(btn) {
        const url = window.location.href;
        navigator.clipboard.writeText(url).then(() => {
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            btn.style.background = '#d4edda'; 

            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.style.background = '#f0f0f0';
            }, 2000);

            if (typeof showToast === 'function') {
                showToast('Link copied to clipboard!', 'success');
            }
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }

    function downloadQRCode() {
        const container = document.getElementById('qrcode-container');
        const img = container.querySelector('img');

        if (img && img.src) {
            const link = document.createElement('a');
            link.href = img.src;
            link.download = 'product-qrcode.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } else {
            const canvas = container.querySelector('canvas');
            if (canvas) {
                const link = document.createElement('a');
                link.href = canvas.toDataURL("image/png");
                link.download = 'product-qrcode.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } else {
                alert("QR Code not ready yet, please try again.");
            }
        }
    }
</script>

<?php include '../footer.php' ?>