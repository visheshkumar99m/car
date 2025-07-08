<?php
// Script to set up the database tables

// Connect to MySQL without selecting a database
$conn = mysqli_connect("localhost", "root", "");

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS cars_data";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Select the database
mysqli_select_db($conn, "cars_data");

// Read the SQL file
$sql_file = file_get_contents("db_setup.sql");

// Split the SQL file into individual statements
$statements = explode(';', $sql_file);

// Execute each statement
foreach ($statements as $statement) {
    $statement = trim($statement);
    if (!empty($statement)) {
        if (mysqli_query($conn, $statement)) {
            echo "Statement executed successfully<br>";
        } else {
            echo "Error executing statement: " . mysqli_error($conn) . "<br>";
        }
    }
}

echo "Setup complete!";
mysqli_close($conn);
?> 