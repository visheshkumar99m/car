<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/footer.php');
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Require admin privileges
require_admin();

// Handle delete request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $car_id = intval($_GET['delete']);
    
    // Delete the car from the database
    $delete_query = "DELETE FROM cars WHERE car_id = ?";
    $stmt = mysqli_prepare($con, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $car_id);
    
    if (mysqli_stmt_execute($stmt)) {
        set_message('Car deleted successfully', 'success');
    } else {
        set_message('Error deleting car: ' . mysqli_error($con), 'error');
    }
    
    // Redirect to refresh the page
    header('Location: cars.php');
    exit;
}

// Handle search and filtering
$search = isset($_GET['search']) ? $_GET['search'] : '';
$brand_filter = isset($_GET['brand']) ? $_GET['brand'] : '';

// Prepare base query
$query = "SELECT c.*, b.brand_name FROM cars c JOIN brands b ON c.brand_id = b.brand_id";
$where_clauses = [];
$params = [];
$param_types = "";

// Add search condition
if (!empty($search)) {
    $where_clauses[] = "(c.car_name LIKE ? OR b.brand_name LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $param_types .= "ss";
}

// Add brand filter
if (!empty($brand_filter)) {
    $where_clauses[] = "b.brand_id = ?";
    $params[] = $brand_filter;
    $param_types .= "i";
}

// Combine where clauses if any
if (count($where_clauses) > 0) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Add ordering
$query .= " ORDER BY c.car_id DESC";

// Prepare and execute the query
$stmt = mysqli_prepare($con, $query);

if (count($params) > 0) {
    mysqli_stmt_bind_param($stmt, $param_types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Fetch all cars
$cars = [];
while ($row = mysqli_fetch_assoc($result)) {
    $cars[] = $row;
}

// Get brands for filter dropdown
$brand_query = "SELECT brand_id, brand_name FROM brands ORDER BY brand_name";
$brand_result = mysqli_query($con, $brand_query);
$brands = [];
while ($row = mysqli_fetch_assoc($brand_result)) {
    $brands[] = $row;
}

// Generate the header
generate_header('Manage Cars');
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
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="cars.php">
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
                <h1 class="h2">Manage Cars</h1>
                <a href="add_car.php" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Add New Car
                </a>
            </div>
            
            <!-- Flash messages -->
            <?php show_message(); ?>
            
            <!-- Search and filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="cars.php" method="get" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search" placeholder="Search by car name or brand" value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="brand" class="form-label">Brand</label>
                            <select class="form-select" id="brand" name="brand">
                                <option value="">All Brands</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['brand_id']; ?>" <?php echo ($brand_filter == $brand['brand_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brand['brand_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Cars table -->
            <div class="card admin-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Car Name</th>
                                    <th>Brand</th>
                                    <th>Price</th>
                                    <th>Type</th>
                                    <th>Year</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($cars) > 0): ?>
                                    <?php foreach ($cars as $car): ?>
                                        <tr>
                                            <td><?php echo $car['car_id']; ?></td>
                                            <td>
                                                <img src="<?php echo htmlspecialchars($car['image_url'] ?? $car['image']); ?>" alt="<?php echo htmlspecialchars($car['car_name']); ?>" width="60" height="40" style="object-fit: cover; border-radius: 4px;">
                                            </td>
                                            <td><?php echo htmlspecialchars($car['car_name']); ?></td>
                                            <td><?php echo htmlspecialchars($car['brand_name']); ?></td>
                                            <td>₹<?php echo number_format($car['price']); ?></td>
                                            <td><?php echo htmlspecialchars($car['type']); ?></td>
                                            <td><?php echo $car['year']; ?></td>
                                            <td>
                                                <a href="edit_car.php?id=<?php echo $car['car_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                                <a href="cars.php?delete=<?php echo $car['car_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this car?')"><i class="bi bi-trash"></i></a>
                                                <a href="../cars_page/car_details.php?id=<?php echo $car['car_id']; ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="bi bi-eye"></i></a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">No cars found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <footer class="mt-5 text-center">
                <p>&copy; 2023 CarWale Admin Panel</p>
            </footer>
        </div>
    </div>
</div>

<?php generate_footer(); ?> 