<?php
// Start session to preserve any existing session data
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if there's a return URL set
if (isset($_GET['return_to']) && !empty($_GET['return_to'])) {
    $_SESSION['return_to'] = $_GET['return_to'];
}

// Redirect to the alternative login page
header("Location: alt_login.php");
exit;
?> 