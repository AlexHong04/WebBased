<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="icon" href="../../public/img/icon/logo.png" />
	<title><?php echo $title ?? "My site"; ?></title>
	<link rel="stylesheet" href="/public/css/pc_reset.css" />
	<link rel="stylesheet" href="/public/css/header_footer.css" />
	<!-- <link rel="stylesheet" href="/public/css/animation.css" /> -->
	<?php if (!empty($pageCSS)): ?>
		<link rel="stylesheet" href="/public/css/<?php echo $pageCSS; ?>" />
	<?php endif; ?>
</head>

<body>
	<header>
		<div class="header-container">
			<div class="logo">Lo<span>vine</span></div>
			<nav>
				<ul>
					<li><a href="/app/views/home.php">Home</a></li>
					<li class="dropdown">
						<a href="#">Category</a>
						<ul class="dropdown-menu">
							<li><a href="#">All</a></li>
							<li><a href="#">Bracelet</a></li>
							<li><a href="#">Earrings</a></li>
							<li><a href="#">Hairclaw</a></li>
							<li><a href="#">Necklace</a></li>
							<li><a href="#">Ring</a></li>
						</ul>
					</li>
					<li><a href="#">Order</a></li>
				</ul>
			</nav>


			<nav>
				<ul>
					<!-- Search form (site-wide) -->
					<form class="header-search" action="/search.php" method="get" role="search" aria-label="Site search">
						<input type="search" name="q" placeholder="Search" aria-label="Search" />
						<button type="submit" aria-label="Search">
							<div class="cart-link">
								<svg class="cart-icon" viewBox="0 0 640 640" width="24" height="24">
									<path fill="currentColor" d="M480 272C480 317.9 465.1 360.3 440 394.7L566.6 521.4C579.1 533.9 579.1 554.2 566.6 566.7C554.1 579.2 533.8 579.2 521.3 566.7L394.7 440C360.3 465.1 317.9 480 272 480C157.1 480 64 386.9 64 272C64 157.1 157.1 64 272 64C386.9 64 480 157.1 480 272zM272 416C351.5 416 416 351.5 416 272C416 192.5 351.5 128 272 128C192.5 128 128 192.5 128 272C128 351.5 192.5 416 272 416z" />
								</svg>
							</div>
						</button>
					</form>

					<li>
						<a href="#" class="cart-link">
							<span class="cart-count">0</span>
							<svg class="cart-icon" viewBox="0 0 24 24" width="24" height="24">
								<path fill="currentColor" d="M17,18C15.89,18 15,18.89 15,20A2,2 0 0,0 17,22A2,2 0 0,0 19,20C19,18.89 18.1,18 17,18M1,2V4H3L6.6,11.59L5.24,14.04C5.09,14.32 5,14.65 5,15A2,2 0 0,0 7,17H19V15H7.42A0.25,0.25 0 0,1 7.17,14.75C7.17,14.7 7.18,14.66 7.2,14.63L8.1,13H15.55C16.3,13 16.96,12.58 17.3,11.97L20.88,5.5C20.95,5.34 21,5.17 21,5A1,1 0 0,0 20,4H5.21L4.27,2M7,18C5.89,18 5,18.89 5,20A2,2 0 0,0 7,22A2,2 0 0,0 9,20C9,18.89 8.1,18 7,18Z" />
							</svg>
						</a>
					</li>
					<li>
						<a href="/app/views/userProfile/profile.php" class="cart-link">
							<svg class="cart-icon" viewBox="0 0 640 640" width="24" height="24">
								<path fill="currentColor" d="M320 312C386.3 312 440 258.3 440 192C440 125.7 386.3 72 320 72C253.7 72 200 125.7 200 192C200 258.3 253.7 312 320 312zM290.3 368C191.8 368 112 447.8 112 546.3C112 562.7 125.3 576 141.7 576L498.3 576C514.7 576 528 562.7 528 546.3C528 447.8 448.2 368 349.7 368L290.3 368z" />
							</svg>
						</a>
					</li>
					<li><a href="/app/views/security/signIn.php">Sign In/Sign Up</a></li>
				</ul>
			</nav>
		</div>
	</header>