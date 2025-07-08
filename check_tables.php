<?php
// Include database connection
require_once('includes/db_connection.php');

echo "<h1>Database Tables Check</h1>";

// Check users table
$check_users = mysqli_query($con, "SHOW TABLES LIKE 'users'");
$users_exists = mysqli_num_rows($check_users) > 0;

echo "<p>Users table exists: " . ($users_exists ? "Yes" : "No") . "</p>";

// If it exists, count users
if ($users_exists) {
    $count_query = mysqli_query($con, "SELECT COUNT(*) as count FROM users");
    $count = mysqli_fetch_assoc($count_query);
    echo "<p>Number of users: " . $count['count'] . "</p>";
    
    // Check for admin user
    $admin_query = mysqli_query($con, "SELECT COUNT(*) as count FROM users WHERE is_admin = 1");
    $admin_count = mysqli_fetch_assoc($admin_query);
    echo "<p>Number of admin users: " . $admin_count['count'] . "</p>";
}

// Check cars table
$check_cars = mysqli_query($con, "SHOW TABLES LIKE 'cars'");
$cars_exists = mysqli_num_rows($check_cars) > 0;

echo "<p>Cars table exists: " . ($cars_exists ? "Yes" : "No") . "</p>";

// If it exists, count cars
if ($cars_exists) {
    $count_query = mysqli_query($con, "SELECT COUNT(*) as count FROM cars");
    $count = mysqli_fetch_assoc($count_query);
    echo "<p>Number of cars: " . $count['count'] . "</p>";
}

// Check brands table
$check_brands = mysqli_query($con, "SHOW TABLES LIKE 'brands'");
$brands_exists = mysqli_num_rows($check_brands) > 0;

echo "<p>Brands table exists: " . ($brands_exists ? "Yes" : "No") . "</p>";

// Check orders table
$check_orders = mysqli_query($con, "SHOW TABLES LIKE 'orders'");
$orders_exists = mysqli_num_rows($check_orders) > 0;

echo "<p>Orders table exists: " . ($orders_exists ? "Yes" : "No") . "</p>";

echo "<h2>Login Test</h2>";
echo "<p>To test login, use: <a href='login.php'>Login Page</a></p>";
echo "<p>To test direct login (debug): <a href='direct_login.php'>Direct Login</a></p>";

echo "<h2>Contact Dealer Test</h2>";
echo "<p>To test contact dealer functionality:</p>";
echo "<ol>";
echo "<li>Browse to <a href='front_page/front_page.php'>Home Page</a></li>";
echo "<li>Click on 'Cars' in the navigation menu</li>";
echo "<li>Select a car to view details</li>";
echo "<li>Click the 'Contact Dealer' button</li>";
echo "</ol>";

// Close connection
mysqli_close($con);
?> 