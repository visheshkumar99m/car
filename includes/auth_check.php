<?php
// Authentication helper functions

/**
 * Check if a user is logged in. If not, redirect to login page with return URL
 *
 * @param string $redirect_url - The URL to redirect to after successful login
 * @return bool - True if user is logged in, false otherwise
 */
function require_login($redirect_url = '') {
    // Start session if not started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    // Check if user is logged in
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        // Set message for user
        $_SESSION['message'] = 'You must be logged in to access that page';
        $_SESSION['message_type'] = 'info';
        
        // Set the return URL
        if (!empty($redirect_url)) {
            $_SESSION['return_to'] = $redirect_url;
        } else {
            $_SESSION['return_to'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        
        // Redirect to login page
        header('Location: ../login_page/login_page.php');
        exit;
    }
    
    return true;
}

/**
 * Check if a user is an admin. If not, redirect with appropriate message
 *
 * @return bool - True if user is an admin, false otherwise
 */
function require_admin() {
    // First check if user is logged in
    require_login();
    
    // Then check if user is an admin
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        // Set message for user
        $_SESSION['message'] = 'You do not have permission to access that page';
        $_SESSION['message_type'] = 'error';
        
        // Redirect to home page
        header('Location: ../front_page/front_page.php');
        exit;
    }
    
    return true;
}

/**
 * Redirect logged-in users away from auth pages
 *
 * @param string $redirect_to - Where to redirect logged-in users
 */
function redirect_logged_in_user($redirect_to = '../front_page/front_page.php') {
    // Start session if not started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Check if user is already logged in
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
        // Check if there's a return URL set
        if (isset($_SESSION['return_to'])) {
            $redirect_to = $_SESSION['return_to'];
            unset($_SESSION['return_to']);
        }
        
        header('Location: ' . $redirect_to);
        exit;
    }
}

/**
 * Set a flash message to be displayed on the next page
 *
 * @param string $message - The message to display
 * @param string $type - The type of message (success, error, info)
 */
function set_message($message, $type = 'info') {
    // Start session if not started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

/**
 * Display a flash message if one exists, then clear it
 */
function show_message() {
    // Start session if not started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['message'])): ?>
        <div class="message-container">
            <div class="message <?php echo isset($_SESSION['message_type']) ? $_SESSION['message_type'] : 'info'; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
        </div>
        <?php 
        // Clear the message after displaying
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    endif;
}
?> 