<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Initialize variables
$email = $password = "";
$login_message = "";
$success = false;

// Include database connection
require_once('includes/db_connection.php');

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    
    echo "<h3>Login Attempt Details:</h3>";
    echo "<p>Email: " . htmlspecialchars($email) . "</p>";
    
    // Check database connection
    if (!$con) {
        $login_message = "Database connection failed: " . mysqli_connect_error();
    } else {
        // Check if users table exists
        $check_table = mysqli_query($con, "SHOW TABLES LIKE 'users'");
        if (mysqli_num_rows($check_table) == 0) {
            $login_message = "The 'users' table does not exist in the database.";
        } else {
            // Prepare a select statement
            $sql = "SELECT * FROM users WHERE email = ?";
            
            if ($stmt = mysqli_prepare($con, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "s", $email);
                
                // Execute the statement
                if (mysqli_stmt_execute($stmt)) {
                    // Store result
                    $result = mysqli_stmt_get_result($stmt);
                    
                    // Check if email exists
                    if (mysqli_num_rows($result) == 1) {
                        // Fetch the row
                        $row = mysqli_fetch_assoc($result);
                        
                        // Display user info for debugging
                        echo "<p>User found in database:</p>";
                        echo "<ul>";
                        echo "<li>ID: " . $row['id'] . "</li>";
                        echo "<li>Name: " . $row['name'] . "</li>";
                        echo "<li>Is Admin: " . ($row['is_admin'] ? 'Yes' : 'No') . "</li>";
                        echo "<li>Password Hash: " . substr($row['password'], 0, 20) . "...</li>";
                        echo "</ul>";
                        
                        // Test password verification
                        echo "<p>Testing password verification...</p>";
                        
                        if (password_verify($password, $row['password'])) {
                            echo "<p style='color: green;'>✅ Password verification successful!</p>";
                            
                            // Set session variables
                            $_SESSION["logged_in"] = true;
                            $_SESSION["id"] = $row['id'];
                            $_SESSION["user_name"] = $row['name'];
                            $_SESSION["email"] = $row['email'];
                            $_SESSION["is_admin"] = $row['is_admin'] == 1;
                            
                            $login_message = "Login successful!";
                            $success = true;
                            
                            // Create simple admin link
                            echo "<div style='margin-top: 20px; padding: 15px; background-color: #e9f7ef; border-radius: 5px;'>";
                            echo "<h3>Admin Access:</h3>";
                            echo "<p>You are now logged in as an admin. You can access the admin dashboard.</p>";
                            echo "<p><a href='admin/dashboard.php' style='display: inline-block; padding: 10px 15px; background-color: #3563E9; color: white; text-decoration: none; border-radius: 5px;'>Go to Admin Dashboard</a></p>";
                            echo "</div>";
                            
                        } else {
                            echo "<p style='color: red;'>❌ Password verification failed.</p>";
                            
                            // Force update password to a known value for admin
                            if ($row['email'] == 'admin@example.com') {
                                echo "<h3>Forcing password reset for admin account</h3>";
                                
                                $raw_password = "admin123";
                                $new_hash = password_hash($raw_password, PASSWORD_DEFAULT);
                                
                                $update_query = "UPDATE users SET password = ? WHERE email = 'admin@example.com'";
                                
                                if ($update_stmt = mysqli_prepare($con, $update_query)) {
                                    mysqli_stmt_bind_param($update_stmt, "s", $new_hash);
                                    
                                    if (mysqli_stmt_execute($update_stmt)) {
                                        echo "<p style='color: green;'>✅ Admin password forcibly reset to 'admin123'</p>";
                                        echo "<p>Please try logging in again with:</p>";
                                        echo "<ul>";
                                        echo "<li>Email: admin@example.com</li>";
                                        echo "<li>Password: admin123</li>";
                                        echo "</ul>";
                                    } else {
                                        echo "<p style='color: red;'>❌ Failed to reset password: " . mysqli_error($con) . "</p>";
                                    }
                                    
                                    mysqli_stmt_close($update_stmt);
                                }
                            } else {
                                $login_message = "Invalid password.";
                            }
                        }
                    } else {
                        $login_message = "No account found with that email.";
                        
                        // If trying to login as admin but account doesn't exist, create it
                        if ($email == 'admin@example.com') {
                            echo "<h3>Creating admin account</h3>";
                            
                            $name = "Administrator";
                            $raw_password = "admin123";
                            $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);
                            $is_admin = 1;
                            
                            $insert_query = "INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, ?)";
                            
                            if ($insert_stmt = mysqli_prepare($con, $insert_query)) {
                                mysqli_stmt_bind_param($insert_stmt, "sssi", $name, $email, $hashed_password, $is_admin);
                                
                                if (mysqli_stmt_execute($insert_stmt)) {
                                    echo "<p style='color: green;'>✅ Admin account created successfully!</p>";
                                    echo "<p>Please try logging in again with:</p>";
                                    echo "<ul>";
                                    echo "<li>Email: admin@example.com</li>";
                                    echo "<li>Password: admin123</li>";
                                    echo "</ul>";
                                } else {
                                    echo "<p style='color: red;'>❌ Failed to create admin account: " . mysqli_error($con) . "</p>";
                                }
                                
                                mysqli_stmt_close($insert_stmt);
                            }
                        }
                    }
                } else {
                    $login_message = "Oops! Something went wrong. Error executing query: " . mysqli_error($con);
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            } else {
                $login_message = "Oops! Something went wrong. Error preparing query: " . mysqli_error($con);
            }
        }
        
        // Close connection
        mysqli_close($con);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 500px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
        }
        button {
            background-color: #3563E9;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .error {
            background-color: #ffe6e6;
            border-left: 5px solid #ff3333;
            color: #721c24;
        }
        .success {
            background-color: #e6ffe6;
            border-left: 5px solid #33cc33;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Direct Login</h1>
        
        <?php if (!empty($login_message)): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo $login_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <div class="password-container">
                        <input type="password" name="password" id="password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">👁️</button>
                    </div>
                </div>
                
                <button type="submit">Login</button>
            </form>
            
            <div style="margin-top: 20px; text-align: center;">
                <p>Try using these admin credentials:</p>
                <p><strong>Email:</strong> admin@example.com</p>
                <p><strong>Password:</strong> admin123</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function togglePassword() {
            var passwordInput = document.getElementById("password");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        }
    </script>
</body>
</html> 