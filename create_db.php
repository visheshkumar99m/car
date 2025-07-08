<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Creating Database</h2>";

// Connect to MySQL without specifying database
$host = "localhost";
$user = "root";
$password = ""; // Try empty password first

// Create connection without database
$conn = @mysqli_connect($host, $user, $password);

// Check connection
if (!$conn) {
    echo "Error connecting to MySQL: " . mysqli_connect_error() . "<br>";
    echo "Trying alternative connections...<br>";
    
    // Try other common passwords
    $passwords = ["root", "password", "mysql", "xampp", "admin"];
    
    foreach ($passwords as $pwd) {
        echo "Testing with password: $pwd<br>";
        $conn = @mysqli_connect($host, $user, $pwd);
        if ($conn) {
            echo "Connected successfully with password: $pwd<br>";
            $password = $pwd;
            break;
        }
    }
    
    if (!$conn) {
        die("Could not connect with any common passwords. Please check your MySQL setup.");
    }
}

echo "Connected to MySQL successfully.<br>";

// Check if database exists
$db_name = "cars_data";
$result = mysqli_query($conn, "SHOW DATABASES LIKE '$db_name'");

if (mysqli_num_rows($result) == 0) {
    echo "Database '$db_name' does not exist. Creating it now...<br>";
    
    // Create database
    if (mysqli_query($conn, "CREATE DATABASE $db_name")) {
        echo "Database created successfully!<br>";
    } else {
        echo "Error creating database: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Database '$db_name' already exists.<br>";
}

// Update connection file with working password
$config_content = '<?php
// Central database connection file
$host = "localhost";
$user = "root";
$password = "' . $password . '"; // Password that worked
$db = "cars_data";

// Try to connect to database
$con = mysqli_connect($host, $user, $password, $db);

// Check connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>';

// Write the configuration to the file
if (file_put_contents("includes/db_connection.php", $config_content)) {
    echo "Connection file updated successfully with working password.<br>";
} else {
    echo "Error: Could not update connection file.<br>";
}

// Close connection
mysqli_close($conn);

echo "<p><a href='cars_page/cars_page.php'>Try accessing your site now</a></p>";
?> 