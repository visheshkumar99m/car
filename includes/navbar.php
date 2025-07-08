<?php
/**
 * Function to generate consistent navbar across all pages
 * 
 * @param string $active_page The current active page ('home', 'about', 'brands', 'cars', 'cart')
 * @return void Outputs the navbar HTML
 */
function generate_navbar($active_page = 'home') {
    // Start the session if it hasn't been started already
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    $is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    $is_admin = $is_logged_in && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    
    // Determine cart count for badge
    $cart_count = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $cart_count = count($_SESSION['cart']);
    }
?>
<!-- CSS to ensure navbar consistency across pages - this overrides page-specific styles -->
<style>
.navbar {
    background-color: #1A202C !important;
    width: 100%;
    height: auto !important;
    position: sticky !important;
    top: 0;
    z-index: 1000 !important;
}

.navbar-nav li:first-child {
    padding-left: 0 !important;
}

.navbar-nav .nav-item {
    padding-left: 0 !important;
}

.navbar-nav .nav-link {
    color: rgba(255, 255, 255, 0.85) !important;
    font-size: 16px !important;
    padding: 0.5rem 1rem !important;
}

.navbar-nav .nav-link:hover,
.navbar-nav .nav-link:focus {
    color: #ffffff !important;
    text-decoration: none !important;
}

.navbar-nav .nav-link.active {
    color: #ffffff !important;
    font-weight: bold !important;
}

.navbar .d-flex {
    margin-right: 0 !important;
    width: auto !important;
}

.navbar .login_btn,
.navbar .register_btn {
    height: auto !important;
    width: auto !important;
}

#car_icon {
    margin-left: 0 !important;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="../front_page/front_page.php">
            <img src="https://cdn-icons-png.flaticon.com/128/18585/18585546.png" alt="CarWale Logo" width="40" class="me-2">
            <span>AutoBuzz</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page == 'home') ? 'active' : ''; ?>" href="../front_page/front_page.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page == 'cars') ? 'active' : ''; ?>" href="../cars_page/cars_page.php">Cars</a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page == 'brands') ? 'active' : ''; ?>" href="../brands_page/brands_page.php">Brands</a>
                </li> -->
                <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page == 'about') ? 'active' : ''; ?>" href="../about_page/about_page.php">About</a>
                </li>
                <?php if ($is_admin): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo ($active_page == 'admin') ? 'active' : ''; ?>" href="../admin/dashboard.php">Admin</a>
                </li>
                <?php endif; ?>
            </ul>
            
            <div class="d-flex align-items-center">
                <?php if ($is_logged_in): ?>
                    <a href="../cart_page/cart_page.php" class="btn btn-outline-light position-relative me-3">
                        <i class="bi bi-cart"></i> Cart
                        <?php if ($cart_count > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo $cart_count; ?>
                        </span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="../profile.php">My Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="../cart_page/cart_page.php" class="btn btn-outline-light position-relative me-3">
                        <i class="bi bi-cart"></i> Cart
                    </a>
                    <a href="../login_page/" class="btn btn-outline-light me-2">Login</a>
                    <a href="../register_page/register_page.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
<?php
}
?> 