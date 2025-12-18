<?php
$title = "Category Home Page";
$pageCSS = "categoryhomepage.css";

include  '../header.php';
include  '../../controllers/productController.php';

$controller = new ProductController();
$products = $controller->index();
$categories = $controller->getAllCategories();
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
            <a href="product-details.php?product_id=<?php echo $product['product_id']; ?>" class="productCard-link">

              <div class="productCard">
                <img src="/public/images/<?php echo $category['category_name']; ?>/<?php echo $product['img_url']; ?>" alt="<?php echo $product['product_name']; ?>">
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

<?php include '../footer.php' ?>