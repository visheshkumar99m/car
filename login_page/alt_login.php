<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

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
$email = $password = "";
$login_err = "";
$success = false;

// Include database connection
require_once('../includes/db_connection.php');

// If form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";
    
    // Validate form data
    if (empty(trim($email))) {
        $login_err = "Please enter email.";
    } elseif (empty(trim($password))) {
        $login_err = "Please enter your password.";
    } else {
        // Check database connection
        if (!$con) {
            $login_err = "Database connection failed: " . mysqli_connect_error();
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
                        
                        // Test password verification
                        if (password_verify($password, $row['password'])) {
                            // Set session variables
                            $_SESSION["logged_in"] = true;
                            $_SESSION["id"] = $row['id'];
                            $_SESSION["user_name"] = $row['name'];
                            $_SESSION["email"] = $row['email'];
                            $_SESSION["is_admin"] = $row['is_admin'] == 1;
                            
                            // Set success message
                            $_SESSION['message'] = "Welcome back, {$row['name']}!";
                            $_SESSION['message_type'] = "success";
                            
                            // Redirect user to return URL or home page
                            $redirect_to = isset($_SESSION['return_to']) ? $_SESSION['return_to'] : '../front_page/front_page.php';
                            unset($_SESSION['return_to']);
                            
                            header("Location: " . $redirect_to);
                            exit;
                        } else {
                            $login_err = "Invalid email or password.";
                        }
                    } else {
                        $login_err = "No account found with that email.";
                    }
                } else {
                    $login_err = "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
            
            // Close connection
            mysqli_close($con);
        }
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
generate_header('Alternative Login', ['login_page_style.css']);
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
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" id="email" placeholder="Enter your email" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group password-container">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" id="password" placeholder="Enter your password" required>
                    <button type="button" class="password-toggle" id="togglePassword">
                        <i class="bi bi-eye"></i>
                    </button>
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