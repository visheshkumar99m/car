<?php
// Include necessary files
require_once('../includes/header.php');
require_once('../includes/navbar.php');
require_once('../includes/footer.php');
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Start the session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Require login for cart operations
if (isset($_GET['remove']) || isset($_POST['checkout'])) {
    require_login();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle cart item removal
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['car_id'] == $remove_id) {
            unset($_SESSION['cart'][$index]);
            $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index the array
            set_message('Item removed from cart', 'success');
            header('Location: cart_page.php');
            exit;
        }
    }
}

// Handle checkout process
if (isset($_POST['checkout']) && count($_SESSION['cart']) > 0) {
    // Here you would typically process the payment and create an order
    // For now, we'll just clear the cart and show a success message
    
    // Create an order in the database (simplified)
    $user_id = $_SESSION['id'];
    $total_amount = 0;
    
    foreach ($_SESSION['cart'] as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
    
    // Add tax
    $total_with_tax = $total_amount + ($total_amount * 0.18);
    
    // Insert the order into database
    $order_sql = "INSERT INTO orders (user_id, total_amount, status, created_at) VALUES (?, ?, 'pending', NOW())";
    $stmt = mysqli_prepare($con, $order_sql);
    mysqli_stmt_bind_param($stmt, "id", $user_id, $total_with_tax);
    
    if (mysqli_stmt_execute($stmt)) {
        $order_id = mysqli_insert_id($con);
        
        // Insert order items
        foreach ($_SESSION['cart'] as $item) {
            $item_sql = "INSERT INTO order_items (order_id, car_id, price, quantity) VALUES (?, ?, ?, ?)";
            $item_stmt = mysqli_prepare($con, $item_sql);
            mysqli_stmt_bind_param($item_stmt, "iidd", $order_id, $item['car_id'], $item['price'], $item['quantity']);
            mysqli_stmt_execute($item_stmt);
            mysqli_stmt_close($item_stmt);
        }
        
        // Clear the cart
        $_SESSION['cart'] = [];
        
        // Set success message
        set_message('Order placed successfully! Thank you for your purchase.', 'success');
        
        // Redirect to order confirmation page
        header('Location: order_confirmation.php?order_id=' . $order_id);
        exit;
    } else {
        set_message('There was an error processing your order. Please try again.', 'error');
    }
    
    mysqli_stmt_close($stmt);
}

// Calculate total price
$total_price = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['price'] * $item['quantity'];
}

// Generate the header
generate_header('Shopping Cart');
?>

<div class="main">
    <?php
    // Generate navbar with 'cart' as the active page
    generate_navbar('cart');
    
    // Display flash messages
    show_message();
    ?>
    
    <div class="container my-5">
        <h1 class="mb-4">Your Shopping Cart</h1>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="cart-container">
                    <?php if (count($_SESSION['cart']) > 0): ?>
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="cart-item">
                                <div class="row align-items-center">
                                    <div class="col-3 col-md-2">
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                                    </div>
                                    <div class="col-9 col-md-5">
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p class="text-muted"><?php echo htmlspecialchars($item['brand']); ?></p>
                                    </div>
                                    <div class="col-6 col-md-3 text-end">
                                        <div class="item-price">₹<?php echo number_format($item['price']); ?></div>
                                    </div>
                                    <div class="col-6 col-md-2 text-end">
                                        <a href="cart_page.php?remove=<?php echo $item['car_id']; ?>" class="btn btn-sm btn-outline-danger">Remove</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="bi bi-cart-x" style="font-size: 4rem; color: var(--secondary-color);"></i>
                            </div>
                            <h4>Your cart is empty</h4>
                            <p class="text-muted mb-4">Add some cars to your cart to see them here</p>
                            <a href="../cars_page/cars_page.php" class="btn btn-primary">Explore Cars</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="cart-summary">
                    <h4 class="mb-4">Order Summary</h4>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal (<?php echo count($_SESSION['cart']); ?> items)</span>
                        <span>₹<?php echo number_format($total_price); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping & Handling</span>
                        <span>₹0</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tax (18%)</span>
                        <span>₹<?php echo number_format($total_price * 0.18); ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <strong>Total</strong>
                        <strong class="text-primary">₹<?php echo number_format($total_price + ($total_price * 0.18)); ?></strong>
                    </div>
                    
                    <?php if (count($_SESSION['cart']) > 0): ?>
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                            <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
                                <button type="submit" name="checkout" class="btn btn-primary w-100">Proceed to Checkout</button>
                            <?php else: ?>
                                <a href="../login_page/login_page.php" class="btn btn-primary w-100">Login to Checkout</a>
                            <?php endif; ?>
                        </form>
                    <?php else: ?>
                        <button class="btn btn-primary w-100" disabled>Proceed to Checkout</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4>CarWale</h4>
                    <p>
                        At CarWale, we're dedicated to making your car buying experience 
                        as smooth as the road ahead. With a wide range of brands, expert guidance, 
                        secure transactions, and innovative features.
                    </p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h4>Contact Us</h4>
                    <p><i class="bi bi-envelope me-2"></i> vishesh1426@gmail.com</p>
                    <p><i class="bi bi-geo-alt me-2"></i> Teerthanker Mahaveer university moradabad</p>
                    <p><i class="bi bi-map me-2"></i> Uttar Pradesh, India</p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h4>Follow Us</h4>
                    <div class="footer-social">
                        <a href="#"><i class="bi bi-facebook me-2"></i></a>
                        <a href="#"><i class="bi bi-twitter me-2"></i></a>
                        <a href="#"><i class="bi bi-instagram me-2"></i></a>
                        <a href="www.linkedin.com/in/vishesh-kumar-42ba58266"><i class="bi bi-linkedin me-2"></i></a>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-center mt-4">
                    <p class="mb-0">&copy; 2023 CarWale. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
</div>

<?php generate_footer(); ?> 