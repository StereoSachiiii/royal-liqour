
<?php
require_once 'core/Session.php';
require_once 'core/Database.php';
require_once 'admin/repositories/UserRepository.php';

$session = Session::getInstance();
$repo = new UserRepository();
header('Content-Type: text/html');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$session->getCsrfInstance()->validateToken($_POST['csrf_token'] ?? '')) {
        die(json_encode(['success' => false, 'message' => 'Invalid CSRF token']));
    }
    $action = $_POST['action'] ?? '';
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '';
    $password = $_POST['password'] ?? '';
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS) ?? 'Admin');
    $errors = [];

    if ($action === 'signup') {
        if (strlen($name) < 2 || strlen($name) > 100) $errors['name'] = 'Name must be 2-100 characters';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email';
        if (strlen($password) < 8) $errors['password'] = 'Password must be 8+ characters';
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
            exit;
        }
        try {
            $user = $repo->createUser($name, $email, null, $password);
            $session->login(['id' => $user->getId(), 'name' => $name, 'email' => $email, 'is_admin' => true]);
            header('Location: /royal-liquor/');
            echo json_encode(['success' => true, 'message' => 'Admin created']);
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/../logs/signup.log', sprintf("[%s] Error: %s\n", date('Y-m-d H:i:s'), $e->getMessage()), FILE_APPEND);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'login') {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email';
        if (!empty($errors)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email', 'errors' => $errors]);
            exit;
        }
        try {
            $user = $repo->getUserByEmail($email);
            if ($user && $user->isActive() && $user->isAdmin() && password_verify($password, $user->getPasswordHash())) {
                $session->login(['id' => $user->getId(), 'name' => $user->getName(), 'email' => $email, 'is_admin' => true]);
                $repo->updateLastLogin($user->getId());
                header('Location: /royal-liquor/');
                exit;
            }
            echo json_encode(['success' => false, 'message' => 'Invalid credentials', 'errors' => ['email' => 'Invalid email or password']]);
        } catch (Exception $e) {
            file_put_contents(__DIR__ . '/../logs/signup.log', sprintf("[%s] Error: %s\n", date('Y-m-d H:i:s'), $e->getMessage()), FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Admin Login</title></head>
<body>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $session->getCsrfInstance()->getToken(); ?>">
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="name" placeholder="Name (for signup)">
        <button type="submit" name="action" value="signup">Signup</button>
        <button type="submit" name="action" value="login">Login</button>
    </form>
</body>
</html>
