<?php
require_once __DIR__.'/../../core/session.php';
require_once __DIR__ . '/../../core/database.php';
require_once __DIR__ .'/../../config/constants.php';
$pdo = Database::getPdo();
$session = Session::getInstance();

header('Content-Type: application/json');

// === POST REQUEST CHECK ===
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}



// === CSRF CHECK ===
$csrfToken = $_POST['csrf_token'] ?? '';
if (!$session->getCsrfInstance()->validateToken($csrfToken)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit;
}

//signup
if(isset($_POST['action'])&&($_POST['action']==='signup')){

//check for all data needed for signup
if(!isset($_POST['name'],$_POST['email'],$_POST['phone'],$_POST['password'],$_POST['confirm_password'])){   
    // header("Location: ".AUTH.".php");
}



// === INPUT VALIDATION ===
$errors = [];
$name     = trim(filter_input(INPUT_POST,'name',FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
$email    = trim(filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL) ?? '');
$phone    = trim(filter_input(INPUT_POST,'phone',FILTER_SANITIZE_SPECIAL_CHARS ) ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
// $adminKey = $_POST['admin_key'] ?? '';

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
    
    

    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([
        ':email'=>$email
    ]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already registered', 'errors' => ['email'=>'Email exists']]);
        exit;
    }

    // Hash password
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Admin check
    // $isAdmin = ($adminKey === 'admin123') ? true : false;

    // === CALL STORED PROCEDURE ===
    $stmt = $pdo->prepare("SELECT * FROM sp_create_user(:name, :email, :phone, :passwordHash)");
    $stmt->execute([
        ':name' => $name,
        ':email'=> $email,
        ':phone' => $phone,
        ':passwordHash' => $passwordHash
    ]);

    // Get the inserted user ID
    $row= $stmt->fetch();

    $userId = $row['user_id'];
    


    // === SET SESSION ===
    $session->login([
        'id'       => $userId,
        'name'     => $name,
        'email'    => $email,
        

    ]);

    echo json_encode([
        'success'  => true,
        'message'  => 'Account created successfully'
        
    ]);
    exit;

} catch (PDOException $e) {
    // Detailed error logging
    $logMessage = sprintf(
        "[%s] Database error in signup.php: %s in %s on line %d\nStack trace:\n%s\nPOST data: %s\n",
        date('Y-m-d H:i:s'),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString(),
        json_encode(array_map(function($v){ return is_string($v)?$v:'[binary]'; }, $_POST))
    );

    // Write to project-specific log file
    file_put_contents(__DIR__ . '/../logs/signup.log', $logMessage, FILE_APPEND | LOCK_EX);

    // Optional: also log to PHP default error log
    error_log($logMessage);
    //echo error
    echo json_encode(['success'=>false, 'message'=>'Database error']);
    exit;
    }
}
//login
if(isset($_POST['action'])&&($_POST['action']==="login")){
//check all data needed for login
    if(!isset($_POST['email'],$_POST['password'])){
        // header("Location: ".AUTH."auth.php");
        $errors['email']='not found';
        $errors['password']='not found' ;

        echo json_encode([
            'success' => false,
            'message' => 'email or password not provided',
            'errors' => $errors,

        ]);
        exit;
    }


    $errors = [];
    $email = filter_input(INPUT_POST,'email',FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email';
    }

     if(!empty($errors)){
        echo json_encode([
                'success' => false ,
                'message' => 'invalid email' ,
                'errors' => $errors,
            ]);
            exit;
        }
try{ 
    $stmt = $pdo->prepare("SELECT id,name,email,password_hash,is_admin FROM users WHERE email=:email");

    $stmt->execute([
    ':email' => $email,
    ]);

    $row = $stmt->fetch();

    if(!$row){
        $errors['email'] = 'Email not found';
        echo json_encode([
            'success' => false,
            'message' => "Invalid credentials",
            'errors' => $errors
        ]);
        exit;
        
    }

    //initialize database
    $user_id = $row['id'];
    $email = $row['email'];
    $passwordHash = $row['password_hash'];
    $name = $row['name'];

    //verify password
    $passwordVerify = password_verify($password,$row['password_hash']);
       if (!$passwordVerify) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid credentials',
            'errors' => ['password' => 'Incorrect password']
        ]);
        exit;
    }

    if($row['is_admin']){

    }


    //check for match
    if(($row['email']===$email)&&$passwordVerify){

        $userData = [
            'id' => $user_id,
            'name' => $name,
            'email' => $email,
            
        ];

         if($row['is_admin']){

              $userData = [
            'id' => $user_id,
            'name' => $name,
            'email' => $email,
            'is_admin' => true
        ];
        
    }



    $session->login($userData);
    echo json_encode([
        'success' => true ,
        'message' => 'login successfull',
        
    ]);
    exit;
    }
        $errors['credentials'] = 'Invalid credentials';

        echo json_encode([
            'success' => false,
            'message' => 'Failed to Login',
            "errors" => $errors
        ]);

    exit;
        }
    catch(Exception $e){

        $logMessage = sprintf(
            "[%s] Database error in signup.php: %s in %s on line %d\nStack trace:\n%s\nPOST data: %s\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString(),
            json_encode(array_map(function($v){ return is_string($v)?$v:'[binary]'; }, $_POST))
        );

        error_log($logMessage);

        file_put_contents(__DIR__ . '/../logs/signup.log', $logMessage, FILE_APPEND | LOCK_EX);

        echo json_encode([
            'success' => false,
            'message' => 'login exception'
        ]);

        exit;





}
}







?>
