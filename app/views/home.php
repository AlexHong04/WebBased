<?php
$title = "Home Page";
$pageCSS = "home.css";

include 'header.php';
require_once __DIR__ . '/../controllers/userController.php';
require_once __DIR__ .'/../helpers/auth.php';
require_once __DIR__ . '/../helpers/html.php';

// authenticate();
// var_dump($_SESSION);
// var_dump($_COOKIE['remember_token']);

$userController = new userController();
// Fetch data for sections
// $top_selling = $userController->getTopSalesData();
// echo "<pre>";
// print_r($top_selling); 
// echo "</pre>";
// exit();
// array (replace with DB queries)
$top_selling = array(
    array("id" => 1, "name" => "Premium Leather Wallet", "price" => 75.00, "img" => "../../public/images/Earrings/pearl_earrings_big.jpeg", "category" => "top", "rating" => 4.5),
    array("id" => 2, "name" => "Smart Fitness Tracker", "price" => 99.99, "img" => "../../public/images/Earrings/pearl_earrings_big.jpeg", "category" => "top", "rating" => 4.2),
    array("id" => 3, "name" => "Wireless Headphones", "price" => 249.00, "img" => "../../public/images/Earrings/pearl_earrings_big.jpeg", "category" => "top", "rating" => 4.8),
);

// $new_arrivals = array(
//     array("id" => 11, "name" => "Eco-Friendly Water Bottle", "price" => 25.00, "img" => "../../public/images/Earrings/pearl_earrings_big.jpeg", "category" => "new"),
//     array("id" => 12, "name" => "Wireless Charging Pad", "price" => 45.00, "img" => "../../public/images/Earrings/pearl_earrings_big.jpeg", "category" => "new"),
//     array("id" => 13, "name" => "Wireless Charging Pad", "price" => 45.00, "img" => "../../public/images/Earrings/pearl_earrings_big.jpeg", "category" => "new"),
// );

// Slider images: replace with your preferred images/paths
$slides = array(
    array('img' => '../../public/images/HeroBackground.png', 'title' => 'Discover Our New Collection', 'subtitle' => 'Fresh styles have just landed. Explore the latest trends.'),
    array('img' => '../../public/images/HeroBackground2.png', 'title' => 'Limited Time Offers', 'subtitle' => 'Don\'t miss our flash deals and exclusive discounts.'),
    array('img' => '../../public/images/HeroBackground3.png', 'title' => 'Handpicked For You', 'subtitle' => 'Top sellers and new arrivals curated daily.')
);


// --- Render helpers ---
function renderProducts($products, $category, $title, $viewAll = '#')
{
?>
    <div class="section-header">
        <h2 class="section-title"><?php echo htmlspecialchars($title); ?></h2>
        <a href="<?php echo $viewAll; ?>" class="section-link">View All</a>
    </div>
    <div class="product-grid" role="list">
        <?php foreach ($products as $p): if (($p['category'] ?? null) !== $category) continue; ?>
            <article class="product-card" role="listitem" aria-label="<?php echo htmlspecialchars($p['name']); ?>">
                <div class="product-img" style="background-image: url('<?php echo htmlspecialchars($p['img']); ?>');"></div>
                <div class="product-body">
                    <p class="product-name"><?php echo htmlspecialchars($p['name']); ?></p>
                    <div class="product-rating" aria-hidden="true">
                        <?php $rating = isset($p['rating']) ? round($p['rating']) : 0;
                        for ($i = 1; $i <= 5; $i++) {
                            echo $i <= $rating ? '<span class="star">★</span>' : '<span class="star empty">☆</span>';
                        } ?>
                        <span class="rating-number"><?php echo isset($p['rating']) ? number_format($p['rating'], 1) : '0.0'; ?></span>
                    </div>
                    <div class="product-price">RM<?php echo number_format($p['price'], 2); ?></div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php
}
?>

<main class="flex-1">
    <section class="slider" aria-label="Homepage slideshow">
        <div class="slides" id="slides">
            <?php foreach ($slides as $s): ?>
                <div class="slide" style="background-image: url('<?php echo htmlspecialchars($s['img']); ?>')" role="group">
                    <div class="slide-overlay"></div>
                    <div class="slide-content">
                        <h2><?php echo htmlspecialchars($s['title']); ?></h2>
                        <p><?php echo htmlspecialchars($s['subtitle']); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button class="slider-arrow left" id="prev" aria-label="Previous slide">◀</button>
        <button class="slider-arrow right" id="next" aria-label="Next slide">▶</button>

        <div class="slider-dot-show" id="dots">
            <?php for ($i = 0; $i < count($slides); $i++): ?>
                <button class="slider-dot <?php echo $i === 0 ? '' : 'inactive'; ?>" data-index="<?php echo $i; ?>" aria-label="Go to slide <?php echo $i + 1; ?>"></button>
            <?php endfor; ?>
        </div>
    </section>

    <?php renderProducts($top_selling, 'top', 'Top Selling Products'); ?>

    <!-- <?php renderProducts($new_arrivals, 'new', 'New Arrivals'); ?> -->

</main>
<?php showToast(); ?>
<?php include 'footer.php'; ?>

<!-- Simple slider script -->
<script src="../../public/js/home.js"></script>