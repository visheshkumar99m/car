<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Login System Fix Tool</h1>";

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once('includes/db_connection.php');

// Check database connection
if (!$con) {
    die("<p style='color: red;'>Database connection failed: " . mysqli_connect_error() . "</p>");
}

// Initialize problem counters
$problems_found = 0;
$problems_fixed = 0;

echo "<h2>Checking for Login System Issues</h2>";

// Check 1: Verify users table exists
echo "<p>Checking for users table...</p>";
$check_table = mysqli_query($con, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($check_table) == 0) {
    echo "<p style='color: red;'>❌ The 'users' table does not exist in the database!</p>";
    $problems_found++;
} else {
    echo "<p style='color: green;'>✅ Users table exists.</p>";
    
    // Check 2: Verify test accounts exist
    echo "<p>Checking for test accounts...</p>";
    
    $check_admin = mysqli_query($con, "SELECT id, password FROM users WHERE email = 'admin@example.com'");
    $check_test = mysqli_query($con, "SELECT id, password FROM users WHERE email = 'test@example.com'");
    
    $admin_exists = mysqli_num_rows($check_admin) > 0;
    $test_exists = mysqli_num_rows($check_test) > 0;
    
    // Create or fix admin account
    if (!$admin_exists) {
        echo "<p style='color: red;'>❌ Admin account not found.</p>";
        $problems_found++;
        
        $admin_name = "Administrator";
        $admin_email = "admin@example.com";
        $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
        
        $insert_admin = "INSERT INTO users (name, email, password, is_admin) 
                         VALUES ('$admin_name', '$admin_email', '$admin_password', 1)";
        
        if (mysqli_query($con, $insert_admin)) {
            echo "<p style='color: green;'>✅ Admin account created successfully.</p>";
            echo "<p>Email: admin@example.com<br>Password: admin123</p>";
            $problems_fixed++;
        } else {
            echo "<p style='color: red;'>Failed to create admin account: " . mysqli_error($con) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Admin account exists.</p>";
        
        // Check admin password hash
        $admin_row = mysqli_fetch_assoc($check_admin);
        $password_info = password_get_info($admin_row['password']);
        $is_hashed = $password_info['algo'] !== 0;
        
        if (!$is_hashed) {
            echo "<p style='color: red;'>❌ Admin password is not properly hashed.</p>";
            $problems_found++;
            
            // Fix admin password
            $admin_id = $admin_row['id'];
            $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
            
            if (mysqli_query($con, "UPDATE users SET password = '$admin_password' WHERE id = $admin_id")) {
                echo "<p style='color: green;'>✅ Admin password properly hashed.</p>";
                $problems_fixed++;
            } else {
                echo "<p style='color: red;'>Failed to update admin password: " . mysqli_error($con) . "</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ Admin password is properly hashed.</p>";
        }
    }
    
    // Create or fix test account
    if (!$test_exists) {
        echo "<p style='color: red;'>❌ Test account not found.</p>";
        $problems_found++;
        
        $test_name = "Test User";
        $test_email = "test@example.com";
        $test_password = password_hash("test123", PASSWORD_DEFAULT);
        
        $insert_test = "INSERT INTO users (name, email, password, is_admin) 
                        VALUES ('$test_name', '$test_email', '$test_password', 0)";
        
        if (mysqli_query($con, $insert_test)) {
            echo "<p style='color: green;'>✅ Test account created successfully.</p>";
            echo "<p>Email: test@example.com<br>Password: test123</p>";
            $problems_fixed++;
        } else {
            echo "<p style='color: red;'>Failed to create test account: " . mysqli_error($con) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✅ Test account exists.</p>";
        
        // Check test password hash
        $test_row = mysqli_fetch_assoc($check_test);
        $password_info = password_get_info($test_row['password']);
        $is_hashed = $password_info['algo'] !== 0;
        
        if (!$is_hashed) {
            echo "<p style='color: red;'>❌ Test password is not properly hashed.</p>";
            $problems_found++;
            
            // Fix test password
            $test_id = $test_row['id'];
            $test_password = password_hash("test123", PASSWORD_DEFAULT);
            
            if (mysqli_query($con, "UPDATE users SET password = '$test_password' WHERE id = $test_id")) {
                echo "<p style='color: green;'>✅ Test password properly hashed.</p>";
                $problems_fixed++;
            } else {
                echo "<p style='color: red;'>Failed to update test password: " . mysqli_error($con) . "</p>";
            }
        } else {
            echo "<p style='color: green;'>✅ Test password is properly hashed.</p>";
        }
    }
    
    // Check 3: Review password hashing for all users
    echo "<p>Checking password hashing for all users...</p>";
    
    $all_users = mysqli_query($con, "SELECT id, name, email, password FROM users");
    $unhashed_count = 0;
    $fixed_count = 0;
    
    while ($user = mysqli_fetch_assoc($all_users)) {
        $password_info = password_get_info($user['password']);
        $is_hashed = $password_info['algo'] !== 0;
        
        if (!$is_hashed) {
            $unhashed_count++;
            echo "<p>User " . htmlspecialchars($user['email']) . " has unhashed password.</p>";
            
            // Set a simple default password for this user (usually you'd want to force a password reset)
            $new_password = "password123"; // You can customize this
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            if (mysqli_query($con, "UPDATE users SET password = '$hashed_password' WHERE id = {$user['id']}")) {
                $fixed_count++;
                echo "<p style='color: green;'>✅ Password for " . htmlspecialchars($user['email']) . " set to '$new_password' and properly hashed.</p>";
            }
        }
    }
    
    if ($unhashed_count > 0) {
        echo "<p style='color: red;'>❌ Found $unhashed_count users with unhashed passwords.</p>";
        $problems_found += $unhashed_count;
        echo "<p style='color: green;'>✅ Fixed $fixed_count user passwords.</p>";
        $problems_fixed += $fixed_count;
    } else {
        echo "<p style='color: green;'>✅ All user passwords are properly hashed.</p>";
    }
}

// Check 4: Verify session is working
echo "<h2>Checking Session Configuration</h2>";

$_SESSION['test_var'] = "Session test at " . date('Y-m-d H:i:s');
echo "<p>Set test variable in session: " . $_SESSION['test_var'] . "</p>";

// Final summary
echo "<h2>Summary</h2>";
echo "<p>Problems found: $problems_found</p>";
echo "<p>Problems fixed: $problems_fixed</p>";

if ($problems_fixed > 0) {
    echo "<p style='color: green;'>✅ Login system has been repaired. Please try logging in again.</p>";
} else if ($problems_found == 0) {
    echo "<p style='color: green;'>✅ No issues found with the login system. If you're still having problems, please try clearing your browser cookies.</p>";
} else {
    echo "<p style='color: red;'>❌ Some issues could not be fixed automatically.</p>";
}

// Close connection
mysqli_close($con);

echo "<h2>Next Steps</h2>";
echo "<p>Try logging in with one of these accounts:</p>";
echo "<ul>";
echo "<li><strong>Admin:</strong> admin@example.com / admin123</li>";
echo "<li><strong>Test User:</strong> test@example.com / test123</li>";
echo "</ul>";
echo "<p><a href='login.php' style='display: inline-block; padding: 10px 15px; background-color: #3563E9; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
?> 