<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once('includes/db_connection.php');

echo "<h1>Setting up Users Table</h1>";

// Create users table
$create_users_table = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($con, $create_users_table)) {
    echo "<p>✅ Users table created successfully!</p>";
} else {
    echo "<p>❌ Error creating users table: " . mysqli_error($con) . "</p>";
    exit;
}

// Check if admin user already exists
$check_admin = "SELECT * FROM users WHERE email = 'admin@example.com'";
$result = mysqli_query($con, $check_admin);

if (mysqli_num_rows($result) == 0) {
    // Create admin user
    $admin_name = "Administrator";
    $admin_email = "admin@example.com";
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT); // Default password: admin123
    $is_admin = 1;

    $insert_admin = "INSERT INTO users (name, email, password, is_admin) 
                     VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $insert_admin);
    mysqli_stmt_bind_param($stmt, "sssi", $admin_name, $admin_email, $admin_password, $is_admin);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>✅ Admin user created successfully!</p>";
        echo "<div style='background-color: #e9f7ef; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>Admin Login Details:</h3>";
        echo "<p><strong>Email:</strong> admin@example.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "</div>";
    } else {
        echo "<p>❌ Error creating admin user: " . mysqli_stmt_error($stmt) . "</p>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "<p>ℹ️ Admin user already exists.</p>";
    echo "<div style='background-color: #e9f7ef; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Admin Login Details:</h3>";
    echo "<p><strong>Email:</strong> admin@example.com</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "</div>";
}

// Create a regular user for testing
$check_user = "SELECT * FROM users WHERE email = 'user@example.com'";
$result = mysqli_query($con, $check_user);

if (mysqli_num_rows($result) == 0) {
    // Create regular user
    $user_name = "Regular User";
    $user_email = "user@example.com";
    $user_password = password_hash("user123", PASSWORD_DEFAULT); // Password: user123
    $is_admin = 0;

    $insert_user = "INSERT INTO users (name, email, password, is_admin) 
                    VALUES (?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($con, $insert_user);
    mysqli_stmt_bind_param($stmt, "sssi", $user_name, $user_email, $user_password, $is_admin);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p>✅ Regular user created successfully!</p>";
        echo "<div style='background-color: #eaf2f8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>Regular User Login Details:</h3>";
        echo "<p><strong>Email:</strong> user@example.com</p>";
        echo "<p><strong>Password:</strong> user123</p>";
        echo "</div>";
    } else {
        echo "<p>❌ Error creating regular user: " . mysqli_stmt_error($stmt) . "</p>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "<p>ℹ️ Regular user already exists.</p>";
    echo "<div style='background-color: #eaf2f8; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>Regular User Login Details:</h3>";
    echo "<p><strong>Email:</strong> user@example.com</p>";
    echo "<p><strong>Password:</strong> user123</p>";
    echo "</div>";
}

// Provide next steps
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Go to <a href='login_page/login_page.php'>Login Page</a></li>";
echo "<li>Use the admin credentials above to log in</li>";
echo "<li>After logging in, you can access the <a href='admin/dashboard.php'>Admin Dashboard</a></li>";
echo "</ol>";

mysqli_close($con);
?> 