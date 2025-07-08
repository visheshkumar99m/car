<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/footer.php');
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Require admin privileges
require_admin();

// Process delete if requested
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $brand_id = $_GET['id'];
    
    // Check if there are cars using this brand
    $check_cars_query = "SELECT COUNT(*) as count FROM cars WHERE brand_id = ?";
    $check_stmt = mysqli_prepare($con, $check_cars_query);
    mysqli_stmt_bind_param($check_stmt, "i", $brand_id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    $car_count = mysqli_fetch_assoc($result)['count'];
    
    if ($car_count > 0) {
        set_message("Cannot delete brand because it has $car_count cars associated with it.", "error");
    } else {
        // Safe to delete
        $delete_query = "DELETE FROM brands WHERE brand_id = ?";
        $delete_stmt = mysqli_prepare($con, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, "i", $brand_id);
        
        if (mysqli_stmt_execute($delete_stmt)) {
            set_message("Brand deleted successfully!", "success");
        } else {
            set_message("Error deleting brand: " . mysqli_error($con), "error");
        }
    }
    
    // Redirect to avoid resubmission
    header("Location: brands.php");
    exit();
}

// Get all brands
$brands_query = "SELECT b.*, COUNT(c.car_id) as car_count 
                FROM brands b 
                LEFT JOIN cars c ON b.brand_id = c.brand_id 
                GROUP BY b.brand_id 
                ORDER BY b.brand_name ASC";
$brands_result = mysqli_query($con, $brands_query);
$brands = [];
if ($brands_result) {
    while ($row = mysqli_fetch_assoc($brands_result)) {
        $brands[] = $row;
    }
}

// Generate the header
generate_header('Manage Brands');
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
                    <a class="nav-link" href="cars.php">
                        <i class="bi bi-car-front me-2"></i> Cars
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="brands.php">
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
                <h1 class="h2">Manage Brands</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                        <i class="bi bi-plus"></i> Add New Brand
                    </button>
                </div>
            </div>
            
            <!-- Flash messages -->
            <?php show_message(); ?>
            
            <!-- Brands table -->
            <div class="card admin-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Brands</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Logo</th>
                                    <th>Brand Name</th>
                                    <th>Cars</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($brands) > 0): ?>
                                    <?php foreach ($brands as $brand): ?>
                                        <tr>
                                            <td><?php echo $brand['brand_id']; ?></td>
                                            <td>
                                                <?php if (!empty($brand['brand_logo'])): ?>
                                                    <img src="<?php echo htmlspecialchars($brand['brand_logo']); ?>" alt="<?php echo htmlspecialchars($brand['brand_name']); ?>" style="width: 40px; height: 40px; object-fit: contain;">
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No Logo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($brand['brand_name']); ?></td>
                                            <td><?php echo $brand['car_count']; ?> cars</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBrandModal<?php echo $brand['brand_id']; ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <a href="brands.php?action=delete&id=<?php echo $brand['brand_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this brand?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit Brand Modal -->
                                        <div class="modal fade" id="editBrandModal<?php echo $brand['brand_id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit Brand</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="update_brand.php" method="post" enctype="multipart/form-data">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="brand_id" value="<?php echo $brand['brand_id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="brand_name<?php echo $brand['brand_id']; ?>" class="form-label">Brand Name</label>
                                                                <input type="text" class="form-control" id="brand_name<?php echo $brand['brand_id']; ?>" name="brand_name" value="<?php echo htmlspecialchars($brand['brand_name']); ?>" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="brand_logo<?php echo $brand['brand_id']; ?>" class="form-label">Brand Logo</label>
                                                                <?php if (!empty($brand['brand_logo'])): ?>
                                                                    <div class="mb-2">
                                                                        <img src="<?php echo htmlspecialchars($brand['brand_logo']); ?>" alt="Current logo" style="max-width: 100px; max-height: 100px;">
                                                                    </div>
                                                                <?php endif; ?>
                                                                <input type="file" class="form-control" id="brand_logo<?php echo $brand['brand_id']; ?>" name="brand_logo" accept="image/*">
                                                                <div class="form-text">Leave empty to keep current logo</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="brand_description<?php echo $brand['brand_id']; ?>" class="form-label">Description</label>
                                                                <textarea class="form-control" id="brand_description<?php echo $brand['brand_id']; ?>" name="brand_description" rows="3"><?php echo htmlspecialchars($brand['description'] ?? ''); ?></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No brands found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Brand Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="add_brand.php" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="brand_name" class="form-label">Brand Name</label>
                        <input type="text" class="form-control" id="brand_name" name="brand_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="brand_logo" class="form-label">Brand Logo</label>
                        <input type="file" class="form-control" id="brand_logo" name="brand_logo" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label for="brand_description" class="form-label">Description</label>
                        <textarea class="form-control" id="brand_description" name="brand_description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php generate_footer(); ?> 