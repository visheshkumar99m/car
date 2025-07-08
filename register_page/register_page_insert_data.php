<?php
session_start();
require_once('../includes/db_connection.php'); // Use the central database connection

// Check connection
if (!$con) {
    $_SESSION['message'] = "Database connection failed: " . mysqli_connect_error();
    $_SESSION['message_type'] = "error";
    header("Location: register_page.php");
    exit();
}

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['user_name'];
    $email = $_POST['user_email'];
    $password = $_POST['user_password'];
    $number = $_POST['user_number'];
    $address = $_POST['user_address'];
    
    // Sanitize inputs to prevent SQL injection
    $name = mysqli_real_escape_string($con, $name);
    $email = mysqli_real_escape_string($con, $email);
    $password = mysqli_real_escape_string($con, $password);
    $number = mysqli_real_escape_string($con, $number);
    $address = mysqli_real_escape_string($con, $address);

    // Check if email already exists in the users table
    $check_email = "SELECT email FROM users WHERE email = ?";
    $stmt = mysqli_prepare($con, $check_email);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        // Email already exists
        $_SESSION['message'] = "User with this email already exists. Please use a different email or login.";
        $_SESSION['message_type'] = "error";
        header("Location: register_page.php");
        exit();
    } else {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $insert_sql = "INSERT INTO users (name, email, password, phone, address, is_admin) 
                      VALUES (?, ?, ?, ?, ?, 0)";
        
        $insert_stmt = mysqli_prepare($con, $insert_sql);
        mysqli_stmt_bind_param($insert_stmt, "sssss", $name, $email, $hashed_password, $number, $address);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            // Registration successful
            $_SESSION['message'] = "Registration successful! You can now login.";
            $_SESSION['message_type'] = "success";
            header("Location: ../login_page/login_page.php");
            exit();
        } else {
            // Registration failed
            $_SESSION['message'] = "Registration failed: " . mysqli_error($con);
            $_SESSION['message_type'] = "error";
            header("Location: register_page.php");
            exit();
        }
    }
} else {
    // If someone tries to access this file directly without submitting the form
    $_SESSION['message'] = "Please fill out the registration form.";
    $_SESSION['message_type'] = "error";
    header("Location: register_page.php");
    exit();
}

mysqli_close($con);
?>