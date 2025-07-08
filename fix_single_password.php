<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<h1>Password Hash Fixer</h1>";

// Check if required parameters are provided
if (!isset($_POST['user_id']) || !isset($_POST['password'])) {
    die("<p style='color: red;'>Error: Missing required parameters.</p>");
}

// Get parameters
$user_id = $_POST['user_id'];
$password = $_POST['password'];

// Include database connection
require_once('includes/db_connection.php');

// Check database connection
if (!$con) {
    die("<p style='color: red;'>Database connection failed: " . mysqli_connect_error() . "</p>");
}

// Create proper hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Update user's password
$update_query = "UPDATE users SET password = ? WHERE id = ?";

if ($stmt = mysqli_prepare($con, $update_query)) {
    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo "<p style='color: green;'>Password hash updated successfully!</p>";
        
        // Get user information
        $user_query = "SELECT name, email FROM users WHERE id = ?";
        $user_stmt = mysqli_prepare($con, $user_query);
        mysqli_stmt_bind_param($user_stmt, "i", $user_id);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        
        if ($user = mysqli_fetch_assoc($user_result)) {
            echo "<p>User: " . htmlspecialchars($user['name']) . " (" . htmlspecialchars($user['email']) . ")</p>";
        }
        
        echo "<p>The plain text password '" . htmlspecialchars($password) . "' has been properly hashed.</p>";
        echo "<p>New hash: " . $hashed_password . "</p>";
    } else {
        echo "<p style='color: red;'>Error updating password: " . mysqli_error($con) . "</p>";
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo "<p style='color: red;'>Error preparing statement: " . mysqli_error($con) . "</p>";
}

// Close connection
mysqli_close($con);

echo "<p><a href='login_debug_test.php' style='display: inline-block; padding: 10px 15px; background-color: #3563E9; color: white; text-decoration: none; border-radius: 5px;'>Back to Debug Tool</a></p>";
echo "<p><a href='login_page/login_page.php' style='display: inline-block; padding: 10px 15px; background-color: #3563E9; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
?> 