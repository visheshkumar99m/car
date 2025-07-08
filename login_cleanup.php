<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Login System Cleanup</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin: 40px auto;
        }
        h1, h2 {
            color: #3563E9;
            margin-bottom: 20px;
        }
        .result {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .btn-action {
            background-color: #3563E9;
            color: white;
            margin-top: 20px;
        }
        .btn-action:hover {
            background-color: #2a4eb7;
            color: white;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1 class='text-center'>Login System Cleanup</h1>";

// Initialize results array
$results = [];

// Check if cleanup was requested
if (isset($_GET['action']) && $_GET['action'] == 'cleanup') {
    // 1. Clear PHP session
    session_start();
    $_SESSION = array();
    
    // Destroy the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
    $results[] = ['success', 'PHP session data cleared successfully'];
    
    // 2. Look for redundant login files
    $redundant_files = [
        'debug_login.php',
        'login_fix.html',
        'login_debug.php',
        'login_data.php',
        'direct_login.php'
    ];
    
    foreach ($redundant_files as $file) {
        if (file_exists($file)) {
            if (unlink($file)) {
                $results[] = ['success', "Deleted redundant file: $file"];
            } else {
                $results[] = ['error', "Failed to delete redundant file: $file"];
            }
        }
    }
    
    // Check login_page directory
    if (is_dir('login_page')) {
        $login_page_files = [
            'login_page/login_data.php'
        ];
        
        foreach ($login_page_files as $file) {
            if (file_exists($file)) {
                if (unlink($file)) {
                    $results[] = ['success', "Deleted redundant file: $file"];
                } else {
                    $results[] = ['error', "Failed to delete redundant file: $file"];
                }
            }
        }
    }
    
    // Check database and clear test junk data
    try {
        require_once('includes/db_connection.php');
        
        if ($con) {
            // Check if users table exists
            $check_table = mysqli_query($con, "SHOW TABLES LIKE 'users'");
            
            if (mysqli_num_rows($check_table) > 0) {
                // Remove any test accounts except admin and test@example.com
                $clean_users = mysqli_query($con, "DELETE FROM users WHERE email LIKE 'test%@%' AND email != 'test@example.com'");
                $affected_rows = mysqli_affected_rows($con);
                
                if ($affected_rows > 0) {
                    $results[] = ['success', "Removed $affected_rows test user(s) from the database"];
                } else {
                    $results[] = ['success', "No test users found to remove"];
                }
                
                // Update admin and test account passwords to be properly hashed
                $admin_account = mysqli_query($con, "SELECT id, password FROM users WHERE email = 'admin@example.com'");
                if (mysqli_num_rows($admin_account) > 0) {
                    $admin = mysqli_fetch_assoc($admin_account);
                    $password_info = password_get_info($admin['password']);
                    $is_hashed = $password_info['algo'] !== 0;
                    
                    if (!$is_hashed) {
                        $hashed_password = password_hash("admin123", PASSWORD_DEFAULT);
                        mysqli_query($con, "UPDATE users SET password = '$hashed_password' WHERE id = {$admin['id']}");
                        $results[] = ['success', "Updated admin password to be properly hashed"];
                    } else {
                        $results[] = ['success', "Admin password is already properly hashed"];
                    }
                }
                
                $test_account = mysqli_query($con, "SELECT id, password FROM users WHERE email = 'test@example.com'");
                if (mysqli_num_rows($test_account) > 0) {
                    $test = mysqli_fetch_assoc($test_account);
                    $password_info = password_get_info($test['password']);
                    $is_hashed = $password_info['algo'] !== 0;
                    
                    if (!$is_hashed) {
                        $hashed_password = password_hash("test123", PASSWORD_DEFAULT);
                        mysqli_query($con, "UPDATE users SET password = '$hashed_password' WHERE id = {$test['id']}");
                        $results[] = ['success', "Updated test user password to be properly hashed"];
                    } else {
                        $results[] = ['success', "Test user password is already properly hashed"];
                    }
                }
            } else {
                $results[] = ['error', "Users table does not exist in the database"];
            }
            
            mysqli_close($con);
        } else {
            $results[] = ['error', "Could not connect to database: " . mysqli_connect_error()];
        }
    } catch (Exception $e) {
        $results[] = ['error', "Database error: " . $e->getMessage()];
    }
}

// Display the results
if (!empty($results)) {
    echo "<div class='result'>";
    echo "<h2>Cleanup Results</h2>";
    echo "<ul>";
    
    foreach ($results as $result) {
        $class = $result[0] == 'success' ? 'success' : 'error';
        echo "<li class='$class'>{$result[1]}</li>";
    }
    
    echo "</ul>";
    echo "</div>";
}

// Display cleanup instructions and button
echo "
    <div class='card'>
        <div class='card-body'>
            <h2>Login System Cleanup</h2>
            <p>This tool will:</p>
            <ul>
                <li>Clear your current session data</li>
                <li>Remove redundant/deprecated login files</li>
                <li>Clean test user accounts from the database</li>
                <li>Ensure admin and test account passwords are properly hashed</li>
            </ul>
            
            <p><strong>Note:</strong> This action will log you out if you're currently logged in.</p>";

if (empty($results)) {
    echo "<a href='?action=cleanup' class='btn btn-action'>Run Cleanup</a>";
} else {
    echo "<a href='login_system_test.php' class='btn btn-action'>Run Diagnostic</a> ";
    echo "<a href='login.php' class='btn btn-action'>Go to Login</a>";
}

echo "
        </div>
    </div>
    </div>
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?> 