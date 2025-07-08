<?php
session_start();

// Set a logout message
$_SESSION['message'] = "You have been successfully logged out.";
$_SESSION['message_type'] = "info";

// Remove all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the home page
header("Location: ../front_page/front_page.php");
exit();
?> 