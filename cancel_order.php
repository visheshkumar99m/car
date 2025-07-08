<?php
// Include necessary files
require_once('includes/db_connection.php');
require_once('includes/auth_check.php');

// Ensure user is logged in
require_login();

// Get order ID from query parameter
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['id'] ?? 0;

// Validate order ID
if ($order_id <= 0) {
    set_message("Invalid order ID.", "error");
    header("Location: profile.php");
    exit();
}

// Check if order exists and belongs to the current user
$check_query = "SELECT order_id, status FROM orders WHERE order_id = ? AND user_id = ?";
$check_stmt = mysqli_prepare($con, $check_query);
mysqli_stmt_bind_param($check_stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($check_stmt);
mysqli_stmt_store_result($check_stmt);

if (mysqli_stmt_num_rows($check_stmt) == 0) {
    set_message("Order not found or you don't have permission to cancel it.", "error");
    header("Location: profile.php");
    exit();
}

// Get order status
mysqli_stmt_bind_result($check_stmt, $order_id, $status);
mysqli_stmt_fetch($check_stmt);

// Only allow cancellation of pending orders
if ($status != 'pending') {
    set_message("Only pending orders can be cancelled.", "error");
    header("Location: profile.php");
    exit();
}

// Update order status to cancelled
$update_query = "UPDATE orders SET status = 'cancelled' WHERE order_id = ? AND user_id = ?";
$update_stmt = mysqli_prepare($con, $update_query);
mysqli_stmt_bind_param($update_stmt, "ii", $order_id, $user_id);

if (mysqli_stmt_execute($update_stmt)) {
    set_message("Order #$order_id has been cancelled successfully.", "success");
} else {
    set_message("Error cancelling order: " . mysqli_error($con), "error");
}

// Redirect back to profile page
header("Location: profile.php");
exit();
?> 