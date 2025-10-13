
<?php
require_once __DIR__ . "/../../config/constants.php";
require_once __DIR__ . "/../../core/session.php";

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
            <div class="welcome-content" id="welcomeContent">
                <h1 id="welcomeTitle">Welcome Back</h1>
                <h2 id="welcomeSubtitle">Continue Your Journey</h2>
                <p id="welcomeText">Sign in to access your account and explore our premium collection of spirits.</p>
                
                <div class="welcome-toggle" id="welcomeToggle">
                    <span id="toggleText">New here?</span>
                    <a href="#" id="welcomeToggleLink">Create an account</a>
                </div>
            </div>
        </div>

        <!-- Right Form Section -->
        <div class="form-section">
            <div class="form-wrapper">
                <!-- Login Form -->
                <div class="form-container active" id="loginForm">
                    <div class="form-header">
                        <h2>Sign In</h2>
                        <p>Welcome back! Please enter your details.</p>
                    </div>

                    <div id="message"></div>

                    <form id="loginFormSubmit" action="auth-handler.php">
                        <div class="form-group">
                            <label for="loginEmail">Email Address</label>
                            <input type="email" id="loginEmail" name="email" required autocomplete="email">
                            <span id="loginEmail-error" class="error"></span>
                        </div>

                        <div class="form-group">
                            <label for="loginPassword">Password</label>
                            <input type="password" id="loginPassword" name="password" required autocomplete="current-password">
                            <span id="loginPassword-error" class="error"></span>
                        </div>

                        <input type="hidden" name="csrf_token" id="loginCsrf" value="<?= $csrfToken?>">
                        <input type="hidden" name="action" value="login">

                        <button type="submit" id="loginBtn">Sign In</button>
                    </form>
                </div>

                <!-- Signup Form -->
                <div class="form-container" id="signupForm">
                    <div class="form-header">
                        <h2>Create Account</h2>
                        <p>Join us and start your premium experience.</p>
                    </div>

                    <div id="signupMessage"></div>

                    <form id="signupFormSubmit" action="auth-handler.php">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required minlength="2" maxlength="100" autocomplete="name">
                            <span id="name-error" class="error"></span>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" required maxlength="254" autocomplete="email">
                            <span id="email-error" class="error"></span>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number (Optional)</label>
                            <input type="tel" id="phone" name="phone" placeholder="+94771234567" maxlength="15" autocomplete="tel">
                            <span id="phone-error" class="error"></span>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
                            <small>At least 8 characters with uppercase, lowercase, number, and special character</small>
                            <span id="password-error" class="error"></span>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="8" maxlength="72" autocomplete="new-password">
                            <span id="confirm_password-error" class="error"></span>
                        </div>
                        <input type="hidden" name="action" value="signup">
                        <input type="hidden" name="csrf_token" id="signupCsrf" value="<?= $csrfToken?>">

                        <button type="submit" id="signupBtn">Create Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle between login and signup
        const loginFormDiv = document.getElementById('loginForm');
        const signupFormDiv = document.getElementById('signupForm');
        const welcomeToggleLink = document.getElementById('welcomeToggleLink');
        const toggleText = document.getElementById('toggleText');
        const welcomeTitle = document.getElementById('welcomeTitle');
        const welcomeSubtitle = document.getElementById('welcomeSubtitle');
        const welcomeText = document.getElementById('welcomeText');

        welcomeToggleLink.addEventListener('click', (e) => {
            e.preventDefault();
            
            if (loginFormDiv.classList.contains('active')) {
                // Switch to signup
                loginFormDiv.classList.remove('active');
                signupFormDiv.classList.add('active');
                
                // Update welcome section
                welcomeTitle.textContent = 'Join Us';
                welcomeSubtitle.textContent = 'Get Started Today';
                welcomeText.textContent = 'Create your account and unlock exclusive access to premium spirits and personalized recommendations.';
                toggleText.textContent = 'Already a member?';
                welcomeToggleLink.textContent = 'Sign in';
            } else {
                // Switch to login
                signupFormDiv.classList.remove('active');
                loginFormDiv.classList.add('active');
                
                // Update welcome section
                welcomeTitle.textContent = 'Welcome Back';
                welcomeSubtitle.textContent = 'Continue Your Journey';
                welcomeText.textContent = 'Sign in to access your account and explore our premium collection of spirits.';
                toggleText.textContent = 'New here?';
                welcomeToggleLink.textContent = 'Create an account';
            }
        });

        // Signup Form Handler
        document.getElementById('signupFormSubmit').addEventListener('submit', function(e) {
            e.preventDefault();
            
            document.querySelectorAll('.error').forEach(el => el.textContent = '');
            document.getElementById('signupMessage').textContent = '';
            
            const submitBtn = document.getElementById('signupBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Creating Account...';
            
            const formData = new FormData(this);
            
            const password = formData.get('password');
            const confirmPassword = formData.get('confirm_password');
            
            if (password !== confirmPassword) {
                document.getElementById('confirm_password-error').textContent = 'Passwords do not match';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
                return;
            }
            
            fetch("<?=AUTH_HANDLER?>", {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then((response) => {
                console.log(response);
               return response.json()})
            .then(data => {
                const messageDiv = document.getElementById('signupMessage');
                
                if (data.success) {
                    messageDiv.innerHTML = '<strong style="color: green;">' + data.message + '</strong>';
                    this.reset();
                    
                  window.location.href="<?=WELCOME ?>"
                } else {
                    messageDiv.innerHTML = '<strong style="color: red;">' + data.message + '</strong>';
                    
                    if (data.errors) {
                        Object.keys(data.errors).forEach(field => {
                            const errorEl = document.getElementById(field + '-error');
                            if (errorEl) {
                                errorEl.textContent = data.errors[field];
                            }
                        });
                    }
                    
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Create Account';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('signupMessage').innerHTML = 
                    '<strong style="color: red;">An error occurred. Please try again.</strong>';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
            });
        });

        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const errorEl = document.getElementById('password-error');
            
            if (password.length === 0) {
                errorEl.textContent = '';
                return;
            }
            
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[^A-Za-z0-9]/.test(password);
            const isLongEnough = password.length >= 8;
            
            let missing = [];
            if (!hasUppercase) missing.push('uppercase');
            if (!hasLowercase) missing.push('lowercase');
            if (!hasNumber) missing.push('number');
            if (!hasSpecial) missing.push('special character');
            if (!isLongEnough) missing.push('8 characters minimum');
            
            if (missing.length > 0) {
                errorEl.textContent = 'Missing: ' + missing.join(', ');
                errorEl.style.color = 'orange';
            } else {
                errorEl.textContent = 'Strong password!';
                errorEl.style.color = 'green';
            }
        });

        // Login Form Handler
        document.getElementById('loginFormSubmit').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear error boxes
            document.getElementById('loginEmail-error').textContent = '';
            document.getElementById('loginPassword-error').textContent = '';
            document.getElementById('message').textContent = '';
            
            const submitBtn = document.getElementById('loginBtn');
            const formData = new FormData(this);
            
            const email = formData.get('email');
            const password = formData.get('password');
            
            if (!email) {
                document.getElementById('loginEmail-error').textContent = 'Email is required';
                return;
            }
            
            if (!password) {
                document.getElementById('loginPassword-error').textContent = 'Password is required';
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.textContent = 'Logging in...';
            
            fetch('<?=AUTH_HANDLER?>', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then((res) => {
                
                console.log(res);
                return res.json()})
            .then(data => {
                const messageDiv = document.getElementById('message');
                
                if (data.success) {
                    messageDiv.innerHTML = '<strong style="color: green;">' + data.message + '</strong>';
                    document.getElementById('loginFormSubmit').reset();
                    
                    window.location.href="<?=WELCOME ?>"
                } else {
                    messageDiv.innerHTML = '<strong style="color: red;">' + data.message + '</strong>';
                }
                
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorEl = document.getElementById('login' + field.charAt(0).toUpperCase() + field.slice(1) + '-error');
                        if (errorEl) {
                            errorEl.textContent = data.errors[field];
                            errorEl.style.color = 'red';
                        }
                    });
                }
                
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In';
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('message').innerHTML = 
                    '<strong style="color: red;">An error occurred. Please try again.</strong>';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Sign In';
            });
        });
    </script>
</body>
</html>