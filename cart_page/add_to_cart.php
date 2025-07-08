<?php
// Start the session
session_start();

// Include database connection
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Initialize response
$response = [
    'success' => false,
    'message' => 'An error occurred',
    'redirect' => false,
    'redirect_url' => ''
];

// Check if car_id is provided
if (isset($_POST['car_id']) && !empty($_POST['car_id'])) {
    $car_id = intval($_POST['car_id']);
    
    // Check if the user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        // User is not logged in, save the car_id in session and redirect to login
        $_SESSION['pending_cart_add'] = $car_id;
        
        $response['success'] = false;
        $response['message'] = 'Please login to add items to your cart';
        $response['redirect'] = true;
        $response['redirect_url'] = '../login_page/login_page.php';
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Validate the car_id against the database
    $query = "SELECT c.*, b.brand_name FROM cars c JOIN brands b ON c.brand_id = b.brand_id WHERE c.car_id = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "i", $car_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Car exists in the database
        $car = mysqli_fetch_assoc($result);
        
        // Initialize cart if it doesn't exist
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        // Check if the car is already in the cart
        $car_in_cart = false;
        foreach ($_SESSION['cart'] as $index => $item) {
            if ($item['car_id'] == $car_id) {
                $car_in_cart = true;
                // Optionally, increment quantity if needed
                $_SESSION['cart'][$index]['quantity']++;
                break;
            }
        }
        
        if (!$car_in_cart) {
            // Add the car to the cart
            $_SESSION['cart'][] = [
                'car_id' => $car['car_id'],
                'name' => $car['car_name'],
                'brand' => $car['brand_name'],
                'price' => $car['price'],
                'image' => $car['image'],
                'quantity' => 1
            ];
        }
        
        $response['success'] = true;
        $response['message'] = 'Car added to cart successfully';
        $response['cart_count'] = count($_SESSION['cart']);
    } else {
        $response['message'] = 'Car not found';
    }
} else {
    $response['message'] = 'Car ID is required';
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
?> 