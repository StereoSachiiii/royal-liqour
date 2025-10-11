<?php
require_once 'CSRF.php'; // include your CSRF class

class Session {

    private $timeout; // inactivity timeout in seconds
    public CSRF $csrf; // CSRF instance

    public function __construct($timeout = 1800) { // default 30 min
        $this->timeout = $timeout;

        // SESSION HARDENING SETTINGS
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.use_strict_mode', 1);      // Prevent session fixation
            ini_set('session.use_only_cookies', 1);     // No URL session IDs
            ini_set('session.cookie_httponly', 1);      // JS cannot read cookies
            ini_set('session.cookie_secure', 0);        // Set 1 on HTTPS
            ini_set('session.cookie_samesite', 'Lax');  // CSRF mitigation
            session_start();
        }

        // INACTIVITY TIMEOUT
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $this->timeout) {
            $this->destroy(); // auto logout
            $this->initGuest();
        }
        $_SESSION['last_activity'] = time(); // reset timer

        // GUEST USER LOGIC
        if (!isset($_SESSION['user_id'])) {
            $this->initGuest();
        }

        // CSRF TOKEN
        $this->csrf = new CSRF($this);
        $this->csrf->getToken(); // ensures token exists
    }

    // SESSION ACCESSORS
    public function set(string $key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key) {
        return isset($_SESSION[$key]);
    }

    // LOGIN & LOGOUT
    public function login(array $userData) {
        session_regenerate_id(true); // prevent fixation

        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['name'];
        $_SESSION['email'] = $userData['email'] ?? null;
        $_SESSION['is_admin'] = $userData['is_admin'] ?? 0;
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();

        // Optional: reset guest info
        unset($_SESSION['guest_id']);
        unset($_SESSION['is_guest']);
    }

    public function logout() {
        $this->destroy();
        $this->initGuest();
    }

    public function isLoggedIn() {
        return $this->get('logged_in', false) === true;
    }

    public function isAdmin() {
        return $this->get('is_admin', 0) == 1;
    }

    public function getUserId() {
        return $this->isLoggedIn() ? $this->get('user_id') : $this->get('guest_id');
    }

    public function getUsername() {
        return $this->get('username', 'Guest');
    }

    // GUEST USER INITIALIZATION
    private function initGuest() {
        if (!isset($_SESSION['guest_id'])) {
            $_SESSION['is_guest'] = true;
            $_SESSION['guest_id'] = 'guest_' . bin2hex(random_bytes(16)); // secure random ID
            $_SESSION['username'] = 'Guest';
        }
    }

    public function isGuest() {
        return $this->get('is_guest', false);
    }

    // DESTROY SESSION
    public function destroy() {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
?>
