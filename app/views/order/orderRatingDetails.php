<?php
session_start();
$title = "Order Rating & Review";
$pageCSS = "orderrating.css";

include '../header.php';
include '../../controllers/orderController.php';
include '../../controllers/reviewController.php';
require_once __DIR__ . '/../../helpers/auth.php';

authenticate();
$memberController = new userController();
$orderController = new OrderController();
$reviewController = new ReviewController();

// $orderId = $_GET['order_id'] ?? null;
// $reviewItems = $orderController->getReviewOrders($orderId);
$item = $orderController->getReviewOrderDetails();
if ($item != null) $custId = $item['customer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $orderId = $_GET['order_id'] ?? null;
  $reviewController->reviewOrder();
  $memberController->updateRewardPoint($custId);
  $_SESSION['show_success_popup'] = true;
}
?>

<section class="order-rating-section">
  <div class="order-rating-header">
    <a href="#" class="back-link" onclick="history.back(); return false;">&#x293A;</a>
    <h1>Order Rating & Review</h1>
  </div>
  <?php if (!empty($item)): ?>
    <div class="modal-content">
      <div class="order-card">
        <div class="reviewOrderCard">
          <div class="col image-col">
            <img src="/public/images/<?= $item['category_name'] ?>/<?= $item['img_url'] ?>" alt="<?= $item['product_name'] ?>">
          </div>

          <div class="col info-col">
            <p class="name"><?= $item['product_name'] ?></p>
            <p class="description"><?= $item['description'] ?></p>
            <p class="variant">Variant: <?= $item['variant_name'] ?></p>
            <p class="payment">Ordered On: <?= $item['payment_time'] ?></p>
          </div>

          <div class="col qty-col">x<?= $item['order_qty'] ?></div>

          <div class="col subtotal-col">
            RM <?= number_format($item['price'] * $item['order_qty'], 2) ?>
          </div>
        </div>
      </div>

      <form method="POST" enctype="multipart/form-data" id="reviewForm">
        <input type="hidden" name="product_variant_id" value="<?= $item['product_variant_id'] ?>" id="modalProductId">
        <input type="hidden" name="rating" value="0" id="modalRating">

        <div class="review-rating">
          <label for="modalRating">Rating</label>
          <div class="stars" id="starContainer">
            <?php for ($i = 1; $i <= 5; $i++): ?>
              <span class="star" data-value="<?= $i ?>">&#9733;</span>
            <?php endfor; ?>
          </div>
          <p class="rating-error" style="color:red; font-size:14px; display:none; margin-top:5px;">
            Please select a rating.
          </p>
        </div>

        <div class="review-description">
          <label for="modalDescription">Description</label>
          <textarea name="description" id="modalDescription" placeholder="Write your review..."></textarea>
        </div>

        <div class="media-upload-container">
          <div class="drop-zone" id="dropZone">
            <svg xmlns="http://www.w3.org/2000/svg" class="camera-icon" viewBox="0 0 640 640" width="40" height="40">
              <path d="M257.1 96C238.4 96 220.9 105.4 210.5 120.9L184.5 160L128 160C92.7 160 64 188.7 64 224L64 480C64 515.3 92.7 544 128 544L512 544C547.3 544 576 515.3 576 480L576 224C576 188.7 547.3 160 512 160L455.5 160L429.5 120.9C419.1 105.4 401.6 96 382.9 96L257.1 96zM250.4 147.6C251.9 145.4 254.4 144 257.1 144L382.8 144C385.5 144 388 145.3 389.5 147.6L422.7 197.4C427.2 204.1 434.6 208.1 442.7 208.1L512 208.1C520.8 208.1 528 215.3 528 224.1L528 480.1C528 488.9 520.8 496.1 512 496.1L128 496C119.2 496 112 488.8 112 480L112 224C112 215.2 119.2 208 128 208L197.3 208C205.3 208 212.8 204 217.3 197.3L250.5 147.5zM320 448C381.9 448 432 397.9 432 336C432 274.1 381.9 224 320 224C258.1 224 208 274.1 208 336C208 397.9 258.1 448 320 448zM256 336C256 300.7 284.7 272 320 272C355.3 272 384 300.7 384 336C384 371.3 355.3 400 320 400C284.7 400 256 371.3 256 336z" />
            </svg>
            <p>Drag & drop photos/videos here<br>or click to upload</p>
            <input type="file" id="mediaUpload" name="media[]" accept="image/*,video/*" multiple hidden>
          </div>

          <div class="preview"></div>
        </div>

        <div class="submit-btn">
          <button type="submit">Submit Review</button>
        </div>
      </form>
      <div id="cropperModal" class="rating-popup-overlay">
        <div class="center-popup" style="max-width: 600px;">
          <h3>Crop Image</h3>
          <div class="canvas-container" style="position: relative; overflow: hidden; background: #333;">
            <canvas id="cropCanvas" style="max-width: 100%; cursor: crosshair;"></canvas>
          </div>
          <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: center;">
            <button type="button" id="cropCancel" class="back-btn" style="background: #ccc;">Cancel</button>
            <button type="button" id="cropConfirm" class="back-btn">Crop & Save</button>
          </div>
        </div>
      </div>
    </div>
  <?php else: ?>
    <p class="no-order">No order details found.</p>
  <?php endif; ?>
  <?php if (!empty($_SESSION['show_success_popup'])): ?>
    <div class="rating-popup-overlay show">
      <div class="center-popup">
        <h3>Order Rating & Review</h3>
        <p>Your review has been submitted successfully!</p>
        <p>Congratulations! You have earned 10 points on your review.</p>
        <button class="back-btn" onclick="window.location.href='customerOrderHistory.php?id=<?= $item['customer_id'] ?>'">Back to Orders</button>
      </div>
    </div>
  <?php unset($_SESSION['show_success_popup']);
  endif; ?>
</section>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const stars = document.querySelectorAll(".star");
    const ratingInput = document.getElementById("modalRating");
    const dropZone = document.getElementById("dropZone");
    const mediaInput = document.getElementById("mediaUpload");
    const previewDiv = document.querySelector(".media-upload-container .preview");
    const reviewForm = document.getElementById("reviewForm");
    const ratingError = document.querySelector(".rating-error");

    const cropperModal = document.getElementById('cropperModal');
    const canvas = document.getElementById('cropCanvas');
    const ctx = canvas.getContext('2d');

    let selectedFiles = [];
    let currentImg = new Image();
    let originalFile;
    let isDrawing = false;
    let startX, startY;
    let selection = {
      x: 0,
      y: 0,
      w: 0,
      h: 0
    };
    let editingIndex = -1; // Track if we are editing an existing image

    // --- Star Rating ---
    stars.forEach(star => {
      star.addEventListener("click", () => {
        const value = star.dataset.value;
        ratingInput.value = value;
        stars.forEach((s, i) => s.classList.toggle("filled", i < value));

        if (ratingError.style.display !== 'none') {
          ratingError.style.display = 'none';
        }
      });
    });

    // --- File Handling ---
    dropZone.addEventListener("click", () => mediaInput.click());
    ["dragenter", "dragover", "dragleave", "drop"].forEach(e => dropZone.addEventListener(e, ev => ev.preventDefault()));

    dropZone.addEventListener("drop", (e) => {
      const files = Array.from(e.dataTransfer.files);
      handleNewFiles(files);
    });

    mediaInput.addEventListener("change", () => {
      const files = Array.from(mediaInput.files);
      handleNewFiles(files);
    });

    function handleNewFiles(files) {
      files.forEach(file => {
        if (file.type.startsWith('video/')) {
          selectedFiles.push(file);
        } else if (file.type.startsWith('image/')) {
          // For new uploads, we add them directly first, 
          // then user can click them to crop if they want.
          selectedFiles.push(file);
        }
      });
      renderPreviews();
    }

    // --- Open Cropper for Specific File ---
    function openCropper(file, index) {
      editingIndex = index;
      originalFile = file;
      const reader = new FileReader();
      reader.onload = (e) => {
        currentImg = new Image();
        currentImg.onload = () => {
          // Set canvas to dynamic size based on image and screen
          const maxWidth = window.innerWidth * 0.9;
          const maxHeight = window.innerHeight * 0.7;
          let width = currentImg.width;
          let height = currentImg.height;

          const ratio = Math.min(maxWidth / width, maxHeight / height, 1);
          canvas.width = width * ratio;
          canvas.height = height * ratio;

          selection = {
            x: 0,
            y: 0,
            w: 0,
            h: 0
          };
          drawCanvas();
          cropperModal.classList.add('show');
        };
        currentImg.src = e.target.result;
      };
      reader.readAsDataURL(file);
    }

    // --- Selection Logic ---
    // --- Updated Selection Logic ---
    canvas.onmousedown = (e) => {
      isDrawing = true;
      const rect = canvas.getBoundingClientRect();

      // Calculate the scale between the CSS size and the internal canvas size
      const scaleX = canvas.width / rect.width;
      const scaleY = canvas.height / rect.height;

      // Multiply the mouse position by the scale to get accurate coordinates
      startX = (e.clientX - rect.left) * scaleX;
      startY = (e.clientY - rect.top) * scaleY;
    };

    canvas.onmousemove = (e) => {
      if (!isDrawing) return;
      const rect = canvas.getBoundingClientRect();

      const scaleX = canvas.width / rect.width;
      const scaleY = canvas.height / rect.height;

      let curX = (e.clientX - rect.left) * scaleX;
      let curY = (e.clientY - rect.top) * scaleY;

      selection.w = curX - startX;
      selection.h = curY - startY;
      selection.x = startX;
      selection.y = startY;

      drawCanvas();
    };

    canvas.onmouseup = () => isDrawing = false;

    function drawCanvas() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(currentImg, 0, 0, canvas.width, canvas.height);
      if (selection.w !== 0) {
        ctx.fillStyle = "rgba(0, 0, 0, 0.5)";
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.save();
        ctx.beginPath();
        ctx.rect(selection.x, selection.y, selection.w, selection.h);
        ctx.clip();
        ctx.drawImage(currentImg, 0, 0, canvas.width, canvas.height);
        ctx.restore();
        ctx.strokeStyle = "#fff";
        ctx.setLineDash([5, 5]);
        ctx.strokeRect(selection.x, selection.y, selection.w, selection.h);
      }
    }

    // --- Finalize Crop ---
    document.getElementById('cropConfirm').addEventListener('click', () => {
      const tempCanvas = document.createElement('canvas');
      const tempCtx = tempCanvas.getContext('2d');
      const scaleX = currentImg.width / canvas.width;
      const scaleY = currentImg.height / canvas.height;

      let finalX, finalY, finalW, finalH;
      if (Math.abs(selection.w) < 5) {
        finalX = 0;
        finalY = 0;
        finalW = currentImg.width;
        finalH = currentImg.height;
      } else {
        finalW = Math.abs(selection.w) * scaleX;
        finalH = Math.abs(selection.h) * scaleY;
        finalX = (selection.w < 0 ? selection.x + selection.w : selection.x) * scaleX;
        finalY = (selection.h < 0 ? selection.y + selection.h : selection.y) * scaleY;
      }

      tempCanvas.width = finalW;
      tempCanvas.height = finalH;
      tempCtx.drawImage(currentImg, finalX, finalY, finalW, finalH, 0, 0, finalW, finalH);

      tempCanvas.toBlob((blob) => {
        const croppedFile = new File([blob], originalFile.name, {
          type: 'image/jpeg'
        });
        // Replace the old file with the cropped one
        selectedFiles[editingIndex] = croppedFile;
        renderPreviews();
        cropperModal.classList.remove('show');
      }, 'image/jpeg', 0.95);
    });

    document.getElementById('cropCancel').addEventListener('click', () => {
      cropperModal.classList.remove('show');
    });

    // --- Render Previews ---
    function renderPreviews() {
      previewDiv.innerHTML = "";
      const dataTransfer = new DataTransfer();

      selectedFiles.forEach((file, index) => {
        const wrapper = document.createElement("div");
        wrapper.classList.add("media-preview-item");
        const fileURL = URL.createObjectURL(file);
        const type = file.type.split("/")[0];

        // 1. Remove Button (The 'X')
        const removeBtn = document.createElement("span");
        removeBtn.classList.add("remove-btn");
        removeBtn.innerHTML = "&times;";
        removeBtn.onclick = (e) => {
          e.stopPropagation();
          selectedFiles.splice(index, 1);
          renderPreviews();
        };

        // 2. Edit Button (The Pencil - Only for images)
        if (type === "image") {
          const editBtn = document.createElement("span");
          editBtn.classList.add("edit-icon-btn");
          // Inline SVG Pencil Icon
          editBtn.innerHTML = `
                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                </svg>`;
          editBtn.onclick = (e) => {
            e.stopPropagation();
            openCropper(file, index);
          };
          wrapper.appendChild(editBtn);
        }

        // 3. Media Element (Img or Video)
        const elem = document.createElement(type === "image" ? "img" : "video");
        elem.src = fileURL;
        if (type === "video") elem.controls = true;

        // NEW LOGIC: Clicking the image opens in a NEW TAB
        elem.onclick = () => {
          window.open(fileURL, '_blank');
        };

        wrapper.appendChild(removeBtn);
        wrapper.appendChild(elem);
        previewDiv.appendChild(wrapper);
        dataTransfer.items.add(file);
      });
      mediaInput.files = dataTransfer.files;
    }

    // Review form validation
    reviewForm.addEventListener("submit", (e) => {
      if (parseInt(ratingInput.value) === 0) {
        e.preventDefault();
        ratingError.style.display = 'block';
        ratingError.scrollIntoView({
          behavior: 'smooth',
          block: 'center'
        });
      }
    });
  });
</script>

<?php include '../footer.php' ?>