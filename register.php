<?php
// Start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to the proper register page
header("Location: register_page/register_page.php");
exit;
?> 