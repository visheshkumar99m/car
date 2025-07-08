<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>XAMPP MySQL Connection Fixer</h2>";

// Common configurations to try
$configs = [
    // Config 1: Default XAMPP 
    ['host' => 'localhost', 'user' => 'root', 'password' => '', 'port' => null],
    
    // Config 2: XAMPP with IP address
    ['host' => '127.0.0.1', 'user' => 'root', 'password' => '', 'port' => null],
    
    // Config 3: XAMPP with common password
    ['host' => 'localhost', 'user' => 'root', 'password' => 'root', 'port' => null],
    
    // Config 4: XAMPP with password
    ['host' => 'localhost', 'user' => 'root', 'password' => 'password', 'port' => null],
    
    // Config 5: XAMPP with specific port
    ['host' => 'localhost', 'user' => 'root', 'password' => '', 'port' => 3306],
    
    // Config 6: XAMPP with alternative port
    ['host' => 'localhost', 'user' => 'root', 'password' => '', 'port' => 3307],
    
    // Config 7: XAMPP with IP and port
    ['host' => '127.0.0.1', 'user' => 'root', 'password' => '', 'port' => 3306],
    
    // Config 8: XAMPP with socket
    ['host' => 'localhost:/path/to/mysql/mysql.sock', 'user' => 'root', 'password' => '', 'port' => null],
];

$success = false;
$working_config = null;
$db_name = 'cars_data';

// Test each configuration
foreach ($configs as $index => $config) {
    echo "<hr>";
    echo "<strong>Trying Config " . ($index + 1) . ":</strong> ";
    echo "Host: " . $config['host'] . ", ";
    echo "User: " . $config['user'] . ", ";
    echo "Password: " . ($config['password'] ? "[set]" : "[empty]") . ", ";
    echo "Port: " . ($config['port'] ? $config['port'] : "[default]");
    echo "<br>";
    
    if ($config['port']) {
        $conn = @mysqli_connect($config['host'], $config['user'], $config['password'], null, $config['port']);
    } else {
        $conn = @mysqli_connect($config['host'], $config['user'], $config['password']);
    }
    
    if ($conn) {
        echo "✅ Connected to MySQL server!<br>";
        
        // Check for database
        $db_exists = mysqli_query($conn, "SHOW DATABASES LIKE '$db_name'");
        
        if (mysqli_num_rows($db_exists) == 0) {
            echo "Database '$db_name' does not exist. Attempting to create it...<br>";
            
            // Create database
            if (mysqli_query($conn, "CREATE DATABASE $db_name")) {
                echo "✅ Database created successfully!<br>";
            } else {
                echo "❌ Error creating database: " . mysqli_error($conn) . "<br>";
            }
        } else {
            echo "✅ Database '$db_name' exists.<br>";
        }
        
        // Try to connect to the database
        if ($config['port']) {
            $db_conn = @mysqli_connect($config['host'], $config['user'], $config['password'], $db_name, $config['port']);
        } else {
            $db_conn = @mysqli_connect($config['host'], $config['user'], $config['password'], $db_name);
        }
        
        if ($db_conn) {
            echo "✅ Successfully connected to database '$db_name'!<br>";
            $success = true;
            $working_config = $config;
            mysqli_close($db_conn);
        } else {
            echo "❌ Connected to MySQL server but failed to connect to database: " . mysqli_connect_error() . "<br>";
        }
        
        mysqli_close($conn);
        
        if ($success) {
            break;
        }
    } else {
        echo "❌ Connection failed: " . mysqli_connect_error() . "<br>";
    }
}

// Output results
echo "<hr>";
if ($success) {
    echo "<h3>Success! Found Working Configuration:</h3>";
    echo "<pre>";
    print_r($working_config);
    echo "</pre>";
    
    echo "<h3>Your db_connection.php file should look like this:</h3>";
    echo "<pre>";
    echo htmlspecialchars('<?php
// Central database connection file
$host = "' . $working_config['host'] . '";
$user = "' . $working_config['user'] . '";
$password = "' . $working_config['password'] . '";
$db = "' . $db_name . '";
' . ($working_config['port'] ? '$port = ' . $working_config['port'] . ';' : '') . '

// Try to connect to database
' . ($working_config['port'] ? 
'$con = mysqli_connect($host, $user, $password, $db, $port);' : 
'$con = mysqli_connect($host, $user, $password, $db);') . '

// Check connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>');
    echo "</pre>";
    
    // Update the db_connection.php file
    $file_path = 'includes/db_connection.php';
    $contents = '<?php
// Central database connection file
$host = "' . $working_config['host'] . '";
$user = "' . $working_config['user'] . '";
$password = "' . $working_config['password'] . '";
$db = "' . $db_name . '";
' . ($working_config['port'] ? '$port = ' . $working_config['port'] . ';' : '') . '

// Try to connect to database
' . ($working_config['port'] ? 
'$con = mysqli_connect($host, $user, $password, $db, $port);' : 
'$con = mysqli_connect($host, $user, $password, $db);') . '

// Check connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>';
    
    if (file_put_contents($file_path, $contents)) {
        echo "<p>✅ Automatically updated your db_connection.php file with the working configuration!</p>";
    } else {
        echo "<p>❌ Could not update db_connection.php automatically. Please copy the code above manually.</p>";
    }
} else {
    echo "<h3>No working configuration found.</h3>";
    echo "<p>Please check that MySQL is running and that you have the correct credentials.</p>";
    
    echo "<h4>Possible solutions:</h4>";
    echo "<ol>";
    echo "<li>Make sure MySQL service is running in XAMPP Control Panel</li>";
    echo "<li>Check if you can access phpMyAdmin at <a href='http://localhost/phpmyadmin/'>http://localhost/phpmyadmin/</a></li>";
    echo "<li>Try resetting your MySQL root password</li>";
    echo "<li>Check MySQL error logs in XAMPP/mysql/data</li>";
    echo "</ol>";
}
?> 