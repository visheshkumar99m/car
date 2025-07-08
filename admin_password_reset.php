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
    <title>Admin Password Reset</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
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
        h1, h2 {
            color: #333;
        }
        .success {
            background-color: #e6ffe6;
            color: #006600;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .error {
            background-color: #ffe6e6;
            color: #cc0000;
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .btn {
            background-color: #3563E9;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #2a4eb7;
        }
        .button-group {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        .button-group a {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            background-color: #f2f2f2;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Password Reset Tool</h1>';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset_password'])) {
    // Include database connection
    require_once('includes/db_connection.php');
    
    // Check if we have a working database connection
    if (!$con) {
        echo '<div class="error">Database connection failed: ' . mysqli_connect_error() . '</div>';
    } else {
        // Set the new password
        $new_password = 'admin123';
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Check if the admin account exists
        $check_admin = "SELECT id FROM users WHERE email = 'admin@example.com' LIMIT 1";
        $result = mysqli_query($con, $check_admin);
        
        if ($result && mysqli_num_rows($result) > 0) {
            // Admin exists, update the password
            $admin = mysqli_fetch_assoc($result);
            $admin_id = $admin['id'];
            
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            
            if ($stmt = mysqli_prepare($con, $update_query)) {
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $admin_id);
                
                if (mysqli_stmt_execute($stmt)) {
                    echo '<div class="success">
                        <p>✅ Admin password has been reset successfully!</p>
                        <p>You can now log in with the following credentials:</p>
                        <p><strong>Email:</strong> admin@example.com<br>
                        <strong>Password:</strong> admin123</p>
                    </div>';
                } else {
                    echo '<div class="error">Failed to update password: ' . mysqli_error($con) . '</div>';
                }
                
                mysqli_stmt_close($stmt);
            } else {
                echo '<div class="error">Error preparing the statement: ' . mysqli_error($con) . '</div>';
            }
        } else {
            // Admin doesn't exist, create the account
            $name = "Administrator";
            $email = "admin@example.com";
            $is_admin = 1;
            
            $insert_query = "INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, ?)";
            
            if ($stmt = mysqli_prepare($con, $insert_query)) {
                mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hashed_password, $is_admin);
                
                if (mysqli_stmt_execute($stmt)) {
                    echo '<div class="success">
                        <p>✅ Admin account has been created successfully!</p>
                        <p>You can now log in with the following credentials:</p>
                        <p><strong>Email:</strong> admin@example.com<br>
                        <strong>Password:</strong> admin123</p>
                    </div>';
                } else {
                    echo '<div class="error">Failed to create admin account: ' . mysqli_error($con) . '</div>';
                }
                
                mysqli_stmt_close($stmt);
            } else {
                echo '<div class="error">Error preparing the statement: ' . mysqli_error($con) . '</div>';
            }
        }
        
        // Close the connection
        mysqli_close($con);
    }
}

// Display the form
echo '
    <p>Use this tool to reset the admin password if you are having trouble logging in.</p>
    <p>This will set the admin password to <strong>admin123</strong>.</p>
    
    <form method="post" action="' . htmlspecialchars($_SERVER["PHP_SELF"]) . '">
        <input type="hidden" name="reset_password" value="1">
        <button type="submit" class="btn">Reset Admin Password</button>
    </form>
    
    <div class="button-group">
        <a href="login_page/">Go to Login Page</a>
        <a href="login_test.php">Check Login System</a>
        <a href="front_page/front_page.php">Back to Home</a>
    </div>
</div>
</body>
</html>';
?> 