<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once('includes/db_connection.php');

// Check connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Function to execute a query safely
function execute_query($con, $sql, $description) {
    echo "<p>Attempting to $description...</p>";
    
    if (mysqli_query($con, $sql)) {
        echo "<div style='color: green;'>✓ Success: $description</div>";
        return true;
    } else {
        echo "<div style='color: red;'>✗ Error: " . mysqli_error($con) . "</div>";
        return false;
    }
}

// Output header
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Admin Functionality Fix</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body { 
            padding: 20px;
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 { 
            color: #3563E9;
            border-bottom: 2px solid #3563E9;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        h2 {
            margin-top: 20px;
            color: #333;
        }
        .section {
            background: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 5px solid #3563E9;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Admin Functionality Fix</h1>";

echo "<div class='section'>
        <h2>1. Checking Database Tables</h2>";

// Check if users table exists
$users_check = mysqli_query($con, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($users_check) == 0) {
    $users_sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        is_admin TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    execute_query($con, $users_sql, "create users table");
    
    // Add admin user
    $admin_name = "Administrator";
    $admin_email = "admin@example.com";
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    
    $admin_sql = "INSERT INTO users (name, email, password, is_admin) 
                 VALUES ('$admin_name', '$admin_email', '$admin_password', 1)";
    
    execute_query($con, $admin_sql, "create admin user");
    
    // Add test user
    $test_sql = "INSERT INTO users (name, email, password, is_admin) 
                VALUES ('Test User', 'test@example.com', '" . password_hash("test123", PASSWORD_DEFAULT) . "', 0)";
    
    execute_query($con, $test_sql, "create test user");
} else {
    echo "<div style='color: green;'>✓ Users table exists</div>";
}

// Check if brands table exists
$brands_check = mysqli_query($con, "SHOW TABLES LIKE 'brands'");
if (mysqli_num_rows($brands_check) == 0) {
    $brands_sql = "CREATE TABLE brands (
        brand_id INT AUTO_INCREMENT PRIMARY KEY,
        brand_name VARCHAR(100) NOT NULL,
        logo VARCHAR(255),
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    execute_query($con, $brands_sql, "create brands table");
    
    // Add sample brands
    $sample_brands = "INSERT INTO brands (brand_name, logo, description) VALUES
        ('Hyundai', 'https://www.carlogos.org/car-logos/hyundai-logo.png', 'Hyundai Motor Company is a South Korean multinational automotive manufacturer.'),
        ('Mahindra', 'https://www.carlogos.org/car-logos/mahindra-logo.png', 'Mahindra & Mahindra Limited is an Indian multinational automobile manufacturing corporation.'),
        ('Mercedes', 'https://www.carlogos.org/car-logos/mercedes-benz-logo.png', 'Mercedes-Benz is a German global automobile marque and a division of Daimler AG.'),
        ('Honda', 'https://www.carlogos.org/car-logos/honda-logo.png', 'Honda Motor Co., Ltd. is a Japanese public multinational conglomerate manufacturer of automobiles.')";
    
    execute_query($con, $sample_brands, "add sample brands");
} else {
    echo "<div style='color: green;'>✓ Brands table exists</div>";
}

// Check if cars table exists
$cars_check = mysqli_query($con, "SHOW TABLES LIKE 'cars'");
if (mysqli_num_rows($cars_check) == 0) {
    $cars_sql = "CREATE TABLE cars (
        car_id INT AUTO_INCREMENT PRIMARY KEY,
        brand_id INT,
        car_name VARCHAR(100) NOT NULL,
        price DECIMAL(12,2) NOT NULL,
        image VARCHAR(255),
        year INT,
        type VARCHAR(50),
        fuel VARCHAR(50),
        rating DECIMAL(3,1),
        seats INT,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (brand_id) REFERENCES brands(brand_id) ON DELETE CASCADE
    )";
    
    execute_query($con, $cars_sql, "create cars table");
    
    // We need to check if brands exist before adding sample cars
    $check_brands = mysqli_query($con, "SELECT COUNT(*) as count FROM brands");
    $brand_count = 0;
    
    if ($check_brands) {
        $brand_count = mysqli_fetch_assoc($check_brands)['count'];
    }
    
    if ($brand_count > 0) {
        // Add sample cars
        $sample_cars = "INSERT INTO cars (brand_id, car_name, price, image, year, type, fuel, rating, seats, description) VALUES
            (1, 'Hyundai i20', 800000, 'https://stimg.cardekho.com/images/carexteriorimages/630x420/Hyundai/i20/10108/1682674395410/front-left-side-47.jpg?tr=w-456', 2022, 'Hatchback', 'Petrol', 4.2, 5, 'The Hyundai i20 is a hatchback produced by the South Korean manufacturer Hyundai.'),
            (1, 'Hyundai Creta', 1200000, 'https://stimg.cardekho.com/images/carexteriorimages/630x420/Hyundai/Creta/10544/1685527904354/front-left-side-47.jpg?tr=w-456', 2022, 'SUV', 'Petrol/Diesel', 4.5, 5, 'The Hyundai Creta is a compact SUV produced by the South Korean manufacturer Hyundai.'),
            (2, 'Mahindra Thar', 1600000, 'https://stimg.cardekho.com/images/carexteriorimages/630x420/Mahindra/Thar/10585/1690351800432/front-left-side-47.jpg?tr=w-456', 2023, 'SUV', 'Petrol/Diesel', 4.6, 4, 'The Mahindra Thar is an off-road SUV manufactured by Mahindra & Mahindra.')";
        
        execute_query($con, $sample_cars, "add sample cars");
    } else {
        echo "<div style='color: orange;'>⚠ Cannot add sample cars: no brands found in database</div>";
    }
} else {
    echo "<div style='color: green;'>✓ Cars table exists</div>";
}

// Check if orders table exists
$orders_check = mysqli_query($con, "SHOW TABLES LIKE 'orders'");
if (mysqli_num_rows($orders_check) == 0) {
    $orders_sql = "CREATE TABLE orders (
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
    
    if (execute_query($con, $orders_sql, "create orders table")) {
        // Add sample orders (only if users and cars exist)
        $check_users = mysqli_query($con, "SELECT id FROM users LIMIT 1");
        $check_cars = mysqli_query($con, "SELECT car_id FROM cars LIMIT 3");
        
        $has_users = mysqli_num_rows($check_users) > 0;
        $has_cars = mysqli_num_rows($check_cars) > 0;
        
        if ($has_users && $has_cars) {
            $cars = [];
            while ($car = mysqli_fetch_assoc($check_cars)) {
                $cars[] = $car['car_id'];
            }
            
            $user_id = mysqli_fetch_assoc($check_users)['id'];
            
            if (count($cars) >= 3) {
                $sample_orders = [
                    "INSERT INTO orders (user_id, car_id, status, amount, payment_method, shipping_address) 
                     VALUES ($user_id, {$cars[0]}, 'completed', 1500000.00, 'Credit Card', 'Sample Address 1, City, State, PIN')",
                    "INSERT INTO orders (user_id, car_id, status, amount, payment_method, shipping_address) 
                     VALUES ($user_id, {$cars[1]}, 'processing', 2200000.00, 'Bank Transfer', 'Sample Address 2, City, State, PIN')",
                    "INSERT INTO orders (user_id, car_id, status, amount, payment_method, shipping_address) 
                     VALUES ($user_id, {$cars[2]}, 'pending', 1800000.00, 'EMI', 'Sample Address 3, City, State, PIN')"
                ];
                
                foreach ($sample_orders as $order_sql) {
                    execute_query($con, $order_sql, "add sample order");
                }
            } else {
                echo "<div style='color: orange;'>⚠ Not enough cars to create sample orders</div>";
            }
        } else {
            echo "<div style='color: orange;'>⚠ Cannot add sample orders: users or cars missing</div>";
        }
    }
} else {
    echo "<div style='color: green;'>✓ Orders table exists</div>";
}

echo "</div>";

// Fix for image URL issue in orders table
echo "<div class='section'>
        <h2>2. Fixing Database Issues</h2>";

// Add image_url column to cars table if it doesn't exist
$check_image_url = mysqli_query($con, "SHOW COLUMNS FROM cars LIKE 'image_url'");
if (mysqli_num_rows($check_image_url) == 0) {
    $image_url_sql = "ALTER TABLE cars ADD COLUMN image_url VARCHAR(255)";
    execute_query($con, $image_url_sql, "add image_url column to cars table");
    
    // Copy values from image column to image_url
    $update_image_url = "UPDATE cars SET image_url = image WHERE image_url IS NULL";
    execute_query($con, $update_image_url, "copy image values to image_url");
} else {
    echo "<div style='color: green;'>✓ image_url column exists in cars table</div>";
}

// Ensure admin account exists
$admin_check = mysqli_query($con, "SELECT id FROM users WHERE email = 'admin@example.com' AND is_admin = 1");
if (mysqli_num_rows($admin_check) == 0) {
    $admin_insert = "INSERT INTO users (name, email, password, is_admin) 
                   VALUES ('Administrator', 'admin@example.com', '" . password_hash("admin123", PASSWORD_DEFAULT) . "', 1)";
    execute_query($con, $admin_insert, "create admin account");
} else {
    echo "<div style='color: green;'>✓ Admin account exists</div>";
}

// Verify database schema and fix any issues
$schema_issues = [];

// Check brand_id in cars table
$brand_id_check = mysqli_query($con, "SELECT * FROM cars WHERE brand_id IS NULL OR brand_id = 0 LIMIT 1");
if (mysqli_num_rows($brand_id_check) > 0) {
    $schema_issues[] = "Some cars have missing brand_id";
    
    // Try to fix by setting a default brand
    $default_brand_query = mysqli_query($con, "SELECT brand_id FROM brands LIMIT 1");
    if (mysqli_num_rows($default_brand_query) > 0) {
        $default_brand = mysqli_fetch_assoc($default_brand_query)['brand_id'];
        $fix_brand_id = "UPDATE cars SET brand_id = $default_brand WHERE brand_id IS NULL OR brand_id = 0";
        execute_query($con, $fix_brand_id, "fix missing brand_ids in cars table");
    }
}

if (empty($schema_issues)) {
    echo "<div style='color: green;'>✓ No schema issues found</div>";
}

echo "</div>";

// Success message and navigation
echo "<div class='section'>
        <h2>3. Results</h2>";

// Check if all tables exist now
$tables_check = [
    'users' => mysqli_num_rows(mysqli_query($con, "SHOW TABLES LIKE 'users'")),
    'brands' => mysqli_num_rows(mysqli_query($con, "SHOW TABLES LIKE 'brands'")),
    'cars' => mysqli_num_rows(mysqli_query($con, "SHOW TABLES LIKE 'cars'")),
    'orders' => mysqli_num_rows(mysqli_query($con, "SHOW TABLES LIKE 'orders'"))
];

$all_tables_exist = !in_array(0, $tables_check);

if ($all_tables_exist) {
    echo "<div class='success-message'>✅ All required tables have been created and set up successfully!</div>";
} else {
    echo "<div class='error-message'>❌ Some tables are still missing. Please refresh this page to try again.</div>";
    foreach ($tables_check as $table => $exists) {
        echo "<div>" . ($exists ? "✓" : "✗") . " $table table</div>";
    }
}

echo "<div style='margin-top: 20px;'>
        <p>You can now access:</p>
        <ul>
            <li><a href='admin/dashboard.php' class='btn btn-primary'>Admin Dashboard</a></li>
            <li><a href='login_direct_fix.php' class='btn btn-outline-primary mt-2'>Direct Login</a></li>
            <li><a href='login_page/login_page.php' class='btn btn-outline-secondary mt-2'>Standard Login</a></li>
        </ul>
        <p>Login credentials:</p>
        <ul>
            <li><strong>Admin:</strong> admin@example.com / admin123</li>
            <li><strong>Test User:</strong> test@example.com / test123</li>
        </ul>
      </div>";

echo "</div>";
echo "</div>
</body>
</html>";

// Close connection
mysqli_close($con);
?> 