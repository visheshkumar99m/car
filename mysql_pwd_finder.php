<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>MySQL Password Finder</h2>";

// List of common passwords to try
$passwords = ["password", "root", "admin", "mysql", "xampp", ""];

echo "<p>Testing common MySQL passwords...</p>";

foreach ($passwords as $pwd) {
    echo "<hr>";
    echo "Testing password: " . ($pwd === "" ? "[empty]" : $pwd) . "<br>";
    
    try {
        $conn = new mysqli("localhost", "root", $pwd);
        
        if ($conn->connect_error) {
            echo "❌ Connection failed with this password.<br>";
        } else {
            echo "✅ Connection successful with password: " . ($pwd === "" ? "[empty]" : $pwd) . "<br>";
            
            // Now attempt to connect with database
            try {
                $db_conn = new mysqli("localhost", "root", $pwd, "cars_data");
                
                if ($db_conn->connect_error) {
                    echo "Database 'cars_data' doesn't exist or can't be accessed.<br>";
                    
                    // Try to create the database
                    if ($conn->query("CREATE DATABASE IF NOT EXISTS cars_data")) {
                        echo "✅ Created 'cars_data' database successfully.<br>";
                        
                        // Update connection file
                        $content = '<?php
// Central database connection file
$host = "localhost"; 
$user = "root";
$password = "' . $pwd . '"; // Password verified by mysql_pwd_finder.php
$db = "cars_data";

// Try to connect to database
$con = mysqli_connect($host, $user, $password, $db);

// Check connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>';
                        
                        if (file_put_contents('includes/db_connection.php', $content)) {
                            echo "✅ Successfully updated db_connection.php with working credentials!<br>";
                        } else {
                            echo "❌ Could not update db_connection.php file. Please update manually with password: " . ($pwd === "" ? "[empty]" : $pwd) . "<br>";
                        }
                    } else {
                        echo "❌ Failed to create database: " . $conn->error . "<br>";
                    }
                } else {
                    echo "✅ Successfully connected to 'cars_data' database!<br>";
                    
                    // Update connection file
                    $content = '<?php
// Central database connection file
$host = "localhost"; 
$user = "root";
$password = "' . $pwd . '"; // Password verified by mysql_pwd_finder.php
$db = "cars_data";

// Try to connect to database
$con = mysqli_connect($host, $user, $password, $db);

// Check connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>';
                    
                    if (file_put_contents('includes/db_connection.php', $content)) {
                        echo "✅ Successfully updated db_connection.php with working credentials!<br>";
                    } else {
                        echo "❌ Could not update db_connection.php file. Please update manually with password: " . ($pwd === "" ? "[empty]" : $pwd) . "<br>";
                    }
                    
                    $db_conn->close();
                }
            } catch (Exception $e) {
                echo "❌ Error: " . $e->getMessage() . "<br>";
            }
            
            $conn->close();
            echo "<h3>Found working password! You're all set.</h3>";
            exit();
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "<br>";
    }
}

echo "<hr>";
echo "<h3>No common passwords worked.</h3>";
echo "<p>It seems you might have a custom MySQL password. Please try the following:</p>";
echo "<ol>";
echo "<li>Check if you can access phpMyAdmin at <a href='http://localhost/phpmyadmin/' target='_blank'>http://localhost/phpmyadmin/</a></li>";
echo "<li>Try logging into phpMyAdmin with username 'root' and your password</li>";
echo "<li>Manually update includes/db_connection.php with your working password</li>";
echo "<li>If you forgot your password, you may need to reset the MySQL root password.</li>";
echo "</ol>";

echo "<h4>Manual update for db_connection.php:</h4>";
echo "<pre>";
echo htmlspecialchars('<?php
// Central database connection file
$host = "localhost"; 
$user = "root";
$password = "YOUR_PASSWORD_HERE"; // Replace with your actual password
$db = "cars_data";

// Try to connect to database
$con = mysqli_connect($host, $user, $password, $db);

// Check connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>');
echo "</pre>";
?> 