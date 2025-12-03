<?php
require_once __DIR__ . "/../../core/session.php";
require_once __DIR__ . "/../../config/constants.php";

$session = Session::getInstance();
$username = $session->getUsername();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/popup.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/footer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/products.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/categories.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/cart.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/account.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/orders.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/wishlist.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>css/details.css">
</head>
<body>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Left Sidebar (Mobile Menu) -->
<div class="mobile-sidebar" id="mobileSidebar">
    <button class="sidebar-close-btn" id="mobileClose">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
    <div class="sidebar-header">
        <h2>Royal Liquor</h2>
    </div>
    <ul class="sidebar-nav">
        <li><a href="<?= BASE_URL ?>">Home</a></li>
        <li><a href="<?= BASE_URL ?>shop.php">Shop All</a></li>
        <li><a href="<?= BASE_URL ?>myaccount/orders">My Orders</a></li>
        <li><a href="<?= BASE_URL ?>myaccount/wishlist">Wishlist</a></li>
    </ul>
</div>

<!-- Right Sidebar (Profile) -->
<div class="profile-sidebar" id="profileSidebar">
    <button class="profile-close-btn" id="profileClose">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
    <div class="profile-greeting">
        Welcome back,<br>
        <span class="profile-username"><?= $session->getUsername() ?></span>
    </div>
    <div class="profile-card">
        <a href="<?= BASE_URL ?>myaccount/profile.php" class="profile-link">View Profile & Orders</a>
    </div>
    <?php if($session->isLoggedIn()): ?>
    <div class="profile-actions">
        <a href="<?= BASE_URL ?>myaccount/logout.php" class="profile-link logout-link">Sign Out</a>
    </div>
    <?php endif; ?>
</div>

<!-- Header -->
<header>
    <div id="left-panel">
        <div id="menu">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                <rect y="4" width="24" height="2"/>
                <rect y="11" width="24" height="2"/>
                <rect y="18" width="24" height="2"/>
            </svg>
        </div>
    </div>

    <div id="title">
        <h2>Royal Liquor</h2>
    </div>

    <div id="right-panel">
        <!-- Search -->
        <div id="search-container">
            <div class="search-wrapper">
                <button class="search-icon-btn" id="searchBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>
                <input type="text" placeholder="Search products..." id="searchInput">
                <button class="search-close-btn" id="searchCloseBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Cart -->
        <a href="<?= BASE_URL ?>cart.php">
            <div id="cart">
                <div class="count-display"></div>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart" viewBox="0 0 16 16">
                    <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                </svg>
                <!-- <div class="cart-expand">
                    <div class="cart-summary">
                        <div class="description">
                            <span class="message">Your Cart</span>
                            <span class="count"></span>
                           
                        </div>
                        <div class="cart-item">
                            <div class="item-info">
                                <span class="item-name"></span>
                                <span class="item-quantity"></span>
                            </div>
                            <div class="item-price"></div>
                        </div>
                    </div>
                </div> -->
            </div>
        </a>

        <!-- Profile -->
        <div id="profile">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="4"/>
                <path d="M6 20c0-3.33 5.33-5 6-5s6 1.67 6 5"/>
            </svg>
        </div>
    </div>
</header>

<?php require_once __DIR__ . '/../components/breadcrumb.php' ?>

<!-- Cookie Modal -->
<div class="cookie-modal-bg" id="cookieModalBg">
    <div class="cookie-modal">
        We use cookies to enhance your browsing experience and analyze site traffic. By clicking "Accept All", you consent to our use of cookies.
        <div class="actions">
            <button class="cookie-reject" id="cookieReject">Decline</button>
            <button id="cookieAccept">Accept All</button>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>header/header.js" type="module"></script>