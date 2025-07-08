<?php
// Include necessary files
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Require admin privileges
require_admin();

// Check if car ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    set_message('Invalid car ID', 'error');
    header('Location: dashboard.php');
    exit;
}

$car_id = intval($_GET['id']);

// Delete the car from the database
$delete_query = "DELETE FROM cars WHERE car_id = ?";
$stmt = mysqli_prepare($con, $delete_query);
mysqli_stmt_bind_param($stmt, "i", $car_id);

if (mysqli_stmt_execute($stmt)) {
    set_message('Car deleted successfully', 'success');
} else {
    set_message('Error deleting car: ' . mysqli_error($con), 'error');
}

// Redirect back to dashboard
header('Location: dashboard.php');
exit;
?> 