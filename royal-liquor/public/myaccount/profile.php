
<?php
require_once __DIR__ . '/../components/header.php';
?>



<?php if(!$session->isLoggedIn()): ?> 
<div class="account-page-wrapper">
    
    <div class="account-header">
        <h1 class="page-title">Account</h1>
        <p class="page-subtitle">Login or Create Your Private Reserve Account</p>
    </div>

    <div class="account-content-grid">
        
        <div class="section-card existing-customer">
            <h2 class="card-heading">Existing Customer</h2>
            <p class="card-subheading">Please sign in below</p>
            
            <form action="/path/to/login.php" method="POST" class="login-form">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember my email and password</label>
                </div>
                
                <button type="submit" class="btn-primary">Login</button>
                
                <p class="forgot-link">
                    <a href="/path/to/forgot_password.php">Forgotten password?</a>
                </p>
            </form>
        </div>
        
        <div class="section-card new-customer">
            <h2 class="card-heading">New Customer</h2>
            <p class="card-subheading">Register and enjoy these benefits</p>
            
            <ul class="benefit-list">
                <li>Place your order online quickly</li>
                <li>Create your wishlist, set a reminder, share with your friends</li>
                <li>View your order history, print invoices, re-order</li>
                <li>Manage personal details and delivery addresses</li>
                <li>Get exclusive "members only" occasional offers</li>
            </ul>
            
            <a href="/path/to/register.php" class="btn-secondary">Create Account</a>
        </div>
        
    </div>
    
    <div class="newsletter-section">
        <h2 class="newsletter-heading">Stay Up To Date</h2>
        <p class="newsletter-text">Get new and exclusive releases, special offers and inspiring ideas direct to your inbox</p>
        
        <form action="/path/to/newsletter.php" method="POST" class="newsletter-form">
            <input type="email" name="newsletter_email" placeholder="Enter your email address">
            <button type="submit" class="btn-tertiary">Sign up to our newsletter</button>
        </form>
    </div>

</div>
<?php else: ?>
    <div class="account-page-wrapper my-account-dashboard">
        <div class="account-header">
            <h1 class="page-title">My Private Reserve</h1>
            <p class="page-subtitle">Welcome back, <?= htmlspecialchars($session->getUsername()) ?>.</p>

        </div>

        <div class="dashboard-grid">
            
            <div class="dashboard-card profile-snapshot">
                <h2 class="card-heading">Profile Details</h2>
                <p><strong>Name:</strong> <?= htmlspecialchars($session->getUsername()) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($session->getEmail()) ?></p>
                <div>
                    <p><strong>Addresses</strong></p>
                    <div class="addresses"></div>
                </div>
                
                 
                <a href="<?= BASE_URL?>myaccount/addresses.php" class="dashboard-link">Manage Addresses</a>
            </div>

            <div class="dashboard-card activity-summary">
                <h2 class="card-heading">Recent Activity</h2>
                <ul class="activity-list">
                    <li><a href="<?= BASE_URL?>myaccount/orders">View Order History</a></li>
                    <li><a href="<?= BASE_URL?>myaccount/wishlist">Manage Wishlist</a> </li>
                    
                </ul>
                <a href="/path/to/orders.php" class="dashboard-link">Track Latest Order</a>
            </div>


            
            <div class="dashboard-card recommendations">
                <h2 class="card-heading">Exclusive Recommendations</h2>
                <p class="card-subheading">Based on your recent purchase of fine Scotch.</p>
                <ul class="recommendation-list">
                    <li>Try the new **Highland Reserve** Single Malt.</li>
                    <li>Read our guide: **Aging Rum** for the perfect cocktail.</li>
                </ul>
                <a href="/path/to/recommendations.php" class="dashboard-link">Explore New Releases</a>
            </div>

        </div>
        
        <div class="dashboard-logout">
            <a href="/path/to/logout.php" class="btn-primary logout-btn">Sign Out of Account</a>
        </div>
        
    </div>

    <?php endif; ?>

    <script type="module">
        import {getAddresses} from '../utils/addresses.js'
        const dashboardGrid = document.querySelector('.my-account-dashboard')
        const addresses = document.querySelector('.addresses')


       

        const parseAdresses = async (addressList) => {
            let html = ''
            html = addressList.map(((address) =>(`
            
            <div class="address-card">
                address type : ${address.type? address.type : '-'}
                <br>
                address : ${address.address_line1 ? address.address_line1 : '-'} ${address.address_line2 ? address.address_line2 : ''}
                ${address.city? address.city : '-'}
                ${address.state? address.state : '-'}
                <br>
                postal-code : ${address.postal_code ? address.postal_code : '-'}
            </div>
            
            
            
            `)))
            html = html.join('')
            return  html
        }   

        dashboardGrid.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        })

        document.addEventListener('DOMContentLoaded',async ()=>{
            
            const id = Number.parseInt(<?= $session->getUserId()?>)
           
            const addressList = await getAddresses(id)
           
            const html = await parseAdresses(addressList)
            addresses.innerHTML = html
            
        })
       
    </script>