<?php
require_once __DIR__ . "/../../core/session.php";
require_once __DIR__ . "/../config/urls.php";
require_once __DIR__ . "/../config/page-assets.php";

$session = Session::getInstance();
$username = $session->getUsername();
$pageName = $pageName ?? 'home'; // Can be passed from including page
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Royal Liquor - Premium spirits and fine wines">
    <title><?= $pageTitle ?? 'Royal Liquor - Premium Spirits' ?></title>
    
    <!-- Dynamic CSS Loading - Only loads what this page needs -->
    <?php loadPageCSS($pageName); ?>
</head>
<body>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Mobile Sidebar (Left) -->
<aside class="mobile-sidebar" id="mobileSidebar" role="navigation" aria-label="Mobile navigation">
    <button class="sidebar-close-btn" id="mobileClose" aria-label="Close mobile menu">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
    <div class="sidebar-header">
        <h2>Royal Liquor</h2>
    </div>
    <nav>
        <ul class="sidebar-nav">
            <li><a href="<?= getPageUrl('home') ?>">Home</a></li>
            <li><a href="<?= getPageUrl('shop') ?>">Shop All</a></li>
            <li><a href="<?= BASE_URL ?>recipes.php">Recipes</a></li>
            <li><a href="<?= BASE_URL ?>photo-search.php">📷 Photo Search</a></li>
            <li><a href="<?= getPageUrl('account') ?>">My Account</a></li>
            <li><a href="<?= BASE_URL ?>myaccount/taste-profile.php">Taste Profile</a></li>
            <li><a href="<?= getPageUrl('orders') ?>">My Orders</a></li>
            <li><a href="<?= getPageUrl('wishlist') ?>">Wishlist</a></li>
            <li><a href="<?= getPageUrl('about') ?>">About Us</a></li>
            <li><a href="<?= getPageUrl('contact') ?>">Contact</a></li>
        </ul>
    </nav>
</aside>

<!-- Profile Sidebar (Right) -->
<aside class="profile-sidebar" id="profileSidebar" role="complementary" aria-label="User profile">
    <button class="profile-close-btn" id="profileClose" aria-label="Close profile menu">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
    <div class="profile-greeting">
        Welcome back,<br>
        <span class="profile-username"><?= htmlspecialchars($session->getUsername() ?? 'Guest') ?></span>
    </div>
    <div class="profile-card">
        <a href="<?= getPageUrl('account') ?>" class="profile-link">View Profile & Orders</a>
    </div>
    <?php if($session->isLoggedIn()): ?>
    <div class="profile-actions">
        <a href="<?= getPageUrl('logout') ?>" class="profile-link logout-link">Sign Out</a>
    </div>
    <?php else: ?>
    <div class="profile-actions">
        <a href="<?= getPageUrl('login') ?>" class="profile-link">Sign In</a>
    </div>
    <?php endif; ?>
</aside>

<!-- Main Header -->
<header role="banner">
    <div id="left-panel">
        <div id="menu" aria-label="Open mobile menu" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="currentColor" aria-hidden="true">
                <rect y="4" width="24" height="2"/>
                <rect y="11" width="24" height="2"/>
                <rect y="18" width="24" height="2"/>
            </svg>
        </div>
    </div>

    <div id="title">
        <a href="<?= getPageUrl('home') ?>">Royal Liquor</a>
    </div>

    <div id="right-panel">
        <!-- Search -->
        <div id="search-container">
            <div class="search-wrapper">
                <button class="search-icon-btn" id="searchBtn" aria-label="Search">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                </button>
                <input type="search" placeholder="Search products, categories..." id="searchInput" aria-label="Search products" autocomplete="off">
                <button class="search-close-btn" id="searchCloseBtn" aria-label="Close search">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
                <!-- Autocomplete Dropdown -->
                <div class="search-autocomplete" id="searchAutocomplete">
                    <div class="autocomplete-section" id="autocompleteProducts">
                        <div class="autocomplete-heading">Products</div>
                        <div class="autocomplete-items" id="productResults"></div>
                    </div>
                    <div class="autocomplete-section" id="autocompleteCategories">
                        <div class="autocomplete-heading">Categories</div>
                        <div class="autocomplete-items" id="categoryResults"></div>
                    </div>
                    <div class="autocomplete-empty" id="noResults" style="display: none;">
                        No results found
                    </div>
                    <a href="#" class="autocomplete-view-all" id="viewAllResults">
                        View all results →
                    </a>
                </div>
            </div>
        </div>

        <!-- Cart -->
        <a href="<?= getPageUrl('cart') ?>" id="cart" aria-label="Shopping cart">
            <div class="count-display" aria-live="polite"></div>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart" viewBox="0 0 16 16" aria-hidden="true">
                <path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5M3.102 4l1.313 7h8.17l1.313-7zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4m7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4m-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2m7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
            </svg>
        </a>

        <!-- Profile -->
        <div id="profile" aria-label="User profile" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="8" r="4"/>
                <path d="M6 20c0-3.33 5.33-5 6-5s6 1.67 6 5"/>
            </svg>
        </div>
    </div>
</header>

<!-- Dynamic JS Loading -->
<script src="<?= BASE_URL ?>utils/header.js" type="module"></script>
<script src="<?= BASE_URL ?>utils/search.js" type="module"></script>