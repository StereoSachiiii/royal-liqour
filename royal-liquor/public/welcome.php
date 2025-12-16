<?php 
require_once __DIR__ . "/../core/session.php";

$session = Session::getInstance();
$username = htmlspecialchars($session->getUsername() ?? "Guest");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging In...</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #fff, #f9f6f1);
            font-family: "Inter", Arial, sans-serif;
            color: #333;
        }

        #modal {
            background: #ffffff;
            border-radius: 14px;
            padding: 50px 70px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            animation: fadeIn 0.6s ease-out;
        }

        #modal h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            color: #444;
        }

        #username {
            font-weight: 600;
            color: #ff914d;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .spinner {
            width: 60px;
            height: 60px;
            border: 6px solid #f3f3f3;
            border-top: 6px solid #ffb347;
            border-radius: 50%;
            animation: spin 1.5s linear infinite;
            margin: 0 auto 25px auto;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        #message {
            margin-top: 10px;
            font-size: 0.95rem;
            color: #666;
            opacity: 0;
            animation: fadeText 2s ease forwards 1s;
        }

        @keyframes fadeText {
            to { opacity: 1; }
        }

        @keyframes fadeIn {
            0% { opacity: 0; transform: translateY(30px); }
            100% { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div id="modal">
        <h2>Logging you in...</h2>
        <div id="username"><?= $username ?></div>
        <div class="spinner"></div>
        <div id="message">Preparing your dashboard ☕</div>
    </div>

    <script>
        setTimeout(() => {
            window.location.href = "index.php";
        }, 4000);
    </script>
</body>
</html>
