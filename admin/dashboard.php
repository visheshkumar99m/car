<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/footer.php');
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Require admin privileges
require_admin();

// Get statistics for dashboard
// Car count
$car_count_query = "SELECT COUNT(*) as count FROM cars";
$car_result = mysqli_query($con, $car_count_query);
$car_count = mysqli_fetch_assoc($car_result)['count'];

// Brand count
$brand_count_query = "SELECT COUNT(*) as count FROM brands";
$brand_result = mysqli_query($con, $brand_count_query);
$brand_count = mysqli_fetch_assoc($brand_result)['count'];

// User count
$user_count_query = "SELECT COUNT(*) as count FROM users";
$user_result = mysqli_query($con, $user_count_query);
$user_count = mysqli_fetch_assoc($user_result)['count'];

// Order count (if table exists)
$order_count = 0;
$order_count_query = "SELECT COUNT(*) as count FROM orders";
$order_result = mysqli_query($con, $order_count_query);
if ($order_result) {
    $order_count = mysqli_fetch_assoc($order_result)['count'];
} else {
    // Table doesn't exist, show warning message
    set_message("The orders table is missing. Please run the <a href='../create_orders_table.php'>create_orders_table.php</a> script to set it up.", "warning");
}

// Get recent cars
$recent_cars_query = "SELECT c.car_id, c.car_name, c.price, c.created_at, b.brand_name 
                     FROM cars c 
                     JOIN brands b ON c.brand_id = b.brand_id 
                     ORDER BY c.created_at DESC 
                     LIMIT 5";
$recent_cars_result = mysqli_query($con, $recent_cars_query);
$recent_cars = [];
while ($row = mysqli_fetch_assoc($recent_cars_result)) {
    $recent_cars[] = $row;
}

// Generate the header
generate_header('Admin Dashboard');
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block admin-sidebar">
            <div class="d-flex align-items-center p-3 mb-3">
                <img src="https://cdn-icons-png.flaticon.com/128/18585/18585546.png" alt="CarWale Logo" width="40" class="me-2">
                <span class="fs-4 text-white">Admin</span>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cars.php">
                        <i class="bi bi-car-front me-2"></i> Cars
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="brands.php">
                        <i class="bi bi-badge-tm me-2"></i> Brands
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="bi bi-people me-2"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">
                        <i class="bi bi-bag me-2"></i> Orders
                    </a>
                </li>
                <li class="nav-item mt-4">
                    <a class="nav-link" href="../front_page/front_page.php">
                        <i class="bi bi-box-arrow-left me-2"></i> Back to Website
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main content -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
            <!-- Page header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-primary">Export</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Add New
                    </button>
                </div>
            </div>
            
            <!-- Flash messages -->
            <?php show_message(); ?>
            
            <!-- Stats cards -->
            <div class="row mb-4">
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card admin-stats">
                        <div class="card-body">
                            <h5 class="card-title text-muted mb-3">Total Cars</h5>
                            <h3><?php echo $car_count; ?></h3>
                            <a href="cars.php" class="stretched-link text-decoration-none">View all cars</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card admin-stats">
                        <div class="card-body">
                            <h5 class="card-title text-muted mb-3">Brands</h5>
                            <h3><?php echo $brand_count; ?></h3>
                            <a href="brands.php" class="stretched-link text-decoration-none">View all brands</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card admin-stats">
                        <div class="card-body">
                            <h5 class="card-title text-muted mb-3">Users</h5>
                            <h3><?php echo $user_count; ?></h3>
                            <a href="users.php" class="stretched-link text-decoration-none">View all users</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card admin-stats">
                        <div class="card-body">
                            <h5 class="card-title text-muted mb-3">Orders</h5>
                            <h3><?php echo $order_count; ?></h3>
                            <a href="orders.php" class="stretched-link text-decoration-none">View all orders</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent cars table -->
            <div class="card admin-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recently Added Cars</h5>
                    <a href="cars.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Car Name</th>
                                    <th>Brand</th>
                                    <th>Price</th>
                                    <th>Added Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($recent_cars) > 0): ?>
                                    <?php foreach ($recent_cars as $car): ?>
                                        <tr>
                                            <td><?php echo $car['car_id']; ?></td>
                                            <td><?php echo htmlspecialchars($car['car_name']); ?></td>
                                            <td><?php echo htmlspecialchars($car['brand_name']); ?></td>
                                            <td>₹<?php echo number_format($car['price']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($car['created_at'])); ?></td>
                                            <td>
                                                <a href="edit_car.php?id=<?php echo $car['car_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                                <a href="delete_car.php?id=<?php echo $car['car_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this car?')"><i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No cars found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Add New Car</h5>
                            <p class="card-text">Add a new car to the inventory with details, pricing and images.</p>
                            <a href="add_car.php" class="btn btn-primary">Add Car</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Add New Brand</h5>
                            <p class="card-text">Create a new car brand with logo, description and details.</p>
                            <a href="add_brand.php" class="btn btn-primary">Add Brand</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card admin-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">Manage Orders</h5>
                            <p class="card-text">View and manage customer orders, process payments and shipments.</p>
                            <a href="orders.php" class="btn btn-primary">View Orders</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <footer class="mt-5 text-center">
                <p>&copy; 2023 CarWale Admin Panel</p>
            </footer>
        </div>
    </div>
</div>

<?php generate_footer(['https://cdn.jsdelivr.net/npm/chart.js']); ?> 