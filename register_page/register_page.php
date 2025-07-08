<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/navbar.php');
require_once('../includes/footer.php');
require_once('../includes/auth_check.php');

// Start the session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect logged-in users
redirect_logged_in_user();

// Custom CSS for the register page
$custom_css = '
<style>
    .main {
        width: 100%;
        height: auto;
        min-height: 100vh;
        background-image: linear-gradient(to bottom, #e4daf1, #c5a7e2, #9f58e0, #8a2be2);
    }

    .register_main {
        display: flex;
        flex-wrap: wrap;
        padding-top: 40px;
        margin-top: 10px;
    }

    .register_submain1,
    .register_submain2 {
        width: 50%;
        padding: 20px;
    }

    @media (max-width: 768px) {
        .register_submain1,
        .register_submain2 {
            width: 100%;
        }
    }

    .register_submain1 img {
        width: 100%;
        max-width: 500px;
        display: block;
        margin: 0 auto;
    }

    .register_submain2 {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        padding: 40px;
    }

    .register_submain2 h2 {
        color: #8a2be2;
        margin-bottom: 5px;
    }

    .register_submain2 p {
        color: #666;
        margin-bottom: 20px;
    }

    .register_submain2 input {
        width: 100%;
        padding: 12px 15px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }

    .register_submain2 input:focus {
        outline: none;
        border-color: #8a2be2;
        box-shadow: 0 0 0 2px rgba(138, 43, 226, 0.2);
    }

    .register_submit_btn {
        width: 100%;
        padding: 12px;
        background-color: #8a2be2;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        margin-bottom: 15px;
    }

    .register_submit_btn:hover {
        background-color: #7823c7;
    }

    .login_submit_btn {
        display: block;
        width: 100%;
        padding: 12px;
        background-color: white;
        color: #8a2be2;
        border: 2px solid #8a2be2;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
    }

    .login_submit_btn:hover {
        background-color: #f0e6ff;
    }

    .toggle_icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
    }

    .password-wrapper {
        position: relative;
    }

    .footer_box {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-around;
        padding: 50px 0;
        background-image: radial-gradient(circle, rgb(52, 45, 45), rgb(14, 12, 12), black);
        color: white;
    }

    .footer_subbox1 {
        width: 40%;
    }

    .footer_subbox2,
    .footer_subbox3 {
        width: 25%;
    }

    @media (max-width: 768px) {
        .footer_subbox1,
        .footer_subbox2,
        .footer_subbox3 {
            width: 100%;
            padding: 20px;
        }
    }

    .footer_box h2 {
        color: #8a2be2;
        margin-bottom: 20px;
    }

    .footer_subbox1 p,
    .footer_subbox2 p {
        color: white;
        margin-bottom: 10px;
    }

    .footer_subbox3 img {
        width: 30px;
        height: 30px;
        margin-right: 10px;
    }
</style>';

// Generate the header with the custom CSS
generate_header('Register');
echo $custom_css;
?>

<div class="main">
    <?php
    // Generate the navbar
    generate_navbar();
    
    // Display flash messages
    show_message();
    ?>
    
    <div class="register_main">
        <div class="register_submain1">
            <img src="https://carwale.onrender.com/static/media/register.19b33dcecfe39d50b2e8.png" alt="">
        </div>
        <div class="register_submain2">
            <h2><b>WELCOME</b></h2>
            <p><b>Your dream car is waiting!</b></p><br>
            
            <form action="register_page_insert_data.php" method="post">
                <input type="text" name="user_name" id="yourname" placeholder="Enter Your Name" required><br>
                <input type="email" name="user_email" id="youremail" placeholder="Enter Your Email" required><br>
                <div class="password-wrapper">
                    <input type="password" name="user_password" class="passwordField" id="yourpassword" placeholder="Enter password" required>
                    <span class="toggle_icon" onclick="togglePassword()">👁️</span>
                </div><br>
                <input type="number" name="user_number" id="yournumber" placeholder="Enter Your Number" required><br>
                <input type="address" name="user_address" id="youraddress" placeholder="Enter Your Address" required><br>
                <button class="register_submit_btn" type="submit">Register</button><br>
                <a href="../login_page/login_page.php" class="login_submit_btn">Login</a>
            </form>
        </div>
    </div>
</div>

<div class="footer_box">
    <div class="footer_subbox1">
        <h2>carwale</h2>
        <p>At CarWale, we're dedicated to making your car buying experience as smooth as the road ahead. With a wide
            range of brands, expert guidance, secure transactions, and innovative features, we're your trusted
            partner on your journey to finding the perfect ride. Drive your dreams with CarWale, where your
            satisfaction is our ultimate destination.</p>
    </div>
    <div class="footer_subbox2">
        <h2>Contact</h2>
        <p>vishesh1426@gmail.com</p>
        <p>Teerthanker Mahaveer university moradabad</p>
        <p>uttar pradesh, India</p>
    </div>
    <div class="footer_subbox3">
        <h2>Social Media</h2>
        <a href="www.linkedin.com/in/vishesh-kumar-42ba58266"><img src="https://images.rawpixel.com/image_png_800/czNmcy1wcml2YXRlL3Jhd3BpeGVsX2ltYWdlcy93ZWJzaXRlX2NvbnRlbnQvbHIvdjk4Mi1kMS0xMC5wbmc.png"
                alt=""></a>
    </div>
</div>

<script>
function togglePassword() {
  const passwordInput = document.getElementById("yourpassword");
  const icon = document.querySelector(".toggle_icon");

  if (passwordInput.type === "password") {
    passwordInput.type = "text";
    icon.textContent = "🙈"; // Hide icon
  } else {
    passwordInput.type = "password";
    icon.textContent = "👁️"; // Show icon
  }
}
</script>

<?php generate_footer(); ?>