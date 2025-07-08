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

// Check if table already exists
$table_check = mysqli_query($con, "SHOW TABLES LIKE 'orders'");
if (mysqli_num_rows($table_check) > 0) {
    echo "<p>Table 'orders' already exists.</p>";
} else {
    // SQL to create orders table
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
        echo "<p>Table 'orders' created successfully!</p>";
        
        // Add some sample orders for testing
        $sample_orders = [
            "INSERT INTO orders (user_id, car_id, status, amount, payment_method, shipping_address) 
             VALUES (1, 1, 'completed', 1500000.00, 'Credit Card', 'Sample Address 1, City, State, PIN')",
            "INSERT INTO orders (user_id, car_id, status, amount, payment_method, shipping_address) 
             VALUES (1, 2, 'processing', 2200000.00, 'Bank Transfer', 'Sample Address 2, City, State, PIN')",
            "INSERT INTO orders (user_id, car_id, status, amount, payment_method, shipping_address) 
             VALUES (1, 3, 'pending', 1800000.00, 'EMI', 'Sample Address 3, City, State, PIN')"
        ];
        
        $sample_count = 0;
        foreach ($sample_orders as $order_sql) {
            if (mysqli_query($con, $order_sql)) {
                $sample_count++;
            } else {
                echo "<p>Error adding sample order: " . mysqli_error($con) . "</p>";
            }
        }
        
        if ($sample_count > 0) {
            echo "<p>Added $sample_count sample orders for testing.</p>";
        }
    } else {
        echo "<p>Error creating orders table: " . mysqli_error($con) . "</p>";
    }
}

// Close connection
mysqli_close($con);

echo "<p><a href='admin/dashboard.php'>Go to Admin Dashboard</a></p>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Orders Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        p {
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f5f5f5;
            border-left: 4px solid #3563E9;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #3563E9;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        a:hover {
            background-color: #2a4ebf;
        }
    </style>
</head>
<body>
    <h1>Orders Table Setup</h1>
</body>
</html> 