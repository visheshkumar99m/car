<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/footer.php');
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Require admin privileges
require_admin();

// Process actions if requested
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = $_GET['id'];
    
    if ($action == 'delete') {
        // Prevent deleting self
        if ($user_id == $_SESSION['id']) {
            set_message("You cannot delete your own account.", "error");
        } else {
            // Check if user has orders
            $check_orders_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
            $check_stmt = mysqli_prepare($con, $check_orders_query);
            mysqli_stmt_bind_param($check_stmt, "i", $user_id);
            mysqli_stmt_execute($check_stmt);
            $result = mysqli_stmt_get_result($check_stmt);
            $order_count = mysqli_fetch_assoc($result)['count'];
            
            if ($order_count > 0) {
                set_message("Cannot delete user because they have $order_count orders.", "error");
            } else {
                // Safe to delete
                $delete_query = "DELETE FROM users WHERE id = ?";
                $delete_stmt = mysqli_prepare($con, $delete_query);
                mysqli_stmt_bind_param($delete_stmt, "i", $user_id);
                
                if (mysqli_stmt_execute($delete_stmt)) {
                    set_message("User deleted successfully!", "success");
                } else {
                    set_message("Error deleting user: " . mysqli_error($con), "error");
                }
            }
        }
    } 
    else if ($action == 'toggle_admin') {
        // Prevent removing admin from self
        if ($user_id == $_SESSION['id']) {
            set_message("You cannot change your own admin status.", "error");
        } else {
            // Get current status
            $status_query = "SELECT is_admin FROM users WHERE id = ?";
            $status_stmt = mysqli_prepare($con, $status_query);
            mysqli_stmt_bind_param($status_stmt, "i", $user_id);
            mysqli_stmt_execute($status_stmt);
            $result = mysqli_stmt_get_result($status_stmt);
            $row = mysqli_fetch_assoc($result);
            
            // Toggle status
            $new_status = $row['is_admin'] ? 0 : 1;
            $update_query = "UPDATE users SET is_admin = ? WHERE id = ?";
            $update_stmt = mysqli_prepare($con, $update_query);
            mysqli_stmt_bind_param($update_stmt, "ii", $new_status, $user_id);
            
            if (mysqli_stmt_execute($update_stmt)) {
                $status_text = $new_status ? "now an admin" : "no longer an admin";
                set_message("User is $status_text.", "success");
            } else {
                set_message("Error updating user: " . mysqli_error($con), "error");
            }
        }
    }
    
    // Redirect to avoid resubmission
    header("Location: users.php");
    exit();
}

// Get all users with their order counts
$users_query = "SELECT u.*, COUNT(o.order_id) as order_count 
               FROM users u 
               LEFT JOIN orders o ON u.id = o.user_id 
               GROUP BY u.id 
               ORDER BY u.name ASC";
$users_result = mysqli_query($con, $users_query);
$users = [];
if ($users_result) {
    while ($row = mysqli_fetch_assoc($users_result)) {
        $users[] = $row;
    }
}

// Generate the header
generate_header('Manage Users');
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
                    <a class="nav-link active" href="users.php">
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
                <h1 class="h2">Manage Users</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus"></i> Add New User
                    </button>
                </div>
            </div>
            
            <!-- Flash messages -->
            <?php show_message(); ?>
            
            <!-- Users table -->
            <div class="card admin-card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">All Users</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Orders</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($users) > 0): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <?php if ($user['is_admin']): ?>
                                                    <span class="badge bg-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">User</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $user['order_count']; ?></td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $user['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $user['id']; ?>">
                                                        <li>
                                                            <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">
                                                                <i class="bi bi-pencil me-2"></i> Edit
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="users.php?action=toggle_admin&id=<?php echo $user['id']; ?>">
                                                                <?php if ($user['is_admin']): ?>
                                                                    <i class="bi bi-person me-2"></i> Remove Admin
                                                                <?php else: ?>
                                                                    <i class="bi bi-person-check me-2"></i> Make Admin
                                                                <?php endif; ?>
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="users.php?action=delete&id=<?php echo $user['id']; ?>" onclick="return confirm('Are you sure you want to delete this user?')">
                                                                <i class="bi bi-trash me-2"></i> Delete
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        
                                        <!-- Edit User Modal -->
                                        <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Edit User</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form action="update_user.php" method="post">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label for="name<?php echo $user['id']; ?>" class="form-label">Name</label>
                                                                <input type="text" class="form-control" id="name<?php echo $user['id']; ?>" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="email<?php echo $user['id']; ?>" class="form-label">Email</label>
                                                                <input type="email" class="form-control" id="email<?php echo $user['id']; ?>" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <label for="password<?php echo $user['id']; ?>" class="form-label">New Password</label>
                                                                <input type="password" class="form-control" id="password<?php echo $user['id']; ?>" name="password">
                                                                <div class="form-text">Leave empty to keep current password</div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" id="is_admin<?php echo $user['id']; ?>" name="is_admin" value="1" <?php echo $user['is_admin'] ? 'checked' : ''; ?>>
                                                                    <label class="form-check-label" for="is_admin<?php echo $user['id']; ?>">
                                                                        Admin privileges
                                                                    </label>
                                                                </div>
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
                                        <td colspan="6" class="text-center">No users found</td>
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

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="add_user.php" method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin" value="1">
                            <label class="form-check-label" for="is_admin">
                                Admin privileges
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php generate_footer(); ?> 