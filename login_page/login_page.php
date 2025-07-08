<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check session status before including other files
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Debug information - will be shown only in development
$debug_mode = true;

// Function to log debug information
function log_debug($message, $data = null) {
    $log_file = __DIR__ . '/login_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[$timestamp] $message";
    
    if ($data !== null) {
        $log_message .= " | Data: " . print_r($data, true);
    }
    
    file_put_contents($log_file, $log_message . PHP_EOL, FILE_APPEND);
}

// Log attempt
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    log_debug("Login attempt started", ["URI" => $_SERVER["REQUEST_URI"], "Session ID" => session_id()]);
    log_debug("POST data", $_POST);
}

// Include necessary files
require_once('../includes/header.php');
require_once('../includes/navbar.php');
require_once('../includes/footer.php');
require_once('../includes/auth_check.php');

// Redirect already logged in users
redirect_logged_in_user();

// Check for return_to parameter in URL
if (isset($_GET['return_to']) && !empty($_GET['return_to'])) {
    $_SESSION['return_to'] = $_GET['return_to'];
}

// Initialize variables
$email = $password = '';
$email_err = $password_err = $login_err = '';

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter email.";
    } else {
        $email = trim($_POST["email"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($email_err) && empty($password_err)) {
        // Include db connection
        require_once('../includes/db_connection.php');
        
        log_debug("DB connection established, preparing to query database");
        
        // Prepare a select statement
        $sql = "SELECT id, name, email, password, is_admin FROM users WHERE email = ?";
        
        if ($stmt = mysqli_prepare($con, $sql)) {
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            
            // Set parameters
            $param_email = $email;
            
            log_debug("Executing query for email", $email);
            
            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store result
                $result = mysqli_stmt_get_result($stmt);
                
                // Check if email exists, if yes then verify password
                if (mysqli_num_rows($result) == 1) {
                    // Fetch the row
                    $row = mysqli_fetch_assoc($result);
                    $id = $row['id'];
                    $name = $row['name'];
                    $email = $row['email'];
                    $hashed_password = $row['password'];
                    $is_admin = $row['is_admin'];
                    
                    log_debug("User found", ["id" => $id, "name" => $name, "is_admin" => $is_admin]);
                    
                    // Test password verification
                    $password_valid = password_verify($password, $hashed_password);
                    log_debug("Password verification result", ["valid" => $password_valid ? "true" : "false"]);
                    
                    if ($password_valid) {
                        log_debug("Login successful, setting session data");
                        
                        // Clear existing session data
                        $_SESSION = array();
                        
                        // Store data in session variables
                        $_SESSION["logged_in"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["user_name"] = $name;
                        $_SESSION["email"] = $email;
                        $_SESSION["is_admin"] = $is_admin == 1;
                        
                        // Set success message
                        $_SESSION['message'] = "Welcome back, $name!";
                        $_SESSION['message_type'] = "success";
                        
                        // Redirect user to return URL or home page
                        $redirect_to = isset($_SESSION['return_to']) ? $_SESSION['return_to'] : '../front_page/front_page.php';
                        unset($_SESSION['return_to']);
                        
                        log_debug("Redirecting to", $redirect_to);
                        
                        header("Location: " . $redirect_to);
                        exit;
                    } else {
                        // Try direct comparison as fallback (for plain text passwords)
                        if ($password === $hashed_password) {
                            log_debug("Plain text password match, updating to hashed");
                            
                            // Update to hashed password for future logins
                            $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
                            $update_query = "UPDATE users SET password = ? WHERE id = ?";
                            $update_stmt = mysqli_prepare($con, $update_query);
                            mysqli_stmt_bind_param($update_stmt, "si", $new_hashed_password, $id);
                            mysqli_stmt_execute($update_stmt);
                            mysqli_stmt_close($update_stmt);
                            
                            // Set session data (same as above)
                            $_SESSION = array();
                            $_SESSION["logged_in"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["user_name"] = $name;
                            $_SESSION["email"] = $email;
                            $_SESSION["is_admin"] = $is_admin == 1;
                            
                            // Set success message
                            $_SESSION['message'] = "Welcome back, $name!";
                            $_SESSION['message_type'] = "success";
                            
                            // Redirect user to return URL or home page
                            $redirect_to = isset($_SESSION['return_to']) ? $_SESSION['return_to'] : '../front_page/front_page.php';
                            unset($_SESSION['return_to']);
                            
                            log_debug("Redirecting to", $redirect_to);
                            
                            header("Location: " . $redirect_to);
                            exit;
                        } else {
                            // Password is not valid
                            $login_err = "Invalid email or password.";
                            log_debug("Invalid password");
                        }
                    }
                } else {
                    // Email doesn't exist
                    $login_err = "Invalid email or password.";
                    log_debug("No account found with email", $email);
                }
            } else {
                $login_err = "Oops! Something went wrong. Please try again later.";
                log_debug("Query execution failed", mysqli_error($con));
            }
            
            // Close statement
            mysqli_stmt_close($stmt);
        } else {
            $login_err = "Oops! Something went wrong. Please try again later.";
            log_debug("Statement preparation failed", mysqli_error($con));
        }
        
        // Close connection
        mysqli_close($con);
    }
}

// Custom CSS for the login page
$custom_css = '
<style>
    body {
        background-image: linear-gradient(to bottom, #e4daf1, #c5a7e2, #9f58e0, #8a2be2);
        background-attachment: fixed;
    }
    
    .login-container {
        max-width: 500px;
        margin: 120px auto 80px;
        padding: 40px;
        background-color: var(--white);
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .login-logo {
        text-align: center;
        margin-bottom: 30px;
    }
    
    .login-logo img {
        width: 100px;
        margin-bottom: 20px;
        animation-name: pulse;
        animation-duration: 2s;
        animation-iteration-count: infinite;
        animation-timing-function: ease-in-out;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .login-logo h4 {
        font-size: 28px;
        color: #8a2be2;
        margin-bottom: 5px;
    }
    
    .login-logo p {
        font-size: 16px;
        color: #666;
    }
    
    .form-label {
        font-weight: 600;
        color: #333;
    }
    
    .input-group {
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    
    .input-group-text {
        background-color: #8a2be2;
        color: white;
        border: none;
        padding: 12px 15px;
    }
    
    .form-control {
        padding: 12px 15px;
        border: 1px solid #ddd;
    }
    
    .form-control:focus {
        border-color: #8a2be2;
        box-shadow: 0 0 0 0.25rem rgba(138, 43, 226, 0.25);
    }
    
    .password-container {
        position: relative;
    }
    
    .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #666;
        z-index: 10;
    }
    
    .password-toggle:hover {
        color: #8a2be2;
    }
    
    .btn-primary {
        background-color: #8a2be2;
        border-color: #8a2be2;
        padding: 12px;
        font-weight: 600;
        font-size: 18px;
        letter-spacing: 0.5px;
        transition: all 0.3s;
    }
    
    .btn-primary:hover, .btn-primary:focus {
        background-color: #7424c9;
        border-color: #7424c9;
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(138, 43, 226, 0.4);
    }
    
    .text-center a {
        color: #8a2be2;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .text-center a:hover {
        color: #7424c9;
        text-decoration: underline;
    }
    
    .alert-danger {
        background-color: #ffe6e6;
        border-color: #ff8080;
        color: #cc0000;
        border-radius: 8px;
        padding: 12px 15px;
    }
    
    /* Animation for form elements */
    .login-container .mb-3 {
        animation: fadeIn 0.5s ease-in-out forwards;
        opacity: 0;
    }
    
    .login-container .mb-3:nth-child(1) {
        animation-delay: 0.2s;
    }
    
    .login-container .mb-3:nth-child(2) {
        animation-delay: 0.4s;
    }
    
    .login-container .d-grid {
        animation: fadeIn 0.5s ease-in-out forwards;
        animation-delay: 0.6s;
        opacity: 0;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .quick-login {
        margin-top: 20px;
        border-top: 1px solid #eee;
        padding-top: 15px;
        text-align: center;
        animation: fadeIn 0.5s ease-in-out forwards;
        animation-delay: 0.8s;
        opacity: 0;
    }
</style>
';

// Generate the header with custom CSS
generate_header('Login', ['login_page_style.css']);
echo $custom_css;

// Generate the navbar with no active page
generate_navbar('');
?>

<div class="container">
    <div class="login-container">
        <div class="login-logo">
            <img src="https://cdn-icons-png.flaticon.com/128/18585/18585546.png" alt="CarWale Logo">
            <h4>Welcome Back</h4>
            <p class="text-muted">Please login to your account</p>
        </div>
        
        <?php
        // Display error message if there is one
        if (!empty($login_err)) {
            echo '<div class="alert alert-danger mb-4">' . $login_err . '</div>';
        }
        
        // Display flash message
        show_message();
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $email; ?>" id="email" placeholder="Enter your email">
                    <div class="invalid-feedback"><?php echo $email_err; ?></div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group password-container">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="password" placeholder="Enter your password">
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
                    <div class="invalid-feedback"><?php echo $password_err; ?></div>
                </div>
            </div>
            
            <div class="d-grid gap-2 mb-4 mt-4">
                <button type="submit" class="btn btn-primary">Login</button>
            </div>
            
            <div class="text-center">
                <p>Don't have an account? <a href="../register_page/register_page.php">Register</a></p>
            </div>
        </form>
        
        <div class="quick-login">
            <h5>Quick Access:</h5>
            <p><strong>Admin:</strong> admin@example.com / admin123</p>
            <p><strong>Test User:</strong> test@example.com / test123</p>
        </div>
    </div>
</div>

<script>
// Password visibility toggle
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const icon = this.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
});
</script>

<?php generate_footer(); ?>