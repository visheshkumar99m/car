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

// Build SQL query with filters
$query = "SELECT c.car_id, c.car_name, c.price, c.image, c.image_url, c.year, c.type, b.brand_name 
          FROM cars c 
          JOIN brands b ON c.brand_id = b.brand_id";
$where_clauses = [];
$params = [];
$param_types = "";

// Handle filtering
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Search functionality
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $where_clauses[] = "(c.car_name LIKE ? OR b.brand_name LIKE ?)";
        $params[] = $search;
        $params[] = $search;
        $param_types .= "ss";
    }
    
    // Brand filter
    if (isset($_GET['brand']) && !empty($_GET['brand'])) {
        $where_clauses[] = "b.brand_name = ?";
        $params[] = $_GET['brand'];
        $param_types .= "s";
    }
    
    // Price range filter
    if (isset($_GET['minPrice']) && isset($_GET['maxPrice'])) {
        $minPriceFilter = (int)$_GET['minPrice'];
        $maxPriceFilter = (int)$_GET['maxPrice'];
        
        if ($minPriceFilter > 0) {
            $where_clauses[] = "c.price >= ?";
            $params[] = $minPriceFilter;
            $param_types .= "d";
        }
        
        if ($maxPriceFilter > 0) {
            $where_clauses[] = "c.price <= ?";
            $params[] = $maxPriceFilter;
            $param_types .= "d";
        }
    }
}

// Build the complete query
if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Prepare and execute the query
$stmt = mysqli_prepare($con, $query);

if (count($params) > 0) {
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch all car data
$cars = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cars[] = [
        'id' => $row['car_id'],
        'name' => $row['car_name'],
        'brand' => $row['brand_name'],
        'price' => $row['price'],
        'image' => $row['image'],
        'image_url' => $row['image_url'] ?? $row['image'],
        'year' => $row['year'],
        'type' => $row['type']
    ];
}

// Get all unique brands for the filter dropdown
$brand_query = "SELECT brand_name FROM brands ORDER BY brand_name";
$brand_result = mysqli_query($con, $brand_query);
$brands = [];

while ($row = mysqli_fetch_assoc($brand_result)) {
    $brands[] = $row['brand_name'];
}

// Get min and max prices for the price range filter
$price_query = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM cars";
$price_result = mysqli_query($con, $price_query);
$price_row = mysqli_fetch_assoc($price_result);
$minPrice = $price_row['min_price'];
$maxPrice = $price_row['max_price'];

// Generate the header
generate_header('Cars Collection', ['../includes/car_data_styles.css']);
?>

<div class="main">
    <?php 
    // Generate the navbar with 'cars' as the active page
    generate_navbar('cars'); 
    
    // Display flash messages
    show_message();
    ?>
    
    <div class="container my-5">
        <header class="text-center mb-5">
            <h1 class="mb-3">Explore Our <span class="text-primary">Car Collection</span></h1>
            <p class="lead text-muted w-75 mx-auto">Find your dream car from our extensive collection of luxury, sports, SUVs, and economy vehicles.</p>
        </header>
        
        <!-- Search and Filter Section -->
        <div class="search-section">
            <form method="GET" action="cars_page.php" class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Search Cars</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control" id="search" name="search" placeholder="Search by car name or brand" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="brand" class="form-label">Brand</label>
                    <select class="form-select" id="brand" name="brand">
                        <option value="">All Brands</option>
                        <?php foreach ($brands as $brand): ?>
                            <option value="<?php echo htmlspecialchars($brand); ?>" <?php echo (isset($_GET['brand']) && $_GET['brand'] === $brand) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="priceRange" class="form-label">Price Range (₹)</label>
                    <div class="d-flex gap-2">
                        <input type="number" class="form-control" id="minPrice" name="minPrice" placeholder="Min" value="<?php echo isset($_GET['minPrice']) ? htmlspecialchars($_GET['minPrice']) : ''; ?>">
                        <input type="number" class="form-control" id="maxPrice" name="maxPrice" placeholder="Max" value="<?php echo isset($_GET['maxPrice']) ? htmlspecialchars($_GET['maxPrice']) : ''; ?>">
                    </div>
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-filter me-1"></i> Apply Filters
                    </button>
                    <a href="cars_page.php" class="btn btn-outline-secondary px-4 ms-2">
                        <i class="bi bi-x-circle me-1"></i> Clear Filters
                    </a>
                </div>
            </form>
        </div>
        
        <!-- Results Section -->
        <?php if (count($cars) > 0): ?>
            <div class="car-grid">
                <?php foreach ($cars as $car): ?>
                    <div class="car-card">
                        <div class="car-image-container">
                            <img src="<?php echo htmlspecialchars($car['image_url']); ?>" alt="<?php echo htmlspecialchars($car['name']); ?>" class="car-image-element">
                        </div>
                        <div class="car-info">
                            <h5><?php echo htmlspecialchars($car['name']); ?></h5>
                            <p class="car-brand">
                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($car['brand']); ?></span>
                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($car['type']); ?></span>
                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($car['year']); ?></span>
                            </p>
                            <p class="car-price">₹<?php echo number_format($car['price']); ?></p>
                            <div class="d-flex gap-2">
                                <a href="car_details.php?id=<?php echo $car['id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                <button class="btn btn-sm btn-outline-success add-to-cart" data-car-id="<?php echo $car['id']; ?>">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-cars-found">
                <i class="bi bi-car-front text-secondary"></i>
                <h4>No cars found matching your criteria</h4>
                <p class="text-muted">Try different search terms or filters</p>
                <a href="cars_page.php" class="btn btn-primary mt-2">Clear Filters</a>
            </div>
        <?php endif; ?>
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
// Generate the footer with the add-to-cart script
$script_content = "
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function() {
            const carId = this.getAttribute('data-car-id');
            
            // Send AJAX request to add car to cart
            fetch('../cart_page/add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `car_id=${carId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Car added to cart!');
                    // If cart count needs to be updated, you could do it here
                } else {
                    if (data.redirect) {
                        window.location.href = data.redirect_url;
                    } else {
                        alert(data.message || 'Error adding car to cart');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding car to cart');
            });
        });
    });
";
?>

<script>
<?php echo $script_content; ?>
</script>

<?php generate_footer(); ?> 