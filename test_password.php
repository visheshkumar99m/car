<?php
// Clear any previous output
ob_clean();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

function testPassword($password) {
    try {
        $conn = new mysqli("localhost", "root", $password);
        
        if ($conn->connect_error) {
            return false;
        } else {
            $conn->close();
            return true;
        }
    } catch (Exception $e) {
        return false;
    }
}

echo "Testing MySQL connection...<br>";

// List of passwords to try in priority order
$passwords = [
    "",           // No password (XAMPP default)
    "root",       // Common root password
    "password",   // Common password
    "mysql",      // Product name
    "admin",      // Common admin password
    "xampp",      // Software name
    "1234",       // Simple password
    "12345",      // Simple password
    "123456",     // Simple password
];

// Try each password
foreach ($passwords as $pwd) {
    echo "Testing password: " . ($pwd === "" ? "[empty]" : $pwd) . "... ";
    
    if (testPassword($pwd)) {
        echo "SUCCESS!<br>";
        
        // Try to create and connect to the database
        try {
            // Create connection to MySQL
            $conn = new mysqli("localhost", "root", $pwd);
            
            // Check if database exists
            $result = $conn->query("SHOW DATABASES LIKE 'cars_data'");
            
            if ($result->num_rows == 0) {
                echo "Database 'cars_data' does not exist. Creating it...<br>";
                // Create the database
                if ($conn->query("CREATE DATABASE cars_data")) {
                    echo "Database created successfully!<br>";
                } else {
                    echo "Error creating database: " . $conn->error . "<br>";
                }
            } else {
                echo "Database 'cars_data' already exists.<br>";
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
            
            if (file_put_contents("includes/db_connection.php", $content)) {
                echo "Successfully updated the database connection file!<br>";
            } else {
                echo "Failed to update the connection file. Please update it manually with:<br>";
                echo "<pre>" . htmlspecialchars($content) . "</pre>";
            }
            
            $conn->close();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "<br>";
        }
        
        exit("✅ Connection configuration completed!");
    } else {
        echo "Failed<br>";
    }
}

echo "<br>Could not find a working password. Please try the following:<br>";
echo "1. Check if MySQL is running<br>";
echo "2. Try accessing phpMyAdmin at <a href='http://localhost/phpmyadmin/' target='_blank'>http://localhost/phpmyadmin/</a><br>";
echo "3. If you know your password, update the includes/db_connection.php file manually<br>";
?> 