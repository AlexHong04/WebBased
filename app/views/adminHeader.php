<?php
require_once __DIR__ . '/../controllers/userController.php';
require_once __DIR__ . '/../helpers/auth.php';
authorizeAdmin();
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
  $controller = new userController();
  $controller->signOut();
  exit;
}
$currentId =  $_SESSION['adminId'] ?? null;
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="../../public/img/icon/logo.png" />
  <title><?php echo $title ?? "My site"; ?></title>
  <link rel="stylesheet" href="/public/css/pc_reset.css" />
  <link rel="stylesheet" href="/public/css/header_footer.css" />
  <?php if (!empty($pageCSS)): ?>
    <link rel="stylesheet" href="/public/css/<?php echo $pageCSS; ?>" />
  <?php endif; ?>
</head>

<body>
  <header>
    <div class="header-container">
      <div class="logo">
        <a href="/app/views/adminDashboard.php">
          <img src="/public/images/lovine_logo_circle.png" alt="Lovine Logo" class="logo-image" />
        </a>
      </div>
      <nav>
        <ul>
          <li><a href="/app/views/adminDashboard.php">Home</a></li>
          <li><a href="/app/views/member/memberListing.php">Member</a></li>
          <li><a href="/app/views/order/adminOrderListing.php">Order</a></li>
          <li><a href="/app/views/product/productList.php">Product</a></li>
          <li><a href="/app/views/product/remindUser.php">Notify User</a></li>
          <li><a href="/app/views/admin/adminListing.php">Admin</a></li>
        </ul>
      </nav>


      <nav>
        <ul>
          <!-- Search form (site-wide) -->
          <!-- <form class="header-search" action="/search.php" method="get" role="search" aria-label="Site search">
            <input type="search" name="q" placeholder="Search" aria-label="Search" />
            <button type="submit" aria-label="Search">
              <div class="cart-link">
                <svg class="cart-icon" viewBox="0 0 640 640" width="24" height="24">
                  <path fill="currentColor" d="M480 272C480 317.9 465.1 360.3 440 394.7L566.6 521.4C579.1 533.9 579.1 554.2 566.6 566.7C554.1 579.2 533.8 579.2 521.3 566.7L394.7 440C360.3 465.1 317.9 480 272 480C157.1 480 64 386.9 64 272C64 157.1 157.1 64 272 64C386.9 64 480 157.1 480 272zM272 416C351.5 416 416 351.5 416 272C416 192.5 351.5 128 272 128C192.5 128 128 192.5 128 272C128 351.5 192.5 416 272 416z" />
                </svg>
              </div>
            </button>
          </form> -->

          <?php if (isset($_SESSION['adminId'])): ?>
            <li class="dropdown">
              <a href="#" class="cart-link" title="My Account">
                <svg class="cart-icon" viewBox="0 0 640 640" width="24" height="24">
                  <path fill="currentColor" d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112 447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576 528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z" />
                </svg>
              </a>

              <ul class="dropdown-menu" style="right: 0; left: auto; min-width: 150px;">
                <li>
                  <a href="/app/views/userProfile/adminProfile.php?=<?=$currentId?>" style="display: flex; align-items: center; gap: 10px;">

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" fill="currentColor">
                      <path d="M384 112L384 128C384 145.7 369.7 160 352 160L288 160C270.3 160 256 145.7 256 128L256 112L192 112C183.2 112 176 119.2 176 128L176 512C176 520.8 183.2 528 192 528L448 528C456.8 528 464 520.8 464 512L464 128C464 119.2 456.8 112 448 112L384 112zM128 128C128 92.7 156.7 64 192 64L448 64C483.3 64 512 92.7 512 128L512 512C512 547.3 483.3 576 448 576L192 576C156.7 576 128 547.3 128 512L128 128zM288 384L352 384C396.2 384 432 419.8 432 464C432 472.8 424.8 480 416 480L224 480C215.2 480 208 472.8 208 464C208 419.8 243.8 384 288 384zM264 288C264 257.1 289.1 232 320 232C350.9 232 376 257.1 376 288C376 318.9 350.9 344 320 344C289.1 344 264 318.9 264 288z" />
                    </svg>

                    <span>My Profile</span>
                  </a>
                </li>
                <li>
                  <a href="/app/views/home.php?action=logout" style="display: flex; align-items: center; gap: 10px;">

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="20" height="20" fill="currentColor">
                      <path d="M224 160C241.7 160 256 145.7 256 128C256 110.3 241.7 96 224 96L160 96C107 96 64 139 64 192L64 448C64 501 107 544 160 544L224 544C241.7 544 256 529.7 256 512C256 494.3 241.7 480 224 480L160 480C142.3 480 128 465.7 128 448L128 192C128 174.3 142.3 160 160 160L224 160zM566.6 342.6C579.1 330.1 579.1 309.8 566.6 297.3L438.6 169.3C426.1 156.8 405.8 156.8 393.3 169.3C380.8 181.8 380.8 202.1 393.3 214.6L466.7 288L256 288C238.3 288 224 302.3 224 320C224 337.7 238.3 352 256 352L466.7 352L393.3 425.4C380.8 437.9 380.8 458.2 393.3 470.7C405.8 483.2 426.1 483.2 438.6 470.7L566.6 342.7z" />
                    </svg>

                    <span>Logout</span>
                  </a>
                </li>
              </ul>
            </li>

          <?php else: ?>
            <li><a href=" /app/views/security/signIn.php">Sign In/Sign Up</a>
            </li>
          <?php endif; ?>
        </ul>
      </nav>
    </div>
  </header>