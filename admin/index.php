<?php
require_once __DIR__ . "/../core/session.php";
require_once __DIR__ . "/../config/constants.php";

$session = Session::getInstance();
$currentUser = [
    "user_id" => $session->get("user_id"),
    "username" => $session->get("username"),
    "email" => $session->get("email"),
    "is_admin" => $session->get("is_admin"),
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="css/index.css">

</head>
<body>
    <!-- ================= Sidebar ================= -->
    <aside id="sidebar">
        <div class="sidebar-header">
            <h2>Royal Liquor</h2>
        </div>
        
        <ul class="sidebar-menu">

        </ul>
    </aside>

    <!-- ================= Header ================= -->
    <header id="admin-header">
        <div class="header-left">
            <h1> Dashboard</h1>
             <div id="breadcrumb" class="breadcrumb">
            <!-- Breadcrumb renders here -->
            </div>
        </div>
        <div class="header-right">
            <span><?= htmlspecialchars($currentUser['username']) ?></span>
            <a href="<?= BASE_URL ?>admin/logout.php" class="logout-btn">Logout</a>
        </div>
        
    </header>

    <!-- ================= Main Content ================= -->
    <main id="content">
        <!-- Dynamic admin content loaded here -->
       
    </main>

    <!-- ================= Modals ================= -->
    <!-- <div id="modal-root"></div> -->

    <!-- ================= JS ================= -->
<!--  -->
<script type="module" src="js/router.js"></script>

</body>
</html>
