<?php
/**
 * MyAccount Shared Layout
 * Provides consistent sidebar navigation for all account pages
 */

// TODO: Uncomment session checks for production
// Auth check MUST happen before any output
// require_once dirname(dirname(__DIR__)) . "/core/Session.php";
// require_once dirname(__DIR__) . "/config/urls.php";
// 
// $session = Session::getInstance();
// $isLoggedIn = $session->isLoggedIn();
// 
// // Redirect to login if not authenticated (before any HTML output)
// if (!$isLoggedIn) {
//     header('Location: ' . getPageUrl('login') . '?redirect=' . urlencode($_SERVER['REQUEST_URI']));
//     exit;
// }
// 
// $username = $session->getUsername() ?? 'Guest';

// Mock user data for testing
$username = 'Test User';
$userEmail = 'test@example.com';
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');

// Now include header (which outputs HTML)
require_once dirname(__DIR__) . "/components/header.php";
?>

<div class="account-wrapper">
    <!-- Sidebar Navigation -->
    <aside class="account-sidebar">
        <div class="account-user">
            <div class="account-avatar">
                <?= strtoupper(substr($username, 0, 1)) ?>
            </div>
            <div class="account-user-info">
                <span class="account-username"><?= htmlspecialchars($username) ?></span>
                <span class="account-email"><?= htmlspecialchars($userEmail) ?></span>
            </div>
        </div>

        <nav class="account-nav">
            <a href="<?= BASE_URL ?>myaccount/" class="account-nav-item <?= $currentPage === 'index' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span>
                Dashboard
            </a>
            <a href="<?= BASE_URL ?>myaccount/orders.php" class="account-nav-item <?= $currentPage === 'orders' ? 'active' : '' ?>">
                <span class="nav-icon">📦</span>
                My Orders
            </a>
            <a href="<?= BASE_URL ?>myaccount/wishlist.php" class="account-nav-item <?= $currentPage === 'wishlist' ? 'active' : '' ?>">
                <span class="nav-icon">❤️</span>
                Wishlist
            </a>
            <a href="<?= BASE_URL ?>myaccount/addresses.php" class="account-nav-item <?= $currentPage === 'addresses' ? 'active' : '' ?>">
                <span class="nav-icon">📍</span>
                Addresses
            </a>

        </nav>

        <div class="account-nav-footer">
            <a href="<?= BASE_URL ?>myaccount/logout.php" class="account-nav-item logout">
                <span class="nav-icon">🚪</span>
                Sign Out
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="account-content">
