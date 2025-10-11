<?php 
//varables that are needed for database connection


// Security

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') ?: 'royal_liquor');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 18499);
define("DB_PORT",getenv('DB_PORT') ?:intval(5432));
// Security
define('BCRYPT_COST', intval(getenv('BCRYPT_COST') ?: 10));   // bcrypt cost; tune per server
define('ADMIN_SECRET_KEY', getenv('ADMIN_SECRET_KEY') ?: 'admin123'); // rotate in prod

// Rate Limiting (signup attempts)
define('MAX_SIGNUP_ATTEMPTS', intval(getenv('MAX_SIGNUP_ATTEMPTS') ?: 10));
define('SIGNUP_ATTEMPT_WINDOW', intval(getenv('SIGNUP_ATTEMPT_WINDOW') ?: 3600)); // seconds

// Session
define('SESSION_LIFETIME', intval(getenv('SESSION_LIFETIME') ?: 3600)); // seconds

// Error reporting (dev vs prod)
if (getenv('APP_ENV') === 'production') {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');         // hide errors from users
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
} else {
    // development
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/../logs/php_errors.log');
}


//path 
define('BASE_URL', '/royal-liquor/public/');
?>