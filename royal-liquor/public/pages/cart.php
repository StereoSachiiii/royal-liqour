<?php 
require_once __DIR__ . '/config/urls.php';
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../components/header.php';

$session = Session::getInstance();
$userId = $session->getUserId();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, device-scale=1.0">
    <title>Shopping Cart | Royal Liquor</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/pages/cart.css">
</head>
<body data-user-id="<?= $userId ?? 'null' ?>">

<div class="cart-page">
    <!-- Header -->
    <div class="cart-header">
        <h1 class="cart-title">
            Shopping Cart
            <span class="cart-item-count" id="cart-item-count">0 items</span>
        </h1>
        <p class="cart-subtitle">Review your selection before checkout</p>
    </div>

    <!-- Main Content -->
    <div class="cart-content">
        <!-- Cart Items Section -->
        <div class="cart-items-section">
            <div class="cart-items-list">
                <!-- Items will be rendered here by JavaScript -->
            </div>
        </div>

        <!-- Summary Panel -->
        <div class="cart-summary">
            <h2 class="summary-title">Order Summary</h2>
            
            <div class="summary-row">
                <span class="summary-label">Subtotal:</span>
                <span class="summary-value" id="summary-subtotal">$0.00</span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Tax (10%):</span>
                <span class="summary-value" id="summary-tax">$0.00</span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Shipping:</span>
                <span class="summary-value" id="summary-shipping">$0.00</span>
            </div>
            
            <div class="summary-total">
                <span>Total:</span>
                <span class="summary-total-value" id="summary-total">$0.00</span>
            </div>
            
            <button id="checkout-btn" class="checkout-btn">
                Proceed to Checkout
            </button>
            
            <button id="clear-cart-btn" class="clear-cart-btn">
                Clear Cart
            </button>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container"></div>

<!-- Load JavaScript -->
<script type="module" src="<?= BASE_URL ?>assets/js/pages/cart.js"></script>

<?php require_once __DIR__ . '/footer/footer.php'; ?>

</body>
</html>
