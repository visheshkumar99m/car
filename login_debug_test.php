<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

echo "<h1>Login Debug Tool</h1>";

// Include database connection
require_once('includes/db_connection.php');

// Check database connection
if (!$con) {
    die("<p style='color: red;'>Database connection failed: " . mysqli_connect_error() . "</p>");
} else {
    echo "<p style='color: green;'>Database connection successful!</p>";
    
    // Check if users table exists
    $check_table = mysqli_query($con, "SHOW TABLES LIKE 'users'");
    if (mysqli_num_rows($check_table) == 0) {
        echo "<p style='color: red;'>The 'users' table does not exist in the database!</p>";
    } else {
        echo "<p style='color: green;'>Users table exists.</p>";
        
        // Check users in database
        $query = "SELECT id, name, email, password, is_admin FROM users";
        $result = mysqli_query($con, $query);
        
        if (!$result) {
            echo "<p style='color: red;'>Error querying users: " . mysqli_error($con) . "</p>";
        } else {
            $count = mysqli_num_rows($result);
            echo "<p>Found $count users in the database:</p>";
            
            if ($count > 0) {
                echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
                echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Password Hash Info</th><th>Is Admin</th></tr>";
                
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . $row['name'] . "</td>";
                    echo "<td>" . $row['email'] . "</td>";
                    
                    // Check if password is hashed
                    $password_info = password_get_info($row['password']);
                    $is_hashed = $password_info['algo'] !== 0;
                    
                    if ($is_hashed) {
                        echo "<td style='color: green;'>Properly hashed (Algorithm: " . $password_info['algoName'] . ")</td>";
                    } else {
                        echo "<td style='color: red;'>Not hashed: " . substr($row['password'], 0, 5) . "...</td>";
                    }
                    
                    echo "<td>" . ($row['is_admin'] ? 'Yes' : 'No') . "</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            }
        }
    }
}

// Check for test credentials
echo "<h2>Test Login Credentials</h2>";
echo "<form method='post'>";
echo "<label for='email'>Email:</label>";
echo "<input type='email' name='email' value='admin@example.com' required><br><br>";
echo "<label for='password'>Password:</label>";
echo "<input type='password' name='password' value='admin123' required><br><br>";
echo "<button type='submit'>Test Login</button>";
echo "</form>";

// Process test login
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    
    echo "<h3>Login Test Results:</h3>";
    echo "<p>Attempting login with email: " . htmlspecialchars($email) . "</p>";
    
    // Look up user
    $sql = "SELECT id, name, email, password, is_admin FROM users WHERE email = ?";
    
    if ($stmt = mysqli_prepare($con, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                
                echo "<p style='color: green;'>User found in database.</p>";
                
                // Check if password is already hashed
                $password_info = password_get_info($row['password']);
                $is_hashed = $password_info['algo'] !== 0;
                
                echo "<p>Password is " . ($is_hashed ? "hashed" : "not hashed") . "</p>";
                
                // Try both password verification methods
                $verify_result = password_verify($password, $row['password']);
                $direct_match = ($password === $row['password']);
                
                echo "<p>password_verify() result: " . ($verify_result ? "SUCCESS" : "FAILED") . "</p>";
                echo "<p>Direct string comparison: " . ($direct_match ? "SUCCESS" : "FAILED") . "</p>";
                
                if ($verify_result || $direct_match) {
                    echo "<p style='color: green; font-weight: bold;'>Login successful!</p>";
                    
                    // If password is not hashed but matches directly, offer to update it
                    if (!$is_hashed && $direct_match) {
                        echo "<form method='post' action='fix_single_password.php'>";
                        echo "<input type='hidden' name='user_id' value='" . $row['id'] . "'>";
                        echo "<input type='hidden' name='password' value='" . htmlspecialchars($password) . "'>";
                        echo "<p>Password needs to be hashed.</p>";
                        echo "<button type='submit'>Update Password Hash</button>";
                        echo "</form>";
                    }
                } else {
                    echo "<p style='color: red; font-weight: bold;'>Login failed! Invalid password.</p>";
                }
            } else {
                echo "<p style='color: red;'>No user found with email: " . htmlspecialchars($email) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>Query execution failed: " . mysqli_error($con) . "</p>";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "<p style='color: red;'>Prepare statement failed: " . mysqli_error($con) . "</p>";
    }
}

// Check session data
echo "<h2>Current Session Data</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

mysqli_close($con);
?> 