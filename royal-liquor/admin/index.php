
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

    <link rel="stylesheet" href="assets/css/admin.css">

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
            <button id="mobile-menu-toggle" class="btn btn-ghost mobile-menu-btn" aria-label="Toggle menu">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 12h18M3 6h18M3 18h18"/>
                </svg>
            </button>
            <span><?= htmlspecialchars($currentUser['username']) ?></span>
            <a href="<?= BASE_URL ?>admin/logout.php" class="btn btn-primary logout-btn">Logout</a>
        </div>
        
    </header>
    <div id="modal" class="hidden">
    <div id="modal-body" class="modal-content"></div>
    <span id="modal-close">&times;</span>
</div>

    <!-- ================= Main Content ================= -->
    <main id="content">
        <!-- Dynamic admin content loaded here -->
       
    </main>

<script type="module" src="js/router.js"></script>
<script>
// Vanilla JS for sidebar toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            const isActive = sidebar.classList.contains('active');
            
            // Toggle sidebar
            sidebar.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
            
            // Close sidebar when clicking outside on mobile
            if (window.innerWidth <= 768 && !isActive) {
                setTimeout(() => {
                    document.addEventListener('click', closeSidebarOutside);
                }, 100);
            } else {
                document.removeEventListener('click', closeSidebarOutside);
            }
        });
        
        function closeSidebarOutside(e) {
            if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                sidebar.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
                document.removeEventListener('click', closeSidebarOutside);
            }
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
            }
        });
    }
    
    // Close sidebar when clicking a menu item on mobile
    const menuItems = document.querySelectorAll('.sidebar-menu a');
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
                mobileMenuToggle.classList.remove('active');
            }
        });
    });
});
</script>

</body>
</html>
