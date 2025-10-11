<?php
require_once __DIR__.'/../session/session.php';
require_once __DIR__ . '/../config/database.php';
$database = new Database();
$session = new Session(); // starts session & CSRF

header('Content-Type: application/json');

// === POST REQUEST CHECK ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// === CSRF CHECK ===
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$session->csrf->validateToken($csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

if(!isset($_POST['name'],$_POST['email'],$_POST['phone'],$_POST['password'],$_POST['confirm_password'])){
    
    header("Location :signup.php");

}

// === INPUT VALIDATION ===
$errors = [];
$name     = trim(filter_input(INPUT_POST,'name',FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$email    = trim(filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL) ?? '');
$phone    = trim(filter_input(INPUT_POST,'phone',FILTER_SANITIZE_SPECIAL_CHARS ) ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$adminKey = $_POST['admin_key'] ?? '';

// Name
if (strlen($name) < 2 || strlen($name) > 100) {
    $errors['name'] = 'Name must be 2-100 characters';
}

// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email';
}

// Password
if ($password !== $confirm) {
    $errors['confirm_password'] = 'Passwords do not match';
}
if (strlen($password) < 8) {
    $errors['password'] = 'Password too short';
}

// Stop if validation errors
if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
    exit;
}

// === DATABASE CONNECTION ===
try {
    $pdo = $database->getPdo();
    

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered', 'errors' => ['email'=>'Email exists']]);
        exit;
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Admin check
    $isAdmin = ($adminKey === 'admin123') ? 1 : 0;

    // === CALL STORED PROCEDURE ===
    $stmt = $pdo->prepare("CALL sp_create_user(:name, :email, :phone, :passwordHash)");
    $stmt->execute([$name, $email, $phone, $passwordHash]);

    // Get the inserted user ID
    $userId = $pdo->query("SELECT LAST_INSERT_ID()")->fetchColumn();

    // === SET SESSION ===
    $session->login([
        'id'       => $userId,
        'name'     => $name,
        'email'    => $email,
        'is_admin' => $isAdmin
    ]);

    echo json_encode([
        'success'  => true,
        'message'  => 'Account created successfully',
        'redirect' => $isAdmin ? 'admin_dashboard.php' : 'dashboard.php'
    ]);
    exit;

} catch (PDOException $e) {
    // log error in production
    echo json_encode(['success'=>false, 'message'=>'Database error']);
    exit;
}
?>
