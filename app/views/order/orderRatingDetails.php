<?php
session_start();
$title = "Order Rating & Review";
$pageCSS = "orderrating.css";

include '../header.php';
include '../../controllers/orderController.php';
include '../../controllers/reviewController.php';

$memberController = new userController();
$orderController = new OrderController();
$reviewController = new ReviewController();

$item = $orderController->getReviewOrderDetails();
$custId = $item['customer_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
  <div class="modal-content">
    <div class="order-card">
      <div class="orderCard">
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
  </div>
  <?php if (!empty($_SESSION['show_success_popup'])): ?>
    <div class="popup-overlay show">
      <div class="center-popup">
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
    let selectedFiles = [];

    // Star rating
    stars.forEach(star => {
      star.addEventListener("click", () => {
        const value = star.dataset.value;
        ratingInput.value = value;
        stars.forEach((s, i) => {
          s.classList.toggle("filled", i < value);
        });
      });
    });

    // 📂 Click to open file dialog
    dropZone.addEventListener("click", () => mediaInput.click());

    // 🚫 Prevent default drag behavior
    ["dragenter", "dragover", "dragleave", "drop"].forEach(event => {
      dropZone.addEventListener(event, e => e.preventDefault());
    });

    // 🎨 Visual feedback
    dropZone.addEventListener("dragover", () => dropZone.classList.add("drag-over"));
    dropZone.addEventListener("dragleave", () => dropZone.classList.remove("drag-over"));
    dropZone.addEventListener("drop", (e) => {
      dropZone.classList.remove("drag-over");
      handleFiles(e.dataTransfer.files);
    });

    // 📁 File input change
    mediaInput.addEventListener("change", () => handleFiles(mediaInput.files));

    function handleFiles(files) {
      const newFiles = Array.from(files);

      newFiles.forEach(file => {
        const exists = selectedFiles.some(
          f => f.name === file.name && f.size === file.size
        );
        if (!exists) {
          selectedFiles.push(file);
        }
      });

      renderPreviews();
    }

    function renderPreviews() {
      previewDiv.innerHTML = "";

      const dataTransfer = new DataTransfer();

      selectedFiles.forEach((file, index) => {
        const type = file.type.split("/")[0];
        let elem;

        if (type === "image") {
          elem = document.createElement("img");
        } else if (type === "video") {
          elem = document.createElement("video");
          elem.controls = true;
        } else return;

        elem.src = URL.createObjectURL(file);

        const wrapper = document.createElement("div");
        wrapper.classList.add("media-preview-item");

        const removeBtn = document.createElement("span");
        removeBtn.classList.add("remove-btn");
        removeBtn.innerHTML = "&times;";
        removeBtn.onclick = () => removeFile(index);

        wrapper.appendChild(removeBtn);
        wrapper.appendChild(elem);
        previewDiv.appendChild(wrapper);

        dataTransfer.items.add(file);
      });

      mediaInput.files = dataTransfer.files;
    }

    function removeFile(index) {
      selectedFiles.splice(index, 1);
      renderPreviews();
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