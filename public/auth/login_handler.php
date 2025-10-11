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

// === INPUT VALIDATION ===
$errors = [];
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email';
}

// Password
if(strlen($password) < 8) {
    $errors['password'] = 'Password too short';
}

//stop if validation errors
if(!empty($errors)){
    echo json_encode(
        [
            'success' => false,
            'message' => "Validation failed",
            'error' => $errors

        ]
        );
        exit;
}


try{
    $pdo = $database->getPdo();

    //check if email exists
    
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);

    if(!$stmt->fetch()){
        echo json_encode(
            [
                'success' => false, 'message' => 'Email not  registered', 'errors' => ['email'=>'Email does not exist']
            ]
            );
        exit;
    }

    //hash inpput password



    //check if admin

   

    //database check
    $sql = 'SELECT 
            id, name, email, is_admin ,password_hash
            FROM 
            users 
            WHERE
            email = :email
            AND
            is_active = :is_active
            AND
            is_anonymized = :is_anonymized'
            ;


    $stmt = $pdo->prepare($sql);

$stmt->execute([
    ':email'        => $email,
    ':is_active'    => 1,
    ':is_anonymized'=> 0
]);

    $row =  $stmt->fetch();

    $isAdmin = $row['is_admin'];

    //set session 
    if($row && password_verify($password,$row['password_hash'])){
 $session->login([
        'id' => $row['id'],
        'name'     => $row['name'],
        'email'    => $row['email'],
        'is_admin' => $row['is_admin']

 ]);
    }
        echo json_encode([
        'success'  => true,
        'message'  => 'Log in success',
        'redirect' =>  $isAdmin ? 'admin_dashboard.php' : '../welcome.php'
    ]);
    exit;

   



}catch(Exception $e){
      echo json_encode(['success'=>false, 'message'=>'Database error']);
    exit;
}


?>