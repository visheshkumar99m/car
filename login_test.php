<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Basic styling
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 40px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2, h3 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .code {
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login System Test</h1>';

// Include database connection
require_once('includes/db_connection.php');

// 1. Check database connection
echo '<h2>1. Database Connection</h2>';
if ($con) {
    echo '<p class="success">✅ Database connection successful</p>';
} else {
    echo '<p class="error">❌ Database connection failed: ' . mysqli_connect_error() . '</p>';
    exit;
}

// 2. Check if users table exists
echo '<h2>2. Users Table</h2>';
$check_table = mysqli_query($con, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($check_table) > 0) {
    echo '<p class="success">✅ Users table exists</p>';
    
    // 3. Display all users in the table
    echo '<h3>Current Users:</h3>';
    $users_query = "SELECT id, name, email, password, is_admin, created_at FROM users";
    $users_result = mysqli_query($con, $users_query);
    
    if ($users_result && mysqli_num_rows($users_result) > 0) {
        echo '<table>';
        echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Password Hash</th><th>Admin</th><th>Created</th></tr>';
        
        while ($row = mysqli_fetch_assoc($users_result)) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['name']) . '</td>';
            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
            echo '<td>' . substr(htmlspecialchars($row['password']), 0, 20) . '...</td>';
            echo '<td>' . ($row['is_admin'] ? 'Yes' : 'No') . '</td>';
            echo '<td>' . htmlspecialchars($row['created_at'] ?? 'N/A') . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
    } else {
        echo '<p class="error">❌ No users found in the table</p>';
    }
    
    // 4. Test password verification for admin user
    echo '<h3>Password Verification Test:</h3>';
    $check_admin = "SELECT id, password FROM users WHERE email = 'admin@example.com' LIMIT 1";
    $admin_result = mysqli_query($con, $check_admin);
    
    if ($admin_result && mysqli_num_rows($admin_result) > 0) {
        $admin = mysqli_fetch_assoc($admin_result);
        $admin_id = $admin['id'];
        $stored_hash = $admin['password'];
        
        echo '<p>Testing admin password verification:</p>';
        $test_password = 'admin123';
        
        if (password_verify($test_password, $stored_hash)) {
            echo '<p class="success">✅ Password verification success for "admin123"</p>';
        } else {
            echo '<p class="error">❌ Password verification failed for "admin123"</p>';
            
            // Offer to reset admin password
            echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">';
            echo '<input type="hidden" name="reset_admin_password" value="1">';
            echo '<button type="submit">Reset Admin Password</button>';
            echo '</form>';
        }
    } else {
        echo '<p class="error">❌ Admin user not found</p>';
    }
    
    // 5. Process admin password reset if requested
    if (isset($_POST['reset_admin_password'])) {
        $new_password = 'admin123';
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update_query = "UPDATE users SET password = ? WHERE email = 'admin@example.com'";
        if ($update_stmt = mysqli_prepare($con, $update_query)) {
            mysqli_stmt_bind_param($update_stmt, "s", $hashed_password);
            
            if (mysqli_stmt_execute($update_stmt)) {
                echo '<p class="success">✅ Admin password reset successfully!</p>';
                echo '<p>New password: admin123</p>';
            } else {
                echo '<p class="error">❌ Error resetting admin password: ' . mysqli_error($con) . '</p>';
            }
            
            mysqli_stmt_close($update_stmt);
        }
    }
} else {
    echo '<p class="error">❌ Users table does not exist</p>';
}

// 6. Session information
echo '<h2>3. Session Information</h2>';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

echo '<div class="code">';
echo '<pre>';
print_r($_SESSION);
echo '</pre>';
echo '</div>';

// 7. Login links
echo '<h2>4. Login Options</h2>';
echo '<ul>';
echo '<li><a href="login_page/login_page.php">Original Login Page</a></li>';
echo '<li><a href="login_page/alt_login.php">Alternative Login Page</a></li>';
echo '<li><a href="direct_login.php">Direct Login Page</a></li>';
echo '</ul>';

// Close the connection
mysqli_close($con);

echo '</div></body></html>';
?> 