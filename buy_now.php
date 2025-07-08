<?php
// Include necessary files
require_once('includes/db_connection.php');
require_once('includes/auth_check.php');
require_once('includes/header.php');
require_once('includes/navbar.php');

// Ensure user is logged in
require_login();

// Get car ID from query string
$car_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate car ID
if ($car_id <= 0) {
    set_message("Invalid car ID.", "error");
    header("Location: front_page/front_page.php");
    exit();
}

// Get car details
$car_query = "SELECT c.*, b.brand_name
             FROM cars c
             JOIN brands b ON c.brand_id = b.brand_id
             WHERE c.car_id = ?";
$car_stmt = mysqli_prepare($con, $car_query);
mysqli_stmt_bind_param($car_stmt, "i", $car_id);
mysqli_stmt_execute($car_stmt);
$result = mysqli_stmt_get_result($car_stmt);

if (mysqli_num_rows($result) == 0) {
    set_message("Car not found.", "error");
    header("Location: front_page/front_page.php");
    exit();
}

$car = mysqli_fetch_assoc($result);

// Generate the header
generate_header('Buy Now - ' . $car['car_name']);
generate_navbar('cars');
?>

<div class="container my-5">
    <div class="row">
        <!-- Car details side -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h2 class="card-title mb-4">Order Summary</h2>
                    <div class="d-flex mb-4">
                        <?php if (!empty($car['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($car['image_url']); ?>" alt="<?php echo htmlspecialchars($car['car_name']); ?>" class="me-3" style="width: 150px; height: 100px; object-fit: cover; border-radius: 8px;">
                        <?php endif; ?>
                        <div>
                            <h4><?php echo htmlspecialchars($car['car_name']); ?></h4>
                            <h5 class="text-muted"><?php echo htmlspecialchars($car['brand_name']); ?></h5>
                            <h3 class="text-primary">₹<?php echo number_format($car['price']); ?></h3>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Car Specifications</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Fuel Type:</span>
                                <span><?php echo htmlspecialchars($car['fuel_type']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Engine:</span>
                                <span><?php echo htmlspecialchars($car['engine']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Transmission:</span>
                                <span><?php echo htmlspecialchars($car['transmission']); ?></span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between">
                                <span>Seating Capacity:</span>
                                <span><?php echo htmlspecialchars($car['seating_capacity']); ?> persons</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <small>Please complete the form on the right to place your order for this car. Our team will contact you to arrange delivery and finalize payment details.</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Order form side -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Complete Your Order</h2>
                    
                    <!-- Flash messages -->
                    <?php show_message(); ?>
                    
                    <form method="post" action="place_order.php">
                        <input type="hidden" name="car_id" value="<?php echo $car_id; ?>">
                        
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select payment method</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Credit Card">Credit Card</option>
                                <option value="EMI">EMI Financing</option>
                                <option value="Cash">Cash</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Delivery Address</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes (Optional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="terms" required>
                            <label class="form-check-label" for="terms">I agree to the terms and conditions</label>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Place Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once('includes/footer.php'); ?> 