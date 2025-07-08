<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/navbar.php');
require_once('../includes/footer.php');
require_once('../includes/db_connection.php');

// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables
$name = $email = $phone = $message = '';
$name_err = $email_err = $phone_err = $message_err = '';
$car_id = 0;
$car_name = 'Unknown Car';

// Process car ID from URL
if (isset($_GET['car_id']) && !empty($_GET['car_id'])) {
    $car_id = intval($_GET['car_id']);
    
    // Get car details for the form
    $query = "SELECT car_name FROM cars WHERE car_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $car_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $car = mysqli_fetch_assoc($result);
        $car_name = $car['car_name'];
    } else {
        // Redirect to cars page if car not found
        header('Location: cars_page.php');
        exit;
    }
}

// If user is logged in, pre-fill form with user data
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    $name = $_SESSION['user_name'];
    $email = $_SESSION['email'];
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
        // Check if email format is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        }
    }
    
    // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter your phone number.";
    } else {
        $phone = trim($_POST["phone"]);
        // Check if phone format is valid (simple validation)
        if (!preg_match("/^[0-9]{10}$/", $phone)) {
            $phone_err = "Please enter a valid 10-digit phone number.";
        }
    }
    
    // Validate message
    if (empty(trim($_POST["message"]))) {
        $message_err = "Please enter your message.";
    } else {
        $message = trim($_POST["message"]);
    }
    
    // If no errors, process the form
    if (empty($name_err) && empty($email_err) && empty($phone_err) && empty($message_err)) {
        // In a real application, you would save this to a database
        // For now, we'll just simulate success
        
        // Set success message
        $_SESSION['message'] = "Your message has been sent! A dealer will contact you soon.";
        $_SESSION['message_type'] = "success";
        
        // Redirect back to car details
        header("Location: car_details.php?id=" . $car_id);
        exit;
    }
}

// Generate the header with custom CSS
generate_header('Contact Dealer');
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <a href="car_details.php?id=<?php echo $car_id; ?>" class="btn btn-outline-secondary">&larr; Back to Car Details</a>
        </div>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">Contact Dealer</h2>
                    <p class="text-center text-muted mb-4">Interested in <?php echo htmlspecialchars($car_name); ?>? Fill out this form to get in touch with our dealer.</p>
                    
                    <?php
                    // Display flash message
                    show_message();
                    ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?car_id=" . $car_id; ?>" method="post">
                        <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Your Name</label>
                            <input type="text" name="name" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" id="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Enter your name">
                            <div class="invalid-feedback"><?php echo $name_err; ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Enter your email">
                            <div class="invalid-feedback"><?php echo $email_err; ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" name="phone" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" id="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Enter your phone number">
                            <div class="invalid-feedback"><?php echo $phone_err; ?></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea name="message" class="form-control <?php echo (!empty($message_err)) ? 'is-invalid' : ''; ?>" id="message" rows="5" placeholder="What would you like to know about this car?"><?php echo htmlspecialchars($message); ?></textarea>
                            <div class="invalid-feedback"><?php echo $message_err; ?></div>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php generate_footer(); ?> 