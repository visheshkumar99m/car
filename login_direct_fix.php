<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Include database connection
require_once('includes/db_connection.php');

// Initialize variables
$login_message = "";
$success = false;

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    
    // Check database connection
    if (!$con) {
        $login_message = "Database connection failed: " . mysqli_connect_error();
    } else {
        // Basic email validation
        if (empty($email)) {
            $login_message = "Please enter an email address";
        } else {
            // Prepare a select statement
            $sql = "SELECT id, name, email, password, is_admin FROM users WHERE email = ?";
            
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
                        
                        // First, try password_verify
                        if (password_verify($password, $row['password'])) {
                            // Password is correct
                            $_SESSION["logged_in"] = true;
                            $_SESSION["id"] = $row['id'];
                            $_SESSION["user_name"] = $row['name'];
                            $_SESSION["email"] = $row['email'];
                            $_SESSION["is_admin"] = $row['is_admin'] == 1;
                            
                            $login_message = "Login successful! Redirecting...";
                            $success = true;
                            
                            // Redirect after a short delay
                            header("refresh:1;url=front_page/front_page.php");
                        } 
                        // If that fails, try direct comparison (for unhashed passwords)
                        else if ($password === $row['password']) {
                            // Password matches but isn't hashed - set it up properly now
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $update_query = "UPDATE users SET password = ? WHERE id = ?";
                            $update_stmt = mysqli_prepare($con, $update_query);
                            mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $row['id']);
                            mysqli_stmt_execute($update_stmt);
                            mysqli_stmt_close($update_stmt);
                            
                            // Log the user in
                            $_SESSION["logged_in"] = true;
                            $_SESSION["id"] = $row['id'];
                            $_SESSION["user_name"] = $row['name'];
                            $_SESSION["email"] = $row['email'];
                            $_SESSION["is_admin"] = $row['is_admin'] == 1;
                            
                            $login_message = "Login successful! Your password has been secured. Redirecting...";
                            $success = true;
                            
                            // Redirect after a short delay
                            header("refresh:1;url=front_page/front_page.php");
                        }
                        else {
                            $login_message = "Invalid password. Password format: " . substr($row['password'], 0, 10) . "...";
                        }
                    } else {
                        $login_message = "No account found with that email.";
                    }
                } else {
                    $login_message = "Error executing query: " . mysqli_error($con);
                }
                
                mysqli_stmt_close($stmt);
            } else {
                $login_message = "Error preparing query: " . mysqli_error($con);
            }
        }
        
        // Try to create admin account if it doesn't exist and login failed
        if (!$success && $email == "admin@example.com") {
            $create_admin = "INSERT INTO users (name, email, password, is_admin) VALUES ('Administrator', 'admin@example.com', ?, 1)";
            $hashed_password = password_hash("admin123", PASSWORD_DEFAULT);
            
            $insert_stmt = mysqli_prepare($con, $create_admin);
            mysqli_stmt_bind_param($insert_stmt, "s", $hashed_password);
            
            // Try to insert - ignore if it fails due to duplicate
            @mysqli_stmt_execute($insert_stmt);
            mysqli_stmt_close($insert_stmt);
            
            $login_message .= "<br>Try admin@example.com with password: admin123";
        }
        
        // Try to create test account
        if (!$success && $email == "test@example.com") {
            $create_test = "INSERT INTO users (name, email, password) VALUES ('Test User', 'test@example.com', ?)";
            $hashed_password = password_hash("test123", PASSWORD_DEFAULT);
            
            $insert_stmt = mysqli_prepare($con, $create_test);
            mysqli_stmt_bind_param($insert_stmt, "s", $hashed_password);
            
            // Try to insert - ignore if it fails due to duplicate
            @mysqli_stmt_execute($insert_stmt);
            mysqli_stmt_close($insert_stmt);
            
            $login_message .= "<br>Try test@example.com with password: test123";
        }
        
        mysqli_close($con);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emergency Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: linear-gradient(to bottom, #e4daf1, #c5a7e2, #9f58e0, #8a2be2);
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            width: 100%;
            max-width: 450px;
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header img {
            width: 100px;
            margin-bottom: 20px;
            animation: pulse 2s infinite ease-in-out;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .login-header h2 {
            color: #8a2be2;
            margin-bottom: 5px;
        }
        .form-control {
            padding: 12px 15px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            border-color: #8a2be2;
            box-shadow: 0 0 0 0.25rem rgba(138, 43, 226, 0.25);
        }
        .btn-primary {
            background-color: #8a2be2;
            border-color: #8a2be2;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .btn-primary:hover {
            background-color: #7424c9;
            border-color: #7424c9;
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        .quick-login {
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <img src="https://cdn-icons-png.flaticon.com/128/18585/18585546.png" alt="CarWale Logo">
            <h2>Emergency Login</h2>
            <p class="text-muted">Direct login for your project submission</p>
        </div>
        
        <?php if (!empty($login_message)): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo $login_message; ?>
            </div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required>
            </div>
            
            <div class="d-grid gap-2 mb-3">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
        </form>
        
        <div class="quick-login">
            <h5>Quick Access Accounts:</h5>
            <p><strong>Admin:</strong> admin@example.com / admin123</p>
            <p><strong>Test User:</strong> test@example.com / test123</p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 