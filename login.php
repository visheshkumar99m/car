<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// If there's a return_to parameter in the URL, save it in the session
if (isset($_GET['return_to']) && !empty($_GET['return_to'])) {
    $_SESSION['return_to'] = $_GET['return_to'];
}

// Redirect to the proper login page
$redirect_url = 'login_page/login_page.php';

// If there's a return_to parameter and it's not already in the session, add it to the URL
if (isset($_GET['return_to']) && !empty($_GET['return_to']) && !isset($_SESSION['return_to'])) {
    $redirect_url .= '?return_to=' . urlencode($_GET['return_to']);
}

header("Location: " . $redirect_url);
exit;
?> 