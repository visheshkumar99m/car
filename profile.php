<?php
// Include necessary files
require_once('includes/db_connection.php');
require_once('includes/auth_check.php');
require_once('includes/header.php');

// Ensure user is logged in
require_login();

// Handle form submission for profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Check if email already exists for other users
    $email_check = "SELECT id FROM users WHERE email = ? AND id != ?";
    $email_stmt = mysqli_prepare($con, $email_check);
    mysqli_stmt_bind_param($email_stmt, "si", $email, $user_id);
    mysqli_stmt_execute($email_stmt);
    mysqli_stmt_store_result($email_stmt);
    
    if (mysqli_stmt_num_rows($email_stmt) > 0) {
        $errors[] = "Email already exists for another user";
    }
    
    // Handle password change if requested
    $password_changed = false;
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        // All password fields must be filled
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $errors[] = "All password fields are required to change password";
        } elseif ($new_password != $confirm_password) {
            $errors[] = "New passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters";
        } else {
            // Verify current password
            $password_query = "SELECT password FROM users WHERE id = ?";
            $password_stmt = mysqli_prepare($con, $password_query);
            mysqli_stmt_bind_param($password_stmt, "i", $user_id);
            mysqli_stmt_execute($password_stmt);
            $result = mysqli_stmt_get_result($password_stmt);
            $user_data = mysqli_fetch_assoc($result);
            
            if (!password_verify($current_password, $user_data['password'])) {
                $errors[] = "Current password is incorrect";
            } else {
                $password_changed = true;
            }
        }
    }
    
    // If no errors, update user profile
    if (empty($errors)) {
        // Update query depends on whether password is being changed
        if ($password_changed) {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($update_stmt, "sssi", $name, $email, $password_hash, $user_id);
        } else {
            $update_query = "UPDATE users SET name = ?, email = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($update_stmt, "ssi", $name, $email, $user_id);
        }
        
        if (mysqli_stmt_execute($update_stmt)) {
            // Update session data
            $_SESSION['user_name'] = $name;
            $_SESSION['email'] = $email;
            
            set_message("Profile updated successfully!", "success");
        } else {
            set_message("Error updating profile: " . mysqli_error($con), "error");
        }
    } else {
        set_message("Error: " . implode(", ", $errors), "error");
    }
}

// Get user data
$user_id = $_SESSION['id'] ?? 0;
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($con, $user_query);
mysqli_stmt_bind_param($user_stmt, "i", $user_id);
mysqli_stmt_execute($user_stmt);
$result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($result);

// Get user's orders
$orders_query = "SELECT o.*, c.car_name, c.price, c.image_url
                FROM orders o
                JOIN cars c ON o.car_id = c.car_id
                WHERE o.user_id = ?
                ORDER BY o.order_date DESC";
$orders_stmt = mysqli_prepare($con, $orders_query);
mysqli_stmt_bind_param($orders_stmt, "i", $user_id);
mysqli_stmt_execute($orders_stmt);
$orders_result = mysqli_stmt_get_result($orders_stmt);
$orders = [];
while ($row = mysqli_fetch_assoc($orders_result)) {
    $orders[] = $row;
}

// Generate the header
generate_header('My Profile');
?>

<div class="container my-5">
    <div class="row">
        <!-- Profile Sidebar -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=3563E9&color=fff&size=128" class="rounded-circle img-fluid" style="width: 100px; height: 100px;">
                    </div>
                    <h4 class="card-title"><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    <?php if ($user['is_admin']): ?>
                        <span class="badge bg-danger mb-3">Administrator</span>
                    <?php endif; ?>
                    
                    <div class="d-grid gap-2 mt-4">
                        <?php if ($user['is_admin']): ?>
                            <a href="admin/dashboard.php" class="btn btn-outline-primary">
                                <i class="bi bi-speedometer2 me-2"></i> Admin Dashboard
                            </a>
                        <?php endif; ?>
                        <a href="front_page/front_page.php" class="btn btn-outline-secondary">
                            <i class="bi bi-car-front me-2"></i> Browse Cars
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger">
                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Tabs -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">Profile</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders" type="button" role="tab" aria-controls="orders" aria-selected="false">My Orders</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security" type="button" role="tab" aria-controls="security" aria-selected="false">Password</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <!-- Flash messages -->
                    <?php show_message(); ?>
                    
                    <div class="tab-content" id="profileTabContent">
                        <!-- Profile Tab -->
                        <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                            <h5 class="card-title mb-4">Edit Profile</h5>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Orders Tab -->
                        <div class="tab-pane fade" id="orders" role="tabpanel" aria-labelledby="orders-tab">
                            <h5 class="card-title mb-4">My Orders</h5>
                            <?php if (count($orders) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($orders as $order): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <?php if (!empty($order['image_url'])): ?>
                                                        <img src="<?php echo htmlspecialchars($order['image_url']); ?>" alt="<?php echo htmlspecialchars($order['car_name']); ?>" class="me-3" style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px;">
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($order['car_name']); ?></h6>
                                                        <span class="text-muted">Order #<?php echo $order['order_id']; ?></span>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <h6 class="mb-1">₹<?php echo number_format($order['amount']); ?></h6>
                                                    <small class="text-muted"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></small>
                                                </div>
                                            </div>
                                            <div class="mt-2 d-flex justify-content-between align-items-center">
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
                                                <?php if ($order['status'] == 'pending'): ?>
                                                    <a href="cancel_order.php?id=<?php echo $order['order_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to cancel this order?')">Cancel Order</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p class="mb-0">You haven't placed any orders yet.</p>
                                    <a href="front_page/front_page.php" class="alert-link">Browse cars</a> to make your first purchase!
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Security Tab -->
                        <div class="tab-pane fade" id="security" role="tabpanel" aria-labelledby="security-tab">
                            <h5 class="card-title mb-4">Change Password</h5>
                            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                                <!-- Hidden fields to preserve profile data -->
                                <input type="hidden" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="form-text">Password must be at least 6 characters long.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('includes/footer.php'); ?> 