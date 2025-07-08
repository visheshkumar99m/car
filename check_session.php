<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP Session Configuration Check</h1>";

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    echo "<p style='color: green;'>Session started successfully.</p>";
} else if (session_status() == PHP_SESSION_ACTIVE) {
    echo "<p style='color: green;'>Session was already active.</p>";
} else {
    echo "<p style='color: red;'>Error: Could not start session.</p>";
}

// Check session ID
echo "<p>Session ID: " . session_id() . "</p>";

// Get and display session configuration
echo "<h2>Session Configuration</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";

$session_settings = [
    'session.save_path',
    'session.name',
    'session.cookie_lifetime',
    'session.cookie_path',
    'session.cookie_domain',
    'session.cookie_secure',
    'session.cookie_httponly',
    'session.cookie_samesite',
    'session.use_strict_mode',
    'session.use_cookies',
    'session.use_only_cookies',
    'session.gc_maxlifetime',
    'session.gc_probability',
    'session.gc_divisor'
];

foreach ($session_settings as $setting) {
    $value = ini_get($setting);
    echo "<tr><td>" . $setting . "</td><td>" . $value . "</td></tr>";
}

echo "</table>";

// Test setting and retrieving session variables
echo "<h2>Session Variable Test</h2>";

// Set a test variable
$_SESSION['test_var'] = "This is a test value set at " . date('Y-m-d H:i:s');

echo "<p>Test variable set: " . $_SESSION['test_var'] . "</p>";
echo "<p>All session variables:</p>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Session path check
echo "<h2>Session Save Path Check</h2>";
$save_path = ini_get('session.save_path');
echo "<p>Session save path: " . ($save_path ?: "Not set (using default)") . "</p>";

if (!empty($save_path)) {
    if (is_dir($save_path)) {
        echo "<p style='color: green;'>✓ Save path exists and is a directory.</p>";
        
        if (is_writable($save_path)) {
            echo "<p style='color: green;'>✓ Save path is writable.</p>";
        } else {
            echo "<p style='color: red;'>✗ Save path is not writable. This could prevent sessions from working!</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Save path does not exist or is not a directory. This could prevent sessions from working!</p>";
    }
}

// Check headers
echo "<h2>HTTP Headers Check</h2>";
echo "<p>Note: If you see headers already sent warnings in your PHP logs, there might be output before session_start()</p>";

$headers_sent = headers_sent($file, $line);
if ($headers_sent) {
    echo "<p style='color: red;'>✗ Headers have already been sent from $file on line $line. This could affect session cookies!</p>";
} else {
    echo "<p style='color: green;'>✓ Headers have not been sent yet. Good!</p>";
}

echo "<h2>Navigation</h2>";
echo "<p><a href='login_debug_test.php' style='display: inline-block; padding: 10px 15px; background-color: #3563E9; color: white; text-decoration: none; border-radius: 5px;'>Debug Login Tool</a></p>";
echo "<p><a href='login.php' style='display: inline-block; padding: 10px 15px; background-color: #3563E9; color: white; text-decoration: none; border-radius: 5px;'>Go to Login Page</a></p>";
?> 