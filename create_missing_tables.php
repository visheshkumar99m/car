<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once('includes/db_connection.php');

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to create the orders table if it doesn't exist
function create_orders_table($con) {
    $table_check = mysqli_query($con, "SHOW TABLES LIKE 'orders'");
    if (mysqli_num_rows($table_check) == 0) {
        echo "<div class='alert alert-info'>Creating orders table...</div>";
        
        $sql = "CREATE TABLE orders (
            order_id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            car_id INT NOT NULL,
            order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
            amount DECIMAL(10,2) NOT NULL,
            payment_method VARCHAR(50),
            shipping_address TEXT,
            notes TEXT,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (car_id) REFERENCES cars(car_id) ON DELETE CASCADE
        )";

        if (mysqli_query($con, $sql)) {
            echo "<div class='alert alert-success'>Orders table created successfully!</div>";
            
            // Add sample orders for testing
            $check_users = mysqli_query($con, "SELECT id FROM users WHERE id = 1");
            $check_cars = mysqli_query($con, "SELECT car_id FROM cars LIMIT 3");
            
            $has_users = mysqli_num_rows($check_users) > 0;
            $has_cars = mysqli_num_rows($check_cars) > 0;
            
            if ($has_users && $has_cars) {
                $cars = [];
                while ($car = mysqli_fetch_assoc($check_cars)) {
                    $cars[] = $car['car_id'];
                }
                
                if (count($cars) >= 3) {
                    $sample_orders = [
                        "INSERT INTO orders (user_id, car_id, status, amount, payment_method, shipping_address) 
                         VALUES (1, {$cars[0]}, 'completed', 1500000.00, 'Credit Card', 'Sample Address 1, City, State, PIN')",
                        "INSERT INTO orders (user_id, car_id, status, amount, payment_method, shipping_address) 
                         VALUES (1, {$cars[1]}, 'processing', 2200000.00, 'Bank Transfer', 'Sample Address 2, City, State, PIN')",
                        "INSERT INTO orders (user_id, car_id, status, amount, payment_method, shipping_address) 
                         VALUES (1, {$cars[2]}, 'pending', 1800000.00, 'EMI', 'Sample Address 3, City, State, PIN')"
                    ];
                    
                    $sample_count = 0;
                    foreach ($sample_orders as $order_sql) {
                        if (mysqli_query($con, $order_sql)) {
                            $sample_count++;
                        }
                    }
                    
                    if ($sample_count > 0) {
                        echo "<div class='alert alert-success'>Added $sample_count sample orders for testing.</div>";
                    }
                }
            }
            
            return true;
        } else {
            echo "<div class='alert alert-danger'>Error creating orders table: " . mysqli_error($con) . "</div>";
            return false;
        }
    } else {
        echo "<div class='alert alert-success'>Orders table already exists.</div>";
        return true;
    }
}

// Function to check and fix the cars table
function check_cars_table($con) {
    // Check if the image_url column exists
    $check_image_url = mysqli_query($con, "SHOW COLUMNS FROM cars LIKE 'image_url'");
    
    if (mysqli_num_rows($check_image_url) == 0) {
        echo "<div class='alert alert-info'>Adding image_url column to cars table...</div>";
        
        if (mysqli_query($con, "ALTER TABLE cars ADD COLUMN image_url VARCHAR(255)")) {
            echo "<div class='alert alert-success'>Added image_url column to cars table.</div>";
            
            // Copy data from image to image_url
            if (mysqli_query($con, "UPDATE cars SET image_url = image WHERE image_url IS NULL")) {
                echo "<div class='alert alert-success'>Copied image data to image_url column.</div>";
            }
        } else {
            echo "<div class='alert alert-danger'>Failed to add image_url column: " . mysqli_error($con) . "</div>";
        }
    }
}

// Main execution
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Missing Tables</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 30px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1 {
            color: #3563E9;
            margin-bottom: 30px;
            border-bottom: 2px solid #3563E9;
            padding-bottom: 10px;
        }
        .alert {
            margin-bottom: 15px;
        }
        .btn-primary {
            background-color: #3563E9;
            border-color: #3563E9;
        }
        .btn-primary:hover {
            background-color: #2a4ebf;
            border-color: #2a4ebf;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin System - Create Missing Tables</h1>';

// Check for missing tables
$tables_to_check = ['users', 'brands', 'cars', 'orders'];
$missing_tables = [];

foreach ($tables_to_check as $table) {
    $result = mysqli_query($con, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) == 0) {
        $missing_tables[] = $table;
    }
}

if (!empty($missing_tables)) {
    echo "<div class='alert alert-warning'>Missing tables: " . implode(", ", $missing_tables) . "</div>";
} else {
    echo "<div class='alert alert-success'>All required tables exist.</div>";
}

// Create or check orders table
$orders_ok = create_orders_table($con);

// Check and fix cars table
check_cars_table($con);

// Check for admin user
$admin_check = mysqli_query($con, "SELECT id FROM users WHERE email = 'admin@example.com' AND is_admin = 1");
if (mysqli_num_rows($admin_check) == 0) {
    echo "<div class='alert alert-warning'>Admin user is missing.</div>";
    
    $password_hash = password_hash("admin123", PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, is_admin) 
           VALUES ('Administrator', 'admin@example.com', '$password_hash', 1)";
    
    if (mysqli_query($con, $sql)) {
        echo "<div class='alert alert-success'>Admin user created successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error creating admin user: " . mysqli_error($con) . "</div>";
    }
} else {
    echo "<div class='alert alert-success'>Admin user exists.</div>";
}

// Close connection
mysqli_close($con);

echo '
        <div class="mt-4">
            <h3>Admin Credentials</h3>
            <p><strong>Email:</strong> admin@example.com</p>
            <p><strong>Password:</strong> admin123</p>
        </div>
        
        <div class="mt-4">
            <a href="admin/dashboard.php" class="btn btn-primary">Go to Admin Dashboard</a>
            <a href="login_direct_fix.php" class="btn btn-outline-primary ms-2">Login Page</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>';
?> 