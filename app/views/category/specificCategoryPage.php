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
          <a href="product_detail.php?id=<?php echo $product['product_id']; ?>" class="productCard-link">
            <div class="productCard">
              <img src="/public/images/<?php echo $category['category_name']; ?>/<?php echo $product['img_url']; ?>" alt="<?php echo $product['product_name']; ?>">
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

<?php include '../footer.php'; ?>