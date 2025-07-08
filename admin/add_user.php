<?php
// Include necessary files
require_once('../includes/db_connection.php');
require_once('../includes/auth_check.php');

// Require admin privileges
require_admin();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    // Check if email already exists
    $email_check = "SELECT id FROM users WHERE email = ?";
    $email_stmt = mysqli_prepare($con, $email_check);
    mysqli_stmt_bind_param($email_stmt, "s", $email);
    mysqli_stmt_execute($email_stmt);
    mysqli_stmt_store_result($email_stmt);
    
    if (mysqli_stmt_num_rows($email_stmt) > 0) {
        $errors[] = "Email already exists";
    }
    
    // If no errors, insert new user
    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_query = "INSERT INTO users (name, email, password, is_admin) VALUES (?, ?, ?, ?)";
        $insert_stmt = mysqli_prepare($con, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, "sssi", $name, $email, $password_hash, $is_admin);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            set_message("User added successfully!", "success");
        } else {
            set_message("Error adding user: " . mysqli_error($con), "error");
        }
    } else {
        set_message("Error: " . implode(", ", $errors), "error");
    }
    
    // Redirect back to users list
    header("Location: users.php");
    exit();
} else {
    // Not a POST request, redirect to users list
    header("Location: users.php");
    exit();
}
?> 