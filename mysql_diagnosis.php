<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Begin HTML output
echo '<!DOCTYPE html>
<html>
<head>
    <title>MySQL Connection Diagnosis</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .info { color: blue; }
        .section { margin: 20px 0; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 3px; }
        button { padding: 8px 15px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
    </style>
</head>
<body>
    <h1>MySQL Connection Diagnosis</h1>';

// Check XAMPP installation
echo '<div class="section">
    <h2>1. XAMPP Status Check</h2>';

// Check if XAMPP is installed
if (file_exists('C:\\xampp\\mysql\\bin\\mysql.exe')) {
    echo '<p class="success">✅ XAMPP MySQL found at expected location</p>';
} else {
    echo '<p class="error">❌ XAMPP MySQL not found at C:\\xampp\\mysql\\bin\\mysql.exe</p>';
}

// Check MySQL version
$mysql_version = @shell_exec('mysql --version');
if ($mysql_version) {
    echo '<p class="success">✅ MySQL installed: ' . htmlspecialchars($mysql_version) . '</p>';
} else {
    echo '<p class="error">❌ Could not determine MySQL version</p>';
}

echo '</div>';

// Check MySQL Service
echo '<div class="section">
    <h2>2. MySQL Service Status</h2>';

// Check if MySQL is running
$process_check = @shell_exec('tasklist /FI "IMAGENAME eq mysqld.exe"');
if (stripos($process_check, 'mysqld.exe') !== false) {
    echo '<p class="success">✅ MySQL service is running</p>';
} else {
    echo '<p class="error">❌ MySQL service does not appear to be running</p>';
    echo '<p>Try starting MySQL in XAMPP Control Panel</p>';
}

echo '</div>';

// MySQL Connection Tests
echo '<div class="section">
    <h2>3. MySQL Connection Tests</h2>';

// Test different password combinations
$passwords = [
    '' => 'No password (XAMPP default)',
    'root' => 'Root as password',
    'password' => 'Common password',
    'admin' => 'Admin password',
    'xampp' => 'XAMPP as password',
    '1234' => 'Simple password',
    'mysql' => 'Product name'
];

$success = false;
$working_password = '';

foreach ($passwords as $pwd => $description) {
    echo "<hr>";
    echo "<h3>Testing: $description</h3>";
    
    // Test connection to MySQL server
    $conn = @mysqli_connect('localhost', 'root', $pwd);
    if ($conn) {
        echo '<p class="success">✅ Connected to MySQL server with this password!</p>';
        $success = true;
        $working_password = $pwd;
        
        // Try to connect to database
        $db_conn = @mysqli_connect('localhost', 'root', $pwd, 'cars_data');
        if ($db_conn) {
            echo '<p class="success">✅ Successfully connected to "cars_data" database!</p>';
            mysqli_close($db_conn);
        } else {
            echo '<p class="error">❌ Could not connect to "cars_data" database: ' . mysqli_connect_error() . '</p>';
            
            // Check if database exists
            $result = mysqli_query($conn, "SHOW DATABASES LIKE 'cars_data'");
            if (mysqli_num_rows($result) == 0) {
                echo '<p class="info">ℹ️ Database "cars_data" does not exist. Attempting to create it...</p>';
                
                // Try to create database
                if (mysqli_query($conn, "CREATE DATABASE cars_data")) {
                    echo '<p class="success">✅ Created "cars_data" database successfully!</p>';
                    
                    // Connect to the new database
                    $db_conn = @mysqli_connect('localhost', 'root', $pwd, 'cars_data');
                    if ($db_conn) {
                        echo '<p class="success">✅ Successfully connected to newly created database!</p>';
                        mysqli_close($db_conn);
                    } else {
                        echo '<p class="error">❌ Failed to connect to newly created database: ' . mysqli_connect_error() . '</p>';
                    }
                } else {
                    echo '<p class="error">❌ Failed to create database: ' . mysqli_error($conn) . '</p>';
                }
            }
        }
        
        // Update the connection file
        $content = '<?php
// Central database connection file
$host = "localhost";
$user = "root";
$password = "' . $pwd . '";
$db = "cars_data";

// Try to connect to database
$con = mysqli_connect($host, $user, $password, $db);

// Check connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>';
        
        echo '<p class="info">ℹ️ Updating db_connection.php with working credentials...</p>';
        if (file_put_contents("includes/db_connection.php", $content)) {
            echo '<p class="success">✅ Successfully updated db_connection.php!</p>';
        } else {
            echo '<p class="error">❌ Failed to update db_connection.php. Please update it manually with:</p>';
            echo '<pre>' . htmlspecialchars($content) . '</pre>';
        }
        
        mysqli_close($conn);
        break;
    } else {
        echo '<p class="error">❌ Connection failed: ' . mysqli_connect_error() . '</p>';
    }
}

if (!$success) {
    echo '<hr>';
    echo '<p class="error">❌ Could not connect with any common password.</p>';
    echo '<p>You may need to:</p>';
    echo '<ul>';
    echo '<li>Make sure MySQL service is running</li>';
    echo '<li>Check if you have a custom MySQL password</li>';
    echo '<li>Try accessing phpMyAdmin to verify your credentials</li>';
    echo '<li>Reset your MySQL root password if needed</li>';
    echo '</ul>';
}

echo '</div>';

// phpMyAdmin Access
echo '<div class="section">
    <h2>4. phpMyAdmin Access</h2>
    <p>Try accessing <a href="http://localhost/phpmyadmin/" target="_blank">phpMyAdmin</a> with:</p>
    <ul>
        <li>Username: root</li>
        <li>Password: ' . ($success ? htmlspecialchars($working_password) : 'Try common passwords or your custom password') . '</li>
    </ul>
</div>';

// MySQL Command Line
echo '<div class="section">
    <h2>5. MySQL Command Line Test</h2>
    <p>You can also test MySQL access via command line:</p>
    <ol>
        <li>Open Command Prompt (cmd.exe)</li>
        <li>Navigate to MySQL bin directory: <code>cd C:\\xampp\\mysql\\bin</code></li>
        <li>Try connecting: <code>mysql -u root</code> or <code>mysql -u root -p</code> (if password required)</li>
    </ol>
</div>';

// Final recommendations
echo '<div class="section">
    <h2>6. Recommended Solution</h2>';

if ($success) {
    echo '<p class="success">✅ A working configuration has been found and applied!</p>';
    echo '<p>Your database connection should now work with password: "' . htmlspecialchars($working_password) . '"</p>';
    echo '<button onclick="window.location.href=\'/cars/home_page/cars_page/cars_page.php\'">Try accessing your site</button>';
} else {
    echo '<p class="error">❌ No working configuration found.</p>';
    echo '<p>The most likely issues are:</p>';
    echo '<ol>';
    echo '<li>MySQL is not running - Start it in XAMPP Control Panel</li>';
    echo '<li>MySQL has a custom password - You need to find out what it is</li>';
    echo '<li>MySQL is configured to reject connections from the current user/host</li>';
    echo '</ol>';
    
    echo '<h3>Manual Connection File Update</h3>';
    echo '<p>If you know your MySQL password, update includes/db_connection.php with:</p>';
    echo '<pre>';
    echo htmlspecialchars('<?php
// Central database connection file
$host = "localhost";
$user = "root";
$password = "YOUR_ACTUAL_PASSWORD"; // Replace with your actual password
$db = "cars_data";

// Try to connect to database
$con = mysqli_connect($host, $user, $password, $db);

// Check connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>');
    echo '</pre>';
}

echo '</div>';

// End HTML
echo '</body></html>';
?> 