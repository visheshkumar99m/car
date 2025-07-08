<?php
// Include necessary files
require_once('includes/db_connection.php');
require_once('includes/auth_check.php');

// Ensure user is logged in
require_login();

// Process order submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $car_id = $_POST['car_id'] ?? 0;
    $payment_method = $_POST['payment_method'] ?? '';
    $shipping_address = $_POST['shipping_address'] ?? '';
    $notes = $_POST['notes'] ?? '';
    
    // Get user ID from session
    $user_id = $_SESSION['id'] ?? 0;
    
    // Validation
    $errors = [];
    
    if (empty($car_id) || !is_numeric($car_id)) {
        $errors[] = "Invalid car selection";
    }
    
    if (empty($payment_method)) {
        $errors[] = "Payment method is required";
    }
    
    if (empty($shipping_address)) {
        $errors[] = "Shipping address is required";
    }
    
    // Verify car exists and get price
    if (empty($errors)) {
        $car_query = "SELECT car_id, price FROM cars WHERE car_id = ?";
        $car_stmt = mysqli_prepare($con, $car_query);
        mysqli_stmt_bind_param($car_stmt, "i", $car_id);
        mysqli_stmt_execute($car_stmt);
        $result = mysqli_stmt_get_result($car_stmt);
        
        if (mysqli_num_rows($result) == 0) {
            $errors[] = "Selected car not found";
        } else {
            $car_data = mysqli_fetch_assoc($result);
            $amount = $car_data['price'];
        }
    }
    
    // If no errors, create the order
    if (empty($errors)) {
        $insert_query = "INSERT INTO orders (user_id, car_id, status, amount, payment_method, shipping_address, notes) 
                        VALUES (?, ?, 'pending', ?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($con, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "iidsss", $user_id, $car_id, $amount, $payment_method, $shipping_address, $notes);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $order_id = mysqli_insert_id($con);
            set_message("Order #$order_id placed successfully! We will contact you soon to confirm the details.", "success");
            header("Location: profile.php");
            exit();
        } else {
            $errors[] = "Error creating order: " . mysqli_error($con);
        }
    }
    
    // If there were errors, display them
    if (!empty($errors)) {
        set_message("Error: " . implode(", ", $errors), "error");
        header("Location: car_details.php?id=$car_id");
        exit();
    }
} else {
    // Not a POST request, redirect to homepage
    header("Location: front_page/front_page.php");
    exit();
}
?> 