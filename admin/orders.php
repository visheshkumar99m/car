<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/footer.php');
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Require admin privileges
require_admin();

// Process actions (cancel, complete, etc.)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $order_id = $_GET['id'];
    
    if ($action == 'complete') {
        $update_sql = "UPDATE orders SET status = 'completed' WHERE order_id = ?";
        $stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        set_message("Order #$order_id marked as completed!", "success");
    } 
    else if ($action == 'process') {
        $update_sql = "UPDATE orders SET status = 'processing' WHERE order_id = ?";
        $stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        set_message("Order #$order_id marked as processing!", "success");
    }
    else if ($action == 'cancel') {
        $update_sql = "UPDATE orders SET status = 'cancelled' WHERE order_id = ?";
        $stmt = mysqli_prepare($con, $update_sql);
        mysqli_stmt_bind_param($stmt, "i", $order_id);
        mysqli_stmt_execute($stmt);
        set_message("Order #$order_id cancelled!", "success");
    }
    
    // Redirect to avoid resubmission
    header("Location: orders.php");
    exit();
}

// Get all orders with car and user details
$orders_query = "SELECT o.*, c.car_name, c.price, c.image_url, u.name as user_name, u.email 
                FROM orders o
                JOIN cars c ON o.car_id = c.car_id
                JOIN users u ON o.user_id = u.id
                ORDER BY o.order_date DESC";
$orders_result = mysqli_query($con, $orders_query);
$orders = [];
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $orders[] = $row;
    }
}

// Generate the header
generate_header('Manage Orders');
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
                    <a class="nav-link active" href="orders.php">
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
                <h1 class="h2">Manage Orders</h1>
            </div>
            
            <!-- Flash messages -->
            <?php show_message(); ?>
            
            <!-- Orders table -->
            <div class="card admin-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover admin-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Car</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($orders) > 0): ?>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['order_id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($order['image_url'])): ?>
                                                        <img src="<?php echo htmlspecialchars($order['image_url']); ?>" alt="<?php echo htmlspecialchars($order['car_name']); ?>" class="me-2" style="width: 50px; height: 40px; object-fit: cover; border-radius: 4px;">
                                                    <?php endif; ?>
                                                    <span><?php echo htmlspecialchars($order['car_name']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div><?php echo htmlspecialchars($order['user_name']); ?></div>
                                                    <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                                </div>
                                            </td>
                                            <td>₹<?php echo number_format($order['amount']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                            <td>
                                                <?php 
                                                    $status_class = '';
                                                    switch($order['status']) {
                                                        case 'completed':
                                                            $status_class = 'bg-success';
                                                            break;
                                                        case 'processing':
                                                            $status_class = 'bg-primary';
                                                            break;
                                                        case 'pending':
                                                            $status_class = 'bg-warning';
                                                            break;
                                                        case 'cancelled':
                                                            $status_class = 'bg-danger';
                                                            break;
                                                    }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($order['status']); ?></span>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $order['order_id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $order['order_id']; ?>">
                                                        <li><a class="dropdown-item" href="orders.php?action=process&id=<?php echo $order['order_id']; ?>">Mark as Processing</a></li>
                                                        <li><a class="dropdown-item" href="orders.php?action=complete&id=<?php echo $order['order_id']; ?>">Mark as Completed</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><a class="dropdown-item text-danger" href="orders.php?action=cancel&id=<?php echo $order['order_id']; ?>" onclick="return confirm('Are you sure you want to cancel this order?')">Cancel Order</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No orders found</td>
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

<?php generate_footer(['https://cdn.jsdelivr.net/npm/chart.js']); ?> 