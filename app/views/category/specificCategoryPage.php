<?php
$title = "Category Specific Page";
$pageCSS = "categorypage.css";

include '../header.php';
include '../../controllers/productController.php';

$controller = new ProductController();
$products = $controller->getProductsByCategoryId();
$categoryName = $controller->getCategory();

?>

<div class="specific-category-page">
  <div class="container">
    <h1><?php echo str_replace('_', ' ', $categoryName['category_name']); ?></h1>
    <hr>

    <div class="products-grid">
      <?php if (empty($products)): ?>
        <p>No products found in this category.</p>
      <?php else: ?>
        <?php foreach ($products as $product): ?>
          <?php
          $images = explode(',', $product['img_url']);
          $firstImage = trim($images[0]); ?>
          <a href="../product/product_details.php?id=<?php echo $product['product_id']; ?>" class="productCard-link">
            <div class="productCard" data-images="<?= htmlspecialchars($product['img_url']) ?>">
              <img src=" /public/images/<?php echo $categoryName['category_name']; ?>/<?php echo $firstImage; ?>" alt="<?php echo $product['product_name']; ?>" class="product-img">
              <h3><?php echo $product['product_name']; ?></h3>
              <p><?php echo $product['description']; ?></p>
              <p class="price">RM <?php echo number_format($product['sale_price'], 2); ?></p>
            </div>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
  document.querySelectorAll('.productCard').forEach(card => {
    const img = card.querySelector('.product-img');
    const images = card.dataset.images
      .split(',')
      .map(i => i.trim());

    let index = 0;
    let interval;

    card.addEventListener('mouseenter', () => {
      if (images.length <= 1) return;

      interval = setInterval(() => {
        index = (index + 1) % images.length;
        img.src = img.src.replace(/[^/]+$/, images[index]);
      }, 800); // change image every 0.8s
    });

    card.addEventListener('mouseleave', () => {
      clearInterval(interval);
      index = 0;
      img.src = img.src.replace(/[^/]+$/, images[0]);
    });
  });
</script>

<?php include '../footer.php'; ?>