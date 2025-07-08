<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/navbar.php');
require_once('../includes/footer.php');
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Start the session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fetch all brands from the database
$query = "SELECT * FROM brands ORDER BY brand_name";
$result = mysqli_query($con, $query);
$brands = [];

while ($row = mysqli_fetch_assoc($result)) {
    $brands[] = $row;
}

// Generate the header
generate_header('Car Brands', ['../includes/car_data_styles.css']);
?>

<div class="main">
    <?php 
    // Generate navbar with 'brands' as the active page
    generate_navbar('brands'); 
    
    // Display flash messages
    show_message();
    ?>
    
    <div class="container my-5">
        <header class="text-center mb-5">
            <h1 class="mb-3">Explore Our <span class="text-primary">Car Brands</span></h1>
            <p class="lead text-muted w-75 mx-auto">Discover a wide range of automotive brands, each with their unique history, design philosophy, and technological innovations.</p>
        </header>
        
        <div class="brands-grid">
            <?php foreach ($brands as $brand): ?>
                <div class="brand-card">
                    <img src="<?php echo !empty($brand['logo']) ? htmlspecialchars($brand['logo']) : '../assets/logos/default.png'; ?>" 
                         alt="<?php echo htmlspecialchars($brand['brand_name']); ?> Logo"
                         class="brand-logo">
                    <h3 class="brand-name"><?php echo htmlspecialchars($brand['brand_name']); ?></h3>
                    <div class="brand-description"><?php echo htmlspecialchars(substr($brand['description'], 0, 120)); ?>...</div>
                    <a href="<?php echo strtolower($brand['brand_name']); ?>/<?php echo strtolower($brand['brand_name']); ?>_car.php" 
                       class="btn btn-primary">View Cars</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4>CarWale</h4>
                    <p>
                        At CarWale, we're dedicated to making your car buying experience 
                        as smooth as the road ahead. With a wide range of brands, expert guidance, 
                        secure transactions, and innovative features.
                    </p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h4>Contact Us</h4>
                    <p><i class="bi bi-envelope me-2"></i> vishesh1426@gmail.com</p>
                    <p><i class="bi bi-geo-alt me-2"></i> Teerthanker Mahaveer university moradabad</p>
                    <p><i class="bi bi-map me-2"></i> Uttar Pradesh, India</p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h4>Follow Us</h4>
                    <div class="footer-social">
                        <a href="#"><i class="bi bi-facebook me-2"></i></a>
                        <a href="#"><i class="bi bi-twitter me-2"></i></a>
                        <a href="#"><i class="bi bi-instagram me-2"></i></a>
                        <a href="www.linkedin.com/in/vishesh-kumar-42ba58266"><i class="bi bi-linkedin me-2"></i></a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center mt-4">
                    <p class="mb-0">&copy; 2023 CarWale. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
</div>

<?php
// Generate the footer
generate_footer();
?>