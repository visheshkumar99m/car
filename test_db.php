<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Attempting to connect to MySQL...<br>";

// Try connection without password first (XAMPP default)
$mysqli = @mysqli_connect("localhost", "root", "", "cars_data");

if (!$mysqli) {
    echo "Error without password: " . mysqli_connect_error() . "<br>";
    
    // Try with the password 'password'
    $mysqli = @mysqli_connect("localhost", "root", "password", "cars_data");
    
    if (!$mysqli) {
        echo "Error with password 'password': " . mysqli_connect_error() . "<br>";
        
        // Try with empty password and no database specified
        $mysqli = @mysqli_connect("localhost", "root", "");
        
        if (!$mysqli) {
            echo "Error connecting to MySQL without specifying database: " . mysqli_connect_error() . "<br>";
            echo "Please check that MySQL is running and credentials are correct.<br>";
        } else {
            echo "Successfully connected to MySQL without specifying a database.<br>";
            echo "Let's check if the 'cars_data' database exists:<br>";
            
            // Check if cars_data database exists
            $result = mysqli_query($mysqli, "SHOW DATABASES LIKE 'cars_data'");
            if (mysqli_num_rows($result) > 0) {
                echo "Database 'cars_data' exists but could not connect to it.<br>";
            } else {
                echo "Database 'cars_data' does not exist. You may need to create it.<br>";
            }
            mysqli_close($mysqli);
        }
    } else {
        echo "Successfully connected with password 'password'!<br>";
        mysqli_close($mysqli);
    }
} else {
    echo "Successfully connected without a password!<br>";
    mysqli_close($mysqli);
}
?> 