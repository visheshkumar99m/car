<?php
// Central database connection file
$host = "localhost";
$user = "root";
$password = ""; // Password that worked
$db = "cars_data";

// Try to connect to database
$con = mysqli_connect($host, $user, $password, $db);

// Check connection
if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>