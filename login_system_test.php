<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Login System Diagnostic</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 900px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin: 40px auto;
        }
        h1 {
            color: #3563E9;
            margin-bottom: 30px;
            text-align: center;
        }
        h2 {
            color: #1A202C;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e6e6e6;
        }
        .status {
            margin: 20px 0;
            padding: 15px;
            border-radius: 8px;
        }
        .status-ok {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        .status-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 5px solid #ffc107;
        }
        .status-error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        .fix-btn {
            background-color: #3563E9;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .fix-btn:hover {
            background-color: #2a4eb7;
            color: white;
        }
        .result-table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .result-table th, .result-table td {
            padding: 8px 12px;
            border: 1px solid #dee2e6;
        }
        .result-table th {
            background-color: #f8f9fa;
        }
        .navigation {
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class='container'>";

echo "<h1><i class='bi bi-shield-lock'></i> Login System Diagnostic</h1>";

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Functions to help with presentation
function display_status($message, $type = 'ok') {
    $class = 'status-' . $type;
    echo "<div class='status $class'>$message</div>";
}

function display_table_start() {
    echo "<table class='result-table'><thead><tr>";
}

function display_table_end() {
    echo "</tbody></table>";
}

// Check 1: PHP Version
echo "<h2>1. PHP Environment</h2>";
$php_version = phpversion();
if (version_compare($php_version, '7.0.0', '>=')) {
    display_status("PHP Version: $php_version - Compatible with modern hashing functions.", "ok");
} else {
    display_status("PHP Version: $php_version - Outdated. May cause issues with password hashing.", "error");
}

// Check 2: Database Connection
echo "<h2>2. Database Connection</h2>";
try {
    require_once('includes/db_connection.php');
    
    if ($con) {
        display_status("Successfully connected to the database", "ok");
        
        // Check for user table
        $check_table = mysqli_query($con, "SHOW TABLES LIKE 'users'");
        if (mysqli_num_rows($check_table) > 0) {
            display_status("Users table exists", "ok");
            
            // Check table structure
            $structure_query = mysqli_query($con, "DESCRIBE users");
            $columns = [];
            while ($column = mysqli_fetch_assoc($structure_query)) {
                $columns[] = $column['Field'];
            }
            
            $required_columns = ['id', 'name', 'email', 'password', 'is_admin'];
            $missing_columns = array_diff($required_columns, $columns);
            
            if (empty($missing_columns)) {
                display_status("Users table has all required columns", "ok");
            } else {
                display_status("Users table is missing required columns: " . implode(", ", $missing_columns), "error");
            }
            
            // Check for users
            $count_query = mysqli_query($con, "SELECT COUNT(*) as total FROM users");
            $count = mysqli_fetch_assoc($count_query)['total'];
            
            if ($count > 0) {
                display_status("Users table contains $count user records", "ok");
                
                // Check admin account
                $admin_query = mysqli_query($con, "SELECT id, name, email, password, is_admin FROM users WHERE email = 'admin@example.com'");
                if (mysqli_num_rows($admin_query) > 0) {
                    $admin = mysqli_fetch_assoc($admin_query);
                    $password_info = password_get_info($admin['password']);
                    $is_hashed = $password_info['algo'] !== 0;
                    
                    if ($is_hashed) {
                        display_status("Admin account is properly configured with hashed password", "ok");
                    } else {
                        display_status("Admin account exists but password is not hashed", "warning");
                        echo "<a href='login_fix.php' class='fix-btn'>Fix Admin Password</a>";
                    }
                } else {
                    display_status("Admin account does not exist", "warning");
                    echo "<a href='login_fix.php' class='fix-btn'>Create Admin Account</a>";
                }
                
                // Check for unhashed passwords
                $all_users = mysqli_query($con, "SELECT id, email, password FROM users");
                $unhashed_count = 0;
                
                display_table_start();
                echo "<th>Email</th><th>Password Status</th></tr></thead><tbody>";
                
                while ($user = mysqli_fetch_assoc($all_users)) {
                    $password_info = password_get_info($user['password']);
                    $is_hashed = $password_info['algo'] !== 0;
                    
                    if (!$is_hashed) {
                        $unhashed_count++;
                    }
                    
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                    echo "<td>" . ($is_hashed ? "<span style='color: green;'>Hashed</span>" : "<span style='color: red;'>Not Hashed</span>") . "</td>";
                    echo "</tr>";
                }
                
                display_table_end();
                
                if ($unhashed_count > 0) {
                    display_status("Found $unhashed_count user(s) with unhashed passwords", "warning");
                    echo "<a href='login_fix.php' class='fix-btn'>Fix All Passwords</a>";
                } else {
                    display_status("All user passwords are properly hashed", "ok");
                }
            } else {
                display_status("Users table is empty", "warning");
                echo "<a href='login_fix.php' class='fix-btn'>Create Test Users</a>";
            }
        } else {
            display_status("Users table does not exist", "error");
            echo "<a href='create_users_table.php' class='fix-btn'>Create Users Table</a>";
        }
    } else {
        display_status("Failed to connect to the database: " . mysqli_connect_error(), "error");
    }
} catch (Exception $e) {
    display_status("Error: " . $e->getMessage(), "error");
}

// Check 3: Session Configuration
echo "<h2>3. Session Configuration</h2>";
$session_id = session_id();

if (!empty($session_id)) {
    display_status("Session is active with ID: $session_id", "ok");
    
    // Test session variable
    $_SESSION['test_var'] = "Testing session at " . date('Y-m-d H:i:s');
    
    display_status("Successfully set test session variable", "ok");
    
    // Check key session settings
    $session_path = ini_get('session.save_path');
    $session_lifetime = ini_get('session.gc_maxlifetime');
    $session_cookie_lifetime = ini_get('session.cookie_lifetime');
    
    echo "<p>Session save path: $session_path</p>";
    echo "<p>Session max lifetime: $session_lifetime seconds</p>";
    echo "<p>Session cookie lifetime: $session_cookie_lifetime seconds</p>";
    
    if (empty($session_path) || !is_dir($session_path) || !is_writable($session_path)) {
        display_status("Session save path may be invalid or not writable", "warning");
    }
} else {
    display_status("Failed to start session", "error");
}

// Check 4: Login/Redirect Flow
echo "<h2>4. Login Flow Check</h2>";

// Get all files involved in login
$login_files = [
    'login.php' => file_exists('login.php'),
    'login_page/login_page.php' => file_exists('login_page/login_page.php'),
    'includes/auth_check.php' => file_exists('includes/auth_check.php'),
    'includes/db_connection.php' => file_exists('includes/db_connection.php')
];

display_table_start();
echo "<th>File</th><th>Status</th></tr></thead><tbody>";

foreach ($login_files as $file => $exists) {
    echo "<tr>";
    echo "<td>$file</td>";
    echo "<td>" . ($exists ? "<span style='color: green;'>Exists</span>" : "<span style='color: red;'>Missing</span>") . "</td>";
    echo "</tr>";
}

display_table_end();

$missing_files = array_filter($login_files, function($exists) { return !$exists; });

if (empty($missing_files)) {
    display_status("All required login files exist", "ok");
} else {
    display_status("Some login files are missing", "error");
}

// Summary and Next Steps
echo "<h2>Summary</h2>";
echo "<p>This diagnostic tool has checked your login system configuration. Please fix any warnings or errors indicated above.</p>";
echo "<p>For the best results:</p>";
echo "<ol>";
echo "<li>Ensure all passwords are properly hashed using PHP's password_hash() function</li>";
echo "<li>Make sure session handling is consistent across all pages</li>";
echo "<li>Verify that the login flow from login.php to login_page.php works correctly</li>";
echo "<li>Clear your browser cookies if you encounter persistent login issues</li>";
echo "</ol>";

echo "<div class='navigation'>";
echo "<a href='login_fix.php' class='fix-btn'>Run Auto-Fix</a> &nbsp;";
echo "<a href='login.php' class='fix-btn'>Go to Login Page</a> &nbsp;";
echo "<a href='front_page/front_page.php' class='fix-btn'>Go to Home Page</a>";
echo "</div>";

// Close database connection if open
if (isset($con) && $con) {
    mysqli_close($con);
}

echo "</div>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>