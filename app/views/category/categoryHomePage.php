<?php
$title = "Category Home Page";
$pageCSS = "categoryhomepage.css";

include  '../header.php';
include  '../../controllers/productsController.php';

$controller = new ProductController();
$products = $controller->index();
$categories = $controller->fetchAllCategories();
?>

<div class="category-container">

  <?php foreach ($categories as $category): ?>
    <div class="category">
      <h2><?php echo str_replace('_', ' ', $category['category_name']); ?></h2>

      <div class="products-wrapper">
        <div class="products">

          <?php
          $categoryProducts = array_filter($products, function ($p) use ($category) {
            return $p['category_id'] === $category['category_id'];
          });

          $displayProducts = array_slice($categoryProducts, 0, 4);

          foreach ($displayProducts as $product):
          ?>
            <a href="../product/product_details.php?id=<?php echo $product['product_id']; ?>" class="productCard-link">
              <div class="productCard" data-images="<?= htmlspecialchars($product['img_url']) ?>">
                <?php
                $images = explode(',', $product['img_url']);
                $firstImage = trim($images[0]); ?>
                <img src="/public/images/<?php echo $category['category_name']; ?>/<?php echo $firstImage; ?>" alt="<?php echo $product['product_name']; ?>" class="product-img">
                <h3><?php echo $product['product_name']; ?></h3>
                <p><?php echo $product['description']; ?></p>
                <p class="price">RM <?php echo number_format($product['sale_price'], 2); ?></p>
              </div>
            </a>
          <?php endforeach; ?>

        </div>

        <?php if (count($categoryProducts) > 3): ?>
          <div class="next-btn-container">
            <a href="specificCategoryPage.php?id=<?php echo $category['category_id']; ?>" class="next-btn">&gt;</a>
            <!-- <button class="next-btn" data-post="">&gt;</button> -->
          </div>
        <?php endif; ?>
      </div>

    </div>
  <?php endforeach; ?>

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

<?php include '../footer.php' ?>