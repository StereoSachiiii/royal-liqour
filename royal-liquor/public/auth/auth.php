<?php
// Use the public config for URLs
require_once __DIR__ . "/../config/urls.php";
require_once __DIR__ . "/../../core/Session.php";

// Define WELCOME redirect destination
if (!defined('WELCOME')) {
    define('WELCOME', BASE_URL);
}

// Initialize session (handles guest users automatically)
$session = Session::getInstance();
$csrfToken = $session->getCsrfInstance()->getToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication - Royal Liquor</title>
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
    <div class="auth-container">
        <!-- Left Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <h1 id="welcomeTitle">Welcome Back</h1>
                <h2 id="welcomeSubtitle">Continue Your Journey</h2>
                <p id="welcomeText">Sign in to access your account and explore our premium collection of spirits.</p>
                <div class="welcome-toggle">
                    <span id="toggleText">New here?</span>
                    <a href="#" id="toggleLink">Create an account</a>
                </div>
            </div>
        </div>

        <!-- Right Forms -->
        <div class="form-section">
            <div class="form-wrapper">
                <!-- Login Form -->
                <div class="form-container active" id="loginContainer">
                    <div class="form-header">
                        <h2>Sign In</h2>
                        <p>Welcome back! Please enter your details.</p>
                    </div>
                    <div id="loginMessage"></div>
                    <form id="loginForm">
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" required autocomplete="email">
                            <span class="error" id="loginEmail-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required autocomplete="current-password">
                            <span class="error" id="loginPassword-error"></span>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="action" value="login">
                        <button type="submit">Sign In</button>
                    </form>
                </div>

                <!-- Signup Form -->
                <div class="form-container" id="signupContainer">
                    <div class="form-header">
                        <h2>Create Account</h2>
                        <p>Join us and start your premium experience.</p>
                    </div>
                    <div id="signupMessage"></div>
                    <form id="signupForm">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" required minlength="2" maxlength="100" autocomplete="name">
                            <span class="error" id="name-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" required autocomplete="email">
                            <span class="error" id="email-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Phone Number (Optional)</label>
                            <input type="tel" name="phone" placeholder="+94771234567" autocomplete="tel">
                            <span class="error" id="phone-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required minlength="8" autocomplete="new-password">
                            <small>At least 8 chars: uppercase, lowercase, number, symbol</small>
                            <span class="error" id="password-error"></span>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password" required autocomplete="new-password">
                            <span class="error" id="confirm_password-error"></span>
                        </div>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                        <input type="hidden" name="action" value="register">
                        <button type="submit">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle Login ↔ Register
        const loginContainer = document.getElementById('loginContainer');
        const signupContainer = document.getElementById('signupContainer');
        const toggleLink = document.getElementById('toggleLink');
        const toggleText = document.getElementById('toggleText');
        const welcomeTitle = document.getElementById('welcomeTitle');
        const welcomeSubtitle = document.getElementById('welcomeSubtitle');
        const welcomeText = document.getElementById('welcomeText');

        toggleLink.addEventListener('click', e => {
            e.preventDefault();
            if (loginContainer.classList.contains('active')) {
                loginContainer.classList.remove('active');
                signupContainer.classList.add('active');
                welcomeTitle.textContent = 'Join Us';
                welcomeSubtitle.textContent = 'Get Started Today';
                welcomeText.textContent = 'Create your account and unlock exclusive access to premium spirits.';
                toggleText.textContent = 'Already have an account?';
                toggleLink.textContent = 'Sign in';
            } else {
                signupContainer.classList.remove('active');
                loginContainer.classList.add('active');
                welcomeTitle.textContent = 'Welcome Back';
                welcomeSubtitle.textContent = 'Continue Your Journey';
                welcomeText.textContent = 'Sign in to access your account and explore our premium collection of spirits.';
                toggleText.textContent = 'New here?';
                toggleLink.textContent = 'Create an account';
            }
        });

        // Unified Form Handler
        const handleSubmit = async (e) => {
            e.preventDefault();
            const form = e.target;
            const isLogin = form.id === 'loginForm';
            const messageDiv = isLogin ? document.getElementById('loginMessage') : document.getElementById('signupMessage');
            const button = form.querySelector('button[type="submit"]');

            // Reset UI
            form.querySelectorAll('.error').forEach(el => el.textContent = '');
            messageDiv.textContent = '';
            button.disabled = true;
            button.textContent = 'Processing...';

            const formData = new FormData(form);

            try {
                const res = await fetch('auth-handler.php', {
                    method: 'POST',
                    body: formData,
                    credentials: 'include' // Important for session cookies
                });

                const data = await res.json();
                
           

                if (data.success) {
                    messageDiv.innerHTML = `<strong style="color:green;">${data.message || 'Success!'}</strong>`;
                    setTimeout(() => location.href = '<?= WELCOME ?>', 1200);
                } else {
                    messageDiv.innerHTML = `<strong style="color:red;">${data.message || 'Error occurred'}</strong>`;
                    if (data.context?.errors) {
                        Object.entries(data.context.errors).forEach(([field, msg]) => {
                            const el = document.getElementById(field + '-error') || document.getElementById('login' + field[0].toUpperCase() + field.slice(1) + '-error');
                            if (el) el.textContent = msg;
                        });
                    }
                }
            } catch (err) {
                console.error(err);
                messageDiv.innerHTML = '<strong style="color:red;">Network error. Please try again.</strong>';
            } finally {
                button.disabled = false;
                button.textContent = isLogin ? 'Sign In' : 'Create Account';
            }
        };

        document.getElementById('loginForm').addEventListener('submit', handleSubmit);
        document.getElementById('signupForm').addEventListener('submit', handleSubmit);
    </script>
</body>
</html>