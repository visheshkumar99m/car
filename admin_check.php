<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once('includes/db_connection.php');

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Status array to track each functionality's state
$status = [
    'database_connection' => $con ? 'Working' : 'Failed',
    'tables' => [],
    'admin_user' => 'Not Found',
    'car_count' => 0,
    'brand_count' => 0,
    'user_count' => 0,
    'order_count' => 0
];

// Check for required tables
$required_tables = ['users', 'brands', 'cars', 'orders'];
foreach ($required_tables as $table) {
    $result = mysqli_query($con, "SHOW TABLES LIKE '$table'");
    $status['tables'][$table] = mysqli_num_rows($result) > 0 ? 'Exists' : 'Missing';
}

// Check for admin user
if ($status['tables']['users'] === 'Exists') {
    $admin_check = mysqli_query($con, "SELECT id FROM users WHERE email = 'admin@example.com' AND is_admin = 1");
    $status['admin_user'] = mysqli_num_rows($admin_check) > 0 ? 'Found' : 'Not Found';
}

// Get counts
if ($status['tables']['cars'] === 'Exists') {
    $car_result = mysqli_query($con, "SELECT COUNT(*) as count FROM cars");
    $status['car_count'] = mysqli_fetch_assoc($car_result)['count'];
}

if ($status['tables']['brands'] === 'Exists') {
    $brand_result = mysqli_query($con, "SELECT COUNT(*) as count FROM brands");
    $status['brand_count'] = mysqli_fetch_assoc($brand_result)['count'];
}

if ($status['tables']['users'] === 'Exists') {
    $user_result = mysqli_query($con, "SELECT COUNT(*) as count FROM users");
    $status['user_count'] = mysqli_fetch_assoc($user_result)['count'];
}

if ($status['tables']['orders'] === 'Exists') {
    $order_result = mysqli_query($con, "SELECT COUNT(*) as count FROM orders");
    $status['order_count'] = mysqli_fetch_assoc($order_result)['count'];
}

// Get column info for cars table
$cars_columns = [];
if ($status['tables']['cars'] === 'Exists') {
    $columns_result = mysqli_query($con, "SHOW COLUMNS FROM cars");
    while ($column = mysqli_fetch_assoc($columns_result)) {
        $cars_columns[] = $column['Field'];
    }
}

// Close connection
mysqli_close($con);

// Check if all required functionality is available
$all_tables_exist = !in_array('Missing', $status['tables']);
$admin_user_exists = $status['admin_user'] === 'Found';
$db_connected = $status['database_connection'] === 'Working';

$all_good = $all_tables_exist && $admin_user_exists && $db_connected && 
            $status['car_count'] > 0 && $status['brand_count'] > 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Functionality Check</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 30px;
        }
        .container {
            max-width: 900px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        h1 {
            color: #3563E9;
            margin-bottom: 30px;
            border-bottom: 2px solid #3563E9;
            padding-bottom: 10px;
        }
        .status-card {
            margin-bottom: 20px;
            border-radius: 10px;
            overflow: hidden;
        }
        .card-header {
            font-weight: bold;
            font-size: 18px;
        }
        .status-working {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .table {
            margin-bottom: 0;
        }
        .admin-link {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background-color: #3563E9;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .admin-link:hover {
            background-color: #2a4eb7;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .login-link {
            display: inline-block;
            margin-top: 20px;
            margin-left: 10px;
            padding: 12px 25px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .login-link:hover {
            background-color: #5a6268;
            color: white;
        }
        .fix-link {
            display: inline-block;
            margin-top: 20px;
            margin-left: 10px;
            padding: 12px 25px;
            background-color: #ffc107;
            color: #212529;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            transition: all 0.3s;
        }
        .fix-link:hover {
            background-color: #e0a800;
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Functionality Check</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="card status-card mb-4">
                    <div class="card-header">System Status</div>
                    <div class="card-body">
                        <p><strong>Database Connection:</strong> 
                            <span class="badge <?php echo $status['database_connection'] === 'Working' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $status['database_connection']; ?>
                            </span>
                        </p>
                        <p><strong>Admin User:</strong> 
                            <span class="badge <?php echo $status['admin_user'] === 'Found' ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo $status['admin_user']; ?>
                            </span>
                        </p>
                        <p><strong>Overall Status:</strong> 
                            <span class="badge <?php echo $all_good ? 'bg-success' : 'bg-warning'; ?>">
                                <?php echo $all_good ? 'Ready' : 'Needs Attention'; ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card status-card">
                    <div class="card-header">Record Counts</div>
                    <div class="card-body">
                        <p><strong>Cars:</strong> <?php echo $status['car_count']; ?></p>
                        <p><strong>Brands:</strong> <?php echo $status['brand_count']; ?></p>
                        <p><strong>Users:</strong> <?php echo $status['user_count']; ?></p>
                        <p><strong>Orders:</strong> <?php echo $status['order_count']; ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Table Status -->
        <div class="card status-card mb-4">
            <div class="card-header">Required Tables</div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($status['tables'] as $table => $table_status): ?>
                            <tr>
                                <td><?php echo $table; ?></td>
                                <td>
                                    <span class="badge <?php echo $table_status === 'Exists' ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $table_status; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Cars Table Structure -->
        <?php if (!empty($cars_columns)): ?>
        <div class="card status-card mb-4">
            <div class="card-header">Cars Table Structure</div>
            <div class="card-body">
                <p>Columns: <?php echo implode(', ', $cars_columns); ?></p>
                <p>
                    <strong>Image URL Column:</strong> 
                    <span class="badge <?php echo in_array('image_url', $cars_columns) ? 'bg-success' : 'bg-warning'; ?>">
                        <?php echo in_array('image_url', $cars_columns) ? 'Present' : 'Missing'; ?>
                    </span>
                </p>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Summary and Links -->
        <div class="alert <?php echo $all_good ? 'alert-success' : 'alert-warning'; ?> mt-4">
            <?php if ($all_good): ?>
                <h4 class="alert-heading">All Systems Ready!</h4>
                <p>The admin panel is fully functional and ready to use.</p>
            <?php else: ?>
                <h4 class="alert-heading">Attention Required</h4>
                <p>Some components are missing or not properly configured. Please run the fix script.</p>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="admin/dashboard.php" class="admin-link">Go to Admin Dashboard</a>
            <a href="login_direct_fix.php" class="login-link">Login Page</a>
            <?php if (!$all_good): ?>
                <a href="create_missing_tables.php" class="fix-link">Run Fix Script</a>
            <?php endif; ?>
        </div>
        
        <div class="mt-4 text-center">
            <p>For admin access, use the following credentials:</p>
            <p><strong>Email:</strong> admin@example.com</p>
            <p><strong>Password:</strong> admin123</p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 